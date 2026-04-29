<?php

namespace App\Http\Controllers;

use App\Models\PhuongXa;
use Illuminate\Http\Request;

class PhuongXaController extends Controller
{
    public function getPhuongXaByQuanHuyen(Request $request)
    {
        $request->validate([
            'quan_huyen_id' => 'required|exists:quan_huyens,id',
        ]);

        $phuongXas = PhuongXa::where('quan_huyen_id', $request->quan_huyen_id)
            ->get(['id', 'ten']);

        return response()->json([
            'status' => 'success',
            'message' => 'Lấy danh sách phường xã thành công',
            'data' => $phuongXas
        ]);
    }
    public function getAll()
    {
        $data = PhuongXa::select('id', 'ten', 'quan_huyen_id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Lấy danh sách phường xã thành công',
            'data' => $data
        ]);
    }
}
