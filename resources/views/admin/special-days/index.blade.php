@extends('layouts.admin')

@section('title', 'Manage Holidays / No Office Days')

@section('content')
<div class="bg-white p-6 rounded shadow w-full">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold">Special Days</h2>
        <a href="{{ route('admin.special-days.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
            Add Special Day
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full table-auto border border-gray-300 text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="w-[150px] px-4 py-2 border text-left">Date</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Title</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Type</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Status</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-800 text-center">
                @forelse($specialDays as $day)
                    <tr class="{{ $loop->odd ? 'bg-white' : 'bg-gray-50' }} hover:bg-blue-50 transition">
                        <td class="px-4 py-2 border">
                            {{ isset($day['date']) ? \Carbon\Carbon::parse($day['date'])->format('F j, Y') : 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border">{{ $day['title'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border capitalize">{{ $day['type'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border capitalize">
                            <span class="{{ ($day['status'] ?? '') === 'active' ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                {{ ucfirst($day['status'] ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border space-x-2">
                            <a href="{{ route('admin.special-days.edit', $day['id']) }}"
                               class="inline-block bg-green-200 hover:bg-green-600 text-black font-bold px-4 py-1 rounded-full text-center">
                                Edit
                            </a>
                            <button type="button"
                                    data-url="{{ route('admin.special-days.destroy', $day['id']) }}"
                                    class="delete-btn inline-block bg-red-500 hover:bg-red-700 text-black font-bold px-4 py-1 rounded-full text-center">
                                Trash
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">No special days found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const row = this.closest('tr'); // Get the table row

            Swal.fire({
                title: 'Are you sure?',
                text: "This special day will be moved to Trash.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, move to Trash!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Trashed!',
                            text: data.message || 'Special day has been moved to Trash.',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // ✅ Remove row without reloading page
                        row.classList.add('opacity-50', 'transition', 'duration-300');
                        setTimeout(() => row.remove(), 300);
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong. Please try again.'
                        });
                    });
                }
            });
        });
    });
});
</script>
@endpush

