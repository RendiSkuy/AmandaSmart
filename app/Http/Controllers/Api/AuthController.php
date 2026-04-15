<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle Login B2B Supplier
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'device_name' => 'required', // Untuk mengidentifikasi device 
        ]);

        // Cari user berdasarkan username (bukan email, sesuai rencana B2B)
        $user = User::with('supplier')->where('username', $request->username)->first();

        // Validasi user dan password
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username atau password salah.'
            ], 401);
        }

        // Opsional: Pastikan supplier sedang aktif
        if ($user->supplier && $user->supplier->status !== 'active') {
             return response()->json([
                'status' => 'error',
                'message' => 'Akun Supplier Anda sedang tidak aktif. Silakan hubungi admin.'
            ], 403);
        }

        // Buat token Sanctum
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $user,
                'supplier' => $user->supplier
            ]
        ]);
    }

    /**
     * Ambil data profil user saat ini
     */
    public function me(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => $request->user()->load('supplier')
        ]);
    }

    /**
     * Logout (Hapus Token)
     */
    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ]);
    }
}