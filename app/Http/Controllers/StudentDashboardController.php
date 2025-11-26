<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentDashboardController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    private function toObjectRecursive($array)
    {
        if (is_array($array)) {
            return (object) array_map([$this, 'toObjectRecursive'], $array);
        }
        return $array;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $firebase = app(FirebaseService::class);
        $db = $firebase->getDatabase();

        // Fetch Firebase data
        $appointmentsSnapshot = $db->getReference('appointments')->getValue() ?? [];
        $services = $db->getReference('services')->getValue() ?? [];
        $staffs = $db->getReference('employees')->getValue() ?? [];

        // Convert to collections
       $services = collect($services)->mapWithKeys(function ($s, $id) {
            $s['id'] = $id; // ✅ Add the Firebase key as "id"
            return [$id => (object) $s];
        });
        $staffs = collect($staffs)->mapWithKeys(function ($e, $id) {
            $e['id'] = $id; // Optional — for consistency
            return [$id => (object) $e];
        });

        // Filter appointments belonging to the logged-in user
        $appointments = collect($appointmentsSnapshot)
        ->filter(fn($a, $id) => isset($a['email']) && strtolower($a['email']) === strtolower($user->email))
        ->map(function ($a, $id) use ($services, $staffs) {
            $aObj = $this->toObjectRecursive($a);
            $aObj->id = $id;

            $aObj->service_name = isset($aObj->service_id) && isset($services[$aObj->service_id])
                ? ($services[$aObj->service_id]->title ?? 'N/A')
                : 'N/A';

            $aObj->employee_name = isset($aObj->employee_id) && isset($staffs[$aObj->employee_id])
                ? ($staffs[$aObj->employee_id]->name ?? 'N/A')
                : 'N/A';

            return $aObj;
        });

        // Get current date/time
        $nowDate = now()->toDateString();
        $nowTime = now()->format('H:i:s');

        // Upcoming appointment
        $upcomingAppointment = $appointments
            ->filter(fn($a) => isset($a->status) && strtolower($a->status) === 'pending')
            ->filter(fn($a) =>
                ($a->booking_date > $nowDate) ||
                ($a->booking_date === $nowDate && $a->booking_time >= $nowTime)
            )
            ->sortBy(fn($a) => [$a->booking_date, $a->booking_time])
            ->first();

        // Apply filters
        if ($request->search) {
            $appointments = $appointments->filter(fn($a) =>
                str_contains(strtolower($a->name ?? ''), strtolower($request->search)) ||
                str_contains(strtolower($a->email ?? ''), strtolower($request->search))
            );
        }

        if ($request->status) {
            $appointments = $appointments->where('status', $request->status);
        }

        if ($request->service) {
            $appointments = $appointments->where('category_id', $request->service);
        }

        if ($request->staff) {
            $appointments = $appointments->where('employee_id', $request->staff);
        }

        // Convert to array for Blade
        $appointments = $appointments->values();

        $page = request('page', 1);
        $perPage = 5;

        $appointments = new LengthAwarePaginator(
            $appointments->forPage($page, $perPage),
            $appointments->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('student.dashboard', compact(
            'upcomingAppointment',
            'appointments',
            'services',
            'staffs'
        ));
    }

    public function cancel($id, Request $request)
    {
        $firebase = app(FirebaseService::class);
        $db = $firebase->getDatabase();

        $appointmentRef = $db->getReference("appointments/{$id}");
        $appointment = $appointmentRef->getValue();

        if (
            !$appointment ||
            (isset($appointment['email']) && strtolower($appointment['email']) !== strtolower(auth()->user()->email)) ||
            (isset($appointment['status']) && strtolower($appointment['status']) !== 'pending')
        ) {
            return response()->json(['success' => false, 'message' => 'Appointment not found or cannot be cancelled.'], 404);
        }

        $appointmentRef->update(['status' => 'Cancelled']);

        return response()->json(['success' => true, 'message' => 'Appointment cancelled successfully.']);
    }
}
