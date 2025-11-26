@extends('layouts.admin')

@section('title', 'All Users')

@section('content')
<div class="bg-white p-6 rounded shadow w-full">
    <div class="flex justify-between items-center mb-4">
        <!-- Left: Title -->
        <h2 class="text-xl font-semibold whitespace-nowrap">All Users</h2>

        <!-- Middle: Filters -->
        <div class="flex flex-wrap gap-2 sm:gap-4">
            <input id="searchInput" type="text" placeholder="Search name or email..." value="{{ request('search') }}" class="px-3 py-2 border rounded w-48 sm:w-56" />

            <select id="roleSelect" class="px-3 py-2 border rounded w-40">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>Client</option>
            </select>

            <select id="statusSelect" class="px-3 py-2 border rounded w-40">
                <option value="">All Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <!-- Right: Add New -->
        <a href="{{ route('admin.users.create') }}" class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 transition">
            Add New
        </a>
        <a href="{{ route('admin.users.trash') }}" class="bg-gray-600 text-white px-3 py-2 rounded hover:bg-gray-700 transition">
            View Trash
        </a>
    </div>

    
    <div class="overflow-x-auto w-full">
        <table class="w-full border-collapse border border-gray-300 text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th class="w-[50px] px-4 py-2 border text-left">#</th>
                    <th class="w-[200px] px-4 py-2 border text-left">Name</th>
                    <th class="w-[200px] px-4 py-2 border text-left">Email</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Role</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Status</th>
                    <th class="w-[180px] px-4 py-2 border text-left">Action</th>
                </tr>
            </thead>
            <tbody class="text-gray-800 text-center">
                @foreach($users as $index => $user)
                    <tr class="border-t">
                        <td class="px-4 py-2 border">{{ $users->firstItem() + $index }}</td>
                        <td class="px-4 py-2 border">{{ $user->name }}</td>
                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                        <td class="px-4 py-2 border">
                            @php
                                $type = strtolower($user->user_type ?? 'unknown');
                                $styles = [
                                    'admin' => 'bg-red-100 text-red-800',
                                    'employee' => 'bg-green-100 text-green-800',
                                    'student' => 'bg-amber-100 text-amber-800', // Use amber instead of brown
                                    'unknown' => 'bg-gray-200 text-gray-700',     // Fallback
                                ];
                            @endphp
                            <span class="inline-block text-xs px-2 py-1 rounded {{ $styles[$type] ?? $styles['unknown'] }}">
                                {{ \Illuminate\Support\Str::title($type) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border">
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $user->status ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }}">
                                {{ $user->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border space-x-2">
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                            class="inline-block bg-green-200 hover:bg-green-600 text-black font-bold px-4 py-1 rounded-full text-center">
                                Edit
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" data-swal="delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-block bg-red-500 hover:bg-red-700 text-black font-bold px-4 py-1 rounded-full text-center">
                                    Trash
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Delete confirmation with SweetAlert
        document.querySelectorAll('form[data-swal="delete"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // stop normal submission

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // submit if confirmed
                    }
                });
            });
        });

        // Filter application
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const role = document.getElementById('roleSelect').value;
            const status = document.getElementById('statusSelect').value;

            if (!search && !role && status === '') {
                window.location.href = `{{ route('admin.users.index') }}`;
                return;
            }

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (role) params.append('role', role);
            if (status !== '') params.append('status', status);

            window.location.href = `{{ route('admin.users.index') }}?${params.toString()}`;
        }

        document.getElementById('searchInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyFilters();
            }
        });

        document.getElementById('roleSelect').addEventListener('change', applyFilters);
        document.getElementById('statusSelect').addEventListener('change', applyFilters);
    });
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif
@endpush
