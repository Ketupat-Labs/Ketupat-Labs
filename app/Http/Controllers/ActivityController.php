<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\LessonAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::find(session('user_id'));
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $isTeacher = $user->role === 'teacher';
        $classrooms = [];
        $lessons = [];
        $events = [];
        $selectedClass = null;
        $classroom_id = $request->get('classroom_id');

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

            // Get all lessons (assuming can assign any lesson)
            $lessons = Lesson::all(); // Alternatively, filter by subject if applicable

            // Fetch Assignments for Calendar
            if ($selectedClass) {
                $assignments = LessonAssignment::with('lesson')
                    ->where('classroom_id', $selectedClass->id)
                    ->whereNotNull('due_date')
                    ->get();

                foreach ($assignments as $assignment) {
                    $events[] = [
                        'title' => $assignment->lesson->title,
                        'start' => $assignment->due_date, // Full datetime
                        'notes' => $assignment->notes,
                        'lesson_id' => $assignment->lesson_id,
                        'id' => $assignment->id
                    ];
                }
            }

        } else {
            // Student View
            // Get student's enrolled classrooms
            // Just show all assignments across all classes? Or filter by class?
            // Let's show all for now.
            $classroomIds = $user->enrolledClassrooms()->pluck('classrooms.id');

            $assignments = LessonAssignment::with(['lesson', 'classroom'])
                ->whereIn('classroom_id', $classroomIds)
                ->whereNotNull('due_date')
                ->get();

            foreach ($assignments as $assignment) {
                $events[] = [
                    'title' => $assignment->lesson->title . ' (' . $assignment->classroom->title . ')',
                    'start' => $assignment->due_date,
                    'notes' => $assignment->notes,
                    'lesson_id' => $assignment->lesson_id,
                    'id' => $assignment->id
                ];
            }
        }

        return view('activity.index', compact('classrooms', 'lessons', 'events', 'isTeacher', 'selectedClass', 'classroom_id', 'month', 'year'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = User::find(session('user_id'));
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'classroom_id' => 'required|exists:classrooms,id',
            'lesson_id' => 'required|exists:lessons,id',
            'due_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // Check ownership of classroom
        $classroom = Classroom::where('id', $request->classroom_id)
            ->where('teacher_id', $user->id)
            ->firstOrFail();

        LessonAssignment::updateOrCreate(
            [
                'classroom_id' => $request->classroom_id,
                'lesson_id' => $request->lesson_id
            ],
            [
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'type' => 'Mandatory' // Defaulting
            ]
        );

        return redirect()->route('activity.index', ['classroom_id' => $request->classroom_id])
            ->with('success', 'Aktiviti berjaya dikemaskini.');
    }
}
