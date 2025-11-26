@extends('layouts.admin')

@section('title', 'Trashed Categories')

@section('content')
<div class="w-full bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Trashed Categories</h2>
        <a href="{{ route('admin.categories.index') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
            Back to Categories
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

    @if($categories->count() > 0)
        <div class="overflow-x-auto w-full">
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead class="bg-gray-100 text-gray-700 font-semibold">
                    <tr>
                        <th class="w-[50px] px-4 py-2 border text-left">#</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Image</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Title</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Deleted At</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 text-center">
                    @foreach($categories as $index => $category)
                        <tr class="border-t">
                            <td class="px-4 py-2 border">{{ $loop->iteration }}</td>
                            <td class="px-4 py-2 border">
                                @if(!empty($category['image']))
                                    <img src="{{ asset($category['image']) }}" 
                                        alt="{{ $category['title'] }}" 
                                        class="w-16 h-16 object-cover rounded mx-auto">
                                @else
                                    <span class="text-gray-400 italic">No Image</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 border">{{ $category['title'] }}</td>
                            <td class="px-4 py-2 border text-sm text-gray-500">
                                {{ $category['deleted_at'] ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 border space-x-2">
                                <!-- Restore -->
                                <form action="{{ route('admin.categories.restore', $category['id']) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                        class="inline-block bg-green-700 hover:bg-green-500 text-white font-semibold px-4 py-1 rounded-full text-center transition">
                                        Restore
                                    </button>
                                </form>

                                <!-- Permanent Delete -->
                                <form action="{{ route('admin.categories.force_delete', $category['id']) }}" method="POST" class="inline-block delete-category-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="inline-block bg-red-700 hover:bg-red-500 text-white font-semibold px-4 py-1 rounded-full text-center transition delete-category-btn">
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
            No trashed categories found.
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-category-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('.delete-category-form');

            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the category and cannot be undone.",
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
