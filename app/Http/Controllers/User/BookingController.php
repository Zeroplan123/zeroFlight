<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->with('schedule')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('user.history', compact('bookings'));
    }

    public function create(Schedule $schedule)
    {
        $paymentAccounts = (array) config('payment.accounts', []);

        return view('user.booking', compact('schedule', 'paymentAccounts'));
    }

    public function store(Request $request, Schedule $schedule)
    {
        $paymentAccounts = (array) config('payment.accounts', []);

        $data = $request->validate([
            'total_seats' => ['required', 'integer', 'min:1'],
            'payment_method' => [
                'required',
                Rule::in(array_keys($paymentAccounts)),
            ],
            'images' => ['required', 'image', 'max:2048'],
        ]);

        $totalSeats = (int) $data['total_seats'];
        $totalPrice = (int) $schedule->price * $totalSeats;

        $imagePath = $request->file('images')->store('booking-proofs', 'public');

        try {
            DB::transaction(function () use ($request, $schedule, $data, $totalSeats, $totalPrice, $imagePath) {
                $updated = Schedule::query()
                    ->whereKey($schedule->getKey())
                    ->where('stock', '>=', $totalSeats)
                    ->decrement('stock', $totalSeats);

                if ($updated !== 1) {
                    throw new \RuntimeException('INSUFFICIENT_STOCK');
                }

                Booking::create([
                    'user_id' => $request->user()->id,
                    'schedule_id' => $schedule->getKey(),
                    'total_seats' => $totalSeats,
                    'total_price' => $totalPrice,
                    'images' => $imagePath,
                    'payment_method' => $data['payment_method'],
                    'status' => 'pending',
                ]);
            });
        } catch (\RuntimeException $exception) {
            Storage::disk('public')->delete($imagePath);

            if ($exception->getMessage() === 'INSUFFICIENT_STOCK') {
                return back()
                    ->withInput()
                    ->withErrors(['total_seats' => 'Stok kursi tidak mencukupi untuk jumlah kursi yang dipilih.']);
            }

            throw $exception;
        }

        return redirect()
            ->route('user.history')
            ->with('success', 'Booking berhasil dibuat. Silakan tunggu konfirmasi admin.');
    }
}
