<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">Pilih schedule yang tersedia untuk melakukan booking.</p>
        </div>
    </x-slot>

    <div class="bg-gray-50/70">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Schedule Penerbangan</h3>
                        @if (!empty($search))
                            <p class="mt-0.5 text-sm text-gray-600">
                                Hasil untuk: <span class="font-semibold text-gray-900">"{{ $search }}"</span>
                            </p>
                        @endif
                    </div>

                    <form action="{{ route('dashboard') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
                        <label class="sr-only" for="q">Cari schedule</label>
                        <input id="q" name="q" type="text" value="{{ $search ?? request('q') }}"
                            placeholder="Cari maskapai / asal / tujuan..."
                            class="block w-full rounded-md border-gray-300 bg-white text-sm shadow-sm focus:border-gray-900 focus:ring-gray-900/20 sm:w-72" />
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-gray-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                            Cari
                        </button>
                        @if (!empty($search))
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center rounded-md border border-gray-900 bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-900 hover:text-white focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-100 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                            <tr class="border-b border-gray-200">
                                <th class="py-3 px-4">Maskapai</th>
                                <th class="py-3 px-4">Asal</th>
                                <th class="py-3 px-4">Tujuan</th>
                                <th class="py-3 px-4">Jadwal</th>
                                <th class="py-3 px-4 text-right">Harga</th>
                                <th class="py-3 px-4 text-right">Stok</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($schedules as $schedule)
                                <tr class="align-top hover:bg-gray-50/80">
                                    <td class="py-3 px-4 whitespace-nowrap font-semibold text-gray-900">{{ $schedule->plane_name }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">{{ $schedule->origin }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">{{ $schedule->destination }}</td>
                                    <td class="py-3 px-4 whitespace-nowrap text-gray-700">
                                        {{ optional($schedule->departure_time)->format('d M Y H:i') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right font-medium text-gray-900">
                                        Rp {{ number_format($schedule->price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap text-right">
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-900">
                                            {{ $schedule->stock }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex justify-end">
                                            @if ($schedule->stock > 0)
                                                <form action="{{ route('user.bookings.create', $schedule) }}" method="GET">
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-md bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                                                        Pesan
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 text-sm">Habis</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-500">
                                        Belum ada schedule.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (isset($schedules) && method_exists($schedules, 'links'))
                    <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
                        {{ $schedules->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
