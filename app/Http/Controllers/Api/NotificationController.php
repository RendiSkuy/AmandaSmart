<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data' => [],
            'unread_count' => 0
        ]);
    }

    public function markAsRead($id)
    {
        return response()->json([
            'status' => 'success', 
            'message' => 'Notifikasi telah dibaca'
        ]);
    }
}