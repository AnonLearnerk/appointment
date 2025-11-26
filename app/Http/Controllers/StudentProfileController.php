<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;

class StudentProfileController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    /**
     * Show Edit Profile page
     */
    public function edit()
    {
        $firebaseUserId = session('firebase_user_id');

        if (!$firebaseUserId) {
            return redirect()->route('login')->withErrors(['error' => 'Please log in again.']);
        }

        // 🔹 Fetch user data from Firebase
        $firebaseUser = $this->db->getReference('users/' . $firebaseUserId)->getValue();

        if (!$firebaseUser) {
            return redirect()->route('student.dashboard')->withErrors(['error' => 'User data not found in Firebase.']);
        }

        // 🔹 Store in session (for convenience)
        session(['firebase_user' => $firebaseUser]);

        // 🔹 Pass to view
        return view('student.profile-edit', ['firebaseUser' => (object) $firebaseUser]);
    }


    /**
     * Update Student Profile
     */
    public function update(Request $request)
    {
        $firebaseUid = session('firebase_user_id');

        if (!$firebaseUid) {
            return response()->json(['errors' => ['user' => ['User not found in session.']]], 404);
        }

        $userRef = $this->db->getReference('users/' . $firebaseUid);
        $firebaseUser = $userRef->getValue();

        if (!$firebaseUser) {
            return response()->json(['errors' => ['user' => ['User not found in Firebase.']]], 404);
        }

        // Validation
        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'phone'         => 'required|string|max:20',
            'password'      => 'nullable|string|min:6|confirmed',
            'current_password' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'remove_image'  => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Handle password update
        if (!empty($validated['password'])) {
            if (empty($validated['current_password'])) {
                return response()->json([
                    'errors' => ['current_password' => ['Current password is required to change your password.']]
                ], 422);
            }

            if (!Hash::check($validated['current_password'], $firebaseUser['password'])) {
                return response()->json([
                    'errors' => ['current_password' => ['Current password is incorrect.']]
                ], 422);
            }

            $firebaseUser['password'] = Hash::make($validated['password']);
        }

        // Update basic info
        $firebaseUser['name']  = $validated['name'];
        $firebaseUser['email'] = $validated['email'];
        $firebaseUser['phone'] = $validated['phone'];

        // Handle image removal
        if (!empty($validated['remove_image']) && $validated['remove_image']) {
            if (!empty($firebaseUser['image']) && Storage::disk('public')->exists($firebaseUser['image'])) {
                Storage::disk('public')->delete($firebaseUser['image']);
            }
            $firebaseUser['image'] = null;
        }

        // Handle image upload
        if ($request->hasFile('profile_image')) {
            if (!empty($firebaseUser['image']) && Storage::disk('public')->exists($firebaseUser['image'])) {
                Storage::disk('public')->delete($firebaseUser['image']);
            }
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $firebaseUser['image'] = $path;
        }

        // Save to Firebase
        $userRef->update($firebaseUser);

        // Update session
        session(['firebase_user' => $firebaseUser]);

        return response()->json(['success' => 'Profile updated successfully!']);
    }
}
