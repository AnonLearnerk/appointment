<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;

class MarkNoShowAppointments extends Command
{
    protected $signature = 'appointments:mark-no-show';
    protected $description = 'Mark pending appointments as No Show after 11:59 PM of their booking date.';

    public function handle(): void
    {
        $now = Carbon::now();

        $affected = Appointment::where('status', 'Pending')
            ->whereDate('booking_date', '<', $now->toDateString()) // any booking before today
            ->update(['status' => 'No Show']);

        $this->info("Updated {$affected} appointments to No Show.");
    }
}
