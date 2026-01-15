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

        $publicActivities = collect([]);

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

            // Fetch Public Activities
            $publicActivities = \App\Models\Activity::where('is_public', true)
                ->with('teacher')
                ->latest()
                ->get();
        }

        return view('assignments.index', compact('assignments', 'user', 'publicActivities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): \Illuminate\View\View
    {
        $user = \App\Models\User::find(session('user_id'));
        if (!$user) {
            abort(403);
        }

        $classrooms = \App\Models\Classroom::where('teacher_id', $user->id)->get();

        // Prepare data for Edit Modal (Grouping data by lesson)
        $lessons = \App\Models\Lesson::where('teacher_id', $user->id)->latest()->get();
        $lessonStates = [];
        foreach ($lessons as $lesson) {
            $assignedClassIds = \App\Models\LessonAssignment::where('lesson_id', $lesson->id)
                ->pluck('classroom_id')
                ->toArray();

            $lessonStates[$lesson->id] = [
                'is_public' => $lesson->is_public,
                'classroom_ids' => $assignedClassIds
            ];
        }

        // Fetch Recent Lesson Assignments
        $assignments = \App\Models\LessonAssignment::with(['lesson', 'classroom'])
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->latest()
            ->take(50) // Limit for performance
            ->get();

        // --- ACTIVITY / CALENDAR LOGIC ---
        $month = $request->get('month', date('n'));
        $year = $request->get('year', date('Y'));
        
        // Fix: Use Carbon instead of cal_days_in_month to avoid 500 error if calendar extension missing
        $date = \Carbon\Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $date->daysInMonth;
        $firstDayOfMonth = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Activities (Split by Type)
        $games = \App\Models\Activity::where('teacher_id', $user->id)->where('type', 'Game')->get();
        $quizzes = \App\Models\Activity::where('teacher_id', $user->id)->where('type', 'Quiz')->get();
        if ($games->isEmpty() && $quizzes->isEmpty()) {
            $games = \App\Models\Activity::where('teacher_id', $user->id)->get();
        }

        // Selected Class for Calendar
        $events = [];
        // Fetch events for ALL classrooms belonging to the teacher
        if ($classrooms->isNotEmpty()) {
            $actAssignments = \App\Models\ActivityAssignment::with(['activity', 'classroom'])
                ->whereIn('classroom_id', $classrooms->pluck('id'))
                // ->whereNotNull('due_date') // Column missing in DB
                ->get();

            foreach ($actAssignments as $aa) {
                // Fix: Check if activity exists to prevent 500 error
                if (!$aa->activity) continue;
                
                $events[] = [
                    'title' => $aa->activity->title . ' (' . $aa->classroom->name . ')', // Append class name for context
                    'start' => $aa->due_date,
                    'notes' => $aa->notes,
                    'activity_id' => $aa->activity_id,
                    'id' => $aa->id
                ];
            }
        }

        $selectedClass = $classrooms->first(); // Default fallback

        // Check for specific tab request
        $activeTab = $request->has('month') || $request->has('classroom_id') ? 'activity' : 'lesson';
        if ($request->has('tab'))
            $activeTab = $request->get('tab');

        // Fetch Recent Activity Assignments
        $activity_id = $request->get('activity_id');
        $preselectedActivityId = $activity_id;
        $classroom_id = $request->get('classroom_id');

        $assignmentsCollection = \App\Models\ActivityAssignment::with(['activity', 'classroom'])
            ->whereIn('classroom_id', $classrooms->pluck('id'))
            ->latest()
            ->get();

        // Fetch Public Activities by this teacher (Pseudo-Assignments)
        $publicActivities = \App\Models\Activity::with('assignments')
            ->where('teacher_id', $user->id)
            ->where('is_public', true)
            ->latest()
            ->get()
            ->map(function ($activity) {
                $pseudo = new \stdClass();
                $pseudo->id = 'public_' . $activity->id;
                $pseudo->activity = $activity;
                $pseudo->classroom = (object) ['name' => 'Public (Semua Pelajar)'];
                $pseudo->due_date = null;
                $pseudo->notes = '';
                $pseudo->is_pseudo = true;
                return $pseudo;
            });

        $activityAssignments = $assignmentsCollection->toBase()->merge($publicActivities)->sortByDesc('created_at');

        // Build Activity States for JS Lookup
        $activityStates = [];
        foreach ($activityAssignments as $aa) {
            $act = $aa->activity;
            if (!$act)
                continue;

            // Determine classroom IDs
            $classroomIds = [];
            if ($act->relationLoaded('assignments')) {
                $classroomIds = $act->assignments->pluck('classroom_id')->toArray();
            }

            // Critical Fallback: Ensure the data contains at least the current classroom if empty
            if (empty($classroomIds) && isset($aa->classroom_id) && $aa->classroom_id) {
                $classroomIds = [$aa->classroom_id];
            }

            // Ensure ID is string for safe lookup
            $key = (string) $aa->id;

            $activityStates[$key] = [
                'id' => $aa->id,
                'activity_id' => $act->id,
                'title' => $act->title,
                'date' => $aa->due_date ? \Carbon\Carbon::parse($aa->due_date)->format('Y-m-d\TH:i') : '',
                'notes' => $aa->notes ?? '',
                'is_public' => (bool) $act->is_public,
                'classroom_ids' => $classroomIds
            ];
        }

        // Additional data for activity scheduling form pre-fill
        $assignedClassroomIdsForActivity = [];
        $isActivityPublic = false;
        if ($preselectedActivityId) {
            $act = \App\Models\Activity::find($preselectedActivityId);
            if ($act && $act->teacher_id == $user->id) {
                $assignedClassroomIdsForActivity = \App\Models\ActivityAssignment::where('activity_id', $preselectedActivityId)
                    ->pluck('classroom_id')
                    ->toArray();
                $isActivityPublic = $act->is_public;
            }
        }

        return view('assignments.create', compact(
            'classrooms',
            'lessons',
            'assignments',
            'lessonStates',
            'activityAssignments',
            'activityStates',
            'month',
            'year',
            'daysInMonth',
            'firstDayOfMonth',
            'games',
            'quizzes',
            'events',
            'selectedClass',
            'activeTab',
            'preselectedActivityId',
            'classroom_id',
            'assignedClassroomIdsForActivity',
            'isActivityPublic'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'classroom_ids' => 'array',
            'classroom_ids.*' => 'exists:class,id',
            'lessons' => 'required|array',
            'lessons.*' => 'exists:lesson,id',
            'is_public' => 'boolean',
            'assigned_at' => 'required|date',
            'due_date' => 'nullable|date|after:assigned_at',
            'notes' => 'nullable|string',
        ]);

        $isPublic = $request->has('is_public');
        $classroomIds = $request->input('classroom_ids', []);

        foreach ($request->lessons as $lessonId) {
            $lesson = \App\Models\Lesson::find($lessonId);
            if (!$lesson)
                continue;

            // 1. Update Public Visibility
            if ($isPublic) {
                $lesson->update(['is_public' => true]);
            } elseif (count($classroomIds) > 0) {
                // Only set private if explicitly assigning to classes (strict mode)
                $lesson->update(['is_public' => false]);
            }

            // 2. Create Class Assignments
            if (count($classroomIds) > 0) {
                $classrooms = \App\Models\Classroom::with('students')->whereIn('id', $classroomIds)->get();

                foreach ($classrooms as $classroom) {
                    // Create Assignment (Update if exists to allow date changes)
                    \App\Models\LessonAssignment::updateOrCreate([
                        'classroom_id' => $classroom->id,
                        'lesson_id' => $lessonId,
                    ], [
                        'type' => 'Mandatory',
                        'assigned_at' => $request->assigned_at,
                        'due_date' => $request->due_date,
                        'notes' => $request->notes,
                    ]);

                    // Enroll Students
                    foreach ($classroom->students as $student) {
                        $wasEnrolled = \App\Models\Enrollment::where('user_id', $student->id)
                            ->where('lesson_id', $lessonId)
                            ->exists();

                        \App\Models\Enrollment::firstOrCreate([
                            'user_id' => $student->id,
                            'lesson_id' => $lessonId,
                        ], [
                            'status' => 'in_progress',
                            'progress' => 0,
                        ]);

                        // Notify student if newly enrolled
                        if (!$wasEnrolled) {
                            \App\Models\Notification::create([
                                'user_id' => $student->id,
                                'type' => 'lesson_assigned',
                                'title' => 'Pelajaran Baharu Ditugaskan',
                                'message' => 'Pelajaran "' . $lesson->title . '" telah ditugaskan kepada kelas "' . $classroom->name . '"',
                                'related_type' => 'lesson',
                                'related_id' => $lesson->id,
                                'is_read' => false,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('assignments.create')->with('success', 'Assignments updated successfully.');
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
        $assignment = \App\Models\LessonAssignment::with('classroom.students')->findOrFail($id);

        // Optional: Cleanup enrollments if needed. 
        // For now, simple delete of assignment record.
        // If we want to be strict:
        // $studentIds = $assignment->classroom->students->pluck('id');
        // \App\Models\Enrollment::where('lesson_id', $assignment->lesson_id)->whereIn('user_id', $studentIds)->delete();

        $assignment->delete();

        return back()->with('success', 'Assignment removed.');
    }
}
