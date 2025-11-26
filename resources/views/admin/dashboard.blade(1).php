@extends('layouts.admin')

@section('title', 'CTU - Guidance Office')

@section('content_header')
    <h1 class="mb-4 text-xl font-semibold">Appointments Calendar</h1>
@stop

@section('content')
    <!-- Calendar Container -->
    <div class="w-full overflow-x-auto bg-white shadow rounded p-4">
        <div id="calendar-wrapper" style="min-width: 1000px;">
            <div id="calendar-loading" class="text-center py-4 text-gray-500 hidden">
                <svg class="w-6 h-6 animate-spin inline-block mr-2 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                    </path>
                </svg>
                <span>Loading calendar...</span>
            </div>
            <div id="calendar"></div>
            <!-- Legend -->
            <div class="mt-4 flex justify-end pr-4">
                <div class="flex space-x-2 text-sm text-gray-700">
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-4 rounded border-2" style="background-color: #ffdc52ff; border-color: #cab01bff;"></span>
                        <span>Today</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="w-4 h-4 rounded border" style="background-color: #f87171; border-color: #7f1d1d;"></span>
                        <span>Holiday / Special Day</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointment Modal Wrapper -->
    <div x-data="{ open: false }"
        x-show="open"
        @open-modal.window="open = true"
        @close-modal.window="open = false"
        class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50"
        style="display: none;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <!-- Modal Card -->
        <div @click.away="open = false" class="bg-white rounded-lg shadow-lg max-w-3xl w-full p-6">
            <input type="hidden" name="appointment_id" id="modalAppointmentId">

            <!-- Modal Header -->
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h2 class="text-xl font-bold text-gray-800">Appointment Details</h2>
                <button type="button" @click="open = false"
                    class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
            </div>

            <!-- Modal Table Body -->
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm text-gray-700">
                    <tbody>
                        <tr>
                            <td class="font-semibold py-2 pr-4 w-1/4">Client:</td>
                            <td class="py-2"><span id="modalAppointmentName">N/A</span></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="font-semibold py-2 pr-4">Service:</td>
                            <td class="py-2"><span id="modalService">N/A</span></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-2 pr-4">Email:</td>
                            <td class="py-2"><span id="modalEmail">N/A</span></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="font-semibold py-2 pr-4">Phone:</td>
                            <td class="py-2"><span id="modalPhone">N/A</span></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-2 pr-4">Staff:</td>
                            <td class="py-2"><span id="modalStaff">N/A</span></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="font-semibold py-2 pr-4">Date & Time:</td>
                            <td class="py-2"><span id="modalStartTime">N/A</span></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-2 pr-4">Notes:</td>
                            <td class="py-2"><span id="modalNotes">N/A</span></td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="font-semibold py-2 pr-4">Current Status:</td>
                            <td class="py-2"><span id="modalStatusBadge">N/A</span></td>
                        </tr>
                        <tr>
                            <td class="font-semibold py-2 pr-4">Change Status:</td>
                            <td class="py-2">
                                <select name="status" id="modalStatusSelect"
                                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring focus:border-blue-300">
                                    <option value="Pending">Pending</option>
                                    <option value="Cancelled">Cancelled</option>
                                    <option value="Completed">Completed</option>
                                    <option value="No Show">No Show</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end mt-6 space-x-3">
                <button type="submit" id="modalUpdateButton"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition duration-200">
                    Update Status
                </button>
                <button type="button" @click="open = false"
                    class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded transition duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="{{ asset('css/fullcalendar.min.css') }}" rel="stylesheet" />
    <style>
        #calendar-wrapper {
            margin: 0 auto;
        }

        #calendar {
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .fc {
            font-size: 15px;
        }

        .fc-toolbar h2 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 20px 0;
            color: #111827;
        }

        .fc-toolbar .fc-button {
            background-color: #2563eb;
            border: none;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 14px;
            margin: 0 4px;
        }

        .fc-toolbar .fc-button:hover {
            background-color: #1d4ed8;
        }

        .fc-day-header {
            background-color: #f9fafb;
            border: none;
            padding: 10px 0;
            font-weight: 600;
            color: #374151;
        }

        .fc td, .fc th {
            border: none !important;
            padding: 8px;
            vertical-align: top;
        }

        .fc-day {
            padding: 8px;
            background-color: white;
            border-radius: 8px;
            box-shadow: inset 0 0 0 1px #e5e7eb;
        }

        .fc-day.fc-other-month {
            background-color: #f3f4f6;
            color: #9ca3af;
        }

        .fc-today {
            background-color: #ffdc52ff !important;
            box-shadow: inset 0 0 0 2px #ffdc52ff;
        }

        .fc-day-number {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
            color: #111827;
        }

        .fc-event {
            background-color: #4b5563;
            border: none;
            color: white;
            font-size: 12px;
            padding: 4px 6px;
            border-radius: 6px;
            margin-top: 4px;
        }

        .fc-event:hover {
            background-color: #374151;
        }

        .fc-daygrid-day-frame {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .fc-daygrid-event {
            margin-bottom: 2px;
            font-size: 11px;
            padding: 2px 4px;
        }

        .fc-daygrid-day-events::-webkit-scrollbar {
            width: 5px;
        }

        .fc-daygrid-day-events::-webkit-scrollbar-thumb {
            background-color: #d1d5db;
            border-radius: 4px;
        }
    </style>
@stop

@section('js')
    <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                loading: function (isLoading) {
                    const loadingDiv = document.getElementById('calendar-loading');
                    if (isLoading) {
                        loadingDiv.classList.remove('hidden');
                    } else {
                        loadingDiv.classList.add('hidden');
                    }
                },
                initialView: 'dayGridMonth',
                validRange: {
                    start: new Date(new Date().getFullYear(), new Date().getMonth(), 1), // First day of current month
                    end: new Date(new Date().getFullYear(), new Date().getMonth() + 1, 1) // Last day of current month
                },
                height: 'auto',
                contentHeight: 'auto',
                dayMaxEventRows: 3,
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: 3
                    }
                },
                fixedWeekCount: true,
                headerToolbar: {
                    left: '',
                    center: 'title',
                    right: ''
                },
                dayCellDidMount: function(info) {
                    info.el.querySelector('.fc-daygrid-day-frame')?.classList.add('custom-day-frame');
                },
                eventSources: [
                    {
                        url: '/admin/appointments/calendar-data',
                        method: 'GET',
                        failure: () => console.error('Failed to load appointments.')
                    },
                    {
                        url: '/admin/special-days/calendar-data',
                        method: 'GET',
                        display: 'background',
                        color: '#bb0707ff',
                        failure: () => console.error('Failed to load special days.')
                    }
                ],
                eventClick: function (info) {
                    if (info.event.display === 'background') {
                        Swal.fire({
                            icon: 'info',
                            title: info.event.title || 'Non-Working Day',
                            text: 'This date is marked as a special day or holiday.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    const event = info.event;

                    document.getElementById('modalAppointmentId').value = event.id;
                    document.getElementById('modalAppointmentName').textContent = event.title?.split(' - ')[0] || 'N/A';
                    document.getElementById('modalService').textContent = event.extendedProps.service || 'N/A';
                    document.getElementById('modalEmail').textContent = event.extendedProps.email || 'N/A';
                    document.getElementById('modalPhone').textContent = event.extendedProps.phone || 'N/A';
                    document.getElementById('modalStaff').textContent = event.extendedProps.employee || 'N/A';
                    document.getElementById('modalNotes').textContent = event.extendedProps.description || 'N/A';

                    const start = new Date(event.start);
                    document.getElementById('modalStartTime').textContent = start.toLocaleString('en-US', {
                        dateStyle: 'long',
                        timeStyle: 'short'
                    });

                    const status = event.extendedProps.status || 'Pending';
                    const statusColors = {
                        'Pending': '#f59e0b',
                        'Cancelled': '#ef4444',
                        'Completed': '#10b981',
                        'No Show': '#f97316',
                    };

                    const badgeColor = statusColors[status] || '#6b7280';
                    const statusSelect = document.getElementById('modalStatusSelect');
                    statusSelect.value = status;
                    statusSelect.disabled = ['Cancelled', 'Completed', 'No Show'].includes(status);

                    const updateButton = document.getElementById('modalUpdateButton');
                    updateButton.disabled = ['Cancelled', 'Completed', 'No Show'].includes(status);
                    updateButton.classList.toggle('opacity-50', updateButton.disabled);
                    updateButton.classList.toggle('cursor-not-allowed', updateButton.disabled);

                    document.getElementById('modalStatusBadge').innerHTML =
                        `<span class="inline-block px-2 py-1 text-white rounded" style="background-color: ${badgeColor};">${status}</span>`;

                    window.dispatchEvent(new Event('open-modal'));
                },
                eventContent: function (arg) {
                    const title = arg.event.title;
                    const time = arg.timeText;
                    const service = arg.event.extendedProps.service || '';
                    const status = arg.event.extendedProps.status;

                    let statusHtml = '';
                    if (status) {
                        const statusColors = {
                            'Pending': { bg: '#f59e0b', text: 'black' },
                            'Cancelled': { bg: '#ef4444', text: 'white' },
                            'Completed': { bg: '#10b981', text: 'white' },
                            'No Show': { bg: '#f97316', text: 'black' },
                        };
                        const { bg, text } = statusColors[status] || { bg: '#6b7280', text: 'white' };

                        statusHtml = `
                            <span class="inline-block mt-1 px-2 py-0.5 text-[11px] font-medium rounded-full"
                                style="background-color: ${bg}; color: ${text};">
                                ${status}
                            </span>
                        `;
                    }

                    return {
                        html: `
                            <div class="text-xs leading-tight">
                                <div class="font-semibold">${time} ${title}</div>
                                <div class="text-gray-600">${service}</div>
                                ${statusHtml}
                            </div>
                        `
                    };
                }
            });

            calendar.render();

            // ✅ AJAX status update
            document.getElementById('modalUpdateButton').addEventListener('click', function () {
                const appointmentId = document.getElementById('modalAppointmentId').value;
                const status = document.getElementById('modalStatusSelect').value;
                const updateBtn = this;

                updateBtn.disabled = true;
                updateBtn.textContent = 'Updating...';

                fetch("{{ route('admin.appointments.update-status') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        appointment_id: appointmentId,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: data.message || 'Appointment status updated.',
                            confirmButtonColor: '#2563eb'
                        }).then(() => {
                            const updatedEvent = calendar.getEventById(appointmentId);
                            calendar.refetchEvents();
                            window.dispatchEvent(new Event('close-modal'));
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Something went wrong.',
                            confirmButtonColor: '#dc2626'
                        });
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to update status.',
                        confirmButtonColor: '#dc2626'
                    });
                })
                .finally(() => {
                    updateBtn.disabled = false;
                    updateBtn.textContent = 'Update Status';
                });
            });

            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Success', text: '{{ session('success') }}', confirmButtonColor: '#2563eb' });
            @endif

            @if (session('error'))
                Swal.fire({ icon: 'error', title: 'Error', text: '{{ session('error') }}', confirmButtonColor: '#dc2626' });
            @endif
        });
    </script>
@stop
