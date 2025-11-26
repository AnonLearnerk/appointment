@extends('layouts.admin')

@section('title', 'All Services')

@section('content')
<div class="w-full bg-white p-6 rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">All Services</h2>
        <a href="{{ route('admin.services.create') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
             Add New
        </a>
        <a href="{{ route('admin.services.trash') }}"
           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
             View Trash
        </a>
    </div>

    <div class="overflow-x-auto w-full">
        <table class="w-full border-collapse border border-gray-300 text-sm">
            <thead class="bg-gray-100 text-gray-700 font-semibold">
                <tr>
                    <th class="w-[50px] px-4 py-2 border text-left">#</th>
                    <th class="w-[50px] px-4 py-2 border text-left">Image</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Title</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Description</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Status</th>
                    <th class="w-[150px] px-4 py-2 border text-left">Action</th>
                </tr>
            </thead>
            <tbody class="text-gray-800 text-center">
                @forelse($services as $index => $service)
                    @php
                        $serviceId = $service['id'] ?? $index;
                        $title = $service['title'] ?? 'No Title';
                        $body = $service['body'] ?? '';
                        $status = $service['status'] ?? 0;
                        $imageUrl = $service['image_url'] ?? (!empty($service['image']) ? asset('storage/' . $service['image']) : null);
                    @endphp
                    <tr class="border-t">
                        <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $title }}" class="w-16 h-16 object-cover rounded mx-auto">
                            @else
                                <span class="text-gray-400 italic">No Image</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border">{{ $title }}</td>
                        <td class="px-4 py-2 border">{{ $body }}</td>
                        <td class="px-4 py-2 border">
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $status ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' }}">
                                {{ $status ? 'Active' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 border space-x-2">
                            <a href="{{ route('admin.services.edit', $serviceId) }}"
                            class="inline-block bg-green-200 hover:bg-green-600 text-black font-bold px-4 py-1 rounded-full text-center">
                                Edit
                            </a>
                            <form action="{{ route('admin.services.destroy', $serviceId) }}" method="POST" class="inline delete-service-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" 
                                        class="inline-block bg-red-500 hover:bg-red-700 text-black font-bold px-4 py-1 rounded-full text-center delete-service-btn">
                                    Trash
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-2 border text-center text-gray-500">No services found.</td>
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
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.delete-service-btn').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('.delete-service-form');

            Swal.fire({
                title: 'Are you sure?',
                text: "This service will be moved to trash.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, trash it!',
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
