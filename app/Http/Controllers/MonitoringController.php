<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Ensure teacher
        $currentUser = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$currentUser || $currentUser->role !== 'teacher') {
            abort(403);
        }

        $query = \App\Models\User::where('role', 'student')->with(['enrollments.lesson', 'submissions']);

        // Filter by class (if we had a class/section field on users, but we don't really have one yet aside from the new classrooms table)
        // For now, just fetch all students
        $students = $query->get();

        // Process data for the view to match the legacy format roughly
        $studentProgress = [];
        foreach ($students as $student) {
            // Get all lessons (or enrolled ones)
            // For simplicity, let's show progress for enrolled lessons
            foreach ($student->enrollments as $enrollment) {
                $studentProgress[] = [
                    'student_name' => $student->full_name,
                    'class' => 'General', // Placeholder as we don't have user-class link yet
                    'lesson_title' => $enrollment->lesson->title,
                    'progress' => $enrollment->progress,
                    'status' => ucfirst($enrollment->status),
                    'last_accessed' => $enrollment->updated_at->diffForHumans(),
                ];
            }
        }

        return view('monitoring.index', compact('studentProgress'));
    }
}
