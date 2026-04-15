<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                {{ __('Riwayat Pesanan') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">Riwayat booking dan status pembayaran.</p>
        </div>
    </x-slot>

    <div class="bg-gray-50/70">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                @if (session('success'))
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 text-sm text-gray-900">
                        <span class="font-semibold">Berhasil:</span> {{ session('success') }}
                    </div>
                @endif

                <div class="flex items-center justify-between gap-4 border-b border-gray-200 bg-gray-50 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Riwayat Booking</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            <tr class="border-b border-gray-200">
                                <th class="py-3 px-4">Maskapai</th>
                                <th class="py-3 px-4">Rute</th>
                                <th class="py-3 px-4">Jadwal</th>
                                <th class="py-3 px-4 text-right">Kursi</th>
                                <th class="py-3 px-4 text-right">Total</th>
                                <th class="py-3 px-4">Metode</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Bukti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($bookings as $booking)
                                <tr class="align-top hover:bg-gray-50/80">
                                    <td class="py-3 px-4 whitespace-nowrap font-semibold text-gray-900">{{ $booking->schedule->plane_name ?? '-' }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        {{ $booking->schedule->origin ?? '-' }} &rarr;
                                        {{ $booking->schedule->destination ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">
                                        {{ optional(optional($booking->schedule)->departure_time)->format('d M Y H:i') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right">{{ $booking->total_seats }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right font-medium text-gray-900">
                                        Rp {{ number_format($booking->total_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">{{ str_replace('_', ' ', $booking->payment_method) }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @php
                                            $statusColor = match ($booking->status) {
                                                'confirmed' => 'text-green-900',
                                                'cancelled' => 'text-red-800 ',
                                                default => 'text-gray-800 ',
                                            };
                                        @endphp
                                        <span
                                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColor }}">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        @if ($booking->images)
                                            <a target="_blank" href="{{ route('bookings.proof', $booking) }}"
                                                class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1 hover:bg-gray-50">
                                                <img src="{{ route('bookings.proof', $booking) }}" alt="Bukti pembayaran"
                                                    class="h-14 w-14 rounded-md border border-gray-200 bg-white object-contain"
                                                    loading="lazy" />
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-gray-500">
                                        Belum ada booking.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (isset($bookings) && method_exists($bookings, 'links'))
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        {{ $bookings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
