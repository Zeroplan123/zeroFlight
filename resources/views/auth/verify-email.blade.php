<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Verifikasi email</h1>
    <p class="text-sm text-gray-500 mb-6">
        Cek inbox email kamu, lalu klik link verifikasi. Jika belum menerima email, kamu bisa kirim ulang.
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            Link verifikasi baru sudah dikirim ke email kamu.
        </div>
    @endif

    <div class="mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button class="w-full justify-center">
                Kirim Ulang Email Verifikasi
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit"
                class="w-full mt-3 inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
