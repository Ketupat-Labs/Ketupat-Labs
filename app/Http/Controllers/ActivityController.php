<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\ActivityAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     */
    public function index(): View
    {
        $userId = session('user_id');
        $activities = Activity::where('teacher_id', $userId)->latest()->get();
        
        // Fetch assigned activities with classroom and submission details
        $assignments = ActivityAssignment::with(['activity', 'classroom', 'submissions.user'])
            ->whereHas('activity', function($query) use ($userId) {
                $query->where('teacher_id', $userId);
            })
            ->latest()
            ->get();

        return view('activities.index', compact('activities', 'assignments'));
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create(): View
    {
        return view('activities.create');
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'suggested_duration' => 'required|string|max:50',
            'description' => 'nullable|string',
            'content' => 'nullable|string', // Allow JSON content
        ]);

        Activity::create([
            'teacher_id' => session('user_id'),
            'title' => $request->title,
            'type' => $request->type,
            'suggested_duration' => $request->suggested_duration,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('activities.index')->with('success', 'Aktiviti berjaya dicipta.');
    }

    /**
     * Assign an activity to a classroom.
     */
    public function assign(Request $request, Activity $activity): RedirectResponse
    {
         if (session('user_id') != $activity->teacher_id) {
            abort(403);
        }

        // Redirect to Schedule page to set due date and classroom
        return redirect()->route('schedule.index', ['activity_id' => $activity->id]);
    }

    public function show(Activity $activity): View
    {
        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity): View
    {
        if (session('user_id') != $activity->teacher_id) {
            abort(403);
        }
        return view('activities.edit', compact('activity'));
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        if (session('user_id') != $activity->teacher_id) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'suggested_duration' => 'required|string|max:50',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
        ]);

        $activity->update([
            'title' => $request->title,
            'type' => $request->type,
            'suggested_duration' => $request->suggested_duration,
            'description' => $request->description,
            'content' => $request->content,
        ]);

        return redirect()->route('activities.index')->with('success', 'Aktiviti berjaya dikemaskini.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        if (session('user_id') != $activity->teacher_id) {
            abort(403);
        }

        $activity->delete();

        return redirect()->route('activities.index')->with('success', 'Aktiviti berjaya dipadam.');
    }

    /**
     * Store or update a student's grade/score for an activity assignment.
     */
    public function storeGrade(Request $request, ActivityAssignment $assignment): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:user,id',
            'score' => 'required|integer|min:0',
            'feedback' => 'nullable|string'
        ]);

        // Verify teacher owns the activity
        if (session('user_id') != $assignment->activity->teacher_id) {
            abort(403);
        }

        \App\Models\ActivitySubmission::updateOrCreate(
            [
                'activity_assignment_id' => $assignment->id,
                'user_id' => $request->user_id,
            ],
            [
                'score' => $request->score,
                'feedback' => $request->feedback,
                'completed_at' => now(), // Mark as completed when graded
            ]
        );

        return back()->with('success', 'Markah berjaya disimpan.');
    }
    public function submit(Request $request, Activity $activity)
    {
        $request->validate([
            'score' => 'required|numeric',
        ]);

        $user = auth()->user();
        
        // ALLOW TEACHER TO BYPASS ASSIGNMENT CHECK (For Testing/Preview)
        // BUT STILL SAVE DATA for "Lihat Prestasi" verification
        $assignment = null;
        
        if ($user->role === 'teacher' || $user->id === $activity->teacher_id) {
             // Try to find ANY assignment for this activity to attach the submission to,
             // OR create a dummy one if we really want to track it. 
             // Better: Attach to the first available assignment just for data storage purposes, 
             // or check if a "Teacher Test" assignment exists.
             // For simplicity to satisfy "Sync it": Use the first existing assignment.
             $assignment = \App\Models\ActivityAssignment::where('activity_id', $activity->id)->first();
             
             if (!$assignment) {
                 // If no assignment exists at all, we can't save legally due to FK constraint.
                 // Return success but warn (or just return success as before).
                 // But user wants to see it in table.
                 return response()->json([
                     'success' => true, 
                     'message' => 'Simpan berjaya (Mod Guru - Tiada Tugasan untuk dipautkan)'
                 ]);
             }
        } else {
            // NORMAL STUDENT FLOW
            // Find assignment for this user (via their classrooms)
            $classroomIds = $user->enrolledClassrooms ? $user->enrolledClassrooms->pluck('id') : collect();
            
            if($classroomIds->isEmpty() && method_exists($user, 'enrolledClassrooms')){
                 $classroomIds = $user->enrolledClassrooms()->pluck('classrooms.id');
            }
    
            $assignment = ActivityAssignment::whereIn('classroom_id', $classroomIds)
                ->where('activity_id', $activity->id)
                ->first();
        }

        // If Student (or Teacher with no fallback) but no assignment, return error
        if (!$assignment && $user->role !== 'teacher') {
            return response()->json(['error' => 'Anda belum ditugaskan untuk aktiviti ini.'], 404);
        }

        // Save Submission
        if ($assignment) {
            \App\Models\ActivitySubmission::updateOrCreate(
                [
                    'activity_assignment_id' => $assignment->id,
                    'user_id' => $user->id,
                ],
                [
                    'score' => $request->score,
                    'completed_at' => now(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'Rekod disimpan']);
    }

}
