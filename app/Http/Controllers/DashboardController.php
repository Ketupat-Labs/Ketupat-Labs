<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user() ?? (object)['name' => 'Cikgu'];
        
        // 1. Total Students
        $totalStudents = Student::count();

        // 2. Completed Lessons (Total entries in StudentAnswer)
        $completedLessons = StudentAnswer::count();

        // 3. Needs Attention (Students with marks <= 0, which is <= 20% of 3 marks)
        // Using total_marks column. 0/3 = 0%. 1/3 = 33%.
        $needsAttention = StudentAnswer::where('total_marks', '<=', 0)
                            ->distinct('student_id')
                            ->count('student_id');

        // 4. User Lessons (Total lessons in system for now)
        $userLessons = Lesson::count();

        // 5. Recent Lessons
        $recentLessons = Lesson::latest()->take(3)->get();
        if ($recentLessons->isEmpty()) {
            // Fallback for display if no lessons exist (matches raw php logic)
            $recentLessons = collect([]);
        }

        // Add display category if missing (optional, but good for UI)
        foreach($recentLessons as $lesson) {
             if(!$lesson->category) $lesson->category = 'HCI';
             if(!$lesson->duration) $lesson->duration = '12 min';
        }
        
        return view('dashboard', compact(
            'user',
            'totalStudents',
            'completedLessons',
            'needsAttention',
            'userLessons',
            'recentLessons'
        ));
    }
}

