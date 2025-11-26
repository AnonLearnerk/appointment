<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bname' => 'trialsht',
            'email' => 'admin@yourmail.com',
            'phone' => '+63 922 000 0000',
            'logo' => '',
            'meta_title' => 'CTU- Guidance Appointment System',
        ];
    }
}
