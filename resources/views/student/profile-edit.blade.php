@extends('layouts.student')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white shadow-md rounded-lg mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Profile</h2>

    <form id="editProfileForm" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <!-- Name -->
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-medium mb-1">Name</label>
            <input type="text" id="name" name="name"
                value="{{ old('name', $firebaseUser->name ?? '') }}"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
            <input type="email" id="email" name="email"
                value="{{ old('email', $firebaseUser->email ?? '') }}"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Phone -->
        <div class="mb-4">
            <label for="phone" class="block text-gray-700 font-medium mb-1">Phone Number</label>
            <input type="text" id="phone" name="phone"
                value="{{ old('phone', $firebaseUser->phone ?? '') }}"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Profile Image -->
        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Profile Image</label>

            @php
                use Illuminate\Support\Str;

                $imagePath = $firebaseUser->image ?? '';
                $imageUrl = '';

                if ($imagePath) {
                    if (Str::startsWith($imagePath, ['http', 'https'])) {
                        $imageUrl = $imagePath;
                    } elseif (Str::startsWith($imagePath, 'profile_images/')) {
                        $imageUrl = asset('storage/' . $imagePath);
                    } else {
                        $imageUrl = asset($imagePath);
                    }
                }
            @endphp

            <div class="my-2 flex flex-col items-center border border-gray-300 p-3 rounded">
                <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center overflow-hidden mb-2 relative">
                    <span id="previewText" class="text-gray-500 absolute {{ $imageUrl ? 'hidden' : '' }}">Preview</span>
                    <img id="imagePreview"
                        src="{{ $imageUrl }}"
                        alt="Preview"
                        class="h-full w-full object-cover {{ $imageUrl ? '' : 'hidden' }}" />
                </div>
                <button type="button" id="removeImageBtn"
                    class="{{ $imageUrl ? '' : 'hidden' }} bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                    Remove Image
                </button>
            </div>

            <input type="file" id="profile_image" name="profile_image" accept="image/*" class="mt-2">
            <input type="hidden" name="remove_image" id="removeImageField" value="0">
        </div>

        <hr class="my-6">

        <!-- Password fields -->
        <div class="mb-4">
            <label for="current_password" class="block text-gray-700 font-medium mb-1">
                Current Password <span class="text-sm text-gray-500">(required only when changing password)</span>
            </label>
            <input type="password" id="current_password" name="current_password"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-medium mb-1">New Password</label>
            <input type="password" id="password" name="password"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="block text-gray-700 font-medium mb-1">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Submit button with spinner -->
        <button type="submit" id="saveBtn"
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition flex items-center justify-center gap-2">
            <svg id="spinner" class="hidden w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                </path>
            </svg>
            <span id="saveText">Save Changes</span>
        </button>

        <div id="formMessage" class="mt-4"></div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editProfileForm');
    const messageDiv = document.getElementById('formMessage');
    const profileImageInput = document.getElementById('profile_image');
    const imagePreview = document.getElementById('imagePreview');
    const previewText = document.getElementById('previewText');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const removeImageField = document.getElementById('removeImageField');
    const saveBtn = document.getElementById('saveBtn');
    const spinner = document.getElementById('spinner');
    const saveText = document.getElementById('saveText');

    // Image preview handler
    profileImageInput?.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
                previewText.classList.add('hidden');
                removeImageBtn.classList.remove('hidden');
                removeImageField.value = 0;
            };
            reader.readAsDataURL(file);
        }
    });

    // Remove image handler
    removeImageBtn?.addEventListener('click', function() {
        imagePreview.src = '';
        imagePreview.classList.add('hidden');
        previewText.classList.remove('hidden');
        profileImageInput.value = '';
        removeImageField.value = 1;
        removeImageBtn.classList.add('hidden');
    });

    // AJAX form submission
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        // Show spinner
        spinner.classList.remove('hidden');
        saveText.textContent = 'Saving...';
        saveBtn.disabled = true;
        messageDiv.innerHTML = '';
        messageDiv.className = 'text-gray-600';

        try {
            const response = await fetch("{{ route('student.profile.update') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'PATCH'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                messageDiv.innerHTML = data.success;
                messageDiv.className = 'text-green-600 font-medium';
            } else if (data.errors) {
                const errors = Object.values(data.errors).flat().join('<br>');
                messageDiv.innerHTML = errors;
                messageDiv.className = 'text-red-600 font-medium';
            } else {
                messageDiv.innerHTML = 'An unexpected error occurred.';
                messageDiv.className = 'text-red-600 font-medium';
            }

        } catch (error) {
            console.error(error);
            messageDiv.innerHTML = 'Network error. Please try again.';
            messageDiv.className = 'text-red-600 font-medium';
        } finally {
            // Hide spinner
            spinner.classList.add('hidden');
            saveText.textContent = 'Save Changes';
            saveBtn.disabled = false;
        }
    });
});
</script>
@endpush
@endsection
