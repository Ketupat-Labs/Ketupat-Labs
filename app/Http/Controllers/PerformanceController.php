<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $selectedClass = $request->get('class', '5A');
        $selectedLesson = $request->get('lesson');
        
        // Get available classes and lessons for filters
        $classes = Student::distinct()->pluck('class');
        $lessons = Lesson::where('class', $selectedClass)->get();
        
        // Get students for selected class
        $students = Student::where('class', $selectedClass)->get();
        
        // Get performance data
        $performanceData = [];
        $lessonStats = [];
        
        foreach ($students as $student) {
            $studentData = [
                'student' => $student,
                'answers' => []
            ];
            
            // Get student answers, filtered by selected lesson if any
            $query = StudentAnswer::where('student_id', $student->student_id)
                ->with('lesson');
                
            if ($selectedLesson) {
                $query->where('lesson_id', $selectedLesson);
            }
            
            $answers = $query->get();
            
            foreach ($answers as $answer) {
                $studentData['answers'][$answer->lesson_id] = [
                    'answers' => [
                        'q1' => $answer->q1_answer ?? false,
                        'q2' => $answer->q2_answer ?? false,
                        'q3' => $answer->q3_answer ?? false,
                    ],
                    'total_marks' => $answer->total_marks,
                    'lesson' => $answer->lesson
                ];
            }
            
            $performanceData[] = $studentData;
        }
        
        // Calculate lesson statistics for summary
        if ($selectedLesson) {
            $lessonStats = StudentAnswer::where('lesson_id', $selectedLesson)
                ->selectRaw('AVG(total_marks) as average_marks, 
                            MAX(total_marks) as max_marks,
                            MIN(total_marks) as min_marks,
                            COUNT(*) as total_students')
                ->first();
        }
        
        return view('performance.index', compact(
            'classes', 
            'lessons', 
            'students',
            'performanceData',
            'selectedClass',
            'selectedLesson',
            'lessonStats'
        ));
    }
    
    public function studentDetail($studentId)
    {
        $student = Student::with(['answers.lesson'])->findOrFail($studentId);
        
        // Calculate class averages for each lesson
        $classAverages = [];
        $studentAverages = [];
        
        foreach ($student->answers as $answer) {
            $lessonId = $answer->lesson_id;
            $class = $student->class;
            
            // Get class average for this lesson
            $classAverage = StudentAnswer::where('lesson_id', $lessonId)
                ->whereHas('student', function($query) use ($class) {
                    $query->where('class', $class);
                })
                ->avg('total_marks');
                
            $classAverages[$lessonId] = round($classAverage, 2);
            
            // Calculate student average (assuming 3 questions per lesson)
            $totalQuestions = 3;
            $studentAverages[$lessonId] = [
                'marks' => $answer->total_marks,
                'total_questions' => $totalQuestions,
                'percentage' => round(($answer->total_marks / $totalQuestions) * 100, 2)
            ];
        }
        
        // Calculate overall averages
        $overallStudentAvg = $student->answers->avg('total_marks');
        $overallClassAvg = StudentAnswer::whereHas('student', function($query) use ($student) {
            $query->where('class', $student->class);
        })->avg('total_marks');
        
        return view('performance.student-detail', compact(
            'student', 
            'classAverages', 
            'studentAverages',
            'overallStudentAvg',
            'overallClassAvg'
        ));
    }
}
