<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatBotRequest;
use App\Models\KhachHang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request as LaravelRequest;

class TrainChatController extends Controller
{
    public function chat(ChatBotRequest $request)
    {
        $message   = trim((string) $request->input('message'));
        $role      = $request->input('role', 'guest');
        $sessionId = $request->input('session_id') ?: 'guest_' . Str::uuid()->toString();
        $user      = Auth::guard('sanctum')->user();

        // Resolve actor from authenticated user (overrides frontend-sent role)
        $actor = $this->resolveActor($request, $user);

        $payload = [
            'message'        => $message,
            'session_id'     => $sessionId,
            'role'           => $role,
            'actor'          => $actor,  // canonical actor for Python policy
            'action_type'    => $request->input('action_type'),
            'action_payload' => $request->input('action_payload', []),
            'user_context'   => [
                'is_logged_in' => (bool) $user,
                'user_id'      => $user->id ?? null,
                'ten'          => $user->ten ?? null,
                'email'        => $user->email ?? null,
            ],
        ];

        try {
            $baseUrl = rtrim(config('services.chatbot_bds.url', env('CHATBOT_BDS_URL', 'http://127.0.0.1:5002')), '/');
            $response = Http::timeout(30)->post($baseUrl . '/chat', $payload);

            if (!$response->successful()) {
                Log::warning('chatbot_bds_http_fail', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallbackResponse('http_fail');
            }

            $data = $response->json();
            Log::info('CHATBOT_RAW', [
                'response' => $data
            ]);
            $data = $this->handleChatbotBooking($request, is_array($data) ? $data : []);

            $chatData = $this->normalizeChatbotData($data);

            return response()->json([
            'status' => true,
            'data' => [
                'reply' => $chatData['reply'] ?? 'Mình chưa xử lý được câu hỏi này. Bạn thử hỏi lại rõ hơn nhé.',
                'intent' => $chatData['intent'] ?? null,
                'context' => $chatData['context'] ?? null,
                'suggestions' => $chatData['suggestions'] ?? [],
                'quick_replies' => $chatData['quick_replies'] ?? [],
                'is_markdown' => true,
                'processing_time' => $chatData['processing_time'] ?? null,
                ],
            ]);
            } catch (\Throwable $e) {
            Log::error('chatbot_bds_exception', [
                'message' => $e->getMessage(),
                'session_id' => $sessionId,
                'role' => $role,
            ]);
            return $this->fallbackResponse('exception');
        }
    }

    private function normalizeChatbotData(array $data): array
    {
        $nested = isset($data['data']) && is_array($data['data']) ? $data['data'] : [];

        return [
            'reply' => $data['response'] ?? $nested['reply'] ?? null,
            'intent' => $data['intent'] ?? $nested['intent'] ?? null,
            'context' => $data['context'] ?? $nested['context'] ?? null,
            'suggestions' => $data['suggestions'] ?? $nested['suggestions'] ?? [],
            'quick_replies' => $nested['quick_replies'] ?? $data['quick_replies'] ?? [],
            'is_markdown' => $nested['is_markdown'] ?? true,
            'processing_time' => $data['processing_time'] ?? $nested['processing_time'] ?? null,
        ];
    }

    private function handleChatbotBooking(ChatBotRequest $request, array $data): array
    {
        $bookingRequest = $data['booking_request'] ?? null;
        if (!$bookingRequest || !is_array($bookingRequest)) {
            return $data;
        }

        $user = Auth::guard('sanctum')->user();
        if (!$user || !($user instanceof KhachHang)) {
            Log::info('chatbot_booking_rejected', [
                'reason' => 'unauthenticated',
                'session_id' => $request->input('session_id'),
            ]);
            $data['response'] = 'Bạn cần đăng nhập tài khoản khách hàng để đặt lịch xem nhà bằng chatbot.';
            $data['intent'] = 'appointment';
            $data['context'] = 'booking';
            return $data;
        }

        $payload = [
            'bat_dong_san_id' => $bookingRequest['bat_dong_san_id'] ?? null,
            'ngay_hen' => $bookingRequest['ngay_hen'] ?? null,
            'gio_hen' => $bookingRequest['gio_hen'] ?? null,
            'ghi_chu' => $bookingRequest['ghi_chu'] ?? 'Đặt lịch qua chatbot',
        ];

        if (!$this->isFutureBookingDateTime($payload['ngay_hen'], $payload['gio_hen'])) {
            Log::info('chatbot_booking_rejected', [
                'reason' => 'datetime_not_future',
                'session_id' => $request->input('session_id'),
                'payload' => $payload,
            ]);
            $data['response'] = 'Thời gian hẹn phải sau thời điểm hiện tại. Bạn chọn lại ngày giờ khác giúp mình nhé.';
            $data['intent'] = 'appointment';
            $data['context'] = 'booking';
            return $data;
        }

        $internalRequest = LaravelRequest::create('/api/khach-hang/lich-hen/dat', 'POST', $payload);
        $bookingResponse = app(LichHenXemNhaController::class)->datLich($internalRequest);
        $bookingData = $bookingResponse->getData(true);

        if (($bookingData['status'] ?? false) === true) {
            Log::info('booking_confirmed', [
                'source' => 'chatbot',
                'session_id' => $request->input('session_id'),
                'lich_hen_id' => $bookingData['data']['id'] ?? null,
                'payload' => $payload,
            ]);
            $title = $bookingData['data']['bat_dong_san']['tieu_de'] ?? 'bất động sản này';
            $data['response'] = "Đặt lịch thành công cho <b>{$title}</b> vào {$payload['ngay_hen']} lúc {$payload['gio_hen']}. Môi giới sẽ xác nhận sớm trong mục lịch hẹn của bạn.";
            $data['booking_result'] = $bookingData['data'] ?? null;
            $data['intent'] = 'appointment';
            $data['context'] = 'booking';
            return $data;
        }

        Log::info('chatbot_booking_rejected', [
            'reason' => 'booking_flow_error',
            'session_id' => $request->input('session_id'),
            'message' => $bookingData['message'] ?? null,
        ]);
        $data['response'] = $bookingData['message'] ?? 'Chưa thể đặt lịch lúc này. Bạn thử chọn lại ngày giờ khác nhé.';
        $data['intent'] = 'appointment';
        $data['context'] = 'booking';
        return $data;
    }

    private function isFutureBookingDateTime(?string $date, ?string $time): bool
    {
        if (!$date || !$time) {
            return false;
        }

        try {
            return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}")->greaterThan(now());
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Resolve the canonical actor string from auth + request.
     * Auth takes priority over frontend-sent role/actor.
     */
    private function resolveActor(ChatBotRequest $request, $user): string
    {
        // If authenticated, derive actor from model class (most secure)
        if ($user) {
            $class = get_class($user);
            if (str_contains($class, 'MoiGioi')) return 'broker';
            if (str_contains($class, 'KhachHang')) return 'customer';
            if (str_contains($class, 'Admin')) return 'admin';
        }

        // Fallback to frontend-sent actor/role
        $raw = strtolower(trim(
            $request->input('actor') ??
            $request->input('role') ??
            'guest'
        ));

        return match(true) {
            in_array($raw, ['moi_gioi', 'moi-gioi', 'broker', 'agent', 'seller']) => 'broker',
            in_array($raw, ['khach_hang', 'khach-hang', 'customer', 'khach', 'user']) => 'customer',
            $raw === 'admin' => 'admin',
            default => 'guest',
        };
    }

    private function fallbackResponse(string $reason)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'reply' => 'Trợ lý AI đang bận một chút. Bạn có thể thử lại hoặc chọn nhanh: tìm BĐS, định giá, gói tin, đặt lịch xem nhà.',
                'intent' => 'fallback',
                'context' => null,
                'suggestions' => [],
                'quick_replies' => ['Tìm BĐS', 'Định giá', 'Gói tin', 'Đặt lịch xem nhà'],
                'is_markdown' => true,
                'fallback_reason' => $reason,
            ],
        ]);
    }
}
