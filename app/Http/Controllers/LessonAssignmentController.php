<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LessonAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->role === 'teacher') {
            // Teacher: Show all assignments for their classrooms
            $classrooms = \App\Models\Classroom::where('teacher_id', $user->id)->pluck('id');
            $assignments = \App\Models\LessonAssignment::whereIn('classroom_id', $classrooms)
                ->with(['classroom', 'lesson'])
                ->latest('assigned_at')
                ->get();
        } else {
            // Student: Show assignments for enrolled classrooms
            $classroomIds = $user->enrolledClassrooms()->pluck('id');
            $assignments = \App\Models\LessonAssignment::whereIn('classroom_id', $classroomIds)
                ->with(['classroom', 'lesson'])
                ->latest('assigned_at')
                ->get();
        }

        return view('assignments.index', compact('assignments', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classrooms = \App\Models\Classroom::all();
        $lessons = \App\Models\Lesson::where('is_published', true)->get();
        return view('assignments.create', compact('classrooms', 'lessons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'classroom_id' => 'required|exists:classes,id',
            'lessons' => 'required|array',
            'lessons.*' => 'exists:lessons,id',
        ]);

        $classroom = \App\Models\Classroom::with('students')->findOrFail($request->classroom_id);
        $students = $classroom->students;

        foreach ($request->lessons as $lessonId) {
            // 1. Create the Class Assignment
            \App\Models\LessonAssignment::firstOrCreate([
                'classroom_id' => $classroom->id,
                'lesson_id' => $lessonId,
            ], [
                'type' => 'Mandatory',
                'assigned_at' => now(),
            ]);

            // 2. Bulk Enroll Students (US006-01)
            foreach ($students as $student) {
                \App\Models\Enrollment::firstOrCreate([
                    'user_id' => $student->id,
                    'lesson_id' => $lessonId,
                ], [
                    'status' => 'in_progress', // Correct enum value
                    'progress' => 0,
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Lessons assigned and students enrolled successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
