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
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['exists:user,id'],
        ]);

        $addedCount = 0;
        $errors = [];

        foreach ($validated['student_ids'] as $studentId) {
            $student = \App\Models\User::find($studentId);

            if ($student->role !== 'student') {
                $errors[] = "User {$student->full_name} is not a student.";
                continue;
            }

            if ($classroom->students()->where('user.id', $student->id)->exists()) {
                // Silently skip if already enrolled, or add to notice
                continue;
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
                if (!$forum->members()->where('user_id', $student->id)->exists()) {
                    $forum->members()->attach($student->id, ['role' => 'member']);
                    $forum->member_count = $forum->members()->count();
                    $forum->save();
                }
            }

            // Add student to group chat - create if it doesn't exist
            $groupChat = $classroom->getClassroomGroupChat();
            if (!$groupChat) {
                // Group chat doesn't exist, create it now
                \Illuminate\Support\Facades\Log::warning('[Classroom] Group chat missing for classroom, creating now', [
                    'classroom_id' => $classroom->id,
                    'classroom_name' => $classroom->name,
                ]);
                
                try {
                    $groupChatName = $classroom->name;
                    if ($classroom->subject) {
                        $groupChatName .= ' ' . $classroom->subject;
                    }
                    if ($classroom->year) {
                        $groupChatName .= ' ' . $classroom->year;
                    }
                    
                    $groupChat = \App\Models\Conversation::create([
                        'type' => 'group',
                        'name' => $groupChatName,
                        'created_by' => $classroom->teacher_id,
                    ]);
                    
                    // Add teacher as participant
                    $groupChat->participants()->attach($classroom->teacher_id);
                    
                    \Illuminate\Support\Facades\Log::info('[Classroom] Group chat created successfully', [
                        'conversation_id' => $groupChat->id,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('[Classroom] Failed to create group chat', [
                        'error' => $e->getMessage(),
                    ]);
                    // Continue without group chat
                    $groupChat = null;
                }
            }
            
            // Add student to group chat if it exists
            if ($groupChat && !$groupChat->participants()->where('user_id', $student->id)->exists()) {
                $groupChat->participants()->attach($student->id);
                \Illuminate\Support\Facades\Log::info('[Classroom] Student added to group chat', [
                    'student_id' => $student->id,
                    'conversation_id' => $groupChat->id,
                ]);
            }

            // Backfill existing assignments for this student
            $assignments = $classroom->assignments;
            foreach ($assignments as $assignment) {
                \App\Models\Enrollment::firstOrCreate([
                    'user_id' => $student->id,
                    'lesson_id' => $assignment->lesson_id,
                ], [
                    'status' => 'in_progress',
                    'progress' => 0,
                ]);
            }

            $addedCount++;
        }

        $message = "{$addedCount} pelajar telah berjaya ditambah.";
        if (!empty($errors)) {
            $message .= " Namun terdapat ralat: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    public function removeStudent(Classroom $classroom, $studentId)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher')
            abort(403);

        // Remove student from classroom
        $classroom->students()->detach($studentId);
        
        // Remove student from group chat if it exists
        $groupChat = $classroom->getClassroomGroupChat();
        if ($groupChat) {
            $groupChat->participants()->detach($studentId);
            \Illuminate\Support\Facades\Log::info('[Classroom] Student removed from group chat', [
                'student_id' => $studentId,
                'conversation_id' => $groupChat->id,
            ]);
        }
        
        // Remove student from forum if it exists
        $forum = \App\Models\Forum::where('class_id', $classroom->id)->first();
        if ($forum) {
            $forum->members()->detach($studentId);
            $forum->member_count = $forum->members()->count();
            $forum->save();
        }

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
