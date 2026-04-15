<x-guest-layout>
    <h1 class="text-xl font-semibold text-gray-800 mb-1">Konfirmasi password</h1>
    <p class="text-sm text-gray-500 mb-6">Masukkan password untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-5">
            <x-primary-button class="w-full justify-center">
                Konfirmasi
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
