<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Buat akun</h1>
    <p class="text-sm text-gray-500 mb-6">Isi data di bawah untuk mendaftar</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-4">
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mb-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mb-5">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center">
            {{ __('Daftar') }}
        </x-primary-button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-5">
        {{ __('Sudah punya akun?') }}
        <a href="{{ route('login') }}" class="text-gray-800 font-medium underline underline-offset-2 hover:text-gray-600">
            {{ __('Masuk') }}
        </a>
    </p>
</x-guest-layout>