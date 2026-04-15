<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingProofController extends Controller
{
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        $role = strtolower(trim((string) $user->role));
        $isAdmin = $role === 'admin';
        $isOwner = (int) $booking->user_id === (int) $user->id;

        if (! $isAdmin && ! $isOwner) {
            abort(403, 'Unauthorized');
        }

        $path = (string) $booking->images;

        if ($path === '') {
            abort(404);
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}

