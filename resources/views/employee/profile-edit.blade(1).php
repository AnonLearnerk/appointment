@extends('layouts.employee')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-xl mx-auto p-6 bg-white shadow-md rounded-lg mt-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Edit Profile</h2>

    <div id="message"></div>

    <!-- Name -->
    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Name</label>
        <input type="text" id="name" value="{{ $user->name ?? '' }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Email -->
    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" id="email" value="{{ $user->email ?? '' }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Profile Image -->
    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Profile Image</label>
        <div class="my-2 flex flex-col items-center border border-gray-300 p-3 rounded">
            <img 
                id="imagePreview"
                src="{{ !empty($user->image) ? asset('storage/'.$user->image) : 'https://via.placeholder.com/200x150?text=No+Image' }}"
                alt="Profile Image Preview"
                class="h-32 w-32 object-cover rounded-full border-4 border-indigo-200 shadow-md mb-3"
            />

            <button type="button"
                    id="removeImageBtn"
                    class="{{ !empty($user->image) ? '' : 'hidden' }} bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                Remove Image
            </button>
        </div>
        <input type="file" id="profile_image" accept="image/*" class="mt-2">
        <input type="hidden" name="remove_image" id="removeImageField" value="0">
    </div>

    <!-- Divider -->
    <hr class="my-6">
    
    <!-- Current Password -->
    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Current Password</label>
        <input type="password" id="current_password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- New Password -->
    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">New Password</label>
        <input type="password" id="password" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <!-- Confirm New Password -->
    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-1">Confirm New Password</label>
        <input type="password" id="password_confirmation" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <button id="saveChanges" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
        Save Changes
    </button>
</div>

<script>
    const saveBtn = document.getElementById('saveChanges');
    const imagePreview = document.getElementById('imagePreview');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const profileImageInput = document.getElementById('profile_image');
    const removeImageField = document.getElementById('removeImageField');
    const removeImageBtn = document.getElementById('removeImageBtn');

    saveBtn.addEventListener('click', async () => {
        // Disable button while saving
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';

        const formData = new FormData();
        formData.append('current_password', document.getElementById('current_password').value);
        formData.append('name', nameInput.value);
        formData.append('email', emailInput.value);
        formData.append('password', document.getElementById('password').value);
        formData.append('password_confirmation', document.getElementById('password_confirmation').value);

        const profileImage = profileImageInput.files[0];
        if (profileImage) formData.append('profile_image', profileImage);
        formData.append('remove_image', removeImageField.value);

        try {
            const res = await fetch('{{ route("employee.profile.update") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await res.json();

            if (res.ok) {
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated!',
                    text: data.success,
                    timer: 1800,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                // ✅ Update displayed profile info dynamically
                if (data.user) {
                    if (data.user.image_url) {
                        imagePreview.src = data.user.image_url;
                        removeImageBtn.classList.remove('hidden');
                        removeImageField.value = 0;
                    } else {
                        imagePreview.src = 'https://via.placeholder.com/200x150?text=No+Image';
                        removeImageBtn.classList.add('hidden');
                    }
                    nameInput.value = data.user.name;
                    emailInput.value = data.user.email;
                }

                // Clear sensitive fields
                document.getElementById('current_password').value = '';
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
                profileImageInput.value = '';

            } else if (res.status === 422) {
                let errorMessages = '';
                Object.values(data.errors).forEach(errArr => {
                    errArr.forEach(err => errorMessages += err + '\n');
                });
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: errorMessages,
                    customClass: { popup: 'whitespace-pre-line' }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong. Please try again later.'
                });
            }

        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: 'Could not reach the server.'
            });
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save Changes';
        }
    });

    // --- Image Preview Logic ---
    profileImageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                imagePreview.classList.remove('hidden');
                removeImageBtn.classList.remove('hidden');
                removeImageField.value = 0;
            }
            reader.readAsDataURL(file);
        }
    });

    // --- Remove Image Logic ---
    removeImageBtn.addEventListener('click', function () {
        imagePreview.src = 'https://via.placeholder.com/200x150?text=No+Image';
        profileImageInput.value = '';
        removeImageField.value = 1;
        removeImageBtn.classList.add('hidden');
    });
</script>

 
@endsection
