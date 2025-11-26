@extends('layouts.admin')

@section('title', 'Trashed Special Days')

@section('content')
<div class="w-full bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Trashed Special Days</h2>
        <a href="{{ route('admin.special-days.index') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
           Back to Special Days
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($specialDays->count() > 0)
        <div class="overflow-x-auto w-full">
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-gray-100 text-gray-700 font-semibold">
                    <tr>
                        <th class="px-4 py-2 border text-left">#</th>
                        <th class="px-4 py-2 border text-left">Date</th>
                        <th class="px-4 py-2 border text-left">Title</th>
                        <th class="px-4 py-2 border text-left">Type</th>
                        <th class="px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 text-center">
                    @foreach($specialDays as $day)
                        <tr id="row-{{ $day['id'] }}" class="border-t hover:bg-gray-50 transition">
                            <td class="px-4 py-2 border">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 border">
                                {{ isset($day['date']) ? \Carbon\Carbon::parse($day['date'])->format('F j, Y') : 'N/A' }}
                            </td>
                            <td class="px-4 py-2 border">{{ $day['title'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border capitalize">{{ $day['type'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border space-x-2">
                                <button type="button"
                                        data-id="{{ $day['id'] }}"
                                        class="restore-btn inline-block bg-green-200 hover:bg-green-600 text-black font-bold px-4 py-1 rounded-full">
                                    Restore
                                </button>

                                <button type="button"
                                        data-id="{{ $day['id'] }}"
                                        class="force-delete-btn inline-block bg-red-500 hover:bg-red-700 text-black font-bold px-4 py-1 rounded-full">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white p-6 rounded shadow text-center text-gray-500">
            No trashed special days found.
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = '{{ csrf_token() }}';
    const restoreTemplate = '{{ route("admin.special-days.restore", ":id") }}';
    const forceDeleteTemplate = '{{ route("admin.special-days.forceDelete", ":id") }}';

    const buildUrl = (template, id) => template.replace(':id', id);

    // ✅ Restore Special Day
    document.querySelectorAll('.restore-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const url = buildUrl(restoreTemplate, id);

            Swal.fire({
                title: 'Restore this special day?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, restore'
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Special day restored successfully!',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // ✅ Fade out then remove
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.classList.add('opacity-50', 'transition', 'duration-300');
                        setTimeout(() => row.remove(), 300);
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Could not restore. Try again.', 'error');
                });
            });
        });
    });

    // ✅ Force Delete
    document.querySelectorAll('.force-delete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const url = buildUrl(forceDeleteTemplate, id);

            Swal.fire({
                title: 'Permanently delete?',
                text: "This cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete permanently'
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message || 'Special day permanently deleted!',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // ✅ Fade out then remove
                    const row = document.getElementById('row-' + id);
                    if (row) {
                        row.classList.add('opacity-50', 'transition', 'duration-300');
                        setTimeout(() => row.remove(), 300);
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Could not delete. Try again.', 'error');
                });
            });
        });
    });
});
</script>
@endpush
@endsection
