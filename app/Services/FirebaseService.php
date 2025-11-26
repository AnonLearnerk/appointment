<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Database;
use Kreait\Firebase\Storage;

class FirebaseService
{
    protected Database $database;
    protected Storage $storage;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('app/firebase-admin-sdk.json'))
            ->withDatabaseUri('https://appointment-system-b9648-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
        $this->storage = $factory->createStorage(); // ✅ Enable Firebase Storage
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getStorage(): Storage
    {
        return $this->storage;
    }
}
