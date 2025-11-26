<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\FirebaseService;

class ProfileController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->db = $firebaseService->getDatabase();
    }

    /**
     * Show profile edit page
     */
    public function edit(Request $request): View
    {
        $uid = session('firebase_user_id');

        $user = $this->db->getReference("users/{$uid}")->getValue();

        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update profile in Firebase
     */
    public function update(Request $request): JsonResponse
    {
        $uid = session('firebase_user_id');
        $user = $this->db->getReference("users/{$uid}")->getValue();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in Firebase.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|numeric',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Handle password update
        if ($request->filled('password')) {
            if (!isset($user['password']) || !Hash::check($request->input('current_password'), $user['password'])) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'current_password' => ['The current password is incorrect.']
                    ]
                ], 422);
            }

            if (Hash::check($request->input('password'), $user['password'])) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'password' => ['New password must be different from the current password.']
                    ]
                ], 422);
            }

            $user['password'] = Hash::make($request->input('password'));
        }

        // Update profile
        $user['name'] = $request->input('name');
        $user['email'] = strtolower($request->input('email'));
        $user['phone'] = $request->input('phone');

        $this->db->getReference("users/{$uid}")->update($user);

        return response()->json([
            'success' => true,
            'message' => 'Profile Information has been successfully updated!'
        ]);
    }
}
