<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAnswer;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Get all students with low scores (20% and below)
        $notifications = [];
        
        $allAnswers = StudentAnswer::with(['student', 'lesson'])->get();
        
        foreach ($allAnswers as $answer) {
            $percentage = ($answer->total_marks / 3) * 100;
            
            if ($percentage <= 20) {
                $notifications[] = [
                    'id' => $answer->id,
                    'student' => $answer->student,
                    'lesson' => $answer->lesson,
                    'score' => $answer->total_marks,
                    'percentage' => round($percentage, 1),
                    'class' => $answer->student->class,
                    'created_at' => $answer->created_at
                ];
            }
        }
        
        // Sort by most recent first
        usort($notifications, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });
        
        return view('notifications.index', compact('notifications'));
    }
}


