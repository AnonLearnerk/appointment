@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 rounded shadow">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">Home</a> / Edit User
    </div>

    <h2 class="text-2xl font-semibold mb-6">Edit User: {{ $user['name'] ?? 'Unknown' }}</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            <ul class="list-disc ml-6">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user['id']) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Name</label>
                <input type="text" name="name" value="{{ old('name', $user['name'] ?? '') }}"
                       class="w-full border px-3 py-2 rounded" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $user['email'] ?? '') }}"
                       class="w-full border px-3 py-2 rounded" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $user['phone'] ?? '') }}"
                       class="w-full border px-3 py-2 rounded" required>
            </div>

            <div>
                <label class="block mb-1 font-medium">Role</label>
                <select id="roleSelect" name="roles" class="w-full border px-3 py-2 rounded" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('roles', $selectedRole) == $role ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Status</label>
                <select name="status" class="w-full border px-3 py-2 rounded" required>
                    <option value="1" {{ old('status', $user['status'] ?? 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $user['status'] ?? 1) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>

        <!-- Employee Fields -->
        <div id="employeeFields" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 {{ old('roles', $selectedRole) === 'employee' ? '' : 'hidden' }}">
            <div>
                <label class="block mb-1 font-medium">Slot Duration (mins)</label>
                <select id="slot_duration" name="slot_duration" class="w-full border px-3 py-2 rounded">
                    @foreach($steps as $step)
                        <option value="{{ $step }}" {{ old('slot_duration', $employeeData['slot_duration'] ?? 30) == $step ? 'selected' : '' }}>
                            {{ $step }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Break Duration (mins)</label>
                <select id="break_duration" name="break_duration" class="w-full border px-3 py-2 rounded">
                    @foreach($breaks as $break)
                        <option value="{{ $break }}" {{ old('break_duration', $employeeData['break_duration'] ?? 10) == $break ? 'selected' : '' }}>
                            {{ $break }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block mb-1 font-medium">Services</label>
                <select name="service[]" multiple class="w-full border px-3 py-2 rounded">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" 
                            {{ in_array($service->id, old('service', $employeeData['services'] ?? [])) ? 'selected' : '' }}>
                            {{ $service->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Availability -->
        <div id="availabilitySection" class="mt-6 {{ old('roles', $selectedRole) === 'employee' ? '' : 'hidden' }}">
            <h3 class="font-semibold mb-2">Availability</h3>

            @if ($errors->has('availability'))
                <div class="bg-red-100 text-red-700 px-3 py-2 rounded mb-3">
                    {{ $errors->first('availability') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="table-auto border w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1">Day</th>
                            <th class="border px-2 py-1">Time Slots</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($days as $day)
                        <tr>
                            <td class="border px-2 py-1 font-medium capitalize">{{ $day }}</td>
                            <td class="border px-2 py-1">
                                <div class="space-y-2">
                                    <div class="time-slot-wrapper" data-day="{{ $day }}">
                                        @if(!empty($employeeDays[$day]))
                                            @foreach($employeeDays[$day] as $slot)
                                            <div class="flex gap-2 mb-2">
                                                <input type="time" name="days[{{ $day }}][start][]" 
                                                    value="{{ $slot['start'] ?? '' }}"
                                                    class="start-time border rounded px-2 py-1 w-full">
                                                <input type="time" name="days[{{ $day }}][end][]" 
                                                    value="{{ $slot['end'] ?? '' }}"
                                                    class="end-time border rounded px-2 py-1 w-full" readonly>
                                                <button type="button" class="remove-slot text-red-600">✖</button>
                                            </div>
                                            @endforeach
                                        @endif
                                        <div class="error-box text-red-600 text-sm mt-1"></div>
                                    </div>
                                    <button type="button" class="add-slot text-blue-600 text-sm" data-day="{{ $day }}">+ Add Time Slot</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const slotDurationInput = document.getElementById('slot_duration');
    const breakDurationInput = document.getElementById('break_duration');

    function getDurations() {
        return {
            slotDuration: parseInt(slotDurationInput.value) || 0,
            breakDuration: parseInt(breakDurationInput.value) || 0
        };
    }

    function parseTime(str) {
        const [h, m] = str.split(':').map(Number);
        return new Date(0, 0, 0, h, m);
    }

    function validateDay(wrapper) {
        const { slotDuration, breakDuration } = getDurations();
        const startTimes = wrapper.querySelectorAll('.start-time');
        const endTimes = wrapper.querySelectorAll('.end-time');
        const errorBox = wrapper.querySelector('.error-box');
        errorBox.innerHTML = "";

        let slots = [];
        startTimes.forEach((start, i) => {
            if (start.value && endTimes[i].value) {
                slots.push({
                    start: parseTime(start.value),
                    end: parseTime(endTimes[i].value)
                });
            }
        });

        slots.sort((a, b) => a.start - b.start);

        for (let i = 0; i < slots.length; i++) {
            let s = slots[i];
            const duration = (s.end - s.start) / 60000;
            if (duration !== slotDuration) {
                errorBox.innerHTML = `Each slot must be exactly ${slotDuration} minutes.`;
                return false;
            }

            if (i < slots.length - 1) {
                const gap = (slots[i + 1].start - s.end) / 60000;
                if (gap < breakDuration) {
                    errorBox.innerHTML = `Break between timeslots must be at least ${breakDuration} minutes.`;
                    return false;
                }
            }
        }
        return true;
    }

    function recalcEndTime(startInput) {
        const { slotDuration } = getDurations();
        const endInput = startInput.parentElement.querySelector('.end-time');
        if (!startInput.value) return;
        const startDate = parseTime(startInput.value);
        const endDate = new Date(startDate.getTime() + slotDuration * 60000);
        const hh = String(endDate.getHours()).padStart(2, '0');
        const mm = String(endDate.getMinutes()).padStart(2, '0');
        endInput.value = `${hh}:${mm}`;
    }

    function updateAllSlots() {
        document.querySelectorAll('.time-slot-wrapper').forEach(wrapper => {
            wrapper.querySelectorAll('.start-time').forEach(start => recalcEndTime(start));
            validateDay(wrapper);
        });
    }

    slotDurationInput.addEventListener('change', updateAllSlots);
    breakDurationInput.addEventListener('change', updateAllSlots);

    document.addEventListener('click', function(e) {
        // Add slot
        if (e.target.classList.contains('add-slot')) {
            const day = e.target.dataset.day;
            const wrapper = document.querySelector(`.time-slot-wrapper[data-day="${day}"]`);
            const slotGroup = document.createElement('div');
            slotGroup.classList.add('flex', 'gap-2', 'mb-2');
            slotGroup.innerHTML = `
                <input type="time" name="days[${day}][start][]" class="start-time border rounded px-2 py-1 w-full">
                <input type="time" name="days[${day}][end][]" class="end-time border rounded px-2 py-1 w-full" readonly>
                <button type="button" class="remove-slot text-red-600">✖</button>
            `;
            wrapper.insertBefore(slotGroup, wrapper.querySelector('.error-box'));
        }

        // Remove slot (✅ Works for existing & new)
        if (e.target.classList.contains('remove-slot')) {
            const slotGroup = e.target.closest('.flex');
            const wrapper = e.target.closest('.time-slot-wrapper');
            Swal.fire({
                icon: 'warning',
                title: 'Remove Slot?',
                text: 'Are you sure you want to delete this time slot?',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (result.isConfirmed) {
                    slotGroup.remove();
                    validateDay(wrapper);
                }
            });
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('start-time')) {
            const wrapper = e.target.closest('.time-slot-wrapper');
            recalcEndTime(e.target);
            validateDay(wrapper);
        }
    });

    // AJAX submit
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        updateAllSlots();
        let valid = true;
        document.querySelectorAll('.time-slot-wrapper').forEach(wrapper => {
            if (!validateDay(wrapper)) valid = false;
        });
        if (!valid) {
            Swal.fire({ icon: 'error', title: 'Invalid Availability', text: 'Please fix errors before submitting.' });
            return;
        }
        const formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.valid) {
                Swal.fire({ icon: 'error', title: 'Validation Error', text: data.message });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'User updated successfully.',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                }).then(() => {
                    window.location.href = "{{ route('admin.users.index') }}";
                });
            }
        })
        .catch(() => Swal.fire('Error', 'Something went wrong!', 'error'));
    });
});
</script>
@endpush
