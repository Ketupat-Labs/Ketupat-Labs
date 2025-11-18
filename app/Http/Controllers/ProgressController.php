<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $selectedClass = $request->get('class', '5A');
        
        // Get available classes
        $classes = Student::distinct()->pluck('class');
        
        // Get students for selected class
        $students = Student::where('class', $selectedClass)->get();
        
        // Get lessons for selected class
        $lessons = Lesson::where('class', $selectedClass)->get();
        
        // Build progress data
        $progressData = [];
        
        foreach ($students as $student) {
            $studentProgress = [
                'student' => $student,
                'lessons' => [],
                'completedCount' => 0,
                'totalLessons' => $lessons->count(),
                'completionPercentage' => 0
            ];
            
            foreach ($lessons as $lesson) {
                $answer = StudentAnswer::where('student_id', $student->student_id)
                    ->where('lesson_id', $lesson->id)
                    ->first();
                
                if ($answer) {
                    $percentage = ($answer->total_marks / 3) * 100;
                    $status = 'Completed';
                    if ($percentage <= 20) {
                        $status = 'Completed (Low Score)';
                    }
                    $studentProgress['completedCount']++;
                } else {
                    $status = 'Not Started';
                }
                
                $studentProgress['lessons'][] = [
                    'lesson' => $lesson,
                    'status' => $status,
                    'answer' => $answer,
                    'percentage' => $answer ? ($answer->total_marks / 3) * 100 : 0
                ];
            }
            
            // Calculate completion percentage
            if ($studentProgress['totalLessons'] > 0) {
                $studentProgress['completionPercentage'] = round(
                    ($studentProgress['completedCount'] / $studentProgress['totalLessons']) * 100,
                    1
                );
            }
            
            $progressData[] = $studentProgress;
        }
        
        // Calculate summary statistics
        $summary = [
            'totalStudents' => $students->count(),
            'totalLessons' => $lessons->count(),
            'lessonCompletion' => []
        ];
        
        foreach ($lessons as $lesson) {
            $completedCount = StudentAnswer::where('lesson_id', $lesson->id)
                ->whereHas('student', function($query) use ($selectedClass) {
                    $query->where('class', $selectedClass);
                })
                ->count();
            
            $summary['lessonCompletion'][$lesson->id] = [
                'lesson' => $lesson,
                'completed' => $completedCount,
                'total' => $students->count(),
                'percentage' => $students->count() > 0 ? round(($completedCount / $students->count()) * 100, 1) : 0
            ];
        }
        
        return view('progress.index', compact(
            'classes',
            'selectedClass',
            'progressData',
            'lessons',
            'summary'
        ));
    }
}

