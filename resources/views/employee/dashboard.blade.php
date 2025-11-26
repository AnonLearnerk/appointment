@extends('layouts.employee')

@section('title', 'CTU - Guidance Office')

@section('content')
<div class="p-6 space-y-6">
    <h1 class="text-3xl font-bold text-gray-800">Welcome back, {{ Auth::user()->name }}</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Upcoming Appointments Card --}}
    <div class="bg-gradient-to-r from-blue-50 to-white p-6 rounded-2xl shadow-md border border-gray-200">
        <h2 class="text-2xl font-semibold text-blue-700 mb-4">Upcoming Appointments</h2>

        @if($upcomingAppointments->count())
            <ul class="space-y-4">
                @foreach($upcomingAppointments as $appt)
                    <li class="bg-white border rounded-lg p-4 shadow-sm">
                        <div class="text-gray-800 space-y-1">
                            <!-- <p><span class="font-semibold">Booking ID:</span> {{ $appt['booking_id'] ?? 'N/A' }}</p> -->
                            <p><span class="font-semibold">Client:</span> {{ $appt['name'] }}</p>
                            <p><span class="font-semibold">Email:</span> {{ $appt['email'] }}</p>
                            <p><span class="font-semibold">Phone:</span> {{ $appt['phone'] }}</p>
                            <p><span class="font-semibold">Service:</span> {{ $appt['service_title'] }}</p>
                            <p><span class="font-semibold">Date:</span> 
                                {{ $appt['booking_date'] ? \Carbon\Carbon::parse($appt['booking_date'])->format('F j, Y') : 'N/A' }}
                            </p>
                            <p><span class="font-semibold">Time:</span> {{ \Carbon\Carbon::parse($appt['booking_time'])->format('h:i A') }}</p>

                            <p class="mt-2 font-medium capitalize flex items-center gap-2
                                @if($appt['status'] == 'Pending') text-yellow-600
                                @elseif($appt['status'] == 'Cancelled') text-red-600
                                @elseif($appt['status'] == 'Completed') text-green-600
                                @elseif($appt['status'] == 'No Show') text-gray-600
                                @endif">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3"/>
                                </svg>
                                {{ $appt['status'] }}
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500 italic">No upcoming appointments.</p>
        @endif
    </div>

    {{-- All Appointments Table --}}
    <div class="mt-10 bg-white p-6 rounded-xl shadow-md border">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800">All Appointments</h2>

        @if($allAppointments->count())
        <div class="overflow-x-auto w-full">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <!-- <th class="px-4 py-2 text-left">Booking ID</th> -->
                        <th class="px-4 py-2 text-left">Client</th>
                        <th class="px-4 py-2 text-left">Service</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Time</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($allAppointments as $appt)
                        <tr>
                            <!-- <td class="px-4 py-2">{{ $appt['booking_id'] ?? 'N/A' }}</td> -->
                            <td class="px-4 py-2">{{ $appt['name'] }}</td>
                            <td class="px-4 py-2">{{ $appt['service_title'] }}</td>
                            <td class="px-4 py-2">
                                {{ $appt['booking_date'] ? \Carbon\Carbon::parse($appt['booking_date'])->format('F j, Y') : 'N/A' }}
                            </td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($appt['booking_time'])->format('h:i A') }}</td>
                            <td class="px-4 py-2 capitalize font-medium
                                @if($appt['status'] == 'Pending') text-yellow-600
                                @elseif($appt['status'] == 'Cancelled') text-red-600
                                @elseif($appt['status'] == 'Completed') text-green-600
                                @elseif($appt['status'] == 'No Show') text-gray-600
                                @endif">
                                {{ $appt['status'] }}
                            </td>
                            <td class="px-4 py-2">
                                @if (!in_array($appt['status'], ['Cancelled', 'Completed', 'No Show']))
                                    <form action="{{ route('employee.appointments.updateStatus') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="appointment_id" value="{{ $appt['id'] }}">
                                        <select name="status" class="border rounded px-2 py-1 text-sm capitalize" onchange="this.form.submit()">
                                            <option disabled selected>Update</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                            <option value="No Show">No Show</option>
                                        </select>
                                    </form>
                                @else
                                    <span class="text-gray-400 italic">Locked</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <p class="text-gray-500">You have no appointments yet.</p>
        @endif
    </div>
</div>
@endsection
