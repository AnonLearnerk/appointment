<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index()
    {
        // Retrieve and format appointments for FullCalendar
        $appointments = Appointment::all()->map(function ($appointment) {
            return [
                'title' => $appointment->name . ' - ' . ($appointment->notes ?? 'No reason'),
                'start' => $appointment->booking_date . 'T' . $appointment->booking_time,
                'end' => $appointment->booking_date . 'T' . date('H:i:s', strtotime($appointment->booking_time . ' +1 hour')), // Optional: end time
                'allDay' => false,
            ];
        });

        return view('admin.dashboard', [
            'appointments' => $appointments,
        ]);
    }
    
}
