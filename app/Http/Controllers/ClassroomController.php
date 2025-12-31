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
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher') abort(403);

        $existingNames = Classroom::where('teacher_id', $user->id)->pluck('name');
        return view('classrooms.create', compact('existingNames'));
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

        // Normalize input name: remove spaces, lowercase
        $inputName = strtolower(str_replace(' ', '', $validated['name']));

        // Check for existing classrooms with similar names for this teacher
        $existingClassrooms = Classroom::where('teacher_id', $user->id)->get();

        foreach ($existingClassrooms as $existing) {
            $existingName = strtolower(str_replace(' ', '', $existing->name));
            if ($existingName === $inputName) {
                return back()->with('error', 'Kelas dengan nama yang sama (ejaan hampir serupa) sudah wujud. Sila gunakan nama lain.')->withInput();
            }
        }

        // Group chat will be automatically created via model event
        Classroom::create([
            'teacher_id' => $user->id,
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Classroom created successfully.');
    }

    public function edit(Classroom $classroom)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher' || $classroom->teacher_id !== $user->id)
            abort(403);

        // Get existing names excluding the current classroom
        $existingNames = Classroom::where('teacher_id', $user->id)
            ->where('id', '!=', $classroom->id)
            ->pluck('name');

        return view('classrooms.edit', compact('classroom', 'existingNames'));
    }

    public function update(Request $request, Classroom $classroom)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher' || $classroom->teacher_id !== $user->id)
            abort(403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'subject' => ['required', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        // Normalize input name: remove spaces, lowercase
        $inputName = strtolower(str_replace(' ', '', $validated['name']));

        // Check for existing classrooms with similar names for this teacher (excluding current)
        $existingClassrooms = Classroom::where('teacher_id', $user->id)
            ->where('id', '!=', $classroom->id)
            ->get();

        foreach ($existingClassrooms as $existing) {
            $existingName = strtolower(str_replace(' ', '', $existing->name));
            if ($existingName === $inputName) {
                return back()->with('error', 'Kelas dengan nama yang sama (ejaan hampir serupa) sudah wujud. Sila gunakan nama lain.')->withInput();
            }
        }

        $classroom->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'year' => $validated['year'] ?? null,
        ]);

        return redirect()->route('classrooms.index')->with('success', 'Kelas berjaya dikemaskini.');
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

        // Load teacher and students with avatar_url
        $classroom->load('teacher', 'students');

        // Load lessons with user's specific enrollment/status
        $classroom->load([
            'lessons' => function ($query) use ($user) {
                $query->with([
                    'enrollments' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }
                ]);
                $query->with([
                    'enrollments' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }
                ]);
            },
            'activityAssignments.activity'
        ]);

        // Get students not enrolled in this class (needed for Teacher's Add Student dropdown)
        $availableStudents = [];
        if ($isTeacher) {
            $availableStudents = \App\Models\User::where('role', 'student')
                ->whereDoesntHave('enrolledClassrooms', function ($q) use ($classroom) {
                    $q->where('class.id', $classroom->id);
                })
                ->orderBy('full_name')
                ->get();
        }

        // Check if forum exists for this classroom
        $forum = \App\Models\Forum::where('class_id', $classroom->id)->first();

        return view('classrooms.show', compact('classroom', 'availableStudents', 'user', 'forum'));
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
        ]);

        // Create notification for student enrollment
        \App\Models\Notification::create([
            'user_id' => $student->id,
            'type' => 'class_enrollment',
            'title' => 'Anda Telah Ditambah ke Kelas',
            'message' => 'Anda telah ditambah ke kelas "' . $classroom->name . '" oleh ' . $user->full_name,
            'related_type' => 'classroom',
            'related_id' => $classroom->id,
            'is_read' => false,
        ]);

        // Add student to forum if forum exists for this classroom
        $forum = \App\Models\Forum::where('class_id', $classroom->id)->first();
        if ($forum) {
            // Check if student is already a member
            if (!$forum->members()->where('user_id', $student->id)->exists()) {
                $forum->members()->attach($student->id, ['role' => 'member']);
                // Update member count
                $forum->member_count = $forum->members()->count();
                $forum->save();
            }
        }

        // Add student to group chat if it exists for this classroom
        $groupChat = $classroom->getClassroomGroupChat();
        if ($groupChat) {
            // Check if student is already a participant
            if (!$groupChat->participants()->where('user_id', $student->id)->exists()) {
                $groupChat->participants()->attach($student->id);
            }
        }

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

        // Forum will be automatically deleted via model event
        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Classroom deleted successfully.');
    }

    /**
     * Create forum for a classroom
     */
    public function createForum(Request $request, Classroom $classroom)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user) {
            return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
        }

        // Check if user is the teacher of this classroom
        if ($user->role !== 'teacher' || $classroom->teacher_id !== $user->id) {
            return response()->json(['status' => 403, 'message' => 'Only the classroom teacher can create a forum'], 403);
        }

        // Check if forum already exists
        $existingForum = \App\Models\Forum::where('class_id', $classroom->id)->first();
        if ($existingForum) {
            return response()->json([
                'status' => 200,
                'message' => 'Forum already exists',
                'data' => ['forum_id' => $existingForum->id],
            ]);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Load students relationship to ensure all existing students are included
            $classroom->load('students');
            
            // Create forum with class name + subject as title
            $forumTitle = $classroom->name . ' ' . $classroom->subject;
            
            $forum = \App\Models\Forum::create([
                'created_by' => $user->id,
                'title' => $forumTitle,
                'description' => '', // Blank description as requested
                'category' => null,
                'visibility' => 'class', // Visibility within the class
                'class_id' => $classroom->id,
                'member_count' => 1,
                'post_count' => 0,
            ]);

            // Add teacher as admin member
            $forum->members()->attach($user->id, ['role' => 'admin']);

            // Add all existing classroom students as members
            $students = $classroom->students;
            foreach ($students as $student) {
                // Check if student is already a member (to avoid duplicates)
                if (!$forum->members()->where('user_id', $student->id)->exists()) {
                    $forum->members()->attach($student->id, ['role' => 'member']);
                }
            }

            // Update member count
            $forum->member_count = $forum->members()->count();
            $forum->save();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Forum created successfully',
                'data' => ['forum_id' => $forum->id],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Failed to create forum: ' . $e->getMessage(),
            ], 500);
        }
    }

}
