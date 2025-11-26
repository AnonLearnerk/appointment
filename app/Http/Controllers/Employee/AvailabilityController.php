<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\FirebaseService;

class AvailabilityController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    public function edit()
    {
        $userId = Auth::id();
        $employee = $this->db->getReference('employees/' . $userId)->getValue();

        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $steps = ['10', '15', '20', '30', '45', '60'];
        $breaks = ['5', '10', '15', '20', '25', '30'];

        $availability = [];

        if (isset($employee['days'])) {
            foreach ($employee['days'] as $day => $data) {
                $availability[$day] = [];
                if (isset($data['start'], $data['end'])) {
                    foreach ($data['start'] as $i => $startTime) {
                        $endTime = $data['end'][$i] ?? null;
                        if ($endTime) {
                            $availability[$day][] = [
                                'start' => $startTime,
                                'end'   => $endTime
                            ];
                        }
                    }
                }
            }
        }

        foreach ($daysOfWeek as $day) {
            $availability[$day] = $availability[$day] ?? [];
        }

        return view('employee.availability.edit', compact('employee', 'availability', 'daysOfWeek', 'steps', 'breaks'));
    }

    public function update(Request $request)
    {
        $userId = Auth::id();
        $today = now()->format('Y-m-d');
        $appointmentsRef = $this->db->getReference('appointments');
        $appointments = $appointmentsRef->getValue() ?? [];

        // 🧩 Step 1: Find booked days of week
        $bookedDays = [];
        foreach ($appointments as $id => $appointment) {
            if (
                isset($appointment['employee_id'], $appointment['booking_date'], $appointment['status']) &&
                $appointment['employee_id'] === $userId &&
                $appointment['booking_date'] >= $today &&
                in_array(strtolower($appointment['status']), ['pending'])
            ) {
                $dayOfWeek = strtolower(\Carbon\Carbon::parse($appointment['booking_date'])->format('l'));
                $bookedDays[$dayOfWeek] = true;
            }
        }

        // 🧩 Step 2: Validate the form
        $validated = $request->validate([
            'slot_duration' => 'required|integer|min:1',
            'break_duration' => 'required|integer|min:0',
            'availability' => 'required|array',
            'availability.*' => 'array',
            'availability.*.*.start' => 'required|regex:/^\d{2}:\d{2}$/',
            'availability.*.*.end' => 'required|regex:/^\d{2}:\d{2}$/',
        ]);

        // 🧩 Step 3: Check if they’re trying to modify booked days
        $attemptedDays = array_keys($validated['availability']);
        $lockedDays = array_intersect($attemptedDays, array_keys($bookedDays));
        $transformedDays = [];


        // 🧩 Step 4: Proceed with saving
        $employeeRef = $this->db->getReference('employees/' . $userId);
        $slotDuration = (int) $validated['slot_duration'];
        $breakDuration = (int) $validated['break_duration'];
        $transformedDays = [];

        foreach ($validated['availability'] as $day => $slots) {
            $starts = [];
            $ends = [];

            usort($slots, fn($a, $b) => strcmp($a['start'], $b['start']));
            $lastEnd = null;

            // Loop through validated days
            foreach ($validated['availability'] as $day => $slots) {
                // Skip locked days
                if (in_array($day, $lockedDays)) {
                    continue; // Don't modify this day
                }

                $starts = [];
                $ends = [];
                usort($slots, fn($a, $b) => strcmp($a['start'], $b['start']));
                $lastEnd = null;

                foreach ($slots as $slot) {
                    $start = \Carbon\Carbon::createFromFormat('H:i', $slot['start']);
                    $end = \Carbon\Carbon::createFromFormat('H:i', $slot['end']);

                    $duration = $start->diffInMinutes($end);
                    if ($duration != $slotDuration) {
                        return back()->withErrors([
                            'availability' => "Slot on " . ucfirst($day) . " must be exactly {$slotDuration} minutes long. Got {$duration} minutes."
                        ]);
                    }

                    if ($lastEnd) {
                        $gap = $lastEnd->diffInMinutes($start);
                        if ($gap < $breakDuration) {
                            return back()->withErrors([
                                'availability' => "Break between slots on " . ucfirst($day) . " must be at least {$breakDuration} minutes. Got {$gap} minutes."
                            ]);
                        }
                    }

                    $starts[] = $slot['start'];
                    $ends[]   = $slot['end'];
                    $lastEnd  = $end;
                }

                $transformedDays[$day] = [
                    'start' => $starts,
                    'end'   => $ends
                ];
            }
        }

        // 🧩 Step 5: Update Firebase
        $employeeRef->update([
            'slot_duration'  => $slotDuration,
            'break_duration' => $breakDuration,
            'days'           => array_merge($employeeRef->getValue()['days'] ?? [], $transformedDays),
        ]);

        $flashData = [];

        if (!empty($lockedDays)) {
            $lockedDaysList = implode(', ', array_map('ucfirst', $lockedDays));
            $flashData['warning'] = "Some days ({$lockedDaysList}) were not modified because you already have upcoming appointments.";
        }

        // Always show success for saved days
        $flashData['success'] = 'Availability updated successfully.';

        // ✅ Return JSON for AJAX
        return response()->json($flashData);
    }
}
