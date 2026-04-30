<?php

namespace App\Http\Controllers;

use App\Models\GoiTin;
use App\Models\GiaoDich;
use App\Models\MoiGioi;
use App\Services\SePayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UnmatchedPayment;
use App\Events\ThanhToanThanhCong;

class GiaoDichController extends Controller
{
    protected $sePay;

    public function __construct(SePayService $sePay)
    {
        $this->sePay = $sePay;
    }

    // ✅ Lấy danh sách giao dịch cho Admin
    public function getData(Request $request)
    {
        $query = GiaoDich::with(['moiGioi:id,ten,so_dien_thoai', 'goiTin:id,ten_goi']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ma_giao_dich', 'like', "%{$search}%")
                ->orWhereHas('moiGioi', function ($q) use ($search) {
                    $q->where('ten', 'like', "%{$search}%")
                      ->orWhere('so_dien_thoai', 'like', "%{$search}%");
                });
        }

        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')->paginate($request->input('per_page', 10));

        $stats = [
            'total'   => GiaoDich::count(),
            'success' => GiaoDich::where('trang_thai', 'success')->count(),
            'pending' => GiaoDich::where('trang_thai', 'pending')->count(),
            'failed'  => GiaoDich::whereIn('trang_thai', ['failed', 'fail'])->count(),
        ];

        return response()->json([
            'status' => true,
            'data' => $data,
            'stats' => $stats
        ]);
    }

    // ✅ GIỮ NGUYÊN: Các hàm helper
    private function resolveSePayReturnUrl(Request $request): string
    {
        $configuredReturnUrl = env('SEPAY_RETURN_URL');
        if ($configuredReturnUrl) return $configuredReturnUrl;
        return rtrim($request->getSchemeAndHttpHost(), '/') . '/payment/sepay-return';
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'SE' . now()->format('YmdHis') . random_int(1000, 9999);
        } while (GiaoDich::where('ma_giao_dich', $code)->exists());
        return $code;
    }

    public function createPayment(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        $goi = GoiTin::find($request->goi_tin_id);

        if (!$user) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        if (!$goi) return response()->json(['status' => false, 'message' => 'Package not found'], 404);

        // ✅ CHẶN MUA TRÙNG GÓI NGAY TẠI BACKEND
        if ($user->goi_tin_id == $request->goi_tin_id) {
            return response()->json([
                'status' => false,
                'message' => 'Bạn đang sử dụng gói này rồi. Vui lòng đợi hết hạn hoặc nâng cấp gói cao hơn.'
            ], 400);
        }

        try {
            $paymentData = DB::transaction(function () use ($goi, $user) {
                $orderCode = $this->generateOrderCode();

                $transaction = GiaoDich::create([
                    'moi_gioi_id' => $user->id,
                    'goi_tin_id' => $goi->id,
                    'so_tien' => $goi->gia,
                    'phuong_thuc' => 'sepay',
                    'trang_thai' => GiaoDich::STATUS_PENDING,
                    'ma_giao_dich' => $orderCode,
                ]);

                // ✅ MỚI: Dùng SePay SDK để tạo form HTML
                $paymentHtml = $this->sePay->createPaymentUrl(
                    $orderCode,
                    $goi->gia,
                    "Thanh toan don hang {$orderCode}"
                );

                return [
                    'transaction_id' => $transaction->id,
                    'order_code' => $orderCode,
                    'amount' => $goi->gia,
                    'payment_html' => $paymentHtml
                ];
            });

            return response()->json(['status' => true, 'data' => $paymentData]);
        } catch (\Throwable $exception) {
            Log::error('Create payment failed', ['message' => $exception->getMessage()]);
            return response()->json(['status' => false, 'message' => 'Không thể tạo thanh toán'], 500);
        }
    }

    // ✅ MỚI: Handle URL Return từ SePay
    public function handleSePayReturn(Request $request)
    {
        $status    = $request->query('status', 'error');
        $orderCode = $request->query('order_code');
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        // ✅ FALLBACK: Nếu status=success và webhook chưa xử lý → tự kích hoạt
        if ($status === 'success' && $orderCode) {
            $transaction = GiaoDich::where('ma_giao_dich', $orderCode)->first();

            if ($transaction && $transaction->trang_thai === GiaoDich::STATUS_PENDING) {
                DB::transaction(function () use ($transaction) {
                    $transaction->update([
                        'trang_thai' => GiaoDich::STATUS_SUCCESS,
                        'paid_at'    => now(),
                    ]);
                    $this->activatePackage($transaction);
                });
                Log::info("✅ Return URL fallback activated: {$orderCode}");
            }
        }

        return redirect()->away("{$frontendUrl}/moi-gioi/goi-tin?status={$status}&order_code={$orderCode}");
    }

    // ✅ GIỮ NGUYÊN: Webhook handler
    public function handleSePayWebhook(Request $request)
    {
        Log::info('========== SEPAY WEBHOOK START ==========');

        // ✅ Xác thực request webhook từ SePay (Security check)
        if (!$this->sePay->verifyWebhook($request)) {
            Log::error('❌ Invalid SePay webhook token');
            return response()->json(['success' => false, 'message' => 'Invalid webhook token'], 401);
        }

        $data = $request->json()->all();

        if (isset($data['notification_type']) && $data['notification_type'] === 'ORDER_PAID') {
            return $this->handleOrderPaidWebhook($data);
        }
        if (isset($data['gateway'])) {
            return $this->handleBankNotifyWebhook($data);
        }

        Log::error('❌ Unknown webhook format');
        return response()->json(['success' => false], 422);
    }

    private function handleOrderPaidWebhook($data)
    {
        $orderCode = $data['order']['order_invoice_number'] ?? null;
        $amount = (float) ($data['order']['order_amount'] ?? 0);
        $status = $data['transaction']['transaction_status'] ?? '';
        $sepayerRef = $data['transaction']['transaction_id'] ?? null;
        return $this->processTransaction($orderCode, $amount, $status, $sepayerRef, 'ORDER_PAID');
    }

    private function handleBankNotifyWebhook($data)
    {
        $orderCode = $data['code'] ?? null;
        $amount = (float) ($data['transferAmount'] ?? 0);
        $sepayerRef = $data['referenceCode'] ?? null;
        $status = 'APPROVED';
        return $this->processTransaction($orderCode, $amount, $status, $sepayerRef, 'BANK_NOTIFY');
    }

    private function processTransaction($orderCode, $amount, $status, $sepayerRef, $source)
    {
        Log::info("🔍 Processing: source={$source}, code=" . ($orderCode ?? 'null') . ", amount={$amount}");

        $transaction = null;
        if ($orderCode) {
            $transaction = GiaoDich::where('ma_giao_dich', $orderCode)->lockForUpdate()->first();
        }

        if (!$transaction) {
            Log::warning("⚠️ Not found by code! Trying fallback by amount: {$amount}");
            $transaction = GiaoDich::where('so_tien', $amount)
                ->where('trang_thai', GiaoDich::STATUS_PENDING)
                ->where('created_at', '>=', now()->subMinutes(15))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($transaction) {
                Log::info("✅ FALLBACK MATCHED: {$transaction->ma_giao_dich}");
            } else {
                Log::error("❌ Fallback failed. Saving to UnmatchedPayments.");
                UnmatchedPayment::create([
                    'sepayer_reference' => $sepayerRef,
                    'order_code_from_sepay' => $orderCode,
                    'so_tien' => $amount,
                    'payload' => json_encode(['source' => $source] + (request()->json()->all() ?? [])),
                    'status' => 'unmatched',
                ]);
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }
        }

        if ($transaction->trang_thai === GiaoDich::STATUS_SUCCESS) {
            Log::warning("⚠️ Already processed: {$transaction->ma_giao_dich}");
            return response()->json(['success' => true], 200);
        }

        if ($amount != $transaction->so_tien) {
            Log::error("❌ Amount mismatch", ['sepay' => $amount, 'db' => $transaction->so_tien]);
            $transaction->update(['trang_thai' => GiaoDich::STATUS_FAILED]);
            return response()->json(['success' => false], 400);
        }

        $validStatuses = ['APPROVED', 'SUCCESS', 'CAPTURED', 'COMPLETED'];
        if (!in_array(strtoupper($status), $validStatuses)) {
            Log::error("❌ Payment not approved: {$status}");
            $transaction->update(['trang_thai' => GiaoDich::STATUS_FAILED]);
            return response()->json(['success' => false], 400);
        }

        $transaction->update([
            'trang_thai' => GiaoDich::STATUS_SUCCESS,
            'paid_at' => now(),
            'ma_sepay_txn_ref' => $sepayerRef,
        ]);

        Log::info("✅ Transaction updated: {$transaction->ma_giao_dich}");
        $this->activatePackage($transaction);
        Log::info('========== WEBHOOK END ==========');
        return response()->json(['success' => true], 200);
    }

    private function activatePackage(GiaoDich $transaction)
    {
        $goiTin = GoiTin::find($transaction->goi_tin_id);
        $user = MoiGioi::find($transaction->moi_gioi_id);

        if (!$goiTin || !$user) {
            Log::error("❌ Related models not found");
            return;
        }

        $baseDate = $user->ngay_het_han_goi && $user->ngay_het_han_goi->isFuture()
            ? $user->ngay_het_han_goi->copy()
            : now();

        $user->update([
            'goi_tin_id' => $goiTin->id,
            'so_tin_con_lai' => $user->so_tin_con_lai + $goiTin->so_luong_tin,
            'ngay_het_han_goi' => $baseDate->addDays($goiTin->so_ngay),
        ]);

        Log::info("✅ Package activated for user {$user->id} | New credits: {$user->so_tin_con_lai}");
        event(new ThanhToanThanhCong($user->id));
    }

    // ✅ GIỮ NGUYÊN: Các hàm helper khác (getTransactionStatus, export, etc.)
    public function kichHoatThuCong($id)
    {
        $transaction = GiaoDich::find($id);

        if (!$transaction) {
            return response()->json(['status' => false, 'message' => 'Giao dịch không tồn tại'], 404);
        }

        if ($transaction->trang_thai === GiaoDich::STATUS_SUCCESS) {
            return response()->json(['status' => false, 'message' => 'Giao dịch đã được xử lý trước đó']);
        }

        DB::transaction(function () use ($transaction) {
            $transaction->update(['trang_thai' => GiaoDich::STATUS_SUCCESS, 'paid_at' => now()]);
            $this->activatePackage($transaction);
        });

        $transaction->load(['moiGioi:id,ten,email,so_tin_con_lai,ngay_het_han_goi', 'goiTin:id,ten_goi']);

        return response()->json([
            'status' => true,
            'message' => 'Đã kích hoạt gói tin thành công',
            'data' => $transaction
        ]);
    }

    public function lichSuGiaoDich(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        $data = GiaoDich::with(['goiTin:id,ten_goi,so_ngay,so_luong_tin'])
            ->where('moi_gioi_id', $user->id)
            ->when($request->filled('trang_thai'), fn($q) => $q->where('trang_thai', $request->trang_thai))
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getTransactionStatus($orderCode)
    {
        $transaction = GiaoDich::where('ma_giao_dich', $orderCode)->first();
        if (!$transaction) {
            return response()->json(['status' => false, 'message' => 'Giao dịch không tồn tại'], 404);
        }
        return response()->json([
            'status' => true,
            'data' => [
                'trang_thai' => $transaction->trang_thai,
                'ma_giao_dich' => $transaction->ma_giao_dich,
                'so_tien' => $transaction->so_tien,
                'paid_at' => $transaction->paid_at,
            ]
        ]);
    }

    public function exportGiaoDich(Request $request)
    {
        $format = $request->input('format', 'csv'); // default: csv
        $filename = "giao_dich_" . now()->format('Ymd_His');

        // Build query (dùng chung cho cả 3 định dạng)
        $query = GiaoDich::query()->with(['moiGioi:id,ten,so_dien_thoai,email', 'goiTin:id,ten_goi']);

        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('status')) $query->where('trang_thai', $request->status);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('ma_giao_dich', 'like', "%{$request->search}%")
                    ->orWhereHas('moiGioi', fn($q) => $q->where('ten', 'like', "%{$request->search}%"));
            });
        }

        // Route sang đúng hàm export theo format
        return match ($format) {
            'excel' => $this->exportExcel($query, $filename),
            'pdf'   => $this->exportPdf($query, $filename),
            default => $this->exportCsv($query, $filename)
        };
    }

    /** CSV: Native PHP, siêu nhanh, nhẹ RAM */
    private function exportCsv($query, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        return response()->stream(function () use ($query) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Mã GD', 'Môi giới', 'SĐT', 'Gói tin', 'Số tiền', 'Phương thức', 'Trạng thái', 'Ngày tạo'], ';');

            $query->chunk(500, function ($items) use ($file) {
                foreach ($items as $item) {
                    // ✅ Fix: Kiểm tra cả 2 tên cột
                    $phuongThuc = $item->phuong_thuc_thanh_toan
                        ?? $item->phuong_thuc
                        ?? $item->payment_method
                        ?? 'N/A';

                    fputcsv($file, [
                        $item->ma_giao_dich,
                        $item->moiGioi->ten ?? '',
                        $item->moiGioi->so_dien_thoai ?? '',
                        $item->goiTin->ten_goi ?? '',
                        number_format($item->so_tien, 0, ',', '.'),
                        $phuongThuc, // ✅ Đã fix
                        match ($item->trang_thai) {
                            'success' => 'Thành công',
                            'pending' => 'Chờ xử lý',
                            'failed' => 'Thất bại',
                            'cancelled' => 'Đã hủy',
                            default => $item->trang_thai
                        },
                        $item->created_at->format('d/m/Y H:i')
                    ], ';');
                }
            });
            fclose($file);
        }, 200, $headers);
    }

    /** Excel: Dùng PhpSpreadsheet, format đẹp, hỗ trợ formula */
    private function exportExcel($query, $filename)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Mã GD', 'Môi giới', 'SDT', 'Email', 'Gói tin', 'Số tiền', 'Phương thức', 'Trạng thái', 'Ngày tạo'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        $row = 2;
        $query->chunk(500, function ($items) use ($sheet, &$row) {
            foreach ($items as $item) {
                // ✅ Fix: Kiểm tra nhiều tên cột
                $phuongThuc = $item->phuong_thuc_thanh_toan
                    ?? $item->phuong_thuc
                    ?? $item->payment_method
                    ?? 'N/A';

                $sheet->fromArray([
                    $item->ma_giao_dich,
                    $item->moiGioi->ten ?? '',
                    $item->moiGioi->so_dien_thoai ?? '',
                    $item->moiGioi->email ?? '',
                    $item->goiTin->ten_goi ?? '',
                    $item->so_tien,
                    $phuongThuc, // ✅ Đã fix
                    match ($item->trang_thai) {
                        'success' => 'Thành công',
                        'pending' => 'Chờ xử lý',
                        'failed' => 'Thất bại',
                        'cancelled' => 'Đã hủy',
                        default => $item->trang_thai
                    },
                    $item->created_at->format('d/m/Y H:i')
                ], null, 'A' . $row);
                $row++;
            }
        });

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        return response()->stream(fn() => $writer->save('php://output'), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xlsx\"",
        ]);
    }

    /** PDF: Dùng DomPDF, phù hợp in ấn/báo cáo ngắn */
    private function exportPdf($query, $filename)
    {
        // Lấy data (PDF không nên stream chunk lớn, giới hạn ~2000 dòng)
        $data = $query->limit(1000)->get()->map(fn($item) => [
            'ma_gd' => $item->ma_giao_dich,
            'moi_gioi' => $item->moiGioi->ten ?? '',
            'sdt' => $item->moiGioi->so_dien_thoai ?? '',
            'goi_tin' => $item->goiTin->ten_goi ?? '',
            'so_tien' => number_format($item->so_tien, 0, ',', '.') . ' đ',
            // ✅ SỬA: Kiểm tra nhiều tên cột khác nhau
            'phuong_thuc' => $item->phuong_thuc_thanh_toan
                ?? $item->phuong_thuc
                ?? $item->payment_method
                ?? 'N/A',
            'trang_thai' => match ($item->trang_thai) {
                'success' => 'Thành công',
                'pending' => 'Chờ xử lý',
                'failed' => 'Thất bại',
                'cancelled' => 'Đã hủy',
                default => $item->trang_thai
            },
            'ngay_tao' => $item->created_at->format('d/m/Y H:i')
        ]);

        $html = view('giao_dich_pdf', compact('data'))->render();
        return \PDF::loadHTML($html)
            ->setPaper('a4', 'landscape')
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->download("{$filename}.pdf");
    }
}
