<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class VRSController extends Controller
{
    /**
     * Menampilkan profil lengkap supplier yang sedang login
     */
    public function index(Request $request)
    {
        // Ambil data supplier berdasarkan ID yang nempel di user
        $supplier = Supplier::findOrFail($request->user()->supplier_id);

        return response()->json([
            'status' => 'success',
            'data' => $supplier
        ]);
    }

    /**
     * Update data profil (Misal ganti alamat atau nomor telepon)
     */
    public function update(Request $request)
    {
        $supplier = Supplier::findOrFail($request->user()->supplier_id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'address' => 'string',
            'phone' => 'string|max:20',
            'pic_name' => 'string|max:255',
        ]);

        $supplier->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil supplier berhasil diperbarui',
            'data' => $supplier
        ]);
    }
}