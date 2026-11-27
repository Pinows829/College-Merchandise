<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $catalog = [
            'CCICT' => ['College of Computing, Information and Communications Technology', [
                'Bachelor of Science in Information Systems (BSIS)',
                'Bachelor of Science in Information Technology (BSIT)',
                'Bachelor in Industrial Technology – Computer Technology (BIT-CT)',
            ]],
            'CAS' => ['College of Arts and Sciences', [
                'Bachelor of Science in Psychology (BS Psychology)',
                'Bachelor of Science in Mathematics (BS Math)',
                'Bachelor of Science in Statistics (BS Stat)',
                'Bachelor of Science in Development Communication (BSDevCom)',
                'Bachelor of Arts in English Language (BAEL)',
                'Bachelor of Arts in Literature (BAL)',
                'Batsilyer ng Sining sa Filipino (BAF)',
            ]],
            'COE' => ['College of Engineering', [
                'Bachelor of Science in Civil Engineering (BSCE)',
                'Bachelor of Science in Computer Engineering (BSCpE)',
                'Bachelor of Science in Electronics Engineering (BSECE)',
                'Bachelor of Science in Electrical Engineering (BSEE)',
                'Bachelor of Science in Industrial Engineering (BSIE)',
                'Bachelor of Science in Mechanical Engineering (BSME)',
            ]],
            'COT' => ['College of Technology', [
                'Bachelor of Science in Mechatronics (BSMx)',
                'Bachelor of Science in Graphics and Design (BSGD)',
                'Bachelor of Science in Technology Management (BSTechM)',
                'Bachelor of Industrial Technology – Automotive Technology',
                'Bachelor of Industrial Technology – Civil Technology',
                'Bachelor of Industrial Technology – Electrical Technology',
                'Bachelor of Industrial Technology – Electronics Technology',
                'Bachelor of Industrial Technology – Drafting Technology',
            ]],
            'COED' => ['College of Education', [
                'Bachelor in Elementary Education (BEEd)',
                'Bachelor in Early Childhood Education (BECEd)',
                'Bachelor in Special Needs Education (BSNEd)',
                'Bachelor of Secondary Education – Mathematics (BSEd-Math)',
                'Bachelor of Secondary Education – Science (BSEd-Science)',
                'Bachelor of Secondary Education – English (BSEd-English)',
                'Bachelor of Secondary Education – Filipino (BSEd-Filipino)',
                'Bachelor of Secondary Education – Values Education (BSEd-Values Ed)',
            ]],
            'CME' => ['College of Management and Entrepreneurship', [
                'Bachelor of Public Administration (BPA)',
                'Bachelor of Science in Hospitality Management (BSHM)',
                'Bachelor of Science in Business Administration – Marketing Management (BSBA-MM)',
                'Bachelor of Science in Tourism Management (BSTM)',
            ]],
        ];

        foreach ($catalog as $code => [$name, $courseNames]) {
            $college = College::updateOrCreate(['code' => $code], ['name' => $name]);
            foreach ($courseNames as $courseName) {
                Course::firstOrCreate(['college_id' => $college->id, 'name' => $courseName]);
            }
        }
    }
}
