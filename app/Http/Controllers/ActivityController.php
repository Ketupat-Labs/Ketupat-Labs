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
     * Display a listing of activities (Redirected to unified dashboard).
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('lessons.index', ['tab' => 'activities']);
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

        return redirect()->route('lessons.index', ['tab' => 'activities'])->with('success', 'Aktiviti berjaya dicipta.');
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

        return redirect()->route('lessons.index', ['tab' => 'activities'])->with('success', 'Aktiviti berjaya dikemaskini.');
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

        $wasGraded = \App\Models\ActivitySubmission::where('activity_assignment_id', $assignment->id)
            ->where('user_id', $request->user_id)
            ->whereNotNull('score')
            ->exists();
            
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

        // Notify student when activity is graded (first time only)
        if (!$wasGraded) {
            \App\Models\Notification::create([
                'user_id' => $request->user_id,
                'type' => 'activity_graded',
                'title' => 'Aktiviti Dinilai',
                'message' => 'Aktiviti "' . $assignment->activity->title . '" telah dinilai. Markah: ' . $request->score,
                'related_type' => 'activity',
                'related_id' => $assignment->activity->id,
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Markah berjaya disimpan.');
    }
    public function submit(Request $request, Activity $activity)
    {
        $request->validate([
            'score' => 'required|numeric',
        ]);

        // Consistent session-based auth as used elsewhere in this controller
        $userId = session('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Sila log masuk semula.'], 401);
        }
        
        $user = \App\Models\User::with('enrolledClassrooms')->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Pengguna tidak ditemui.'], 404);
        }

        // 1. Determine Assignment
        $assignment = null;
        
        if ($user->role === 'teacher' || $user->id === $activity->teacher_id) {
             // Teacher bypass: Try to link to an existing assignment if possible
             $assignment = \App\Models\ActivityAssignment::where('activity_id', $activity->id)->first();
             
             // If no assignment exists at all and activity isn't public, teacher can still "play" 
             // but we don't force a database record if we can't link it (or we could save via activity_id now).
             if (!$assignment && !$activity->is_public) {
                  return response()->json([
                      'success' => true, 
                      'message' => 'Simpan berjaya (Mod Guru - Pratinjau sahaja)'
                  ]);
             }
        } else {
            // NORMAL STUDENT FLOW
            // Find assignment for this user (via their classrooms)
            $classroomIds = $user->enrolledClassrooms->pluck('id');
            
            $assignment = ActivityAssignment::whereIn('classroom_id', $classroomIds)
                ->where('activity_id', $activity->id)
                ->first();
        }

        // 2. Access Control Check
        // If not assigned and not a teacher, check if activity is PUBLIC
        if (!$assignment && $user->role !== 'teacher') {
            if (!$activity->is_public) {
                return response()->json(['error' => 'Anda belum ditugaskan untuk aktiviti ini.'], 404);
            }
            // Public activities can proceed without an assignment_id (saving via activity_id)
        }

        // 3. Save Submission
        // We now support both assignment-linked results and direct activity-linked results (for public plays)
        $wasCompleted = \App\Models\ActivitySubmission::where('activity_assignment_id', $assignment ? $assignment->id : null)
            ->where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->exists();
            
        \App\Models\ActivitySubmission::updateOrCreate(
            [
                'activity_assignment_id' => $assignment ? $assignment->id : null,
                'activity_id' => $activity->id,
                'user_id' => $user->id,
            ],
            [
                'score' => $request->score,
                'results' => $request->results,
                'completed_at' => now(),
            ]
        );

        // Create notification for activity completion (first time only)
        if (!$wasCompleted) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'activity_completed',
                'title' => 'Aktiviti Selesai!',
                'message' => 'Tahniah! Anda telah menamatkan aktiviti "' . $activity->title . '"',
                'related_type' => 'activity',
                'related_id' => $activity->id,
                'is_read' => false,
            ]);
            
            // Notify teacher if assignment exists
            if ($assignment && $activity->teacher_id) {
                \App\Models\Notification::create([
                    'user_id' => $activity->teacher_id,
                    'type' => 'activity_submission',
                    'title' => 'Penyerahan Aktiviti Baharu',
                    'message' => $user->full_name . ' telah menyerahkan aktiviti "' . $activity->title . '"',
                    'related_type' => 'activity',
                    'related_id' => $activity->id,
                    'is_read' => false,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Rekod disimpan']);
    }

    public function viewSubmissions(ActivityAssignment $assignment)
    {
        $this->authorizeTeacher();
        $submissions = $assignment->submissions()->with('user')->get();
        return view('activity_submissions.index', compact('assignment', 'submissions'));
    }

    public function showSubmission(ActivitySubmission $submission)
    {
        $this->authorizeTeacher();
        $submission->load(['user', 'assignment.activity']);
        return view('activity_submissions.show', compact('submission'));
    }

    protected function authorizeTeacher()
    {
        if (auth()->user()->role !== 'teacher') {
            abort(403, 'Akses tidak dibenarkan.');
        }
    }
}
