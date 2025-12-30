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

        $classrooms = \App\Models\Classroom::where('teacher_id', $currentUser->id)->get();

        $query = \App\Models\User::where('role', 'student')
            ->with(['enrollments.lesson', 'submissions', 'enrolledClassrooms']);

        $students = $query->get();

        $studentProgress = [];
        foreach ($students as $student) {
            $classIdentified = $student->enrolledClassrooms->first()?->name ?? $student->class ?? 'General';

            foreach ($student->enrollments as $enrollment) {
                $studentProgress[] = [
                    'student_name' => $student->full_name,
                    'class' => $classIdentified,
                    'lesson_title' => $enrollment->lesson->title,
                    'progress' => $enrollment->progress,
                    'status' => ucfirst($enrollment->status),
                    'last_accessed' => $enrollment->updated_at->diffForHumans(),
                ];
            }
        }

        return view('monitoring.index', compact('studentProgress', 'classrooms'));
    }
}
