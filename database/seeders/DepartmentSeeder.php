<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Computer Science',
                'code' => 'CS',
                'description' => 'Department of Computer Science and Information Technology',
                'is_active' => true,
            ],
            [
                'name' => 'Engineering',
                'code' => 'ENG',
                'description' => 'Department of Engineering and Technology',
                'is_active' => true,
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MATH',
                'description' => 'Department of Mathematics and Statistics',
                'is_active' => true,
            ],
            [
                'name' => 'Business Administration',
                'code' => 'BA',
                'description' => 'Department of Business Administration and Management',
                'is_active' => true,
            ],
            [
                'name' => 'Education',
                'code' => 'EDU',
                'description' => 'Department of Education and Teacher Training',
                'is_active' => true,
            ],
            [
                'name' => 'Arts and Sciences',
                'code' => 'AS',
                'description' => 'Department of Arts and Sciences',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
