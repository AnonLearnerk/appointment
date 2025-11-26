@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white rounded shadow p-6">
    <!-- Breadcrumb -->
    <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:underline">Home</a> / Create User
    </div>

    <h2 class="text-2xl font-semibold mb-6">Create New User</h2>

    <form id="createUserForm" method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        <!-- Basic Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border px-3 py-2 rounded" required pattern="[0-9]+">
            </div>
            <div>
                <label class="block mb-1 font-medium">Password</label>
                <input type="password" name="password" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full border px-3 py-2 rounded" required>
            </div>
            <div>
                <label class="block mb-1 font-medium">Role</label>
                <select id="roleSelect" name="roles" class="w-full border px-3 py-2 rounded" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ old('roles') == $role ? 'selected' : '' }}>
                            {{ ucfirst($role) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Employee Fields -->
        <div id="employeeFields" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
            <div>
                <label class="block mb-1 font-medium">Slot Duration (minutes)</label>
                <select id="slot_duration" name="slot_duration" class="w-full border px-3 py-2 rounded">
                    @foreach($steps as $step)
                        <option value="{{ $step }}">{{ $step }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block mb-1 font-medium">Break Duration (minutes)</label>
                <select id="break_duration" name="break_duration" class="w-full border px-3 py-2 rounded">
                    @foreach($breaks as $break)
                        <option value="{{ $break }}">{{ $break }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block mb-1 font-medium">Services</label>
                <select name="service[]" multiple class="w-full border px-3 py-2 rounded">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Availability Section -->
        <div id="availabilitySection" class="mt-6 hidden">
            <h3 class="font-semibold mb-2">Availability</h3>
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
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Create User</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const roleSelect = document.getElementById("roleSelect");
    const employeeFields = document.getElementById("employeeFields");
    const availabilitySection = document.getElementById("availabilitySection");

    // Toggle employee fields
    roleSelect.addEventListener("change", () => {
        if (roleSelect.value === "employee") {
            employeeFields.classList.remove("hidden");
            availabilitySection.classList.remove("hidden");
        } else {
            employeeFields.classList.add("hidden");
            availabilitySection.classList.add("hidden");
        }
    });

    const slotDurationInput = document.getElementById("slot_duration");
    const breakDurationInput = document.getElementById("break_duration");

    function parseTime(str) {
        const [h, m] = str.split(":").map(Number);
        return new Date(0, 0, 0, h, m, 0, 0);
    }

    function getDurations() {
        return {
            slotDuration: parseInt(slotDurationInput.value) || 0,
            breakDuration: parseInt(breakDurationInput.value) || 0
        };
    }

    function recalcEndTime(startInput) {
        const { slotDuration } = getDurations();
        const endInput = startInput.closest(".flex").querySelector(".end-time");
        if (!startInput.value) return;
        const startDate = parseTime(startInput.value);
        const endDate = new Date(startDate.getTime() + slotDuration * 60000);
        const hh = String(endDate.getHours()).padStart(2, "0");
        const mm = String(endDate.getMinutes()).padStart(2, "0");
        endInput.value = `${hh}:${mm}`;
    }

    function validateDay(wrapper) {
        const { slotDuration, breakDuration } = getDurations();
        const startTimes = wrapper.querySelectorAll(".start-time");
        const endTimes = wrapper.querySelectorAll(".end-time");
        const errorBox = wrapper.querySelector(".error-box");
        errorBox.textContent = "";

        let slots = [];
        startTimes.forEach((start, i) => {
            if (start.value && endTimes[i].value) {
                slots.push({
                    start: parseTime(start.value),
                    end: parseTime(endTimes[i].value),
                    startStr: start.value,
                    endStr: endTimes[i].value
                });
            }
        });

        slots.sort((a, b) => a.start - b.start);

        for (let i = 0; i < slots.length; i++) {
            let s = slots[i];
            const duration = (s.end - s.start) / 60000;
            if (duration !== slotDuration) {
                errorBox.textContent = `Each slot must be ${slotDuration} minutes. Error: ${s.startStr}-${s.endStr}.`;
                return false;
            }

            if (i < slots.length - 1) {
                const gap = (slots[i + 1].start - s.end) / 60000;
                if (gap < breakDuration) {
                    errorBox.textContent = `Break between slots must be at least ${breakDuration} minutes.`;
                    return false;
                }
            }
        }
        return true;
    }

    function updateAllSlots() {
        document.querySelectorAll(".time-slot-wrapper").forEach(wrapper => {
            wrapper.querySelectorAll(".start-time").forEach(input => recalcEndTime(input));
            validateDay(wrapper);
        });
    }

    slotDurationInput.addEventListener("change", updateAllSlots);
    breakDurationInput.addEventListener("change", updateAllSlots);

    document.querySelectorAll(".add-slot").forEach(btn => {
        btn.addEventListener("click", () => {
            const day = btn.dataset.day;
            const wrapper = document.querySelector(`.time-slot-wrapper[data-day="${day}"]`);
            const slotGroup = document.createElement("div");
            slotGroup.classList.add("flex", "gap-2", "items-center");
            slotGroup.innerHTML = `
                <input type="time" name="days[${day}][start][]" class="start-time border px-2 py-1 rounded w-full">
                <input type="time" name="days[${day}][end][]" class="end-time border px-2 py-1 rounded w-full" readonly>
                <button type="button" class="remove-slot text-red-600 font-bold">✖</button>
            `;
            wrapper.insertBefore(slotGroup, wrapper.querySelector(".error-box"));

            const startInput = slotGroup.querySelector(".start-time");
            startInput.addEventListener("input", (e) => {
                recalcEndTime(e.target);
                validateDay(wrapper);
            });

            slotGroup.querySelector(".remove-slot").addEventListener("click", () => {
                slotGroup.remove();
                validateDay(wrapper);
            });
        });
    });

    // AJAX submit with SweetAlert
    const form = document.getElementById("createUserForm");
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        updateAllSlots();

        let valid = true;
        document.querySelectorAll(".time-slot-wrapper").forEach(wrapper => {
            if (!validateDay(wrapper)) valid = false;
        });

        if (!valid) {
            Swal.fire("Invalid Availability", "Please fix errors before submitting.", "error");
            return;
        }

        const formData = new FormData(form);
        fetch(form.action, {
            method: "POST",
            body: formData,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(res => res.json())
        .then(data => {
            if (data.valid) {
                Swal.fire({
                    icon: "success",
                    title: "User Created!",
                    text: "The user has been added successfully.",
                }).then(() => window.location.href = "{{ route('admin.users.index') }}");
            } else {
                Swal.fire("Error", data.message || "Validation failed", "error");
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire("Error", "Something went wrong.", "error");
        });
    });
});
</script>
@endpush
