@extends('layouts.admin')

@section('title', 'Add Service')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.services.index') }}" class="text-blue-600 hover:underline">Home</a> / Service
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Add Service</h2>

    <!-- Service Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-4">Service Information</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" id="title" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2" placeholder="Service title here..">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="body" rows="5" class="w-full border border-gray-300 rounded px-3 py-2" placeholder="Full service description..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Excerpt</label>
                    <textarea id="excerpt" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100" disabled></textarea>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded p-6">
                <h3 class="text-lg font-semibold text-white bg-blue-600 -mx-6 -mt-6 p-3 rounded-t">
                    Service Details
                </h3>

                <div class="bg-white shadow rounded p-6">
                    <input type="file" id="image" name="image" class="mb-4" accept="image/*" onchange="previewServiceImage(event)">

                    <div class="border border-gray-300 p-4 flex flex-col justify-center items-center gap-2">
                        <img id="preview" 
                            src="{{ isset($service) && $service->image ? asset($service->image) : 'https://via.placeholder.com/200x150?text=No+image+available' }}" 
                            alt="Preview" 
                            class="w-40 h-auto rounded border">
                        
                        <button type="button" id="removeImageBtn" 
                                class="hidden bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                                onclick="removeServiceImage()">
                            Remove Image
                        </button>
                    </div>

                    <p class="text-sm text-red-600 mb-2 items-center">Note: Image size must be: W-???px H-???px</p>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="1">PUBLISHED</option>
                            <option value="0">DRAFT</option>
                        </select>
                    </div>

                    <div class="mt-6">
                        <button id="saveService" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                            Publish Service
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const bodyInput = document.getElementById("body");
    const excerptInput = document.getElementById("excerpt");
    const saveBtn = document.getElementById("saveService");

    // Auto-generate excerpt
    bodyInput.addEventListener("input", function () {
        const text = this.value.trim();
        let excerpt = text.substring(0, 50);
        if (text.length > 50) excerpt += "...";
        excerptInput.value = excerpt;
    });

    saveBtn.addEventListener("click", function () {
        Swal.fire({
            title: 'Publish Service?',
            text: "This will create a new service entry.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, publish it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            let formData = new FormData();
            formData.append("title", document.getElementById("title").value);
            formData.append("body", document.getElementById("body").value);
            formData.append("status", document.getElementById("status").value);
            if (document.getElementById("image").files[0]) {
                formData.append("image", document.getElementById("image").files[0]);
            }

            fetch("{{ route('admin.services.store') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                if (!res.ok) {
                    const errorData = await res.json();
                    throw errorData;
                }
                return res.json();
            })
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Created!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "{{ route('admin.services.index') }}";
                });
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: err.message || 'Something went wrong while creating.'
                });
            });
        });
    });
});
</script>
@endsection
