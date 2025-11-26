<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseService;

class UpdateUserCreatedAt extends Command
{
    protected $signature = 'users:update-created-at';
    protected $description = 'Add created_at timestamp to old users in Firebase';

    protected FirebaseService $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        parent::__construct();
        $this->firebaseService = $firebaseService;
    }

    public function handle()
    {
        $db = $this->firebaseService->getDatabase();

        $this->info("Fetching all users...");

        $allUsers = $db->getReference('users')->getValue() ?? [];

        foreach ($allUsers as $uid => $user) {
            if (isset($user['created_at'])) {
                $this->info("User $uid already has created_at, skipping...");
                continue;
            }

            $createdAt = time(); // or Carbon::now()->timestamp if you prefer

            $db->getReference("users/{$uid}")->update([
                'created_at' => $createdAt
            ]);

            $this->info("Updated user $uid with created_at: $createdAt");
        }

        $this->info("All users updated!");
    }
}
