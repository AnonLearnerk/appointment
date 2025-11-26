@extends('layouts.admin')

@section('title', 'Add Special Day')

@section('content')
<div class="max-w-xl mx-auto bg-white p-8 rounded-2xl shadow-lg space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Add Holiday / No Office Day</h2>

    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Date</label>
        <input type="date" id="date" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
    </div>

    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Title</label>
        <input type="text" id="title" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
    </div>

    <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Type</label>
        <select id="type" class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
            <option value="holiday">Holiday</option>
            <option value="no_office">No Office</option>
        </select>
    </div>

    <div class="space-y-1">
        <label class="block font-semibold mb-1">Status</label>
        <select id="status" name="status" class="w-full border px-3 py-2 rounded" required>
            <option value="active" {{ old('status', $specialDay->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $specialDay->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div class="flex justify-end">
        <button id="saveSpecialDay" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg shadow-sm transition">
            Add
        </button>
    </div>

    <p class="text-sm text-red-600 leading-relaxed bg-red-50 p-3 rounded-md border border-red-200">
        <strong>Note:</strong> Please reach out to the school administrator for calendar.<br>
        Active Holidays won't allow students to book on that specific date!
    </p>
</div>

<script>
document.getElementById('saveSpecialDay').addEventListener('click', async function () {
    const date = document.getElementById('date').value;
    const title = document.getElementById('title').value;
    const type = document.getElementById('type').value;
    const status = document.getElementById('status').value; // ✅ now works

    try {
        const response = await fetch("{{ route('admin.special-days.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ date, title, type, status }) // ✅ include status
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
                title: 'Success',
                text: data.message,
                confirmButtonColor: '#3085d6',
                timer: 1500,
                showConfirmButton: false
            });

            // Reset form
            document.getElementById('date').value = '';
            document.getElementById('title').value = '';
            document.getElementById('type').value = 'holiday';
            document.getElementById('status').value = 'active'; // ✅ reset to default
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
