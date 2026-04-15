<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    {{ __('Pembayaran') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">Pilih metode pembayaran dan upload bukti pembayaran.</p>
            </div>
            <form action="{{ route('user.bookings.create', $schedule) }}" method="GET">
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

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs text-gray-500">Maskapai</p>
                                <p class="font-semibold text-gray-900">{{ $schedule->plane_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Jadwal</p>
                                <p class="font-semibold text-gray-900">{{ optional($schedule->departure_time)->format('d M Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Rute</p>
                                <p class="font-semibold text-gray-900">{{ $schedule->origin }} &rarr; {{ $schedule->destination }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Jumlah Kursi</p>
                                <p class="font-semibold text-gray-900">{{ $totalSeats }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-xs text-gray-500">Penumpang</p>
                                <p class="font-semibold text-gray-900">{{ implode(', ', $passengers) }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('user.bookings.payment.store', $schedule) }}" enctype="multipart/form-data"
                        class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                                <select id="payment_method" name="payment_method"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900/20">
                                    @foreach ($paymentAccounts as $method => $account)
                                        <option value="{{ $method }}" data-account="{{ $account }}"
                                            @selected(old('payment_method', $selectedMethod) === $method)>
                                            {{ str_replace('_', ' ', $method) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bukti Pembayaran</label>
                                <input name="images" type="file" accept="image/*"
                                    class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:rounded-md file:border file:border-gray-200 file:bg-white file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-900 hover:file:bg-gray-50" />
                                @error('images')
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

                        <div class="pt-2 flex items-center justify-between gap-4">
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                Buat Booking
                            </button>
                        </div>
                    </form>

                    <script>
                        (function () {
                            const methodSelect = document.getElementById('payment_method');
                            const accountEl = document.getElementById('account_text');

                            const updateAccount = () => {
                                const opt = methodSelect.options[methodSelect.selectedIndex];
                                const account = opt ? (opt.getAttribute('data-account') || '') : '';
                                accountEl.textContent = account || 'Pilih metode pembayaran untuk melihat rekening.';
                            };

                            methodSelect.addEventListener('change', updateAccount);
                            updateAccount();
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

