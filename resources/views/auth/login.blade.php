<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Masuk</h1>
    <p class="text-sm text-gray-500 mb-6">Selamat datang kembali</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mb-5">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500" name="remember">
                <span class="ms-2 text-sm text-gray-500">{{ __('Ingat saya') }}</span>
            </label>
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Masuk') }}
        </x-primary-button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-5">
        {{ __('Belum punya akun?') }}
        <a href="{{ route('register') }}" class="text-gray-800 font-medium underline underline-offset-2 hover:text-gray-600">
            {{ __('Daftar') }}
        </a>
    </p>
</x-guest-layout>