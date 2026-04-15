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
    private function wizardSessionKey(Schedule $schedule): string
    {
        return 'booking_wizard.schedule.' . (int) $schedule->getKey();
    }

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
        $wizard = session($this->wizardSessionKey($schedule), []);

        return view('user.booking', compact('schedule', 'wizard'));
    }

    public function store(Request $request, Schedule $schedule)
    {
        $data = $request->validate([
            'total_seats' => ['required', 'integer', 'min:1'],
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*' => ['required', 'string', 'max:150'],
        ]);

        $totalSeats = (int) $data['total_seats'];
        $passengers = array_values(array_map(static fn ($name) => trim((string) $name), $data['passengers'] ?? []));

        if (count($passengers) !== $totalSeats) {
            return back()
                ->withInput()
                ->withErrors(['passengers' => 'Jumlah nama penumpang harus sama dengan jumlah kursi.']);
        }

        $request->session()->put($this->wizardSessionKey($schedule), [
            'total_seats' => $totalSeats,
            'passengers' => $passengers,
        ]);

        return redirect()
            ->route('user.bookings.payment', $schedule)
            ->with('success', 'Data booking tersimpan. Silakan pilih metode pembayaran dan upload bukti.');
    }

    public function wizardPaymentCreate(Request $request, Schedule $schedule)
    {
        $wizard = (array) $request->session()->get($this->wizardSessionKey($schedule), []);

        $totalSeats = (int) ($wizard['total_seats'] ?? 0);
        $passengers = $wizard['passengers'] ?? [];
        $passengers = is_array($passengers) ? array_values(array_filter($passengers)) : [];

        if ($totalSeats < 1 || count($passengers) !== $totalSeats) {
            return redirect()
                ->route('user.bookings.create', $schedule)
                ->withErrors(['passengers' => 'Silakan lengkapi data booking terlebih dahulu.']);
        }

        $paymentAccounts = (array) config('payment.accounts', []);
        $selectedMethod = (string) old('payment_method', array_key_first($paymentAccounts) ?: 'Qris');
        $selectedAccount = $paymentAccounts[$selectedMethod] ?? null;

        $estimatedTotal = $totalSeats * (int) $schedule->price;

        return view('user.booking-payment', compact(
            'schedule',
            'totalSeats',
            'passengers',
            'paymentAccounts',
            'selectedMethod',
            'selectedAccount',
            'estimatedTotal',
        ));
    }

    public function wizardPaymentStore(Request $request, Schedule $schedule)
    {
        $wizard = (array) $request->session()->get($this->wizardSessionKey($schedule), []);

        $totalSeats = (int) ($wizard['total_seats'] ?? 0);
        $passengers = $wizard['passengers'] ?? [];
        $passengers = is_array($passengers) ? array_values(array_filter($passengers)) : [];

        if ($totalSeats < 1 || count($passengers) !== $totalSeats) {
            return redirect()
                ->route('user.bookings.create', $schedule)
                ->withErrors(['passengers' => 'Silakan lengkapi data booking terlebih dahulu.']);
        }

        $paymentAccounts = (array) config('payment.accounts', []);

        $data = $request->validate([
            'payment_method' => [
                'required',
                Rule::in(array_keys($paymentAccounts)),
            ],
            'images' => ['required', 'image', 'max:2048'],
        ]);

        $totalPrice = (int) $schedule->price * $totalSeats;
        $imagePath = $request->file('images')->store('booking-proofs', 'public');
        $booking = null;

        try {
            DB::transaction(function () use ($request, $schedule, $data, $totalSeats, $totalPrice, $passengers, $imagePath, &$booking) {
                $updated = Schedule::query()
                    ->whereKey($schedule->getKey())
                    ->where('stock', '>=', $totalSeats)
                    ->decrement('stock', $totalSeats);

                if ($updated !== 1) {
                    throw new \RuntimeException('INSUFFICIENT_STOCK');
                }

                $booking = Booking::create([
                    'user_id' => $request->user()->id,
                    'schedule_id' => $schedule->getKey(),
                    'total_seats' => $totalSeats,
                    'total_price' => $totalPrice,
                    'passengers' => $passengers,
                    'images' => $imagePath,
                    'payment_method' => $data['payment_method'],
                    'status' => 'pending',
                ]);
            });
        } catch (\RuntimeException $exception) {
            Storage::disk('public')->delete($imagePath);

            if ($exception->getMessage() === 'INSUFFICIENT_STOCK') {
                return back()->withErrors(['total_seats' => 'Stok kursi tidak mencukupi untuk jumlah kursi yang dipilih.']);
            }

            throw $exception;
        }

        if (! $booking) {
            abort(500);
        }

        $request->session()->forget($this->wizardSessionKey($schedule));

        return redirect()
            ->route('user.history')
            ->with('success', 'Booking berhasil dibuat. Silakan tunggu konfirmasi admin.');
    }

    public function paymentCreate(Request $request, Booking $booking)
    {
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $booking->loadMissing('schedule');

        $paymentAccounts = (array) config('payment.accounts', []);
        $selectedAccount = $paymentAccounts[$booking->payment_method] ?? null;

        return view('user.payment', compact('booking', 'paymentAccounts', 'selectedAccount'));
    }

    public function paymentStore(Request $request, Booking $booking)
    {
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        if ($booking->status !== 'pending') {
            return back()->withErrors(['images' => 'Booking ini sudah diproses, bukti pembayaran tidak bisa diubah.']);
        }

        $data = $request->validate([
            'images' => ['required', 'image', 'max:2048'],
        ]);

        $imagePath = $request->file('images')->store('booking-proofs', 'public');
        $oldPath = (string) ($booking->images ?? '');

        $booking->update(['images' => $imagePath]);

        if ($oldPath !== '' && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('user.history')
            ->with('success', 'Bukti pembayaran berhasil diupload. Silakan tunggu konfirmasi admin.');
    }
}
