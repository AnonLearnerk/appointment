@extends('layouts.admin')

@section('title', 'Appointments List')

@section('content')
<div class="bg-white p-6 rounded shadow w-full">
    <!-- Title and Filters in one flex container -->
    <div class="flex md:flex-row md:items-center md:justify-between mb-4 gap-4">
        <!-- Title -->
        <h2 class="text-xl font-semibold whitespace-nowrap">All Appointments</h2>

        <!-- Filters -->
        <div class="flex flex-wrap gap-2 sm:gap-4 items-center">
            <input type="text" id="searchInput" value="{{ request('search') }}" placeholder="Search..." class="px-3 py-2 border rounded w-48 sm:w-56"/>

            <select id="serviceFilter" class="px-3 py-2 border rounded w-40">
                <option value="">All Services</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" {{ request('service') == $service->id ? 'selected' : '' }}>
                        {{ $service->title }}
                    </option>
                @endforeach
            </select>

            <select id="statusFilter" class="px-3 py-2 border rounded w-40">
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
        @include('admin.appointments._table')
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        function bindStatusDropdowns() {
            document.querySelectorAll('.status-dropdown').forEach(dropdown => {
                dropdown.addEventListener('change', function () {
                    const appointmentId = this.dataset.id;
                    const newStatus = this.value;

                    fetch("{{ route('admin.appointments.update-status') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            appointment_id: appointmentId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: 'Status updated successfully.',
                                confirmButtonColor: '#3085d6',
                            }).then(() => {
                                updateFilters(window.location.href); // reload only the table, not whole page
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: data.message || 'Unable to update status.',
                                confirmButtonColor: '#d33',
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Unexpected Error',
                            text: 'An error occurred while updating the status.',
                        });
                    });
                });
            });
        }

        function updateFilters(url = null) {
            const search = document.getElementById('searchInput').value;
            const service = document.getElementById('serviceFilter').value;
            const status = document.getElementById('statusFilter').value;

            let params = new URLSearchParams();
            if (search) params.set('search', search);
            if (service) params.set('service', service);
            if (status) params.set('status', status);

            const fetchUrl = url || ("{{ route('admin.appointments.index') }}?" + params.toString());

            fetch(fetchUrl, { headers: { "X-Requested-With": "XMLHttpRequest" } })
                .then(res => res.text())
                .then(html => {
                    document.getElementById('appointmentsTable').innerHTML = html;
                    bindStatusDropdowns(); // re-bind dropdowns after table reload
                })
                .catch(err => console.error(err));
        }

        // intercept pagination clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('#appointmentsTable .pagination a');
            if (link) {
                e.preventDefault();
                updateFilters(link.href);
            }
        });

        // Trigger filters
        document.getElementById('searchInput').addEventListener('input', debounce(() => updateFilters(), 500));
        document.getElementById('serviceFilter').addEventListener('change', () => updateFilters());
        document.getElementById('statusFilter').addEventListener('change', () => updateFilters());


        // Debounce helper
        function debounce(func, delay) {
            let timeout;
            return function () {
                clearTimeout(timeout);
                timeout = setTimeout(func, delay);
            };
        }

        // run on first load
        bindStatusDropdowns();
    });
</script>
@endpush
