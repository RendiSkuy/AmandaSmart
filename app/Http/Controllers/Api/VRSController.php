<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VrsSchedule;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;

class VRSController extends Controller
{
    /**
     * Menampilkan daftar jadwal booking truk milik supplier
     */
    public function index(Request $request)
    {
        $supplierId = $request->user()->supplier_id;

        $schedules = VrsSchedule::with(['purchaseOrder.details.product'])
            ->whereHas('purchaseOrder.offers', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                      ->where('status', 'accepted');
            })
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
            'scheduled_date'    => 'required|date|after_or_equal:today',
            'time_slot'         => 'required|string'
        ]);

        $supplierId = $request->user()->supplier_id;

        $po = PurchaseOrder::findOrFail($request->purchase_order_id);

        // Pastikan PO milik supplier yang login dan dimenangkan oleh user yang login ini
        $hasAcceptedOffer = \App\Models\Offer::where('purchase_order_id', $po->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'accepted')
            ->exists();
        if (!$hasAcceptedOffer) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak akses untuk melakukan booking pada PO ini.'
            ], 403);
        }

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

        // Batasi kuota antrean: Maksimal 5 truk per slot waktu di tanggal yang sama (Karena DC hanya memiliki 5 tempat)
        $slotCount = VrsSchedule::where('scheduled_date', $request->scheduled_date)
            ->where('time_slot', $request->time_slot)
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($slotCount >= 5) {
            return response()->json([
                'status' => 'error',
                'message' => 'Slot waktu ' . $request->time_slot . ' pada tanggal ' . $request->scheduled_date . ' sudah penuh (Maksimal 5 truk). Silakan pilih tanggal atau slot waktu lain.'
            ], 422);
        }

        $schedule = VrsSchedule::create([
            'purchase_order_id' => $po->id,
            'scheduled_date'    => $request->scheduled_date,
            'time_slot'         => $request->time_slot,
            'status'            => 'booked'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Slot kedatangan truk berhasil dipesan!',
            'data' => $schedule
        ]);
    }

    /**
     * Menampilkan profil supplier yang login
     */
    public function showProfile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'data'   => $request->user()->supplier
        ]);
    }

    /**
     * Memperbarui profil supplier yang login
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'            => 'required|string',
            'whatsapp_number' => 'nullable|string'
        ]);

        $supplier = $request->user()->supplier;
        if ($supplier) {
            $supplier->update($request->only('name', 'whatsapp_number'));
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $supplier
        ]);
    }
}