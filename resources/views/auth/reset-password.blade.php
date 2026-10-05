<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-800">Reset password</h2>
        <p class="text-sm text-gray-500 mt-1">Choose a new secure password for your account</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" class="text-sm font-semibold text-gray-700" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('New Password')" class="text-sm font-semibold text-gray-700" />
            <x-text-input id="password" class="block mt-1 w-full border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters" />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-sm font-semibold text-gray-700" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="Re-enter password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" class="w-full flex justify-center items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mt-6">
            {{ __('Reset Password') }}
        </button>
    </form>
</x-guest-layout>
