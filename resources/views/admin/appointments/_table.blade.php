<table class="w-full border-collapse border border-gray-300 text-sm">
    <thead class="bg-gray-100 text-gray-700 font-semibold">
        <tr>
            <th class="w-[100px] px-4 py-2 border text-left">Booking ID</th>
            <th class="w-[100px] px-4 py-2 border text-left">Name</th>
            <th class="w-[100px] px-4 py-2 border text-left">Email</th>
            <th class="w-[100px] px-4 py-2 border text-left">Phone</th>
            <th class="w-[100px] px-4 py-2 border text-left">Service</th>
            <th class="w-[100px] px-4 py-2 border text-left">Employee</th>
            <th class="w-[100px] px-4 py-2 border text-left">Date</th>
            <th class="w-[100px] px-4 py-2 border text-left">Time</th>
            <th class="w-[100px] px-4 py-2 border text-left">Status</th>
            <th class="w-[100px] px-4 py-2 border text-left">Action</th>
        </tr>
    </thead>
    <tbody class="text-gray-800 text-center">
        @forelse ($appointments as $appointment)
            <tr class="border-t hover:bg-blue-50 transition">
                <td class="px-3 py-2 border">{{ $appointment['booking_id'] ?? 'N/A' }}</td>
                <td class="px-3 py-2 border">{{ $appointment['name'] ?? 'N/A' }}</td>
                <td class="px-3 py-2 border">{{ $appointment['email'] ?? 'N/A' }}</td>
                <td class="px-3 py-2 border">{{ $appointment['phone'] ?? 'N/A' }}</td>
                <td class="px-3 py-2 border">{{ $appointment['service_title'] ?? 'N/A' }}</td>
                <td class="px-3 py-2 border">{{ $appointment['employee_name'] ?? 'N/A' }}</td>
                <td class="px-3 py-2 border">
                    {{ !empty($appointment['booking_date']) ? \Carbon\Carbon::parse($appointment['booking_date'])->format('F j, Y') : 'N/A' }}
                </td>
                <td class="px-3 py-2 border">
                    {{ !empty($appointment['booking_time']) ? \Carbon\Carbon::parse($appointment['booking_time'])->format('g:i A') : 'N/A' }}
                </td>
                <td class="px-3 py-2 border">
                    @php
                        $statusColors = [
                            'Pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                            'Completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800'],
                            'Cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-800'],
                            'No Show' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                        ];
                        $status = $appointment['status'] ?? 'Unknown';
                        $colors = $statusColors[$status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700'];
                    @endphp
                    <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $colors['bg'] }} {{ $colors['text'] }}">
                        {{ $status }}
                    </span>
                </td>
                <td class="px-3 py-2 border">
                    @if (!in_array($appointment['status'] ?? '', ['Cancelled', 'Completed', 'No Show']))
                        <select
                            class="rounded px-2 py-1 text-sm border status-dropdown"
                            data-id="{{ $appointment['id'] ?? '' }}"
                        >
                            <option disabled selected>Update</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Completed">Completed</option>
                            <option value="No Show">No Show</option>
                        </select>
                    @else
                        <span class="text-gray-400 italic">Locked</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="p-4 text-center text-gray-500">
                    No appointments booked at the moment.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>


<div class="mt-4">
    {{ $appointments->appends(request()->query())->links() }}
</div>
