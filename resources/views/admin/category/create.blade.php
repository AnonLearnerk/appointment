@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.categories.index') }}" class="text-blue-600 hover:underline">Home</a> / New Category
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Add Category</h2>

    <div id="category-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        @csrf

        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Title -->
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-4">Category Info</h3>
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Title here.." required>
                    <p class="text-sm text-gray-500 mt-1">The name is how it appears on your site.</p>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-4">Description</h3>
                <textarea name="body" rows="5" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Write description here..."></textarea>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold text-white bg-blue-600 -mx-6 -mt-6 p-3 rounded-t">
                    Category Details
                </h3>

                <!-- Featured Image -->
                <div class="bg-white shadow rounded p-6">
                    <input type="file" name="image" id="imageInput" class="mb-4">

                    <div class="border border-gray-300 p-4 flex flex-col items-center">
                        <img id="imagePreview" 
                            src="https://via.placeholder.com/200x150?text=No+image+available" 
                            alt="Preview" 
                            class="w-40 h-auto mb-2">

                        <button type="button" 
                                id="removeImageBtn" 
                                class="hidden bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                            Remove Image
                        </button>
                    </div>
                    
                     <!-- Status -->
                    <div class="mt-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="PUBLISHED" selected>PUBLISHED</option>
                            <option value="DRAFT">DRAFT</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button" id="publish-btn"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Publish
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const publishBtn = document.getElementById('publish-btn');

    publishBtn.addEventListener('click', function () {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to create this category?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('title', document.getElementById('title').value);
                formData.append('body', document.querySelector('textarea[name="body"]').value);
                formData.append('status', document.getElementById('status').value);

                const imageInput = document.querySelector('input[name="image"]');
                if (imageInput.files.length > 0) {
                    formData.append('image', imageInput.files[0]);
                }

                fetch("{{ route('admin.categories.store') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async res => {
                    let responseData;
                    try {
                        responseData = await res.json();
                        console.log('Response Data:', responseData);  // <--- add this
                    } catch {
                        console.error('Failed to parse JSON');
                        responseData = {};
                    }

                    if (res.status === 422) {
                        // Laravel validation errors
                        if (responseData.errors?.title?.[0] === 'This category already exists.') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Duplicate Category',
                                text: responseData.errors.title[0]
                            });
                        } else {
                            const messages = Object.values(responseData.errors).flat().join('\n');
                            Swal.fire({
                                icon: 'error',
                                title: 'Notice',
                                text: messages
                            });
                        }
                        return;
                    }

                    if (!res.ok) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: responseData.message || 'Something went wrong.'
                        });
                        return;
                    }

                    if (responseData?.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Category Created!',
                            text: responseData.message || 'The category has been successfully created.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                            allowOutsideClick: false
                        }).then(() => {
                            window.location.href = "{{ route('admin.categories.index') }}";
                        });
                    }
                })
                .catch(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Unable to connect to the server. Please try again later.'
                    });
                });
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const placeholder = "https://via.placeholder.com/200x150?text=No+image+available";

    // Show preview when a file is selected
    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                removeImageBtn.classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        } else {
            resetImage();
        }
    });

    // Remove image and reset file input
    removeImageBtn.addEventListener('click', function () {
        imageInput.value = '';
        resetImage();
    });

    function resetImage() {
        imagePreview.src = placeholder;
        removeImageBtn.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
