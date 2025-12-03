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
        $user = Auth::user() ?? (object)['name' => 'test'];
        
        // Get statistics
        $publishedLessons = Lesson::count();
        $userLessons = Lesson::count(); // For now, same as published
        $quizAttempts = StudentAnswer::count();
        $submissions = StudentAnswer::count(); // Using quiz attempts as submissions
        
        // Get recent lessons (latest 5)
        $recentLessons = Lesson::latest()->take(5)->get();
        
        // Add display properties for lessons
        foreach ($recentLessons as $lesson) {
            // Create a title from the first question or use a default
            $lesson->title = 'Introduction to Interaction Design';
            $lesson->duration = '12 mins';
            $lesson->category = 'HCI';
        }
        
        // If no lessons exist, create a default one for display
        if ($recentLessons->isEmpty()) {
            $defaultLesson = (object)[
                'title' => 'Introduction to Interaction Design',
                'duration' => '12 mins',
                'category' => 'HCI'
            ];
            $recentLessons = collect([$defaultLesson]);
        }
        
        return view('dashboard', compact(
            'user',
            'publishedLessons',
            'userLessons',
            'quizAttempts',
            'submissions',
            'recentLessons'
        ));
    }
}

