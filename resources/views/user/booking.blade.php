<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                    {{ __('Booking Tiket') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">Lengkapi detail booking. Setelah itu upload bukti pembayaran.</p>
            </div>
            <form action="{{ route('dashboard') }}" method="GET">
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
                                <p class="text-xs text-gray-500">Harga / Kursi</p>
                                <p class="font-semibold text-gray-900">Rp {{ number_format($schedule->price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Stok Tersedia</p>
                                <p class="font-semibold text-gray-900">{{ $schedule->stock }}</p>
                            </div>
                        </div>
                </div>

                    @if (session('success'))
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-gray-900">
                            <span class="font-semibold">Info:</span> {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('user.bookings.store', $schedule) }}" class="space-y-4">
                        @csrf

                        @php
                            $selectedSeats = (int) old('total_seats', 1);
                            $selectedMethod = (string) old('payment_method', 'Qris');
                            $estimatedTotal = $selectedSeats * (int) $schedule->price;
                            $accounts = $paymentAccounts ?? [];
                            $selectedAccount = $accounts[$selectedMethod] ?? null;
                            $selectedSeats = max(1, $selectedSeats);
                        @endphp

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Jumlah Kursi</label>
                                <input id="total_seats" name="total_seats" type="number" min="1"
                                    max="{{ max(1, (int) $schedule->stock) }}" value="{{ old('total_seats', $wizard['total_seats'] ?? 1) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900/20" />
                                @error('total_seats')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-white p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Data Penumpang</p>
                                    <p class="mt-0.5 text-sm text-gray-600">Isi nama penumpang sesuai jumlah kursi.</p>
                                </div>
                            </div>

                            @error('passengers')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <div id="passengers_container" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @for ($i = 0; $i < $selectedSeats; $i++)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Penumpang {{ $i + 1 }}</label>
                                        <input name="passengers[]" type="text"
                                            value="{{ old('passengers.' . $i, $wizard['passengers'][$i] ?? '') }}"
                                            placeholder="Nama penumpang {{ $i + 1 }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900/20" />
                                        @error('passengers.' . $i)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="rounded-md border border-gray-200 bg-gray-50 p-4 space-y-2">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-semibold text-gray-800">Total Harga</p>
                                <p class="text-sm font-semibold text-gray-900">
                                    Rp <span id="estimated_total">{{ number_format($estimatedTotal, 0, ',', '.') }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center justify-between gap-4">

                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2 disabled:opacity-50"
                                @disabled($schedule->stock <= 0)>
                                Lanjut
                            </button>
                        </div>
                    </form>

                    <script>
                        (function () {
                            const seatInput = document.getElementById('total_seats');
                            const totalEl = document.getElementById('estimated_total');
                            const passengersContainer = document.getElementById('passengers_container');
                            const pricePerSeat = Number({{ (int) $schedule->price }});

                            const formatRupiah = (value) => {
                                return new Intl.NumberFormat('id-ID').format(value);
                            };

                            const updatePassengers = () => {
                                if (!passengersContainer) return;

                                const seats = Math.max(1, Number(seatInput.value || 1));
                                const existingInputs = Array.from(passengersContainer.querySelectorAll('input[name="passengers[]"]'));
                                const existingValues = existingInputs.map((input) => input.value);

                                passengersContainer.innerHTML = '';

                                for (let i = 0; i < seats; i += 1) {
                                    const wrapper = document.createElement('div');

                                    const label = document.createElement('label');
                                    label.className = 'block text-sm font-medium text-gray-700';
                                    label.textContent = `Penumpang ${i + 1}`;

                                    const input = document.createElement('input');
                                    input.name = 'passengers[]';
                                    input.type = 'text';
                                    input.placeholder = `Nama penumpang ${i + 1}`;
                                    input.value = existingValues[i] || '';
                                    input.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900/20';

                                    wrapper.appendChild(label);
                                    wrapper.appendChild(input);
                                    passengersContainer.appendChild(wrapper);
                                }
                            };

                            const updateTotal = () => {
                                const seats = Math.max(1, Number(seatInput.value || 1));
                                totalEl.textContent = formatRupiah(seats * pricePerSeat);
                            };

                            seatInput.addEventListener('input', () => {
                                updateTotal();
                                updatePassengers();
                            });

                            updateTotal();
                            updatePassengers();
                        })();
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
