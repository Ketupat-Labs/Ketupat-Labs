<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassroomController extends Controller
{
    public function index(Request $request)
    {
        // Use session user like other controllers
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user)
            return redirect()->route('login');

        if ($user->role === 'teacher') {
            $classrooms = Classroom::where('teacher_id', $user->id)
                ->orderByDesc('created_at')
                ->get();
        } else {
            $classrooms = Classroom::whereHas('students', function ($q) use ($user) {
                $q->where('user.id', $user->id);
            })
                ->orderByDesc('created_at')
                ->get();
        }

        // If API request, return JSON
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 200,
                'data' => [
                    'classrooms' => $classrooms->map(function ($classroom) {
                        return [
                            'id' => $classroom->id,
                            'name' => $classroom->name,
                            'subject' => $classroom->subject,
                            'year' => $classroom->year,
                        ];
                    })
                ]
            ]);
        }

        $currentUser = $user; // Pass as currentUser for consistency with other views
        return view('classrooms.index', compact('classrooms', 'currentUser'));
    }

    public function create()
    {
        return view('classrooms.create');
    }

    public function store(Request $request)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher')
            abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        Classroom::create([
            'teacher_id' => $user->id,
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    public function show(Request $request, Classroom $classroom)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user)
            return redirect()->route('login');

        // Authorization: Teacher of the class OR Enrolled Student
        $isTeacher = $user->role === 'teacher' && $classroom->teacher_id === $user->id;
        $isStudent = $user->role === 'student' && $classroom->students()->where('user.id', $user->id)->exists();

        if (!$isTeacher && !$isStudent) {
            abort(403);
        }

        // Load students
        $classroom->load('students');

        // Load lessons with user's specific enrollment/status
        $classroom->load([
            'lessons' => function ($query) use ($user) {
                $query->with([
                    'enrollments' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }
                ]);
            }
        ]);

        // Get students not enrolled in this class (needed for Teacher's Add Student dropdown)
        $availableStudents = [];
        if ($isTeacher) {
            $availableStudents = \App\Models\User::where('role', 'student')
                ->whereDoesntHave('enrolledClassrooms', function ($q) use ($classroom) {
                    $q->where('classes.id', $classroom->id);
                })
                ->orderBy('full_name')
                ->get();
        }

        return view('classrooms.show', compact('classroom', 'availableStudents', 'user'));
    }

    public function addStudent(Request $request, Classroom $classroom)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher')
            abort(403);

        $validated = $request->validate([
            'student_id' => ['required', 'exists:user,id'],
        ]);

        $student = \App\Models\User::find($validated['student_id']);

        if ($student->role !== 'student') {
            return back()->with('error', 'User is not a student.');
        }

        if ($classroom->students()->where('user.id', $student->id)->exists()) {
            return back()->with('error', 'Student is already enrolled.');
        }

        $classroom->students()->attach($student->id, [
            'enrolled_at' => now(),
            'class_id' => $classroom->id
        ]);

        // Backfill existing assignments for this student (US002-05 / US006-01)
        $assignments = $classroom->assignments; // Uses the new relationship
        foreach ($assignments as $assignment) {
            \App\Models\Enrollment::firstOrCreate([
                'user_id' => $student->id,
                'lesson_id' => $assignment->lesson_id,
            ], [
                'status' => 'in_progress',
                'progress' => 0,
            ]);
        }

        return back()->with('success', 'Student added and enrolled in existing lessons successfully.');
    }

    public function removeStudent(Classroom $classroom, $studentId)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher')
            abort(403);

        $classroom->students()->detach($studentId);

        return back()->with('success', 'Student removed successfully.');
    }

    public function destroy(Classroom $classroom)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher' || $classroom->teacher_id !== $user->id)
            abort(403);

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Classroom deleted successfully.');
    }
}
