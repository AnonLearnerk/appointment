<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class EmployeeDashboardController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    public function index()
    {
        $userId = Auth::id();
        $employeeId = $userId;
        $now = Carbon::now();

        // Get all appointments
        $appointments = $this->db->getReference('appointments')->getValue() ?? [];

        // Get services to map IDs to titles
        $services = $this->db->getReference('services')->getValue() ?? [];

        // Helper to get service name
        $getServiceTitle = function ($serviceId) use ($services) {
            foreach ($services as $key => $service) {
                if (($service['id'] ?? $key) === $serviceId) {
                    return $service['title'] ?? $service['name'] ?? 'Unknown Service';
                }
            }
            return 'Unknown Service';
        };

        // Normalize and filter
        $allAppointments = collect($appointments)
            ->map(function ($appt, $key) use ($getServiceTitle) {
                return [
                    'id' => $appt['id'] ?? $key,
                    // 'booking_id' => $appt['booking_id'] ?? 'N/A',
                    'name' => $appt['name'] ?? 'N/A',
                    'email' => $appt['email'] ?? 'N/A',
                    'phone' => $appt['phone'] ?? 'N/A',
                    'employee_id' => $appt['employee_id'] ?? '',
                    'service_title' => $getServiceTitle($appt['service_id'] ?? ''),
                    'booking_date' => $appt['booking_date'] ? substr($appt['booking_date'], 0, 10) : null, // assuming date not stored separately
                    'booking_time' => $appt['booking_time'] ?? 'N/A',
                    'status' => $appt['status'] ?? 'Pending',
                ];
            })
            ->filter(fn($appt) => $appt['employee_id'] === $employeeId)
            ->sortByDesc('booking_date')
            ->values();

        $upcomingAppointments = $allAppointments
            ->filter(fn($appt) =>
                isset($appt['booking_date'], $appt['booking_time']) &&
                Carbon::parse($appt['booking_date'].' '.$appt['booking_time'])->gte($now) &&
                $appt['status'] !== 'Cancelled'
            )
            ->sortBy(['booking_date', 'booking_time'])
            ->take(5)
            ->values();

        return view('employee.dashboard', compact('upcomingAppointments', 'allAppointments'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|string',
            'status' => 'required|in:Pending,Completed,Cancelled,No Show',
        ]);

        $userId = Auth::id();
        $appointmentId = $request->appointment_id;

        $appointmentRef = $this->db->getReference('appointments/' . $appointmentId);
        $appointment = $appointmentRef->getValue();

        if (!$appointment) {
            abort(404, 'Appointment not found.');
        }

        if (($appointment['employee_id'] ?? null) !== $userId) {
            abort(403, 'Unauthorized action.');
        }

        $appointmentRef->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Appointment status updated successfully.');
    }
}
