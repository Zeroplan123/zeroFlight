<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_booking_and_stock_is_decremented(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);

        $schedule = Schedule::create([
            'plane_name' => 'Garuda',
            'origin' => 'CGK',
            'destination' => 'DPS',
            'departure_time' => now()->addDay(),
            'price' => 1500000,
            'stock' => 10,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('user.bookings.store', $schedule, absolute: false), [
                'total_seats' => 3,
                'payment_method' => 'Qris',
                'images' => UploadedFile::fake()->image('proof.jpg'),
            ]);

        $response->assertRedirect(route('user.history', absolute: false));

        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'total_seats' => 3,
            'total_price' => 4500000,
            'payment_method' => 'Qris',
            'status' => 'pending',
        ]);

        $schedule->refresh();
        $this->assertSame(7, (int) $schedule->stock);
    }

    public function test_booking_fails_when_stock_is_insufficient(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);

        $schedule = Schedule::create([
            'plane_name' => 'Lion',
            'origin' => 'CGK',
            'destination' => 'SUB',
            'departure_time' => now()->addDay(),
            'price' => 500000,
            'stock' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('user.bookings.create', $schedule, absolute: false))
            ->post(route('user.bookings.store', $schedule, absolute: false), [
                'total_seats' => 3,
                'payment_method' => 'Qris',
                'images' => UploadedFile::fake()->image('proof.jpg'),
            ]);

        $response->assertRedirect(route('user.bookings.create', $schedule, absolute: false));
        $response->assertSessionHasErrors(['total_seats']);

        $this->assertDatabaseCount('bookings', 0);

        $schedule->refresh();
        $this->assertSame(2, (int) $schedule->stock);
    }

    public function test_admin_can_cancel_booking_and_stock_is_restored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'user']);

        $schedule = Schedule::create([
            'plane_name' => 'Citilink',
            'origin' => 'CGK',
            'destination' => 'YIA',
            'departure_time' => now()->addDays(2),
            'price' => 600000,
            'stock' => 7,
        ]);

        $booking = Booking::create([
            'user_id' => $customer->id,
            'schedule_id' => $schedule->id,
            'total_seats' => 3,
            'total_price' => 1800000,
            'images' => 'booking-proofs/dummy.jpg',
            'payment_method' => 'Qris',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.orders.updateStatus', $booking, absolute: false), [
                'status' => 'cancelled',
            ]);

        $response->assertRedirect();

        $booking->refresh();
        $schedule->refresh();

        $this->assertSame('cancelled', $booking->status);
        $this->assertSame(10, (int) $schedule->stock);
    }
}

