@extends('layouts.admin')

@section('title', 'Trashed Users')

@section('content')
<div class="w-full bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Trashed Users</h2>
        <a href="{{ route('admin.users.index') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Back to Users
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            {{ $errors->first() }}
        </div>
    @endif

    @if(!empty($users) && count($users) > 0)
        <div class="overflow-x-auto w-full">
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-gray-100 text-gray-700 font-semibold">
                    <tr>
                        <th class="w-[50px] px-4 py-2 border text-left">#</th>
                        <th class="w-[75px] px-4 py-2 border text-left">Profile</th>
                        <th class="w-[100px] px-4 py-2 border text-left">Name</th>
                        <th class="w-[100px] px-4 py-2 border text-left">Email</th>
                        <th class="w-[100px] px-4 py-2 border text-left">Role</th>
                        <th class="w-[100px] px-4 py-2 border text-left">Deleted At</th>
                        <th class="w-[100px] px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 text-center">
                    @foreach($users as $index => $user)
                        <tr class="border-t">
                            <td class="px-4 py-2 border">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 border">
                                @if(!empty($user['profile']) && filter_var($user['profile'], FILTER_VALIDATE_URL))
                                    <img src="{{ $user['profile'] }}" 
                                         alt="{{ $user['name'] ?? 'User' }}" 
                                         class="w-12 h-12 rounded-full object-cover mx-auto">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mx-auto text-gray-500 text-sm font-semibold">
                                        {{ strtoupper(substr($user['name'] ?? '?', 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2 border">{{ $user['name'] ?? 'Unknown' }}</td>
                            <td class="px-4 py-2 border">{{ $user['email'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border capitalize">{{ $user['user_type'] ?? 'client' }}</td>
                            <td class="px-4 py-2 border text-sm text-gray-500">{{ $user['deleted_at'] ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border space-x-2">
                                <!-- Restore -->
                                <form action="{{ route('admin.users.restore', $user['id']) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                        class="inline-block bg-green-700 hover:bg-green-500 text-white font-semibold px-4 py-1 rounded-full text-center transition">
                                        Restore
                                    </button>
                                </form>

                                <!-- Permanent Delete -->
                                <form action="{{ route('admin.users.force_delete', $user['id']) }}" method="POST" class="inline-block delete-user-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="inline-block bg-red-700 hover:bg-red-500 text-white font-semibold px-4 py-1 rounded-full text-center transition delete-user-btn">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="bg-white p-6 rounded shadow text-center text-gray-500">
            No trashed users found.
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('.delete-user-form');

            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the user and cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
