<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;

class WelcomePageController extends Controller
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    public function index()
    {
        // Fetch all services from Firebase
        $servicesRef = $this->database->getReference('services')->getValue();
        $services = collect();

        if ($servicesRef) {
            foreach ($servicesRef as $id => $service) {
                $services->push((object) [
                    'id'    => $id,
                    'title' => $service['title'] ?? '',
                    'body'  => $service['body'] ?? '',
                    'image' => $service['image'] ?? null,
                ]);
            }
        }

        // Fetch all users from Firebase
        $usersRef = $this->database->getReference('users')->getValue();
        $employees = collect();
        $admin = null;

        if ($usersRef) {
            foreach ($usersRef as $id => $user) {
                $userObj = (object) [
                    'id'    => $id,
                    'name'  => $user['name'] ?? '',
                    'email' => $user['email'] ?? '',
                    'phone' => $user['phone'] ?? '',
                    'image' => $user['image'] ?? null,
                    'user_type' => $user['user_type'] ?? '',
                ];

                if ($userObj->user_type === 'employee') {
                    $employees->push($userObj);
                }

                if (!$admin && $userObj->user_type === 'admin') {
                    $admin = $userObj;
                }
            }
        }

        return view('welcome', compact('services', 'employees', 'admin'));
    }
}
