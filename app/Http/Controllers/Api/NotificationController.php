<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Ambil notifikasi milik user yang sedang login
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
            'unread_count' => $notifications->where('is_read', false)->count()
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        
        // Pastikan hanya pemilik yang bisa menandai sudah baca
        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Notifikasi telah dibaca'
        ]);
    }
}