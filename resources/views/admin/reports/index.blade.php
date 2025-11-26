@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="bg-white p-6 rounded shadow w-full">
    <!-- Header with Title + Dropdown inline -->
    <div class="flex items-center justify-between mb-4">
        <h2 id="report-title" class="text-2xl font-bold">
            Appointment Report ({{ ucfirst($type) }})
        </h2>

        <!-- Dropdown triggers AJAX -->
        <select id="report-filter" class="border rounded p-2">
            <option value="daily" {{ $type == 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly" {{ $type == 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="monthly" {{ $type == 'monthly' ? 'selected' : '' }}>Monthly</option>
            <option value="yearly" {{ $type == 'yearly' ? 'selected' : '' }}>Yearly</option>
        </select>

    </div>

    <!-- Report Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 bg-gray-200 rounded text-center">
            <h3 id="pending" class="text-xl font-bold">{{ $data['Pending'] }}</h3>
            <p>Pending</p>
        </div>
        <div class="p-4 bg-green-100 rounded text-center">
            <h3 id="completed" class="text-xl font-bold">{{ $data['Completed'] }}</h3>
            <p>Completed</p>
        </div>
        <div class="p-4 bg-red-100 rounded text-center">
            <h3 id="cancelled" class="text-xl font-bold">{{ $data['Cancelled'] }}</h3>
            <p>Cancelled</p>
        </div>
        <div class="p-4 bg-yellow-100 rounded text-center">
            <h3 id="no-show" class="text-xl font-bold">{{ $data['No Show'] }}</h3>
            <p>No Show</p>
        </div>
    </div>
</div>

<script>
document.getElementById('report-filter').addEventListener('change', function () {
    const type = this.value;

    fetch(`{{ route('admin.reports.index') }}?type=${type}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest' // ✅ Tell Laravel it's AJAX
        }
    })
    .then(res => res.json())
    .then(res => {
        // Update title
        document.getElementById('report-title').innerText = `Appointment Report (${res.type.charAt(0).toUpperCase() + res.type.slice(1)})`;

        // Update counts
        document.getElementById('pending').innerText   = res.data['Pending'];
        document.getElementById('completed').innerText = res.data['Completed'];
        document.getElementById('cancelled').innerText = res.data['Cancelled'];
        document.getElementById('no-show').innerText   = res.data['No Show'];
    })
    .catch(err => console.error(err));
});
</script>
@endsection
