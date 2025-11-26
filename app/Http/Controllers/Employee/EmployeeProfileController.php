<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;

class EmployeeProfileController extends Controller
{
    protected $db;

    public function __construct(FirebaseService $firebase)
    {
        $this->db = $firebase->getDatabase();
    }

    public function edit()
    {
        $user = Auth::user();
        $firebaseUser = $this->db->getReference('users/' . $user->id)->getValue();

        if (!$firebaseUser) {
            abort(404, 'User not found in Firebase.');
        }

        return view('employee.profile-edit', ['user' => (object) $firebaseUser]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $userRef = $this->db->getReference('users/' . $user->id);
        $firebaseUser = $userRef->getValue();

        if (!$firebaseUser) {
            return response()->json(['errors' => ['user' => ['User not found in Firebase.']]], 404);
        }

        // Validation rules
        $rules = [
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'password'      => 'nullable|string|min:6|confirmed',
            'profile_image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'remove_image'  => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        // Check if password update requested
        if (!empty($validated['password'])) {
            if (empty($request->current_password)) {
                return response()->json([
                    'errors' => ['current_password' => ['Current password is required to change your password.']]
                ], 422);
            }

            if (!Hash::check($request->current_password, $firebaseUser['password'])) {
                return response()->json([
                    'errors' => ['current_password' => ['Current password is incorrect.']]
                ], 422);
            }

            $firebaseUser['password'] = Hash::make($validated['password']);
        }

        // Update basic fields
        $firebaseUser['name'] = $validated['name'];
        $firebaseUser['email'] = $validated['email'];

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

        // Save back to Firebase
        $userRef->update($firebaseUser);

        return response()->json(['success' => 'Profile updated successfully!']);
    }
}
