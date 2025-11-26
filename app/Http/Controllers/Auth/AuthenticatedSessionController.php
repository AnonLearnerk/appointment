<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    /**
     * Show login form
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            return match ($user->user_type) {
                'admin' => redirect()->route('admin.dashboard'),
                'employee' => redirect()->route('employee.dashboard'),
                default => redirect()->route('student.appointments.index'),
            };
        }

        return view('auth.login');
    }

    /**
     * Handle Firebase login
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->has('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // 🔹 Fetch Firebase user by email
        $usersRef = $this->database->getReference('users')->getValue();

        $firebaseUserId = null;
        if ($usersRef) {
            foreach ($usersRef as $uid => $userData) {
                if (isset($userData['email']) && strtolower($userData['email']) === strtolower($user->email)) {
                    $firebaseUserId = $uid;
                    break;
                }
            }
        }

        // 🔹 Store UID in session
        session(['firebase_user_id' => $firebaseUserId]);

        // 🔹 Quick debug: check if UID is stored
        // dd(session('firebase_user_id'));

        return match ($user->user_type) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'employee' => redirect()->intended(route('employee.dashboard')),
            default => redirect()->intended(route('student.appointments.index')),
        };
    }

    /**
     * Logout
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
