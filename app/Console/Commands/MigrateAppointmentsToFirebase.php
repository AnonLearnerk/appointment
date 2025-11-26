<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Kreait\Firebase\Factory;

class MigrateAppointmentsToFirebase extends Command
{
    protected $signature = 'migrate:appointments-firebase';
    protected $description = 'Migrate appointments from MySQL to Firebase Realtime Database';

    public function handle()
    {
        $factory = (new Factory)->withServiceAccount(base_path('firebase-admin-sdk.json'));
        $database = $factory->createDatabase();

        $appointments = Appointment::with(['user', 'employee.user', 'service'])->get();

        foreach ($appointments as $appointment) {
            $database->getReference('appointments/'.$appointment->id)->set([
                'id' => $appointment->id,
                'name' => $appointment->user->name,
                'email' => $appointment->user->email,
                'phone' => $appointment->user->phone,
                'service' => $appointment->service->title ?? 'N/A',
                'employee' => $appointment->employee->user->name ?? 'N/A',
                'date' => $appointment->booking_date,
                'time' => $appointment->booking_time,
                'status' => $appointment->status,
                'notes' => $appointment->notes ?? '',
            ]);
        }

        $this->info('Appointments migrated to Firebase successfully!');
    }
}
