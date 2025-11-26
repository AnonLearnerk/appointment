@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.categories.index') }}" class="text-blue-600 hover:underline">Home</a> / Edit Category
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Category</h2>

    <div id="category-form" class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        @csrf

        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Title -->
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-4">Category Info</h3>
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" name="title"
                        value="{{ old('title', $category->title ?? '') }}"
                        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2"
                        placeholder="Title here.." required>
                    <p class="text-sm text-gray-500 mt-1">The name is how it appears on your site.</p>
                </div>
            </div>

            <!-- Description -->
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-4">Description</h3>
                <textarea id="body" name="body" rows="5"
                    class="w-full border border-gray-300 rounded px-3 py-2"
                    placeholder="Write description here...">{{ old('body', $category->body ?? '') }}</textarea>
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
                            src="{{ !empty($category->image) ? asset($category->image) : 'https://via.placeholder.com/200x150?text=No+image+available' }}"
                            alt="Preview"
                            class="w-40 h-auto mb-2">

                        <button type="button" id="removeImageBtn"
                            class="hidden bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                            Remove Image
                        </button>
                    </div>

                    <input type="hidden" name="remove_image" id="removeImageField" value="0">

                    <p class="text-sm text-red-600 mb-2">Note: Image size must be: W-???px H-???px</p>

                    <!-- Status -->
                    <div class="mt-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="PUBLISHED" {{ ($category->status ?? '') === 'PUBLISHED' ? 'selected' : '' }}>PUBLISHED</option>
                            <option value="DRAFT" {{ ($category->status ?? '') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="button" id="updateCategoryBtn"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Update Category
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('updateCategoryBtn').addEventListener('click', function () {
    const formData = new FormData();
    formData.append('_method', 'PUT');
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('title', document.getElementById('title').value);
    formData.append('body', document.getElementById('body').value);
    formData.append('status', document.getElementById('status').value);
    formData.append('remove_image', document.getElementById('removeImageField').value);

    const imageInput = document.getElementById('imageInput');
    if (imageInput.files[0]) {
        formData.append('image', imageInput.files[0]);
    }

    fetch("{{ route('admin.categories.update', $category->id) }}", {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData
    })
    .then(async response => {
        let data = {};
        try { data = await response.json(); } catch (e) {}
        if (response.status === 422) {
            const errors = Object.values(data.errors || {}).flat().join('\n');
            return Swal.fire({ icon: 'error', title: 'Validation Error', text: errors });
        }
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: data.message || 'Category updated successfully.',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "{{ route('admin.categories.index') }}";
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Something went wrong.' });
        }
    })
    .catch(() => {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Unexpected error occurred.' });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImageBtn');
    const removeImageField = document.getElementById('removeImageField');
    const placeholder = "https://via.placeholder.com/200x150?text=No+image+available";

    if (imagePreview.src && !imagePreview.src.includes('placeholder.com')) {
        removeImageBtn.classList.remove('hidden');
    }

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                imagePreview.src = e.target.result;
                removeImageBtn.classList.remove('hidden');
                removeImageField.value = "0";
            }
            reader.readAsDataURL(file);
        }
    });

    removeImageBtn.addEventListener('click', function () {
        imageInput.value = '';
        imagePreview.src = placeholder;
        removeImageBtn.classList.add('hidden');
        removeImageField.value = "1";
    });
});
</script>
@endpush
@endsection
