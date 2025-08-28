<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = [
            [
                'name' => 'Laptop Dell XPS 13',
                'description' => 'High-performance laptop with Intel i7 processor, 16GB RAM, 512GB SSD',
                'category' => 'Computers',
                'status' => 'available',
                'location' => 'Lab A - Shelf 1',
                'serial_number' => 'DLXPS13-001',
                'image' => null,
            ],
            [
                'name' => 'Arduino Uno Kit',
                'description' => 'Complete Arduino starter kit with sensors, LEDs, and breadboard',
                'category' => 'Electronics',
                'status' => 'available',
                'location' => 'Lab B - Drawer 3',
                'serial_number' => 'ARDUINO-001',
                'image' => null,
            ],
            [
                'name' => '3D Printer Ender 3',
                'description' => 'FDM 3D printer with 220x220x250mm build volume',
                'category' => '3D Printing',
                'status' => 'available',
                'location' => 'Lab C - Table 2',
                'serial_number' => 'ENDER3-001',
                'image' => null,
            ],
            [
                'name' => 'Digital Multimeter',
                'description' => 'Professional digital multimeter with auto-ranging',
                'category' => 'Electronics',
                'status' => 'available',
                'location' => 'Lab B - Toolbox',
                'serial_number' => 'DMM-001',
                'image' => null,
            ],
            [
                'name' => 'Raspberry Pi 4 Model B',
                'description' => 'Single-board computer with 4GB RAM, WiFi, and Bluetooth',
                'category' => 'Computers',
                'status' => 'available',
                'location' => 'Lab A - Shelf 2',
                'serial_number' => 'RPI4-001',
                'image' => null,
            ],
            [
                'name' => 'Soldering Iron Station',
                'description' => 'Temperature-controlled soldering station with stand',
                'category' => 'Electronics',
                'status' => 'available',
                'location' => 'Lab B - Workbench',
                'serial_number' => 'SOLD-001',
                'image' => null,
            ],
            [
                'name' => 'Oscilloscope',
                'description' => 'Digital oscilloscope with 100MHz bandwidth',
                'category' => 'Electronics',
                'status' => 'available',
                'location' => 'Lab B - Equipment Rack',
                'serial_number' => 'OSC-001',
                'image' => null,
            ],
            [
                'name' => 'Laser Cutter',
                'description' => 'CO2 laser cutter with 40W power, 300x200mm work area',
                'category' => 'Laser Cutting',
                'status' => 'available',
                'location' => 'Lab D - Main Area',
                'serial_number' => 'LASER-001',
                'image' => null,
            ],
            [
                'name' => 'Digital Microscope',
                'description' => 'High-resolution digital microscope with 1000x magnification',
                'category' => 'Science Lab',
                'status' => 'available',
                'location' => 'Science Lab - Station 1',
                'serial_number' => 'MICRO-001',
                'image' => null,
            ],
            [
                'name' => 'pH Meter',
                'description' => 'Digital pH meter with automatic temperature compensation',
                'category' => 'Science Lab',
                'status' => 'available',
                'location' => 'Science Lab - Chemical Hood',
                'serial_number' => 'PH-001',
                'image' => null,
            ],
            [
                'name' => 'Analytical Balance',
                'description' => 'Precision analytical balance with 0.0001g readability',
                'category' => 'Science Lab',
                'status' => 'available',
                'location' => 'Science Lab - Balance Room',
                'serial_number' => 'BALANCE-001',
                'image' => null,
            ],
            [
                'name' => 'Centrifuge',
                'description' => 'High-speed centrifuge with 12-tube rotor',
                'category' => 'Science Lab',
                'status' => 'available',
                'location' => 'Science Lab - Station 3',
                'serial_number' => 'CENTRI-001',
                'image' => null,
            ],
            [
                'name' => 'Spectrophotometer',
                'description' => 'UV-Vis spectrophotometer for absorbance measurements',
                'category' => 'Science Lab',
                'status' => 'available',
                'location' => 'Science Lab - Station 2',
                'serial_number' => 'SPEC-001',
                'image' => null,
            ],
            [
                'name' => 'Autoclave',
                'description' => 'Steam sterilizer for laboratory equipment',
                'category' => 'Science Lab',
                'status' => 'available',
                'location' => 'Science Lab - Sterilization Area',
                'serial_number' => 'AUTO-001',
                'image' => null,
            ],
        ];

        foreach ($equipment as $item) {
            Equipment::create($item);
        }
    }
}
