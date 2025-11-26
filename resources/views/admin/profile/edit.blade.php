@extends('layouts.admin')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-md mt-6">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Edit Profile</h2>

    @if (session('status'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded mb-6">
            {{ session('status') }}
        </div>
    @endif

    <!-- Name -->
    <div class="mb-4">
        <label for="name" class="block text-sm font-medium text-gray-700">*Full Name</label>
        <input id="name" name="name" type="text"
               class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
               value="{{ old('name', $user['name'] ?? '') }}" required>
        @error('name')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Email -->
    <div class="mb-4">
        <label for="email" class="block text-sm font-medium text-gray-700">*Email Address</label>
        <input id="email" name="email" type="email"
            oninput="this.value = this.value.toLowerCase()" style="text-transform: lowercase;"
            class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            value="{{ old('email', strtolower($user['email'] ?? '')) }}" required>
        @error('email')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Phone -->
    <div class="mb-4">
        <label for="phone" class="block text-sm font-medium text-gray-700">*Phone Number</label>
        <input id="phone" name="phone" type="text"
            class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            value="{{ old('phone', $user['phone'] ?? '') }}" pattern="[0-9]+" title="Phone number must be numeric" required>
        @error('phone')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Divider -->
    <hr class="my-6">

    <!-- Password Update Section -->
    <h3 class="text-xl font-semibold text-gray-700">Change Password</h3>

    <!-- Current Password -->
    <div class="mb-4">
        <label for="current_password" class="block text-sm font-medium text-gray-700">*Current Password</label>
        <div class="relative mt-2">
            <input id="current_password" name="current_password" type="password"
                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button type="button" onclick="togglePassword('current_password')" 
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                <svg id="icon_current_password" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7
                          a10.056 10.056 0 012.97-4.37M6.235 6.235A9.963 9.963 0 0112 5
                          c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.113 5.215M3 3l18 18"/>
                </svg>
            </button>
        </div>
        @error('current_password')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- New Password -->
    <div class="mb-4">
        <label for="password" class="block text-sm font-medium text-gray-700">*New Password</label>
        <div class="relative mt-2">
            <input id="password" name="password" type="password"
                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button type="button" onclick="togglePassword('password')" 
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                <svg id="icon_password" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7
                          a10.056 10.056 0 012.97-4.37M6.235 6.235A9.963 9.963 0 0112 5
                          c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.113 5.215M3 3l18 18"/>
                </svg>
            </button>
        </div>
        @error('password')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Confirm New Password -->
    <div class="mb-4">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">*Confirm New Password</label>
        <div class="relative mt-2">
            <input id="password_confirmation" name="password_confirmation" type="password"
                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <button type="button" onclick="togglePassword('password_confirmation')" 
                class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-700">
                <svg id="icon_password_confirmation" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7
                          a10.056 10.056 0 012.97-4.37M6.235 6.235A9.963 9.963 0 0112 5
                          c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.113 5.215M3 3l18 18"/>
                </svg>
            </button>
        </div>
        @error('password_confirmation')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Save Button -->
    <div class="pt-4">
        <button id="saveProfileButton"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-md shadow transition w-full sm:w-auto">
            Save Changes
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById('icon_' + fieldId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
        } else {
            input.type = 'password';
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.056 10.056 0 012.97-4.37M6.235 6.235A9.963 9.963 0 0112 5
                      c4.478 0 8.268 2.943 9.542 7a10.05 10.05 0 01-4.113 5.215M3 3l18 18"/>`;
        }
    }

    document.getElementById('saveProfileButton').addEventListener('click', async function () {
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const current_password = document.getElementById('current_password').value;
        const password = document.getElementById('password').value;
        const password_confirmation = document.getElementById('password_confirmation').value;

        if (password && !current_password) {
            Swal.fire({
                icon: 'error',
                title: 'Current Password Required',
                text: 'Please enter your current password to set a new one.'
            });
            return;
        }

        try {
            const response = await fetch("{{ route('admin.profile.update') }}", {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    name,
                    email,
                    phone,
                    current_password,
                    password,
                    password_confirmation
                })
            });

            const result = await response.json();

            // Clear password fields always
            document.getElementById('current_password').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';

            if (response.ok && result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: result.message || 'Profile updated successfully!',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                if (result.errors) {
                    let messages = Object.values(result.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Errors',
                        html: messages,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'An unexpected error occurred.',
                    });
                }
            }
        } catch (error) {
            console.error("Error:", error);
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong while saving.',
            });
        }
    });
</script>
@endpush
