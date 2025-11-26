<?php

namespace App\Auth;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\FirebaseService;

class FirebaseUserProvider implements UserProvider
{
    protected $database;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->database = $firebaseService->getDatabase();
    }

    public function retrieveById($identifier)
    {
        $userData = $this->database->getReference('users/' . $identifier)->getValue();
        if (!$userData) return null;

        $user = new User($userData);
        $user->exists = true;
        return $user;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        // optional, do nothing
    }

    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials['email'])) return null;

        $users = $this->database->getReference('users')
            ->orderByChild('email')
            ->equalTo(strtolower($credentials['email']))
            ->getValue();

        if (!$users) return null;

        $firebaseUser = reset($users);

        $user = new User([
            'id' => $firebaseUser['id'],
            'name' => $firebaseUser['name'],
            'email' => $firebaseUser['email'],
            'phone' => $firebaseUser['phone'],
            'password' => $firebaseUser['password'],
            'user_type' => $firebaseUser['user_type'],
        ]);
        $user->exists = true;

        return $user;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return Hash::check($credentials['password'], $user->password);
    }

    /**
     * Laravel 10+ requires this method
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        return false;
    }
}
