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

            // Get teacher's activities
            $activities = Activity::where('teacher_id', $user->id)->get();

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
            $classroomIds = $user->enrolledClassrooms()->pluck('classrooms.id'); // Verify this relationship format

            $assignments = ActivityAssignment::with(['activity', 'classroom'])
                ->whereIn('classroom_id', $classroomIds)
                ->whereNotNull('due_date')
                ->get();

            foreach ($assignments as $assignment) {
                $events[] = [
                    'title' => $assignment->activity->title . ' (' . $assignment->classroom->name . ')',
                    'start' => $assignment->due_date,
                    'notes' => $assignment->notes,
                    'activity_id' => $assignment->activity_id,
                    'id' => $assignment->id
                ];
            }
        }

        return view('schedule.index', compact(
            'classrooms', 
            'activities', 
            'events', 
            'isTeacher', 
            'selectedClass', 
            'classroom_id', 
            'month', 
            'year',
            'preselectedActivityId'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::find(session('user_id'));
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'classroom_id' => 'required|exists:classes,id',
            'activity_id' => 'required|exists:activities,id',
            'due_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // Check ownership of classroom
        $classroom = Classroom::where('id', $request->classroom_id)
            ->where('teacher_id', $user->id)
            ->firstOrFail();

        // Update or Create ActivityAssignment
        ActivityAssignment::updateOrCreate(
            [
                'classroom_id' => $request->classroom_id,
                'activity_id' => $request->activity_id
            ],
            [
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'status' => 'assigned',
                'assigned_at' => now()
            ]
        );

        return redirect()->route('schedule.index', ['classroom_id' => $request->classroom_id])
            ->with('success', 'Aktiviti berjaya dijadualkan.');
    }
}
