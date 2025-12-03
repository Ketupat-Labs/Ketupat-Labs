<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Create students for Class 5A
        $students5A = [
            ['name' => 'Ali bin Ahmad', 'class' => '5A'],
            ['name' => 'Siti binti Rahman', 'class' => '5A'],
            ['name' => 'Wei Chen', 'class' => '5A'],
            ['name' => 'Aina binti Kamal', 'class' => '5A'],
        ];

        // Create students for Class 5B
        $students5B = [
            ['name' => 'Raj Kumar', 'class' => '5B'],
            ['name' => 'Mei Ling', 'class' => '5B'],
            ['name' => 'Ahmad bin Hassan', 'class' => '5B'],
            ['name' => 'Sarah binti Ismail', 'class' => '5B'],
        ];

        foreach ($students5A as $student) {
            Student::create($student);
        }

        foreach ($students5B as $student) {
            Student::create($student);
        }

        // Create HCI lessons with questions
        $lessons = [
            [
                'class' => '5A',
                'q1' => 'What is usability?',
                'q2' => 'Define user interface', 
                'q3' => 'What is HCI?'
            ],
            [
                'class' => '5A', 
                'q1' => 'What are design principles?',
                'q2' => 'Explain user-centered design',
                'q3' => 'What is affordance?'
            ],
            [
                'class' => '5B',
                'q1' => 'What is usability?',
                'q2' => 'Define user interface', 
                'q3' => 'What is HCI?'
            ],
            [
                'class' => '5B',
                'q1' => 'What are design principles?',
                'q2' => 'Explain user-centered design',
                'q3' => 'What is affordance?'
            ],
        ];

        foreach ($lessons as $lesson) {
            Lesson::create($lesson);
        }

        // Create student answers
        $students = Student::all();
        $lessons = Lesson::all();

        foreach ($students as $student) {
            foreach ($lessons as $lesson) {
                // Only create answers if student and lesson are in same class
                if ($student->class === $lesson->class) {
                    $q1_answer = (bool)rand(0, 1);
                    $q2_answer = (bool)rand(0, 1);
                    $q3_answer = (bool)rand(0, 1);
                    
                    $totalMarks = ($q1_answer ? 1 : 0) + ($q2_answer ? 1 : 0) + ($q3_answer ? 1 : 0);

                    StudentAnswer::create([
                        'student_id' => $student->student_id,
                        'lesson_id' => $lesson->id,
                        'q1_answer' => $q1_answer,
                        'q2_answer' => $q2_answer,
                        'q3_answer' => $q3_answer,
                        'total_marks' => $totalMarks,
                    ]);
                }
            }
        }
    }
}
