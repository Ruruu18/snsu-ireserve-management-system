<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the student users
        $john = User::where('email', 'student@example.com')->first();
        $sarah = User::where('email', 'sarah@example.com')->first();

        // Get some equipment
        $laptop = Equipment::where('name', 'Laptop Dell XPS 13')->first();
        $arduino = Equipment::where('name', 'Arduino Uno Kit')->first();
        $printer = Equipment::where('name', '3D Printer Ender 3')->first();
        $multimeter = Equipment::where('name', 'Digital Multimeter')->first();

        if ($john && $laptop) {
            // John's reservations
            Reservation::create([
                'user_id' => $john->id,
                'equipment_id' => $laptop->id,
                'reservation_date' => Carbon::today()->addDays(2),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'status' => 'pending',
                'purpose' => 'Working on programming project for Computer Science class',
            ]);

            Reservation::create([
                'user_id' => $john->id,
                'equipment_id' => $arduino->id,
                'reservation_date' => Carbon::today()->addDays(5),
                'start_time' => '14:00:00',
                'end_time' => '17:00:00',
                'status' => 'approved',
                'purpose' => 'Electronics lab experiment for Engineering course',
            ]);

            Reservation::create([
                'user_id' => $john->id,
                'equipment_id' => $printer->id,
                'reservation_date' => Carbon::yesterday(),
                'start_time' => '10:00:00',
                'end_time' => '11:00:00',
                'status' => 'completed',
                'purpose' => '3D printing project prototype',
            ]);
        }

        if ($sarah && $multimeter) {
            // Sarah's reservations
            Reservation::create([
                'user_id' => $sarah->id,
                'equipment_id' => $multimeter->id,
                'reservation_date' => Carbon::today()->addDays(1),
                'start_time' => '13:00:00',
                'end_time' => '16:00:00',
                'status' => 'pending',
                'purpose' => 'Circuit testing for Electronics lab',
            ]);

            Reservation::create([
                'user_id' => $sarah->id,
                'equipment_id' => $arduino->id,
                'reservation_date' => Carbon::today()->addDays(3),
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'status' => 'approved',
                'purpose' => 'IoT project development',
            ]);
        }
    }
}
