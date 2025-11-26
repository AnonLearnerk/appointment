<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Kreait\Firebase\Factory;

class ReportController extends Controller
{
    protected $database;

    public function __construct()
    {
        // ✅ Initialize Firebase Realtime Database
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase-admin-sdk.json'))
            ->withDatabaseUri('https://appointment-system-b9648-default-rtdb.asia-southeast1.firebasedatabase.app/');
        $this->database = $factory->createDatabase();
    }

    public function index(Request $request)
    {
        $type = $request->get('type', 'weekly');

        // ✅ Get all appointments from Firebase
        $appointmentsRef = $this->database->getReference('appointments');
        $appointments = $appointmentsRef->getValue() ?? [];

        // ✅ Initialize counters
        $report = [
            'Pending' => 0,
            'Completed' => 0,
            'Cancelled' => 0,
            'No Show' => 0,
        ];

        // ✅ Set date range depending on report type
        $now = Carbon::now();
        $startDate = null;
        $endDate = null;

        switch ($type) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'weekly':
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
            case 'monthly':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'yearly':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
        }

        // ✅ Loop through Firebase data and count statuses within range
        foreach ($appointments as $appointment) {
            if (!isset($appointment['booking_date'], $appointment['status'])) {
                continue;
            }

            $bookingDate = Carbon::parse($appointment['booking_date']);

            if ($bookingDate->between($startDate, $endDate)) {
                $status = $appointment['status'];
                if (isset($report[$status])) {
                    $report[$status]++;
                }
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'type' => $type,
                'data' => $report,
            ]);
        }

        return view('admin.reports.index', [
            'data' => $report,
            'type' => $type,
        ]);
    }
}
