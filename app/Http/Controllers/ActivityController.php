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
        return view('activities.index', compact('activities'));
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
        ]);

        Activity::create([
            'teacher_id' => session('user_id'),
            'title' => $request->title,
            'type' => $request->type,
            'suggested_duration' => $request->suggested_duration,
            'description' => $request->description,
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
}
