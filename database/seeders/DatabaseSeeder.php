<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Employee;
use App\Models\Category;
use App\Models\Service;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed settings if table exists and is empty
        if (Schema::hasTable('settings') && Setting::count() === 0) {
            Setting::factory()->create();
        }

        // Seed user, roles, and permissions
        if (Schema::hasTable('users') && User::count() === 0) {
            $user = $this->createInitialUserWithPermissions();
        }
    }

    protected function createInitialUserWithPermissions()
    {
        $permissions = [
            'permissions.view', 
            'permissions.create', 
            'permissions.edit', 
            'permissions.delete',
            'users.view', 
            'users.create',
            'users.edit', 
            'users.delete',
            'appointments.view', 
            'appointments.create', 
            'appointments.edit',
            'appointments.delete',
            
            
            'services.view',      
            'services.create',    
            'services.edit',      
            'services.delete',    
            
            'categories.view',    
            'categories.create', 
            'categories.edit', 
            'categories.delete',

            'settings.edit'
        ];


        // Create permissions
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $studentRole = Role::firstOrCreate(['name' => 'client']);

        // Assign permissions
        $adminRole->syncPermissions(Permission::all());

        // Create admin user
        $user = User::create([
            'name' => 'Admin',
            'email' => 'CtuGDO@example.com',
            'phone' => '1234567890',
            'status' => 1,
            'user_type' => 'admin',
            'email_verified_at' => now(),
            'password' => Hash::make('@Admin123'),
        ]);


        // Assign role
        $user->assignRole($adminRole);

        return $user;
    }
}
