<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VrsSchedule; // WAJIB ADA
use App\Models\PurchaseOrder; // WAJIB ADA
use App\Models\Supplier;
use Illuminate\Http\Request;

class VRSController extends Controller
{
    /**
     * Menampilkan daftar jadwal booking truk milik supplier
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        $schedules = VrsSchedule::with(['purchaseOrder'])
            ->where('supplier_id', $supplierId)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $schedules
        ]);
    }

    /**
     * Supplier melakukan booking slot kedatangan truk
     */
    public function createBooking(Request $request)
    {
        $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string'
        ]);

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);

        // Cek apakah PO ini sudah pernah di-booking sebelumnya (kecuali yang batal)
        $existing = VrsSchedule::where('purchase_order_id', $po->id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'PO ini sudah memiliki jadwal kedatangan.'
            ], 422);
        }

        $schedule = VrsSchedule::create([
            'supplier_id' => $request->user()->supplier_id,
            'purchase_order_id' => $po->id,
            'dc_id' => $po->dc_id ?? 1,
            'scheduled_date' => $request->scheduled_date,
            'time_slot' => $request->time_slot,
            'status' => 'booked'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Slot kedatangan truk berhasil dipesan!',
            'data' => $schedule
        ]);
    }
}