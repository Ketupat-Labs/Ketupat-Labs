<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Activity;
use App\Models\ActivityAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $isTeacher = $user->role === 'teacher';
        $classrooms = [];
        $activities = []; // Renamed from $lessons
        $events = [];
        $selectedClass = null;
        $classroom_id = $request->get('classroom_id');
        $preselectedActivityId = $request->get('activity_id'); // From redirect
        $selectedLessonId = $request->get('lesson_id');
        $targetLesson = null;
        $assignedClassroomIds = [];
        $isLessonPublic = false;

        if ($selectedLessonId && $isTeacher) {
            $targetLesson = \App\Models\Lesson::find($selectedLessonId);
            if ($targetLesson && $targetLesson->teacher_id == $user->id) {
                 $assignedClassroomIds = \App\Models\LessonAssignment::where('lesson_id', $selectedLessonId)
                                            ->pluck('classroom_id')
                                            ->toArray();
                 $isLessonPublic = $targetLesson->is_public;
            } else {
                // reset if invalid or not owner
                $selectedLessonId = null;
            }
        } elseif ($preselectedActivityId && $isTeacher) {
            // Logic for pre-selected activity (Multi-class support)
            $targetActivity = \App\Models\Activity::find($preselectedActivityId);
            if ($targetActivity && $targetActivity->teacher_id == $user->id) {
                 $assignedClassroomIds = \App\Models\ActivityAssignment::where('activity_id', $preselectedActivityId)
                                            ->pluck('classroom_id')
                                            ->toArray();
                 $isLessonPublic = $targetActivity->is_public; // Reuse variable for generic 'isPublic' state in view
            }
        }

        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));

        if ($isTeacher) {
            $classrooms = Classroom::where('teacher_id', $user->id)->get();

            // Default to first class if not selected
            if (!$classroom_id && $classrooms->isNotEmpty()) {
                $selectedClass = $classrooms->first();
                $classroom_id = $selectedClass->id;
            } elseif ($classroom_id) {
                $selectedClass = $classrooms->find($classroom_id);
            }

            // Get teacher's activities separated by type
            $games = Activity::where('teacher_id', $user->id)
                ->where('type', 'Game')
                ->get();
                
            $quizzes = Activity::where('teacher_id', $user->id)
                ->where('type', 'Quiz')
                ->get();
            
            // Fallback: If no type distinction in data yet, put everything in games or split manually
            if ($games->isEmpty() && $quizzes->isEmpty()) {
                 $games = Activity::where('teacher_id', $user->id)->get();
            }

            // Fetch Assignments for Calendar (Activity Assignments)
            if ($selectedClass) {
                $assignments = ActivityAssignment::with('activity')
                    ->where('classroom_id', $selectedClass->id)
                    ->whereNotNull('due_date')
                    ->get();

                foreach ($assignments as $assignment) {
                    $events[] = [
                        'title' => $assignment->activity->title,
                        'start' => $assignment->due_date, // Full datetime
                        'notes' => $assignment->notes,
                        'activity_id' => $assignment->activity_id,
                        'id' => $assignment->id
                    ];
                }
            }

        } else {
            // Student View
            $classroomIds = $user->enrolledClassrooms()->pluck('classrooms.id'); 

            $assignments = ActivityAssignment::with(['activity', 'classroom'])
                ->whereIn('classroom_id', $classroomIds)
                ->whereNotNull('due_date')
                ->get();

            // Fetch all submissions for these activities by this student
            // ActivitySubmission links to ActivityAssignment, which links to Activity
            $completedActivityIds = \App\Models\ActivitySubmission::where('user_id', $user->id)
                ->with('assignment')
                ->get()
                ->pluck('assignment.activity_id')
                ->filter() // Remove nulls
                ->unique()
                ->toArray();

            foreach ($assignments as $assignment) {
                $isCompleted = in_array($assignment->activity_id, $completedActivityIds);
                
                $events[] = [
                    'title' => $assignment->activity->title . ' (' . $assignment->classroom->name . ')',
                    'start' => $assignment->due_date,
                    'notes' => $assignment->notes,
                    'activity_id' => $assignment->activity_id,
                    'id' => $assignment->id,
                    'is_completed' => $isCompleted
                ];
            }
        }

        return view('schedule.index', compact(
            'classrooms', 
            'games',
            'quizzes', 
            'events', 
            'isTeacher', 
            'selectedClass', 
            'classroom_id', 
            'month', 
            'year',
            'preselectedActivityId',
            'selectedLessonId',
            'targetLesson',
            'assignedClassroomIds',
            'isLessonPublic'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('ScheduleController@store - Incoming Request:', $request->all());

        $user = User::find(session('user_id'));
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'classroom_id' => 'nullable',
            'classroom_ids' => 'nullable|array',
            'is_public' => 'nullable',
            'activity_id' => 'nullable|exists:activity,id',
            'lesson_id' => 'nullable|exists:lesson,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        if (!$request->activity_id && !$request->lesson_id) {
            return back()->with('error', 'Sila pilih Aktiviti atau Pelajaran.');
        }
        
        // Ensure at least one destination is selected (Classroom OR Public)
        if (empty($request->classroom_ids) && !$request->classroom_id && !$request->boolean('is_public')) {
             return back()->with('error', 'Sila pilih sekurang-kurangnya satu Kelas atau set sebagai Public.');
        }

        // Handle Lesson Assignment (Multi-Select Sync)
        if ($request->lesson_id) {
             $lesson = \App\Models\Lesson::where('id', $request->lesson_id)
                ->where('teacher_id', $user->id)
                ->firstOrFail();

             // 1. Update Public Visibility
             $isPublic = $request->has('is_public');
             $lesson->update(['is_public' => $isPublic]);

             // 2. Sync Class Assignments
             // Get array of IDs, filter out 'public' or invalid values just in case
             $classroomIds = $request->input('classroom_ids', []);
             if (!is_array($classroomIds)) {
                 $classroomIds = [];
             }
             
             // Validate that these classrooms belong to the teacher
             $validClassroomIds = Classroom::whereIn('id', $classroomIds)
                ->where('teacher_id', $user->id)
                ->pluck('id')
                ->toArray();

             $lesson->classrooms()->syncWithPivotValues($validClassroomIds, [
                 'type' => 'assigned',
                 'assigned_at' => now(), // Updates timestamp on re-assign, arguably okay.
             ]);

             return redirect()->route('schedule.index', ['lesson_id' => $lesson->id])
                ->with('success', 'Tetapan tugasan pelajaran berjaya dikemaskini.');
        }

        // Handle Activity Assignment
        if ($request->activity_id) {
            // Check if we are updating a specific assignment
            if ($request->assignment_id) {
                $aa = \App\Models\ActivityAssignment::findOrFail($request->assignment_id);
                // Auth check: verify teacher owns the activity
                if ($aa->activity->teacher_id != $user->id) {
                    abort(403);
                }
                
                $aa->update([
                    'due_date' => $request->due_date ? \Carbon\Carbon::parse($request->due_date)->toDateTimeString() : null, 
                    'notes' => $request->notes,
                    'is_public' => $request->boolean('is_public')
                ]);

                return redirect()->route('lessons.index', ['tab' => 'activities'])
                    ->with('success', 'Tugasan aktiviti berjaya dikemaskini.');
            }

            $activity = \App\Models\Activity::where('id', $request->activity_id)
                ->where('teacher_id', $user->id)
                ->firstOrFail();

            // 1. Update Public Visibility (optional, but keep for consistency)
            $isPublic = $request->boolean('is_public');
            $activity->update(['is_public' => $isPublic]);

            // 2. Add New Class Assignments
            $classroomIds = $request->input('classroom_ids', []);
            if (!is_array($classroomIds)) {
                $classroomIds = [];
            }

            // Create new assignments for each selected class
            foreach ($classroomIds as $classroomId) {
                // Validate ownership
                $classroom = Classroom::with('students')->where('id', $classroomId)->where('teacher_id', $user->id)->first();
                if ($classroom) {
                    $assignment = \App\Models\ActivityAssignment::updateOrCreate(
                        ['activity_id' => $activity->id, 'classroom_id' => $classroomId], 
                        [
                            'due_date' => $request->due_date ? \Carbon\Carbon::parse($request->due_date)->toDateTimeString() : null, 
                            'notes' => $request->notes,
                            'assigned_at' => now()
                        ]
                    );
                    
                    if ($assignment->wasRecentlyCreated) {
                        \Illuminate\Support\Facades\Log::info("Activity '{$activity->title}' assigned to class '{$classroom->name}'. Sending notifications to " . $classroom->students->count() . " students.");

                        // Notify all students in the classroom only if it's a new assignment
                        $students = $classroom->students;
                        foreach ($students as $student) {
                            $dueDateText = $assignment->due_date ? ' Tarikh akhir: ' . \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y H:i') : '';
                            
                            try {
                                \App\Models\Notification::create([
                                    'user_id' => $student->id,
                                    'type' => 'activity_assigned',
                                    'title' => 'Aktiviti Baharu Ditugaskan',
                                    'message' => 'Aktiviti "' . $activity->title . '" telah ditugaskan kepada kelas "' . $classroom->name . '"' . $dueDateText,
                                    'related_type' => 'activity',
                                    'related_id' => $activity->id,
                                    'is_read' => false,
                                ]);
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Failed to create notification for student {$student->id}: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
             
            return redirect()->route('lessons.index', ['tab' => 'activities'])
                ->with('success', 'Tugasan aktiviti berjaya dijadualkan.');
        }
    }

    public function destroyActivityAssignment(\App\Models\ActivityAssignment $activityAssignment)
    {
        // Simple auth check via relationship
        if ($activityAssignment->classroom->teacher_id != auth()->id()) {
             abort(403);
        }
        $activityAssignment->delete();
        return back()->with('success', 'Tugasan aktiviti berjaya dipadam.');
    }

    public function updateActivityAssignment(Request $request, \App\Models\ActivityAssignment $activityAssignment)
    {
        // Authorization
        if ($activityAssignment->classroom->teacher_id != auth()->id()) {
             abort(403);
        }

        $request->validate([
             'due_date' => 'nullable|date',
             'notes' => 'nullable|string',
        ]);

        $activityAssignment->update([
             'due_date' => $request->due_date,
             'notes' => $request->notes,
        ]);

        return back()->with('success', 'Tugasan aktiviti berjaya dikemaskini.');
    }

    public function revokePublic(\App\Models\Activity $activity)
    {
        if ($activity->teacher_id != auth()->id()) {
            abort(403);
        }
        $activity->update(['is_public' => false]);
        return back()->with('success', 'Status awam aktiviti berjaya dibatalkan.');
    }

    public function destroy(ActivityAssignment $assignment): RedirectResponse
    {
        $user = User::find(session('user_id'));
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized');
        }

        // Verify the user owns the classroom for this assignment
        $classroom = Classroom::where('id', $assignment->classroom_id)
            ->where('teacher_id', $user->id)
            ->first();

        if (!$classroom) {
            abort(403, 'Unauthorized access to this assignment');
        }

        $assignment->delete();

        return redirect()->back()->with('success', 'Tugasan aktiviti telah dipadam.');
    }
}
