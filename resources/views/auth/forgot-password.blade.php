<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Lupa password</h1>
    <p class="text-sm text-gray-500 mb-6">Masukkan email untuk menerima link reset password.</p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-5">
            <x-primary-button class="w-full justify-center">
                Kirim Link Reset
            </x-primary-button>
        </div>
    </form>

    <p class="text-center text-sm text-gray-500 mt-5">
        <a href="{{ route('login') }}" class="text-gray-800 font-medium underline underline-offset-2 hover:text-gray-600">
            Kembali ke login
        </a>
    </p>
</x-guest-layout>
