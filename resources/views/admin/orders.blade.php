<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                {{ __('Pesanan') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">Pantau dan konfirmasi status booking pelanggan.</p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="p-6">
                @if (session('success'))
                    <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-gray-900">Daftar Booking</h3>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr class="border-b border-gray-200">
                                <th class="py-3 px-4">Customer</th>
                                <th class="py-3 px-4">Maskapai</th>
                                <th class="py-3 px-4">Rute</th>
                                <th class="py-3 px-4">Jadwal</th>
                                <th class="py-3 px-4 text-right">Kursi</th>
                                <th class="py-3 px-4 text-right">Total</th>
                                <th class="py-3 px-4">Metode</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Bukti</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($bookings as $booking)
                                <tr class="align-top hover:bg-gray-50/60">
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $booking->user->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $booking->user->email ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ $booking->schedule->plane_name ?? '-' }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        {{ $booking->schedule->origin ?? '-' }} &rarr;
                                        {{ $booking->schedule->destination ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">
                                        {{ optional(optional($booking->schedule)->departure_time)->format('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right">{{ $booking->total_seats }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">{{ str_replace('_', ' ', $booking->payment_method) }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @php
                                            $statusColor = match ($booking->status) {
                                                'confirmed' => 'text-green-700 bg-green-50 border-green-200',
                                                'cancelled' => 'text-red-700 bg-red-50 border-red-200',
                                                default => 'text-yellow-700 bg-yellow-50 border-yellow-200',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold {{ $statusColor }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($booking->images)
                                            <a target="_blank" href="{{ route('bookings.proof', $booking) }}"
                                                class="inline-flex items-center gap-2">
                                                <img src="{{ route('bookings.proof', $booking) }}" alt="Bukti pembayaran"
                                                    class="h-14 w-14 rounded-md border border-gray-200 bg-white object-contain"
                                                    loading="lazy" />
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('admin.orders.updateStatus', $booking) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="confirmed" />
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-md border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-700 shadow-sm hover:bg-green-100 disabled:cursor-not-allowed disabled:opacity-50"
                                                    @disabled($booking->status !== 'pending')>
                                                    Confirm
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.orders.updateStatus', $booking) }}"
                                                onsubmit="return confirm('Batalkan booking ini? Stok akan dikembalikan.')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled" />
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50"
                                                    @disabled($booking->status === 'cancelled')>
                                                    Cancel
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-10 text-center text-gray-500">
                                        Belum ada booking.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (isset($bookings) && method_exists($bookings, 'links'))
                    <div class="mt-5">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
