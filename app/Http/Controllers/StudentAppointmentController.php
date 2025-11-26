<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Services\FirebaseService;

class StudentAppointmentController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    /** --------------------------
     *  INDEX - Show all appointments for logged-in student
     *  -------------------------- */
        public function index()
    {
        $userId = auth()->id();

        // Fetch all appointments for this user
        $appointmentsSnapshot = $this->db
            ->getReference('appointments')
            ->orderByChild('user_id')
            ->equalTo($userId)
            ->getValue();

        $appointments = $appointmentsSnapshot ? array_values($appointmentsSnapshot) : [];

        // Fetch categories
        $categoriesSnapshot = $this->db->getReference('categories')->getValue();
        $categories = [];

        if ($categoriesSnapshot) {
            foreach ($categoriesSnapshot as $key => $value) {
                // ✅ Only include categories that are published and not deleted
                if (
                    isset($value['status']) &&
                    strtoupper($value['status']) === 'PUBLISHED' &&
                    empty($value['deleted_at'])
                ) {
                    $value['id'] = $key;
                    $categories[] = $value;
                }
            }
        }

        // Fetch services
        $servicesSnapshot = $this->db->getReference('services')->getValue();
        $services = [];

        if ($servicesSnapshot) {
            foreach ($servicesSnapshot as $key => $value) {
                $value['id'] = $key;
                $services[] = $value;
            }
        }

        // Fetch employees
        $employeesSnapshot = $this->db->getReference('employees')->getValue();
        $employees = [];

        if ($employeesSnapshot) {
            foreach ($employeesSnapshot as $key => $value) {
                $value['id'] = $key;
                $employees[] = $value;
            }
        }

        // Group employees by their assigned service
        $staffGroupedByService = [];
        foreach ($employees as $employee) {
            if (isset($employee['services']) && is_array($employee['services'])) {
                foreach ($employee['services'] as $serviceId) {
                    $staffGroupedByService[$serviceId][] = [
                        'id' => $employee['id'] ?? null,
                        'name' => $employee['name'] ?? 'Unnamed',
                        'email' => $employee['email'] ?? 'No email',
                    ];
                }
            }
        }

        return view('student.appointments', compact('appointments', 'categories', 'services', 'employees', 'staffGroupedByService'));
    }

    /** --------------------------
     *  UPDATE STATUS
     *  -------------------------- */
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'required|string',
            'status' => 'required|in:Cancelled,Completed,No Show',
        ]);

        $ref = $this->db->getReference('appointments/' . $validated['appointment_id']);
        $snapshot = $ref->getValue();

        if (!$snapshot) {
            return redirect()->back()->with('error', 'Appointment not found.');
        }

        $ref->update(['status' => $validated['status']]);

        return redirect()->route('student.appointments.index')->with('success', 'Appointment status updated.');
    }

    /** --------------------------
     *  SHOW SERVICES
     *  -------------------------- */
    public function showServices()
    {
        $servicesSnapshot = $this->db->getReference('services')->getValue();
        $services = $servicesSnapshot ? array_values($servicesSnapshot) : [];

        return view('student.services', compact('services'));
    }

    /** --------------------------
     *  SHOW STAFF BY SERVICE
     *  -------------------------- */
    public function showStaff(Request $request)
    {
        $serviceId = $request->get('service_id');

        $employeesSnapshot = $this->db->getReference('employees')->getValue();
        $employees = collect($employeesSnapshot ?? [])->filter(function ($employee) use ($serviceId) {
            return isset($employee['services']) && in_array($serviceId, $employee['services']);
        })->values()->all();

        return view('student.staff', compact('employees', 'serviceId'));
    }

    /** --------------------------
     *  BOOKING STEP FORM
     *  -------------------------- */
    public function steps()
    {
        $categoriesSnapshot = $this->db->getReference('categories')->getValue();
        $categories = $categoriesSnapshot ? array_values($categoriesSnapshot) : [];

        $servicesSnapshot = $this->db->getReference('services')->getValue();
        $services = $servicesSnapshot ? array_values($servicesSnapshot) : [];

        $employeesSnapshot = $this->db->getReference('employees')->getValue();
        $employees = $employeesSnapshot ? array_values($employeesSnapshot) : [];

        $staffGroupedByService = [];

        foreach ($employees as $employee) {
            if (isset($employee['services']) && is_array($employee['services'])) {
                foreach ($employee['services'] as $serviceId) {
                    $staffGroupedByService[$serviceId][] = [
                        'id' => $employee['id'] ?? null,
                        'name' => $employee['name'] ?? 'Unnamed',
                        'email' => $employee['email'] ?? 'No email',
                        'slot_duration' => $employee['slot_duration'] ?? null,
                    ];
                }
            }
        }

        return view('student.appointment-form', compact('categories', 'services', 'staffGroupedByService'));
    }

    /** --------------------------
     *  STORE APPOINTMENT (BOOK)
     *  -------------------------- */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string',
            'service_id' => 'required|string',
            'staff_id' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'group_type' => 'required|string|in:solo,family,friend,others',
            'num_members' => 'required|integer|min:1|max:100',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $bookingDate = Carbon::parse($request->date);
        $bookingTime = Carbon::parse($request->time);

        if ($bookingDate->isPast()) {
            return response()->json([
                'error' => 'past_date',
                'message' => 'You cannot book a time slot in the past.'
            ], 422);
        }

        // Check for duplicates
        $appointmentsSnapshot = $this->db->getReference('appointments')->getValue();

        $alreadyBooked = collect($appointmentsSnapshot ?? [])->contains(function ($appt) use ($request, $bookingDate, $bookingTime) {
            return isset($appt['employee_id'], $appt['booking_date'], $appt['booking_time'], $appt['status'])
                && $appt['employee_id'] == $request->staff_id
                && $appt['booking_date'] === $bookingDate->toDateString()
                && $appt['booking_time'] === $bookingTime->format('H:i:s')
                && $appt['status'] !== 'Cancelled';
        });

        if ($alreadyBooked) {
            return response()->json([
                'error' => 'duplicate_booking',
                'message' => 'This time slot is already booked.'
            ], 422);
        }

        // Prepare data
        $bookingId = strtoupper('BOOK-' . Str::random(5) . '-' . uniqid());
        $user = auth()->user();

        $appointmentData = [
            'id'           => $bookingId,
            'user_id'      => $user->id,
            'employee_id'  => $request->staff_id,
            'service_id'   => $request->service_id,
            'category_id'  => $request->category_id,
            'booking_id'   => $bookingId,
            'booking_date' => $bookingDate->toDateString(),
            'booking_time' => $bookingTime->format('H:i:s'),
            'status'       => 'Pending',
            'name'         => $user->name,
            'email'        => $user->email,
            'phone'        => $user->phone,
            'group_type'   => $request->group_type,
            'num_members'  => $request->num_members,
            'description'  => $request->description ?? null,
            'created_at'   => now()->toDateTimeString(),
        ];

        // Store in Firebase
        $this->db->getReference('appointments/' . $bookingId)->set($appointmentData);

        return response()->json([
            'success' => 'Appointment successfully booked!',
            'redirect' => route('student.appointments.index'),
            'appointment_id' => $bookingId
        ]);
    }

    /** --------------------------
     *  GET AVAILABLE SLOTS
     *  -------------------------- */
    public function getAvailability(Request $request)
    {
        $staffId = $request->query('staff_id');
        $date = $request->query('date');

        $employeeSnapshot = $this->db->getReference('employees/' . $staffId)->getValue();

        if (!$employeeSnapshot || !isset($employeeSnapshot['days'])) {
            return response()->json([]);
        }

        $days = $employeeSnapshot['days'];
        $dayName = strtolower(Carbon::parse($date)->format('l'));

        if (empty($days[$dayName])) {
            return response()->json([]);
        }

        $appointmentsSnapshot = $this->db->getReference('appointments')->getValue();

        $slots = collect($days[$dayName]['start'] ?? [])->map(function ($startTime, $index) use ($days, $dayName, $appointmentsSnapshot, $staffId, $date) {
            $endTime = $days[$dayName]['end'][$index] ?? null;
            if (!$endTime) return null;

            $start = Carbon::parse("$date $startTime");
            $end = Carbon::parse("$date $endTime");

            $isBooked = collect($appointmentsSnapshot ?? [])->contains(function ($appt) use ($staffId, $date, $startTime) {
                return isset($appt['employee_id'], $appt['booking_date'], $appt['booking_time'])
                    && $appt['employee_id'] == $staffId
                    && $appt['booking_date'] === $date
                    && Carbon::parse($appt['booking_time'])->equalTo(Carbon::parse($startTime))
                    && $appt['status'] !== 'Cancelled';
            });

            return [
                'datetime' => $start->toDateTimeString(),
                'range' => $start->format('h:i A') . ' - ' . $end->format('h:i A'),
                'booked' => $isBooked,
                'disabled' => $start->lt(Carbon::now())
            ];
        })->filter()->values();

        return response()->json($slots);
    }

    public function checkDuplicate(Request $request)
    {
        $appointments = $this->db->getReference('appointments')->getValue() ?? [];

        $studentId = auth()->id();
        $selectedDate = $request->input('date');
        $selectedTime = $request->input('time');
        $employeeId = $request->input('employee_id');

        $duplicate = collect($appointments)->contains(function ($appointment) use ($studentId, $selectedDate, $selectedTime, $employeeId) {
            return isset($appointment['user_id'], $appointment['employee_id'], $appointment['booking_date'], $appointment['booking_time'])
                && $appointment['user_id'] == $studentId
                && $appointment['employee_id'] == $employeeId
                && $appointment['booking_date'] == $selectedDate
                && $appointment['booking_time'] == $selectedTime
                && ($appointment['status'] ?? '') !== 'Cancelled';
        });

        return response()->json(['exists' => $duplicate]);
    }

    public function getSpecialDays()
    {
        $specialDaysSnapshot = $this->db->getReference('special_days')->getValue();

        $disabledDates = [];

        if ($specialDaysSnapshot) {
            foreach ($specialDaysSnapshot as $day) {
                if (
                    isset($day['date'], $day['status']) &&
                    $day['status'] === 'active'
                ) {
                    $disabledDates[] = $day['date']; // Format: Y-m-d
                }
            }
        }

        return response()->json($disabledDates);
    }

        /** --------------------------
     *  GET STAFF WORKING DAYS (for date picker)
     *  -------------------------- */
    public function getStaffDays(Request $request)
    {
        $staffId = $request->query('staff_id');
        if (!$staffId) {
            return response()->json(['days' => []]);
        }

        $employee = $this->db->getReference('employees/' . $staffId)->getValue();

        if (!$employee || !isset($employee['days'])) {
            return response()->json(['days' => []]);
        }

        // Firebase structure example:
        // "days": {
        //   "monday": { "start": ["09:00"], "end": ["17:00"] },
        //   "tuesday": { "start": ["09:00"], "end": ["17:00"] }
        // }
        $workingDays = array_keys($employee['days']);

        return response()->json(['days' => $workingDays]);
    }

    /** --------------------------
     *  GET BOOKED DATES FOR STAFF
     *  -------------------------- */
    public function getBookedDates(Request $request)
    {
        $staffId = $request->query('staff_id');
        if (!$staffId) {
            return response()->json([]);
        }

        $appointmentsSnapshot = $this->db->getReference('appointments')->getValue();

        if (!$appointmentsSnapshot) {
            return response()->json([]);
        }

        $bookedDates = collect($appointmentsSnapshot)
            ->filter(function ($appt) use ($staffId) {
                return isset($appt['employee_id'], $appt['booking_date'], $appt['status'])
                    && $appt['employee_id'] === $staffId
                    && $appt['status'] !== 'Cancelled';
            })
            ->pluck('booking_date')
            ->unique()
            ->values()
            ->map(fn($date) => ['date' => $date]);

        return response()->json($bookedDates);
    }
}
