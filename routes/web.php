<?php

use App\Http\Controllers\admin\AdminScheduleController;
use App\Http\Controllers\admin\AdminBookingController;
use App\Http\Controllers\BookingProofController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function (Request $request) {
    $user = auth()->user();

    if (!$user) {
        abort(403, 'Unauthorized');
    }

    $role = strtolower(trim((string) $user->role));

    if ($role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($role === 'user') {
        $search = trim((string) $request->query('q', ''));

        $schedules = Schedule::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('plane_name', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            })
            ->orderBy('departure_time')
            ->paginate(10)
            ->withQueryString();

        return view('user.dashboard', compact('schedules', 'search'));
    }

    abort(403, 'Unauthorized');
})->middleware(['auth'])->name('dashboard');

Route::get('/admin', function () {
    $search = trim((string) request()->query('q', ''));

    $schedules = Schedule::query()
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery
                    ->where('plane_name', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('destination', 'like', "%{$search}%");
            });
        })
        ->orderBy('departure_time')
        ->paginate(10)
        ->withQueryString();

    return view('admin.dashboard', compact('schedules', 'search'));
})->middleware(['auth', 'role:admin'])->name('admin.dashboard');

Route::middleware(['auth', 'role:user'])->name('user.')->group(function () {
    Route::get('/schedules/{schedule}/book', [UserBookingController::class, 'create'])->name('bookings.create');
    Route::post('/schedules/{schedule}/book', [UserBookingController::class, 'store'])->name('bookings.store');
    Route::get('/history', [UserBookingController::class, 'index'])->name('history');
});

Route::get('/bookings/{booking}/proof', [BookingProofController::class, 'show'])
    ->middleware('auth')
    ->name('bookings.proof');

Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/orders', [AdminBookingController::class, 'index'])->name('orders');
        Route::patch('/orders/{booking}', [AdminBookingController::class, 'updateStatus'])->name('orders.updateStatus');

        Route::get('/schedules/create', [AdminScheduleController::class, 'create'])->name('schedules.create');
        Route::post('/schedules', [AdminScheduleController::class, 'store'])->name('schedules.store');
        Route::get('/schedules/{id}/edit', [AdminScheduleController::class, 'edit'])->name('schedules.edit');
        Route::put('/schedules/{id}', [AdminScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{id}', [AdminScheduleController::class, 'destroy'])->name('schedules.destroy');
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
