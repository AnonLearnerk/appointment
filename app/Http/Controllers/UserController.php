<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\FirebaseService;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Service;

class UserController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    /**
     * Fetch only active services from Firebase
     */
    protected function getServicesFromFirebase()
    {
        $all = $this->database->getReference('services')->getValue() ?? [];
        $items = [];

        foreach ($all as $key => $val) {
            if (is_array($val) && isset($val['status']) && (int) $val['status'] === 1) {
                $val['id'] = $key;
                $items[] = (object) $val;
            }
        }
        return $items;
    }

    /**
     * Validate employee time slots against slot duration + break duration
     *
     * Accepts both formats:
     *  - days['monday'] = [ ['start'=>'09:00','end'=>'09:30'], ... ]
     *  - days['monday'] = [ 'start' => ['09:00', ...], 'end' => ['09:30', ...] ]
     *
     * @param array $days
     * @param int $slotDuration
     * @param int $breakDuration
     * @return array [valid => bool, message => string]
     */
    private function validateAvailability(array $days, int $slotDuration, int $breakDuration): array
    {
        foreach ($days as $day => $slots) {
            if (empty($slots)) continue;

            // Normalize input: produce $pairs = [ ['start'=>'HH:MM','end'=>'HH:MM'], ... ]
            $pairs = [];

            // Case A: form submission shape: ['start' => [...], 'end' => [...]]
            if (isset($slots['start']) && is_array($slots['start'])) {
                foreach ($slots['start'] as $idx => $s) {
                    $e = $slots['end'][$idx] ?? null;
                    // skip empty pairs
                    if (($s === null || $s === '') && ($e === null || $e === '')) continue;
                    $pairs[] = ['start' => $s, 'end' => $e];
                }
            } else {
                // Case B: already paired array of slots
                foreach ($slots as $slot) {
                    if (!is_array($slot)) continue;
                    $s = $slot['start'] ?? ($slot[0] ?? null);
                    $e = $slot['end']   ?? ($slot[1] ?? null);
                    if (($s === null || $s === '') && ($e === null || $e === '')) continue;
                    $pairs[] = ['start' => $s, 'end' => $e];
                }
            }

            if (empty($pairs)) continue;

            // Convert to timestamps and validate basic shape
            $normalized = [];
            foreach ($pairs as $slot) {
                if (!isset($slot['start'], $slot['end'])) continue;
                $startStr = trim($slot['start']);
                $endStr   = trim($slot['end']);
                if ($startStr === '' || $endStr === '') continue;

                $start = strtotime($startStr);
                $end   = strtotime($endStr);

                if ($start === false || $end === false) {
                    return ['valid' => false, 'message' => "Invalid time format on {$day}"];
                }

                if ($end <= $start) {
                    return ['valid' => false, 'message' => "End time must be after start time on {$day}"];
                }

                $normalized[] = ['start' => $start, 'end' => $end];
            }

            if (empty($normalized)) continue;

            // Sort by start time
            usort($normalized, fn($a, $b) => $a['start'] <=> $b['start']);

            // Validate
            foreach ($normalized as $i => $slot) {
                $start = $slot['start'];
                $end   = $slot['end'];

                $slotMinutes = intval(($end - $start) / 60);
                if ($slotMinutes !== (int)$slotDuration) {
                    return [
                        'valid' => false,
                        'message' => "Slot duration must be exactly {$slotDuration} minutes on {$day}"
                    ];
                }

                if ($i > 0) {
                    $prevEnd = $normalized[$i - 1]['end'];
                    $diffPrev = intval(($start - $prevEnd) / 60);
                    if ($diffPrev < (int)$breakDuration) {
                        $prevEndStr = date('H:i', $prevEnd);
                        $currStartStr = date('H:i', $start);
                        return [
                            'valid' => false,
                            'message' => "The break between an existing timeslot {$day}, {$prevEndStr} and newly added timeslot {$currStartStr} is too short. Required break: {$breakDuration} minutes."
                        ];
                    }
                }
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * List all users with pagination + filters
     */
    public function index(Request $request)
    {
        $all = $this->database->getReference('users')->getValue() ?? [];

        $items = [];
        foreach ($all as $key => $val) {
            $val = is_array($val) ? $val : [];
            $val['id'] = $key;
            $val['status'] = $val['status'] ?? 1;
            $val['user_type'] = $val['user_type'] ?? 'client';
            $val['created_at'] = $val['created_at'] ?? now()->toDateTimeString(); // fallback timestamp
            $items[] = $val;
        }

        // ✅ Sort by created_at (oldest first)
        usort($items, function ($a, $b) {
            $timeA = strtotime($a['created_at'] ?? '') ?: 0;
            $timeB = strtotime($b['created_at'] ?? '') ?: 0;
            return $timeA <=> $timeB; // oldest first
        });

        // ✅ Search filter
        if ($request->filled('search')) {
            $q = strtolower($request->search);
            $items = array_filter($items, function ($u) use ($q) {
                return str_contains(strtolower($u['name'] ?? ''), $q)
                    || str_contains(strtolower($u['email'] ?? ''), $q);
            });
        }

        // ✅ Role filter
        if ($request->filled('role')) {
            $role = $request->role;
            $items = array_filter($items, fn($u) => ($u['user_type'] ?? '') === $role);
        }

        // ✅ Status filter
        if ($request->has('status') && $request->status !== '') {
            $status = (string) $request->status;
            $items = array_filter($items, fn($u) => (string)($u['status'] ?? '') === $status);
        }

        // ✅ Pagination
        $items = array_values($items);
        $total = count($items);
        $perPage = 10;
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        $paged = array_slice($items, $offset, $perPage);
        $pagedObjects = array_map(fn($u) => (object) $u, $paged);

        $paginator = new LengthAwarePaginator(
            $pagedObjects,
            $total,
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $paginator->appends($request->query());

        return view('admin.users.index', ['users' => $paginator]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $roles = ['employee','client'];
        $services = $this->getServicesFromFirebase();
        $steps = ['10','15','20','30','45','60'];
        $breaks = ['5','10','15','20','25','30'];
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        return view('admin.users.create', compact('roles','services','steps','breaks','days'));
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'nullable|string|max:20',
            'password'       => 'required|string|min:6|confirmed',
            'roles'          => 'required|string|in:employee,client',
            'service'        => 'nullable|array',
            'slot_duration'  => 'nullable|integer',
            'break_duration' => 'nullable|integer',
            'days'           => 'nullable|array',
        ]);

        // ✅ Check if email already exists in Firebase users
        $users = $this->database->getReference('users')->getValue() ?? [];
        foreach ($users as $user) {
            if (isset($user['email']) && strtolower($user['email']) === strtolower($validated['email'])) {
                $msg = 'The email address is already registered.';
                if ($request->ajax()) {
                    return response()->json(['valid' => false, 'message' => $msg]);
                }
                return back()->withErrors(['email' => $msg])->withInput();
            }
        }

        $id = Str::uuid()->toString();
        $createdAt = now()->toDateTimeString(); // ✅ Store creation timestamp

        // ✅ Base user data
        $userData = [
            'id'         => $id,
            'name'       => $validated['name'],
            'email'      => strtolower($validated['email']),
            'phone'      => $validated['phone'] ?? '',
            'status'     => 1,
            'user_type'  => $validated['roles'],
            'password'   => Hash::make($validated['password']),
            'created_at' => $createdAt, // 👈 Added created_at field
        ];

        // ✅ If employee, validate slot and break availability
        if ($validated['roles'] === 'employee') {
            $slotDuration  = $validated['slot_duration'] ?? 30;
            $breakDuration = $validated['break_duration'] ?? 10;
            $days          = $validated['days'] ?? [];

            $check = $this->validateAvailability($days, $slotDuration, $breakDuration);
            if (!$check['valid']) {
                if ($request->ajax()) {
                    return response()->json($check);
                }
                return back()->withErrors(['availability' => $check['message']])->withInput();
            }
        }

        // ✅ Save user in Firebase
        $this->database->getReference("users/{$id}")->set($userData);

        // ✅ If employee, save also to employees
        if ($validated['roles'] === 'employee') {
            $employeeRef = $this->database->getReference("employees/{$id}");
            $employeeRef->set([
                'id'             => $id,
                'name'           => $userData['name'],
                'email'          => $userData['email'],
                'phone'          => $userData['phone'],
                'services'       => $validated['service'] ?? [],
                'slot_duration'  => $validated['slot_duration'] ?? 30,
                'break_duration' => $validated['break_duration'] ?? 10,
                'days'           => $validated['days'] ?? [],
                'created_at'     => $createdAt, // 👈 Added here too
            ]);
        }

        // ✅ Handle response
        if ($request->ajax()) {
            return response()->json([
                'valid'   => true,
                'message' => 'User created successfully!',
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    /**
     * Edit user
     */
    public function edit($id)
    {
        $user = $this->database->getReference("users/{$id}")->getValue();

        if (!$user) {
            return back()->withErrors(['user' => 'User not found in Firebase']);
        }

        $roles = ['client', 'employee']; 
        $selectedRole = $user['user_type'] ?? 'client';

        $employeeData = [];
        $employeeDays = [];
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        if ($selectedRole === 'employee') {
            $employeeData = $this->database->getReference("employees/{$id}")->getValue() ?? [];
            $rawDays = $employeeData['days'] ?? [];

            foreach ($days as $day) {
                $employeeDays[$day] = [];

                if (isset($rawDays[$day]['start']) && isset($rawDays[$day]['end'])) {
                    $starts = $rawDays[$day]['start'];
                    $ends   = $rawDays[$day]['end'];

                    // Loop numeric indices
                    foreach ($starts as $key => $startTime) {
                        $endTime = $ends[$key] ?? null;
                        $employeeDays[$day][] = [
                            'start' => $startTime,
                            'end'   => $endTime
                        ];
                    }
                }
            }
        }

        $steps = [10, 15, 20, 30, 45, 60];
        $breaks = [5, 10, 15, 20, 25, 30];
        $services = $this->getServicesFromFirebase();

        return view('admin.users.edit', compact(
            'user', 'roles', 'selectedRole', 'steps', 'breaks', 'days', 'services', 'employeeDays', 'employeeData'
        ));
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'nullable|string|max:20',
            'roles'          => 'nullable|string|in:employee,client',
            'status'         => 'required|boolean',
            'service'        => 'nullable|array',
            'slot_duration'  => 'required_if:roles,employee|integer',
            'break_duration' => 'required_if:roles,employee|integer',
            'days'           => 'nullable|array',
        ]);

        $userRef = $this->database->getReference("users/{$id}");
        $user = $userRef->getValue();

        if (!$user) {
            return $request->ajax()
                ? response()->json(['valid' => false, 'message' => 'User not found.'])
                : redirect()->route('admin.users.index')->withErrors(['user' => 'User not found']);
        }

        // ✅ Handle role logic
        $newUserType = $user['user_type'];
        if (isset($validated['roles']) && $user['user_type'] !== 'admin') {
            $newUserType = $validated['roles'];
        }

        // ✅ Validate employee availability if applicable
        if ($newUserType === 'employee') {
            $slotDuration  = (int) $validated['slot_duration'];
            $breakDuration = (int) $validated['break_duration'];
            $days          = $validated['days'] ?? [];

            $check = $this->validateAvailability($days, $slotDuration, $breakDuration);
            if (!$check['valid']) {
                return $request->ajax()
                    ? response()->json($check)
                    : back()->withErrors(['availability' => $check['message']])->withInput();
            }
        }

        // ✅ Update basic user info
        $updates = [
            'name'      => $validated['name'],
            'email'     => strtolower($validated['email']),
            'phone'     => $validated['phone'] ?? '',
            'status'    => (int)$validated['status'],
            'user_type' => $user['user_type'],
        ];

        if (isset($validated['roles']) && $user['user_type'] !== 'admin') {
            $updates['user_type'] = $validated['roles'];
        }

        $userRef->update($updates);

        // ✅ Update employee-specific data if user is an employee
        if ($updates['user_type'] === 'employee') {
            $employeeRef = $this->database->getReference("employees/{$id}");
            $employeeData = $employeeRef->getValue() ?? [];

            $employeeData['id']             = $id;
            $employeeData['name']           = $updates['name'];
            $employeeData['email']          = $updates['email'];
            $employeeData['phone']          = $updates['phone'];
            $employeeData['services']       = $validated['service'] ?? [];
            $employeeData['slot_duration']  = (int)$validated['slot_duration'];
            $employeeData['break_duration'] = (int)$validated['break_duration'];

            // ✅ Handle removal of deleted days (fixes timeslot deletion issue)
            $existingDays = array_keys($employeeData['days'] ?? []);
            $newDays = array_keys($validated['days'] ?? []);

            foreach ($existingDays as $day) {
                if (!in_array($day, $newDays)) {
                    unset($employeeData['days'][$day]);
                }
            }

            // ✅ Replace or update current days
            $employeeData['days'] = $validated['days'] ?? [];

            // ✅ Write updated employee data
            $employeeRef->set($employeeData);
        }

        // ✅ Respond based on request type
        if ($request->ajax()) {
            return response()->json(['valid' => true, 'message' => 'User updated successfully!']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function trashView(Request $request)
    {
        $all = $this->database->getReference('users')->getValue() ?? [];
        $trashed = [];

        foreach ($all as $key => $val) {
            $val = is_array($val) ? $val : [];
            if (($val['status'] ?? 1) == 0) { // status = 0 means soft deleted
                $val['id'] = $key;
                $trashed[] = $val;
            }
        }

        return view('admin.users.trash', ['users' => $trashed]);
    }

    public function restore($id)
    {
        $userRef = $this->database->getReference("users/{$id}");
        $user = $userRef->getValue();

        if (!$user) {
            return back()->withErrors(['User not found in Firebase']);
        }

        // Restore user
        $userRef->update(['status' => 1, 'deleted_at' => null]);

        // Restore in employees table if employee
        if (($user['user_type'] ?? '') === 'employee') {
            $this->database->getReference("employees/{$id}")
                ->update(['status' => 1, 'deleted_at' => null]);
        }

        return back()->with('success', 'User restored successfully!');
    }

    public function force_delete($id)
    {
        $userRef = $this->database->getReference("users/{$id}");
        $user = $userRef->getValue();

        if (!$user) {
            return back()->withErrors(['User not found in Firebase']);
        }

        // Delete user record permanently
        $userRef->remove();

        // Delete employee record if any
        if (($user['user_type'] ?? '') === 'employee') {
            $this->database->getReference("employees/{$id}")->remove();
        }

        return back()->with('success', 'User permanently deleted!');
    }

    /**
     * Delete user
     */
    public function destroy(string $id)
    {
        $userRef = $this->database->getReference("users/{$id}");
        $user = $userRef->getValue();

        if (!$user) {
            return back()->withErrors(['User not found in Firebase']);
        }

        if (($user['user_type'] ?? '') === 'admin') {
            return back()->withErrors(['Cannot delete admin user.']);
        }

        // Soft delete instead of remove
        $userRef->update(['status' => 0, 'deleted_at' => now()->toDateTimeString()]);

        if (($user['user_type'] ?? '') === 'employee') {
            $this->database->getReference("employees/{$id}")
                ->update(['status' => 0, 'deleted_at' => now()->toDateTimeString()]);
        }

        return redirect()->back()->with('success', 'User moved to trash successfully!');
    }
}
