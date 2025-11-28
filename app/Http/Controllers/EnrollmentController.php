<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user)
            return redirect()->route('login');

        // Get all published lessons
        $lessons = \App\Models\Lesson::where('is_published', true)->get();

        // Check enrollment status for each lesson
        foreach ($lessons as $lesson) {
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            $lesson->enrolled = $enrollment ? true : false;
            // You might want to check for mandatory assignments here too if you have that logic
        }

        return view('enrollment.index', compact('lessons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user)
            return redirect()->route('login');

        // Check if already enrolled
        $exists = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('lesson_id', $request->lesson_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You are already enrolled in this lesson.');
        }

        \App\Models\Enrollment::create([
            'user_id' => $user->id,
            'lesson_id' => $request->lesson_id,
            'status' => 'enrolled',
            'progress' => 0
        ]);

        return back()->with('success', 'Successfully enrolled in the lesson!');
    }
}
