<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    {{ __('Pembayaran') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">Upload bukti pembayaran untuk melengkapi pesanan.</p>
            </div>
            <form action="{{ route('user.history') }}" method="GET">
                <button type="submit"
                    class="inline-flex items-center rounded-md border border-gray-900 bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                    Kembali
                </button>
            </form>
        </div>
    </x-slot>

    <div class="bg-gray-50/70">
        <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                <div class="p-6 space-y-6">
                    @if (session('success'))
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900">
                            <span class="font-semibold">Info:</span> {{ session('success') }}
                        </div>
                    @endif

                    @if ($booking->status !== 'pending')
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900">
                            <span class="font-semibold">Info:</span> Booking ini sudah diproses, bukti pembayaran tidak bisa diubah.
                        </div>
                    @endif

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-gray-500">Maskapai</p>
                                <p class="font-semibold text-gray-900">{{ $booking->schedule->plane_name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Jadwal</p>
                                <p class="font-semibold text-gray-900">
                                    {{ optional(optional($booking->schedule)->departure_time)->format('d M Y H:i') ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Rute</p>
                                <p class="font-semibold text-gray-900">
                                    {{ $booking->schedule->origin ?? '-' }} &rarr; {{ $booking->schedule->destination ?? '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Jumlah Kursi</p>
                                <p class="font-semibold text-gray-900">{{ $booking->total_seats }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500">Penumpang</p>
                                @php
                                    $passengerNames = $booking->passengers;
                                    if (is_string($passengerNames)) {
                                        $decoded = json_decode($passengerNames, true);
                                        $passengerNames = is_array($decoded) ? $decoded : [];
                                    }
                                    $passengerNames = is_array($passengerNames) ? array_values(array_filter($passengerNames)) : [];
                                @endphp
                                @if (count($passengerNames) > 0)
                                    <p class="font-semibold text-gray-900">{{ implode(', ', $passengerNames) }}</p>
                                @else
                                    <p class="font-semibold text-gray-900">-</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Harga</p>
                                <p class="font-semibold text-gray-900">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Metode Pembayaran</p>
                                <p class="font-semibold text-gray-900">{{ str_replace('_', ' ', $booking->payment_method) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-200 bg-gray-50 p-4 space-y-2">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Tujuan Pembayaran</p>
                                <p class="text-sm text-gray-700">
                                    {{ $selectedAccount ?? 'Rekening tidak ditemukan. Silakan hubungi admin.' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if (!empty($booking->images))
                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <p class="text-sm font-semibold text-gray-900">Bukti Saat Ini</p>
                            <div class="mt-3">
                                <a target="_blank" href="{{ route('bookings.proof', $booking) }}"
                                    class="inline-flex items-center rounded-lg border border-gray-200 bg-white p-1 hover:bg-gray-50">
                                    <img src="{{ route('bookings.proof', $booking) }}" alt="Bukti pembayaran"
                                        class="h-24 w-24 rounded-md border border-gray-200 bg-white object-contain" loading="lazy" />
                                </a>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Upload ulang akan mengganti bukti sebelumnya.</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.payments.store', $booking) }}" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bukti Pembayaran</label>
                            <input name="images" type="file" accept="image/*"
                                class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border file:border-gray-200 file:bg-white file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-900 hover:file:bg-gray-50" />
                            @error('images')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2 flex items-center justify-between gap-4">
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50"
                                @disabled($booking->status !== 'pending')>
                                Upload Bukti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
