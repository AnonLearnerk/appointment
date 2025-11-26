@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
<div class="p-6 space-y-6">

    <div class="p-4 sm:p-6 space-y-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 break-words">
            Welcome back, {{ Auth::user()->name }}
        </h1>

    <!-- Upcoming Appointment Card -->
    <div class="bg-gradient-to-r from-indigo-100 to-white p-4 sm:p-6 rounded-2xl shadow-md border border-gray-200 relative overflow-hidden">
        <!-- Logo covering right half with fade -->
        <div class="absolute top-0 right-0 h-full w-1/2 opacity-20 pointer-events-none">
            <img src="{{ asset('img/ctu.jpg') }}" 
                class="h-full w-full object-cover" 
                alt="Logo"
                style="mask-image: linear-gradient(to left, black, transparent); 
                        -webkit-mask-image: linear-gradient(to left, black, transparent);">
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10m-10 4h6m2 0h.01M4 11h16M4 5h16a2 2 0 012 2v14a2 2 0 01-2 2H4a2 2 0 01-2-2V7a2 2 0 012-2z" />
                </svg>
                <h2 class="text-xl sm:text-2xl font-semibold text-indigo-700">Upcoming Appointment</h2>
            </div>
        </div>

        @if($upcomingAppointment)
            @php $appt = $upcomingAppointment; @endphp
            <div class="space-y-2 text-gray-800">
                <!-- Service -->
                <p class="flex flex-wrap items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 3a.75.75 0 00-.75.75v1.5h6v-1.5a.75.75 0 00-.75-.75h-4.5zM4.5 7.5A2.25 2.25 0 016.75 5.25h10.5A2.25 2.25 0 0119.5 7.5v12a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75v-12z" />
                    </svg>
                    <span class="font-medium">Service:</span> {{ $appt->service_name ?? 'N/A' }}
                </p>

                <!-- Staff -->
                <p class="flex flex-wrap items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A4 4 0 017 17h10a4 4 0 011.879.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="font-medium">Staff:</span> {{ $appt->employee_name ?? 'N/A' }}
                </p>

                <!-- Schedule -->
                <p class="flex flex-wrap items-center gap-2 text-sm sm:text-base">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M4 11h16M4 5h16a2 2 0 012 2v14a2 2 0 01-2 2H4a2 2 0 01-2-2V7a2 2 0 012-2z" />
                    </svg>
                    <span class="font-medium mr-1">Scheduled For:</span>
                    {{ \Carbon\Carbon::parse($appt->booking_date)->format('F j, Y') }}
                    at {{ \Carbon\Carbon::parse($appt->booking_time)->format('h:i A') }}
                </p>

                <!-- Status Message -->
                <div class="mt-4">
                    @php $status = strtolower($appt->status); @endphp
                    @if($status === 'pending')
                        <p class="text-yellow-600 font-semibold">This appointment is currently pending.</p>
                    @elseif($status === 'cancelled')
                        <p class="text-red-600 font-semibold">This appointment was cancelled.</p>
                    @elseif($status === 'completed')
                        <p class="text-green-600 font-semibold">This appointment has been completed.</p>
                    @elseif($status === 'no show')
                        <p class="text-gray-600 font-semibold">You did not show up for this appointment.</p>
                    @else
                        <p class="text-gray-500">Status: {{ ucfirst($status) }}</p>
                    @endif
                </div>
            </div>

            @if($status === 'pending')
                <button type="button" 
                    class="mt-2 px-3 py-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                    onclick="confirmCancel('{{ $appt->id ?? '' }}')">
                    Cancel Appointment
                </button>
            @endif
        @else
            <div class="text-gray-500 italic">
                You don’t have any upcoming appointments.
            </div>
        @endif
    </div>

    <!-- All Appointments Table -->
    <div class="mt-10 bg-white p-4 sm:p-6 rounded-xl shadow-md border">
        <!-- Title + Filters -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
            <!-- Title -->
            <h2 class="text-xl sm:text-2xl font-semibold whitespace-nowrap text-gray-800">
                All Appointments
            </h2>
            <!-- Filters -->
            <div class="flex flex-wrap gap-2 sm:gap-4 items-center">
                <input type="text" id="searchInput" value="{{ request('search') }}" placeholder="Search..." class="px-3 py-2 border rounded w-48 sm:w-56"/>
                <!-- Service -->
                <select id="filter-service"
                    class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm w-40">
                    <option value="">All Services</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" {{ request('service') == $service->id ? 'selected' : '' }}>
                            {{ $service->title }}
                        </option>
                    @endforeach
                </select>

                <!-- Status -->
                <select id="filter-status"
                    class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm w-40">
                    <option value="">All Statuses</option>
                    @foreach (['Pending', 'Completed', 'Cancelled', 'No Show'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div id="appointmentsTable">
            @include('student.partials.appointments-table')
        </div>
    </div>
</div>

<script>
    window.appointmentsUrl = "{{ route('student.dashboard') }}"; 
    window.cancelUrl = @json(route('student.appointments.cancel', ':id'));
</script>

<script src="{{ asset('js/student/dashboard.js') }}"></script>

@endsection
