@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="w-full bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">
            {{ request()->routeIs('admin.categories.trashed') ? 'Trashed Categories' : 'All Categories' }}
        </h2>

        <div class="space-x-2">
            <a href="{{ route('admin.categories.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Add New
            </a>
            @if(request()->routeIs('admin.categories.trashed'))
                <a href="{{ route('admin.categories.index') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    View Active
                </a>
            @else
                <a href="{{ route('admin.categories.trashed') }}"
                   class="bg-red-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    View Trash
                </a>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto w-full">
        <table class="w-full table-fixed border-collapse border border-gray-300 text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th class="w-[50px] px-4 py-2 border text-left">#</th>
                    <th class="w-[50px] px-4 py-2 border text-left">Image</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Title</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Status</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Action</th>
                </tr>
            </thead>

            <tbody class="text-gray-800 text-center">
                @forelse($categories as $index => $category)
                    <tr class="border-t">
                        <td class="px-4 py-2 border">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 border">
                            @if(!empty($category['image']))
                                <img src="{{ asset($category['image']) }}" alt="Image" class="w-8 h-8 object-cover rounded mx-auto">
                            @else
                                <img src="https://via.placeholder.com/48x48?text=No+Image" alt="No image" class="w-8 h-8 object-cover rounded mx-auto">
                            @endif
                        </td>
                        <td class="px-4 py-2 border">{{ $category['title'] }}</td>
                        <td class="px-4 py-2 border">
                            @if($category['status'] === 'PUBLISHED')
                                <span class="inline-block px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 border border-green-300 rounded">
                                    Active
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 border border-red-300 rounded">
                                    In-Active
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border space-x-2">
                            @if(request()->routeIs('admin.categories.trashed'))
                                {{-- Restore --}}
                                <form id="restore-category-{{ $category['id'] }}" 
                                    action="{{ route('admin.categories.restore', $category['id']) }}" 
                                    method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="button"
                                            onclick="confirmRestore('{{ $category['id'] }}')"
                                            class="inline-block bg-green-500 hover:bg-green-700 text-white font-bold px-4 py-1 rounded-full text-center">
                                        Restore
                                    </button>
                                </form>

                                {{-- Permanent Delete --}}
                                <form id="force-delete-category-{{ $category['id'] }}" 
                                    action="{{ route('admin.categories.force_delete', $category['id']) }}" 
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="confirmForceDelete('{{ $category['id'] }}')"
                                            class="inline-block bg-red-700 hover:bg-red-900 text-white font-bold px-4 py-1 rounded-full text-center">
                                        Delete
                                    </button>
                                </form>
                            @else
                                {{-- Edit --}}
                                <a href="{{ route('admin.categories.edit', $category['id']) }}"
                                class="inline-block bg-green-200 hover:bg-green-600 text-black font-bold px-4 py-1 rounded-full text-center">
                                    Edit
                                </a>

                                {{-- Soft Delete --}}
                                <form id="delete-category-{{ $category['id'] }}" 
                                    action="{{ route('admin.categories.destroy', $category['id']) }}" 
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="confirmDelete('{{ $category['id'] }}')"
                                            class="inline-block bg-red-500 hover:bg-red-700 text-white font-bold px-4 py-1 rounded-full text-center">
                                        Trash
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2 border text-center text-gray-500">
                            No categories found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6',
        });
    </script>
@endif

<script>
function confirmDelete(categoryId) {
    Swal.fire({
        title: 'Move to Trash?',
        text: "This category will be moved to trash.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, trash it!',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-category-' + categoryId).submit();
        }
    });
}

function confirmRestore(categoryId) {
    Swal.fire({
        title: 'Restore this category?',
        text: "It will become active again.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, restore it!',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('restore-category-' + categoryId).submit();
        }
    });
}

function confirmForceDelete(categoryId) {
    Swal.fire({
        title: 'Delete permanently?',
        text: "This action cannot be undone!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#b71c1c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('force-delete-category-' + categoryId).submit();
        }
    });
}
</script>

@endsection
