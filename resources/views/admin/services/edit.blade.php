@extends('layouts.admin')

@section('title', 'Edit Service')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">Home</a> / Service
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Service</h2>

    <!-- Service Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-4">Service Information</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" value="{{ old('title', $service->title) }}"
                        class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Service title here..">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="body" rows="5" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Full service description...">{{ old('body', $service->body) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                    <textarea id="excerpt" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100" disabled>{{ old('excerpt', $service->excerpt) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4">
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold text-white bg-blue-600 -mx-6 -mt-6 p-3 rounded-t">
                    Service Details
                </h3>

                <div class="bg-white shadow rounded p-6">
                    <div class="mb-4 border border-gray-300 p-4 flex flex-col items-center">
                        <img 
                            id="imagePreview" 
                            src="{{ $service->image ? asset('storage/' . $service->image) : 'https://via.placeholder.com/200x150?text=No+image+available' }}"
                            alt="Preview" 
                            class="w-40 h-auto mb-3"
                        >

                        @if($service->image)
                            <button 
                                type="button" 
                                id="removeImageBtn" 
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition"
                            >
                                Remove Image
                            </button>
                        @endif
                    </div>

                    <input type="file" id="image" class="mb-4">
                    <input type="hidden" id="remove_image" name="remove_image" value="0">

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="1" {{ $service->status == 1 ? 'selected' : '' }}>PUBLISHED</option>
                            <option value="0" {{ $service->status == 0 ? 'selected' : '' }}>DRAFT</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <button id="updateService" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            Update Service
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass serviceId to JavaScript -->
<script>
    const updateUrl = "{{ route('admin.services.update', $service->id) }}";
    const csrfToken = "{{ csrf_token() }}";
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const bodyInput = document.getElementById("body");
    const excerptInput = document.getElementById("excerpt");
    const updateBtn = document.getElementById("updateService");

    // Auto-generate excerpt
    bodyInput.addEventListener("input", function () {
        const text = this.value.trim();
        let excerpt = text.substring(0, 50);
        if (text.length > 50) excerpt += "...";
        excerptInput.value = excerpt;
    });

    // SweetAlert confirmation before updating
    updateBtn.addEventListener("click", function () {
        Swal.fire({
            title: 'Update Service?',
            text: "This will update the service details.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append("_token", csrfToken);
                formData.append("_method", "PUT");
                formData.append("title", document.getElementById("title").value);
                formData.append("body", document.getElementById("body").value);
                formData.append("excerpt", document.getElementById("excerpt").value);
                formData.append("status", document.getElementById("status").value);
                formData.append("remove_image", document.getElementById("remove_image").value);

                if (document.getElementById("image").files[0]) {
                    formData.append("image", document.getElementById("image").files[0]);
                }
                fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(async response => {
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        throw new Error('Invalid JSON response from server');
                    }

                    if (!response.ok) {
                        if (data.errors) {
                            const errors = Object.values(data.errors).flat().join('\n');
                            Swal.fire({ icon: 'error', title: 'Validation Error', text: errors });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Something went wrong' });
                        }
                        return;
                    }

                    Swal.fire({ icon: 'success', title: 'Updated!', text: data.message })
                        .then(() => window.location.href = data.redirect);
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message });
                });
            }
        });
    });
});

// Remove image logic
document.addEventListener("DOMContentLoaded", function () {
    const removeBtn = document.getElementById("removeImageBtn");
    const preview = document.getElementById("imagePreview");
    const removeImageInput = document.getElementById("remove_image");

    if (removeBtn) {
        removeBtn.addEventListener("click", function () {
            Swal.fire({
                title: 'Remove Image?',
                text: "This will delete the current featured image.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    preview.src = "https://via.placeholder.com/200x150?text=No+image+available";
                    removeImageInput.value = "1";
                    document.getElementById("image").value = "";
                    removeBtn.remove();
                }
            });
        });
    }
});

// Live image preview
document.getElementById("image").addEventListener("change", function (event) {
    const file = event.target.files[0];
    const preview = document.getElementById("imagePreview");

    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endsection
