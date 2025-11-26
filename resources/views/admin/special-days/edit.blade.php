@extends('layouts.admin')

@section('title', 'Edit Special Day')

@section('content')
<div class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-lg space-y-6">
    <h2 class="text-xl font-semibold mb-4">Edit Holiday / No Office Day</h2>

    <div class="space-y-1">
        <label class="block font-semibold mb-1">Date</label>
        <input type="date" id="date" 
               value="{{ $specialDay['date'] ?? '' }}" 
               class="w-full border px-3 py-2 rounded" required>
    </div>

    <div class="space-y-1">
        <label class="block font-semibold mb-1">Title</label>
        <input type="text" id="title" 
               value="{{ $specialDay['title'] ?? '' }}" 
               class="w-full border px-3 py-2 rounded" required>
    </div>

    <div class="space-y-1">
        <label class="block font-semibold mb-1">Type</label>
        <select id="type" class="w-full border px-3 py-2 rounded" required>
            <option value="holiday" {{ ($specialDay['type'] ?? '') === 'holiday' ? 'selected' : '' }}>Holiday</option>
            <option value="no_office" {{ ($specialDay['type'] ?? '') === 'no_office' ? 'selected' : '' }}>No Office</option>
        </select>
    </div>

    <div class="space-y-1">
        <label class="block font-semibold mb-1">Status</label>
        <select id="status" class="w-full border px-3 py-2 rounded" required>
            <option value="active" {{ ($specialDay['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ ($specialDay['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div class="flex justify-end">
        <button id="updateSpecialDay" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Update
        </button>
    </div>

    <p class="text-sm text-red-600 leading-relaxed bg-red-50 p-3 rounded-md border border-red-200">
        <strong>Note:</strong> Please reach out to the school administrator for calendar.<br>
        Active Holidays won't allow students to book on that specific date!
    </p>
</div>

<script>
document.getElementById('updateSpecialDay').addEventListener('click', async function () {
    const date = document.getElementById('date').value;
    const title = document.getElementById('title').value;
    const type = document.getElementById('type').value;
    const status = document.getElementById('status').value;

    try {
        const response = await fetch(`/admin/special-days/{{ $id }}`, {
            method: "POST", // Use POST + spoofed PUT
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-HTTP-Method-Override": "PUT", // Tells Laravel it's a PUT
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ date, title, type, status })
        });

        const data = await response.json();

        if (!response.ok) {
            let errors = Object.values(data.errors || { message: [data.message] })
                .flat()
                .join('<br>');

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errors,
                confirmButtonColor: '#d33'
            });
        } else {
            Swal.fire({
                icon: 'success',
                title: 'Updated Successfully',
                text: data.message,
                confirmButtonColor: '#3085d6',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = "{{ route('admin.special-days.index') }}";
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong. Please try again later.',
            confirmButtonColor: '#d33'
        });
    }
});
</script>
@endsection
