<?php

require __DIR__.'/vendor/autoload.php';

use App\Services\FirebaseService;

$firebaseService = new FirebaseService();
$db = $firebaseService->getDatabase();

echo "Fetching all users...\n";

$allUsers = $db->getReference('users')->getValue() ?? [];

foreach ($allUsers as $uid => $user) {
    if (isset($user['created_at'])) {
        echo "User $uid already has created_at, skipping...\n";
        continue;
    }

    $createdAt = time(); // current timestamp

    $db->getReference("users/{$uid}")->update([
        'created_at' => $createdAt
    ]);

    echo "Updated user $uid with created_at: $createdAt\n";
}

echo "All users updated!\n";
