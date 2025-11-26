<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->withErrors('You must be logged in.');
        }

        $firebase = app(FirebaseService::class)->getDatabase();
        $firebaseUser = $firebase->getReference('users/' . $user->uid)->getValue();

        if (!$firebaseUser) {
            abort(403, 'User not found in Firebase.');
        }

        if (!isset($firebaseUser['role']) || $firebaseUser['role'] !== $role) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
