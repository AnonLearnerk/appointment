<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class AppointmentController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    public function index(Request $request)
    {
        // Fetch all data from Firebase
        $appointments = $this->db->getReference('appointments')->getValue() ?? [];
        $services = $this->db->getReference('services')->getValue() ?? [];
        $employees = $this->db->getReference('users')->getValue() ?? [];

        // Build lookup helpers
        $getServiceTitle = function ($serviceId) use ($services) {
            foreach ($services as $key => $service) {
                if (($service['id'] ?? $key) === $serviceId) {
                    return $service['title'] ?? $service['name'] ?? 'Unknown Service';
                }
            }
            return 'Unknown Service';
        };

        $getEmployeeName = function ($employeeId) use ($employees) {
            foreach ($employees as $key => $emp) {
                if (($emp['id'] ?? $key) === $employeeId) {
                    return $emp['name'] ?? 'Unknown Employee';
                }
            }
            return 'Unknown Employee';
        };

        // Normalize appointments
        $appointments = collect($appointments)
            ->filter(fn($appt) => !empty($appt))
            ->map(function ($appt) use ($getServiceTitle, $getEmployeeName) {
                return [
                    'id' => $appt['id'] ?? 'N/A',
                    'booking_id' => $appt['booking_id'] ?? 'N/A',
                    'name' => $appt['name'] ?? 'N/A',
                    'email' => $appt['email'] ?? 'N/A',
                    'phone' => $appt['phone'] ?? 'N/A',
                    'service_id' => $appt['service_id'] ?? null,
                    'service_title' => $getServiceTitle($appt['service_id'] ?? ''),
                    'employee_name' => $getEmployeeName($appt['employee_id'] ?? ''),
                    'booking_date' => $appt['booking_date'] ?? null,
                    'booking_time' => $appt['booking_time'] ?? 'N/A',
                    'status' => $appt['status'] ?? 'Pending',
                ];
            })
            ->values();

        // Apply filters
        if ($search = $request->input('search')) {
            $appointments = $appointments->filter(function ($appt) use ($search) {
                return str_contains(strtolower($appt['name']), strtolower($search))
                    || str_contains(strtolower($appt['email']), strtolower($search))
                    || str_contains(strtolower($appt['phone']), strtolower($search))
                    || str_contains(strtolower($appt['booking_id']), strtolower($search));
            })->values();
        }

        if ($status = $request->input('status')) {
            $appointments = $appointments->where('status', $status)->values();
        }

        if ($serviceId = $request->input('service')) {
            $appointments = $appointments->filter(fn($appt) => ($appt['service_id'] ?? null) == $serviceId)->values();
        }

        // 🔹 Format services for dropdown (convert Firebase array → simple list)
        $servicesList = collect($services)->map(function ($service, $key) {
            return (object)[
                'id' => $service['id'] ?? $key,
                'title' => $service['title'] ?? $service['name'] ?? 'Unknown Service',
            ];
        });

        // Pagination
        $perPage = 10;
        $page = $request->input('page', 1);
        $paged = $appointments->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $paged,
            $appointments->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Handle AJAX table refresh
        if ($request->ajax()) {
            return view('admin.appointments._table', [
                'appointments' => $paginated
            ])->render();
        }

        return view('admin.appointments.index', [
            'appointments' => $paginated,
            'services' => $servicesList,
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required',
            'status' => 'required|string|in:Cancelled,Completed,No Show',
        ]);

        $ref = $this->db->getReference('appointments/'.$request->appointment_id);
        $appointment = $ref->getValue();

        if (!$appointment) {
            return response()->json(['success' => false, 'message' => 'Appointment not found'], 404);
        }

        // Prevent update if status is already final
        if (in_array($appointment['status'], ['Cancelled', 'Completed', 'No Show'])) {
            return response()->json([
                'success' => false,
                'message' => 'This appointment can no longer be modified.'
            ], 400);
        }

        $ref->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment status updated successfully.'
        ]);
    }

    public function getCalendarData()
    {
        $appointments = $this->db->getReference('appointments')->getValue() ?? [];
        $services = $this->db->getReference('services')->getValue() ?? [];
        $employees = $this->db->getReference('users')->getValue() ?? [];

        // Build lookup helpers for service and employee names
        $getServiceTitle = function ($serviceId) use ($services) {
            foreach ($services as $key => $service) {
                if (($service['id'] ?? $key) === $serviceId) {
                    return $service['title'] ?? $service['name'] ?? 'Unknown Service';
                }
            }
            return 'Unknown Service';
        };

        $getEmployeeName = function ($employeeId) use ($employees) {
            foreach ($employees as $key => $emp) {
                if (($emp['id'] ?? $key) === $employeeId) {
                    return $emp['name'] ?? 'Unknown Employee';
                }
            }
            return 'Unknown Employee';
        };

        // Map appointments into FullCalendar event format
        $events = collect($appointments)->map(function ($appt, $key) use ($getServiceTitle, $getEmployeeName) {
            return [
                'id' => $appt['id'] ?? $key,
                'title' => $appt['name'] ?? 'N/A',
                'service' => $getServiceTitle($appt['service_id'] ?? ''),
                'start' => ($appt['booking_date'] ?? '') . 'T' . ($appt['booking_time'] ?? '00:00:00'),
                'end' => ($appt['booking_date'] ?? '') . 'T' . ($appt['booking_time'] ?? '00:00:00'),
                'status' => $appt['status'] ?? 'Pending',
                'email' => $appt['email'] ?? '',
                'phone' => $appt['phone'] ?? '',
                'employee' => $getEmployeeName($appt['employee_id'] ?? ''),
                'description' => 'Appointment booked by ' . ($appt['name'] ?? 'N/A'),
            ];
        })->values();

        return response()->json($events);
    }

}
