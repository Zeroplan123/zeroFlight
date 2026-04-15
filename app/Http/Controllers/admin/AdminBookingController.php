<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::query()
            ->with(['user', 'schedule'])
            ->latest()
            ->paginate(10);

        return view('admin.orders', compact('bookings'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'cancelled'])],
        ]);

        $targetStatus = $data['status'];

        DB::transaction(function () use ($booking, $targetStatus) {
            $lockedBooking = Booking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status === 'cancelled') {
                return;
            }

            if ($lockedBooking->status === $targetStatus) {
                return;
            }

            $schedule = Schedule::query()
                ->whereKey($lockedBooking->schedule_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($targetStatus === 'cancelled') {
                $schedule->increment('stock', (int) $lockedBooking->total_seats);
            }

            $lockedBooking->update(['status' => $targetStatus]);
        });

        return back()->with('success', 'Status booking berhasil diupdate.');
    }
}

