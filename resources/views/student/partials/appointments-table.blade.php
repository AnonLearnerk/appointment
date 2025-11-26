@if($appointments->count())
    <div class="overflow-x-auto w-full">
        <!-- Desktop Table -->
        <div class="hidden md:block">
            <table class="w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="w-[50px] px-4 py-2 border text-left">Booking ID</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Service</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Staff</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Date</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Time</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Status</th>
                        <th class="w-[150px] px-4 py-2 border text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-800 text-center">
                    @foreach($appointments as $appt)
                        <tr id="appt-{{ $appt->id }}" class="border-t">
                            <td class="px-4 py-2 border">{{ $appt->booking_id ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $appt->service_name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">{{ $appt->employee_name ?? 'N/A' }}</td>
                            <td class="px-4 py-2 border">
                                {{ \Carbon\Carbon::parse($appt->booking_date)->format('F j, Y') }}
                            </td>
                            <td class="px-4 py-2 border">
                                {{ \Carbon\Carbon::parse($appt->booking_time)->format('h:i A') }}
                            </td>
                            <td class="px-4 py-2 border capitalize font-medium status-cell
                                @if($appt->status == 'Pending') text-yellow-600
                                @elseif($appt->status == 'Cancelled') text-red-600
                                @elseif($appt->status == 'Completed') text-green-600
                                @elseif($appt->status == 'No Show') text-gray-600
                                @endif
                            ">
                                {{ $appt->status ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-2 border actions-cell">
                                @if(isset($appt->status) && $appt->status === 'Pending')
                                    <button type="button" 
                                        class="mt-2 px-3 py-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                                        onclick="confirmCancel('{{ $appt->id }}')">
                                        Cancel
                                    </button>
                                @else
                                    <span class="text-gray-400 text-sm italic">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="space-y-4 md:hidden">
            @foreach($appointments as $appt)
                <div class="bg-white shadow-md rounded-xl p-4 border border-gray-200">
                    <div class="text-sm text-gray-600 mb-2">
                        <span class="font-semibold">Booking ID:</span> {{ $appt->booking_id ?? 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Service:</span> {{ $appt->service_name ?? 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Staff:</span> {{ $appt->employee_name ?? 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Date:</span> 
                        {{ \Carbon\Carbon::parse($appt->booking_date)->format('F j, Y') }}
                    </div>
                    <div class="text-sm text-gray-600">
                        <span class="font-semibold">Time:</span> 
                        {{ \Carbon\Carbon::parse($appt->booking_time)->format('h:i A') }}
                    </div>
                    <div class="text-sm font-medium mt-2 status-cell
                        @if($appt->status == 'Pending') text-yellow-600
                        @elseif($appt->status == 'Cancelled') text-red-600
                        @elseif($appt->status == 'Completed') text-green-600
                        @elseif($appt->status == 'No Show') text-gray-600
                        @endif
                    ">
                        Status: {{ $appt->status ?? 'N/A' }}
                    </div>
                    <div class="mt-3 actions-cell">
                        @if(isset($appt->status) && $appt->status === 'Pending')
                            <button type="button" 
                                class="mt-2 px-3 py-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                                onclick="confirmCancel('{{ $appt->id }}')">
                                Cancel
                            </button>
                        @else
                            <span class="text-gray-400 text-sm italic">No actions available</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Removed pagination since collection isn’t paginated --}}
@else
    <p class="text-gray-500">You have no appointments yet.</p>
@endif
