@extends('layouts.employee')

@section('title', 'Edit Availability')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-6">Edit Your Availability</h2>

    <form id="availabilityForm" method="POST" action="{{ route('employee.availability.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="slot_duration" class="block font-medium text-sm text-gray-700">Slot Duration</label>
                <select name="slot_duration" id="slot_duration">
                    @foreach ($steps as $step)
                        <option value="{{ $step }}" {{ ($employee['slot_duration'] ?? null) == $step ? 'selected' : '' }}>
                            {{ $step }} minutes
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="break_duration" class="block font-medium text-sm text-gray-700">Break Duration</label>
                <select name="break_duration" id="break_duration">
                    @foreach ($steps as $step)
                        <option value="{{ $step }}" {{ ($employee['break_duration'] ?? null) == $step ? 'selected' : '' }}>
                            {{ $step }} minutes
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm border">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="border px-3 py-2">Day</th>
                        <th class="border px-3 py-2">Time Slots</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($daysOfWeek as $day)
                        <tr>
                            <td class="border px-3 py-2 capitalize font-medium">{{ $day }}</td>
                            <td class="border px-3 py-2">
                                <div class="space-y-2">
                                    <div class="time-slot-wrapper" data-day="{{ $day }}" data-slot-duration="{{ $employee['slot_duration'] ?? 30 }}">
                                        @foreach($availability[$day] ?? [] as $slot)
                                            <div class="flex gap-2 mb-2 items-center">
                                                <input type="time" name="availability[{{ $day }}][{{ $loop->index }}][start]" value="{{ $slot['start'] ?? '' }}" class="start-time" required>
                                                <input type="time" name="availability[{{ $day }}][{{ $loop->index }}][end]" value="{{ $slot['end'] ?? '' }}" class="end-time bg-gray-100 text-gray-600 cursor-not-allowed" readonly required>
                                                <button type="button" class="remove-slot text-red-600">✖</button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="add-slot text-blue-600 text-sm" data-day="{{ $day }}">+ Add Time Slot</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-right">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                Save Availability
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('availabilityForm');

    // Add/remove slot buttons
    document.querySelectorAll('.add-slot').forEach(button => {
        button.addEventListener('click', function () {
            const day = this.dataset.day;
            const wrapper = document.querySelector(`.time-slot-wrapper[data-day="${day}"]`);
            const timestamp = Date.now();

            const newSlotHTML = `
                <div class="flex gap-2 mb-2 items-center">
                    <input type="time" name="availability[${day}][${timestamp}][start]" class="start-time" required>
                    <input type="time" name="availability[${day}][${timestamp}][end]" class="end-time bg-gray-100 text-gray-600 cursor-not-allowed" readonly required>
                    <button type="button" class="remove-slot text-red-600">✖</button>
                </div>
            `;
            wrapper.insertAdjacentHTML('beforeend', newSlotHTML);
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-slot')) {
            e.target.closest('div.flex').remove();
        }
    });

    // Auto-fill end time based on slot duration
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('start-time')) {
            const startInput = e.target;
            const endInput = startInput.parentElement.querySelector('.end-time');

            const slotDuration = parseInt(document.getElementById('slot_duration')?.value || 30);
            const breakDuration = parseInt(document.getElementById('break_duration')?.value || 0);

            const thisWrapper = startInput.closest('.time-slot-wrapper');
            const allStartTimes = Array.from(thisWrapper.querySelectorAll('.start-time'));
            const currentIndex = allStartTimes.indexOf(startInput);

            if (currentIndex > 0) {
                const prevEndInput = allStartTimes[currentIndex - 1].parentElement.querySelector('.end-time');
                const prevEnd = prevEndInput.value;

                if (prevEnd) {
                    const [prevH, prevM] = prevEnd.split(':').map(Number);
                    const [currH, currM] = startInput.value.split(':').map(Number);

                    const gap = (currH * 60 + currM) - (prevH * 60 + prevM);
                    if (gap < breakDuration) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Break',
                            text: `Gap between this slot and the previous one must be at least ${breakDuration} minutes. You only have ${gap} minutes.`,
                            confirmButtonColor: '#e3342f'
                        });
                        startInput.value = '';
                        endInput.value = '';
                        return;
                    }
                }
            }

            if (!startInput.value) return;

            const [startH, startM] = startInput.value.split(':').map(Number);
            const totalMinutes = startH * 60 + startM + slotDuration;
            const endH = Math.floor(totalMinutes / 60) % 24;
            const endM = totalMinutes % 60;
            endInput.value = `${String(endH).padStart(2, '0')}:${String(endM).padStart(2, '0')}`;
        }
    });

    // AJAX form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error('Network error');

            const data = await response.json();

            let icon = 'success';
            let title = 'Success';
            let text = data.success || '';

            if (data.warning) {
                icon = 'warning';
                title = 'Notice';
                text += `\n${data.warning}`;
            }

            Swal.fire({ icon, title, text, confirmButtonColor: icon === 'success' ? '#4caf50' : '#f59e0b' });

        } catch (err) {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Could not save availability.', confirmButtonColor: '#e3342f' });
        }
    });
});
</script>
@endpush
