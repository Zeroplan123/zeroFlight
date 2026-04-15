<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    {{ __('Booking Tiket') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">Lengkapi detail booking dan upload bukti pembayaran.</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="text-sm font-medium text-gray-700 hover:text-gray-900 hover:underline">Kembali</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="p-6 space-y-6">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-gray-500">Maskapai</p>
                                <p class="font-semibold">{{ $schedule->plane_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Jadwal</p>
                                <p class="font-semibold">{{ optional($schedule->departure_time)->format('d M Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Rute</p>
                                <p class="font-semibold">{{ $schedule->origin }} &rarr; {{ $schedule->destination }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Harga / Kursi</p>
                                <p class="font-semibold">Rp {{ number_format($schedule->price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Stok Tersedia</p>
                                <p class="font-semibold">{{ $schedule->stock }}</p>
                            </div>
                        </div>
                </div>

                    @if (session('success'))
                        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.bookings.store', $schedule) }}" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        @php
                            $selectedSeats = (int) old('total_seats', 1);
                            $selectedMethod = (string) old('payment_method', 'Qris');
                            $estimatedTotal = $selectedSeats * (int) $schedule->price;
                            $accounts = $paymentAccounts ?? [];
                            $selectedAccount = $accounts[$selectedMethod] ?? null;
                        @endphp

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah Kursi</label>
                                <input id="total_seats" name="total_seats" type="number" min="1" value="{{ old('total_seats', 1) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900/20" />
                                @error('total_seats')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                                <select id="payment_method" name="payment_method"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900/20">
                                    @foreach ($accounts as $method => $account)
                                        <option value="{{ $method }}" data-account="{{ $account }}"
                                            @selected($selectedMethod === $method)>
                                            {{ str_replace('_', ' ', $method) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="rounded-md border border-gray-200 bg-gray-50 p-4 space-y-2">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-semibold text-gray-800">Total Harga</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    Rp <span id="estimated_total">{{ number_format($estimatedTotal, 0, ',', '.') }}</span>
                                </p>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Tujuan Pembayaran</p>
                                    <p class="text-sm text-gray-700" id="account_text">
                                        {{ $selectedAccount ?? 'Pilih metode pembayaran untuk melihat rekening.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

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
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                @disabled($schedule->stock <= 0)>
                                Buat Booking
                            </button>
                        </div>
                    </form>

                    <script>
                        (function () {
                            const seatInput = document.getElementById('total_seats');
                            const methodSelect = document.getElementById('payment_method');
                            const totalEl = document.getElementById('estimated_total');
                            const accountEl = document.getElementById('account_text');
                            const pricePerSeat = Number({{ (int) $schedule->price }});

                            const formatRupiah = (value) => {
                                return new Intl.NumberFormat('id-ID').format(value);
                            };

                            const updateTotal = () => {
                                const seats = Math.max(1, Number(seatInput.value || 1));
                                totalEl.textContent = formatRupiah(seats * pricePerSeat);
                            };

                            const updateAccount = () => {
                                const opt = methodSelect.options[methodSelect.selectedIndex];
                                const account = opt ? (opt.getAttribute('data-account') || '') : '';
                                accountEl.textContent = account || 'Pilih metode pembayaran untuk melihat rekening.';
                            };

                            seatInput.addEventListener('input', updateTotal);
                            methodSelect.addEventListener('change', updateAccount);

                            updateTotal();
                            updateAccount();
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
