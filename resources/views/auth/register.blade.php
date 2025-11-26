@extends('layouts.guest')

@section('title', 'Register Page')

@section('slot')

<style>
    body {
        margin: 0;
    }

    .background-blur {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('{{ asset('img/bg.jpg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: blur(8px);
        z-index: -1;
    }

    .form-container {
        position: relative;
        z-index: 10;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 1rem;
    }

    .login-box {
        backdrop-filter: blur(8px); /* Optional: subtle blur inside box */
        background-color: rgba(255, 255, 255, 0.85); /* Semi-transparent white */
    }
</style>

<!-- Blurred Background -->
<div class="background-blur"></div>

<div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <a href="/">
            <img src="{{ asset('img/logoo.png') }}" alt="CTU Logo" class="h-16 w-16 object-contain">
        </a>
    </div>

    <!-- Title -->
    <h2 class="text-2xl font-bold text-center text-indigo-600 mb-6">Register New Account</h2>
    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name">
                <span class="text-gray-500">*</span><span class="text-black">Name</span>
            </x-input-label>
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email -->
        <div>
            <x-input-label for="email">
                <span class="text-gray-500">*</span><span class="text-black">Email</span>
            </x-input-label>
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>


        <!-- Phone Number -->
        <div>
            <x-input-label for="phone">
                <span class="text-gray-500">*</span><span class="text-black">Phone Number</span>
            </x-input-label>
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone"
                          :value="old('phone')" required pattern="^09\d{9}$" maxlength="11"
                          title="Phone number must start with 09 and be 11 digits long" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="relative">
            <x-input-label for="password">
                <span class="text-gray-500">*</span><span class="text-black">Password</span>
            </x-input-label>
            <x-text-input id="password" class="block mt-1 w-full pr-10"
              type="password"
              name="password"
              required autocomplete="new-password"
              pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$"
              title="Password must be at least 8 characters, include an uppercase letter, a number, and a special character." />


            <!-- Eye Icon -->
            <button type="button" onclick="togglePassword('password')" 
                class="absolute top-9 right-3 text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Toggle Password Visibility">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                </svg>
            </button>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="relative">
            <x-input-label for="password_confirmation">
                <span class="text-gray-500">*</span><span class="text-black">Confirm Password</span>
            </x-input-label>
            <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10"
                          type="password"
                          name="password_confirmation" required autocomplete="new-password" />

            <!-- Eye Icon -->
            <button type="button" onclick="togglePassword('password_confirmation')" 
                class="absolute top-9 right-3 text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="Toggle Confirm Password Visibility">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                </svg>
            </button>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-between">
            <a class="underline text-sm text-indigo-600 hover:text-indigo-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button>
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>

<!-- Password Toggle Script -->
<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    }
</script>
@endsection
