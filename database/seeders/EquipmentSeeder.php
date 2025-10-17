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
            // Glassware
            [
                'name' => 'Glass Beakers (250ml)',
                'description' => 'Borosilicate glass beakers for general laboratory use, 250ml capacity',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'BK250-001',
                'total_quantity' => 12,
                'image' => null,
            ],
            [
                'name' => 'Glass Beakers (500ml)',
                'description' => 'Borosilicate glass beakers for general laboratory use, 500ml capacity',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'BK500-001',
                'total_quantity' => 8,
                'image' => null,
            ],
            [
                'name' => 'Test Tubes (15ml)',
                'description' => 'Glass test tubes with rim for general laboratory experiments',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'TT15-001',
                'total_quantity' => 50,
                'image' => null,
            ],
            [
                'name' => 'Conical Flasks (250ml)',
                'description' => 'Erlenmeyer flasks for mixing and heating solutions',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'CF250-001',
                'total_quantity' => 15,
                'image' => null,
            ],
            [
                'name' => 'Graduated Cylinders (100ml)',
                'description' => 'Precise volume measurement cylinders with graduated markings',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'GC100-001',
                'total_quantity' => 10,
                'image' => null,
            ],
            [
                'name' => 'Glass Pipettes (10ml)',
                'description' => 'Volumetric pipettes for accurate liquid transfer',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'PIP10-001',
                'total_quantity' => 20,
                'image' => null,
            ],
            [
                'name' => 'Petri Dishes',
                'description' => 'Glass petri dishes for bacterial culture and microscopy',
                'category' => 'Glassware',
                'status' => 'available',
                'serial_number' => 'PD-001',
                'total_quantity' => 30,
                'image' => null,
            ],

            // Measuring Instruments
            [
                'name' => 'Digital pH Meter',
                'description' => 'Digital pH meter with automatic temperature compensation',
                'category' => 'Measuring Instruments',
                'status' => 'available',
                'serial_number' => 'PH-001',
                'total_quantity' => 3,
                'image' => null,
            ],
            [
                'name' => 'Analytical Balance',
                'description' => 'Precision analytical balance with 0.0001g readability',
                'category' => 'Measuring Instruments',
                'status' => 'available',
                'serial_number' => 'BAL-001',
                'total_quantity' => 2,
                'image' => null,
            ],
            [
                'name' => 'Digital Thermometer',
                'description' => 'Digital thermometer with probe for temperature measurements',
                'category' => 'Measuring Instruments',
                'status' => 'available',
                'serial_number' => 'THERM-001',
                'total_quantity' => 6,
                'image' => null,
            ],
            [
                'name' => 'Electronic Scale (2kg)',
                'description' => 'Digital scale for weighing chemicals and specimens',
                'category' => 'Measuring Instruments',
                'status' => 'available',
                'serial_number' => 'SCALE-001',
                'total_quantity' => 4,
                'image' => null,
            ],

            // Microscopy Equipment
            [
                'name' => 'Compound Microscope',
                'description' => 'High-resolution compound microscope with 40x-1000x magnification',
                'category' => 'Microscopy',
                'status' => 'available',
                'serial_number' => 'MICRO-001',
                'total_quantity' => 8,
                'image' => null,
            ],
            [
                'name' => 'Microscope Slides',
                'description' => 'Glass microscope slides for specimen preparation',
                'category' => 'Microscopy',
                'status' => 'available',
                'serial_number' => 'SLIDE-001',
                'total_quantity' => 100,
                'image' => null,
            ],
            [
                'name' => 'Cover Slips',
                'description' => 'Thin glass cover slips for microscope slide preparation',
                'category' => 'Microscopy',
                'status' => 'available',
                'serial_number' => 'COVER-001',
                'total_quantity' => 200,
                'image' => null,
            ],

            // Heating Equipment
            [
                'name' => 'Hot Plates',
                'description' => 'Electric hot plates for heating solutions and reactions',
                'category' => 'Heating Equipment',
                'status' => 'available',
                'serial_number' => 'HP-001',
                'total_quantity' => 6,
                'image' => null,
            ],
            [
                'name' => 'Bunsen Burners',
                'description' => 'Gas bunsen burners for heating and sterilization',
                'category' => 'Heating Equipment',
                'status' => 'available',
                'serial_number' => 'BB-001',
                'total_quantity' => 10,
                'image' => null,
            ],
            [
                'name' => 'Alcohol Lamps',
                'description' => 'Alcohol-fueled lamps for gentle heating applications',
                'category' => 'Heating Equipment',
                'status' => 'available',
                'serial_number' => 'AL-001',
                'total_quantity' => 8,
                'image' => null,
            ],

            // Laboratory Tools
            [
                'name' => 'Ring Stands',
                'description' => 'Metal ring stands for supporting laboratory apparatus',
                'category' => 'Laboratory Tools',
                'status' => 'available',
                'serial_number' => 'RS-001',
                'total_quantity' => 12,
                'image' => null,
            ],
            [
                'name' => 'Clamps and Rings',
                'description' => 'Metal clamps and rings for securing glassware',
                'category' => 'Laboratory Tools',
                'status' => 'available',
                'serial_number' => 'CR-001',
                'total_quantity' => 25,
                'image' => null,
            ],
            [
                'name' => 'Stirring Rods',
                'description' => 'Glass stirring rods for mixing solutions',
                'category' => 'Laboratory Tools',
                'status' => 'available',
                'serial_number' => 'STR-001',
                'total_quantity' => 20,
                'image' => null,
            ],
            [
                'name' => 'Rubber Stoppers',
                'description' => 'Rubber stoppers in various sizes for sealing containers',
                'category' => 'Laboratory Tools',
                'status' => 'available',
                'serial_number' => 'STOP-001',
                'total_quantity' => 30,
                'image' => null,
            ],

            // Safety Equipment
            [
                'name' => 'Safety Goggles',
                'description' => 'Chemical splash safety goggles for eye protection',
                'category' => 'Safety Equipment',
                'status' => 'available',
                'serial_number' => 'GOGG-001',
                'total_quantity' => 25,
                'image' => null,
            ],
            [
                'name' => 'Lab Aprons',
                'description' => 'Chemical-resistant lab aprons for personal protection',
                'category' => 'Safety Equipment',
                'status' => 'available',
                'serial_number' => 'APRON-001',
                'total_quantity' => 20,
                'image' => null,
            ],
            [
                'name' => 'Latex Gloves',
                'description' => 'Disposable latex gloves for chemical handling',
                'category' => 'Safety Equipment',
                'status' => 'available',
                'serial_number' => 'GLOVE-001',
                'total_quantity' => 100,
                'image' => null,
            ],

            // Specialized Equipment
            [
                'name' => 'Centrifuge',
                'description' => 'High-speed centrifuge with 12-tube rotor for sample separation',
                'category' => 'Specialized Equipment',
                'status' => 'available',
                'serial_number' => 'CENT-001',
                'total_quantity' => 2,
                'image' => null,
            ],
            [
                'name' => 'Spectrophotometer',
                'description' => 'UV-Vis spectrophotometer for absorbance measurements',
                'category' => 'Specialized Equipment',
                'status' => 'available',
                'serial_number' => 'SPEC-001',
                'total_quantity' => 1,
                'image' => null,
            ],
            [
                'name' => 'Magnetic Stirrer',
                'description' => 'Magnetic stirrer with heating plate for solution mixing',
                'category' => 'Specialized Equipment',
                'status' => 'available',
                'serial_number' => 'MAG-001',
                'total_quantity' => 4,
                'image' => null,
            ],
        ];

        foreach ($equipment as $item) {
            Equipment::create($item);
        }
    }
}
