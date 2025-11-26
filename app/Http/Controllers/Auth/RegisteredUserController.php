<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    /**
     * Show the registration form.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration request and store user in Firebase.
     */
    public function store(Request $request): RedirectResponse
    {
        // Basic validation (no unique: rule because we'll check Firebase manually)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'regex:/^09\d{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
        ]);

        // --- Manual uniqueness checks in Firebase ---
        $users = $this->database->getReference('users')->getValue() ?? [];

        // normalize for comparison
        $emailToCheck = strtolower($request->email);
        $phoneToCheck = $request->phone;

        foreach ($users as $existing) {
            if (!is_array($existing)) {
                continue; // defensive
            }

            if (isset($existing['email']) && strtolower($existing['email']) === $emailToCheck) {
                return back()->withInput()->withErrors(['email' => 'This email is already registered.']);
            }

            if (isset($existing['phone']) && $existing['phone'] === $phoneToCheck) {
                return back()->withInput()->withErrors(['phone' => 'This phone number is already registered.']);
            }
        }

        // --- Save to Firebase ---
        $userId = (string) Str::uuid();

        $this->database
            ->getReference("users/{$userId}")
            ->set([
                'id' => $userId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status'     => 1,
                'password' => Hash::make($request->password),
                'user_type' => 'client',
                'created_at' => now()->toDateTimeString(),
            ]);

        return redirect()->route('login')->with('status', 'Registration successful. Please login.');
    }
}
