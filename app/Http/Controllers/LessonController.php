<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LessonController extends Controller
{
    // --- READ (INDEX) - UC004: Display list of lessons for the current teacher ---
    public function index(): View
    {
        // Fetch lessons created ONLY by the currently authenticated teacher
        $lessons = Lesson::where('teacher_id', session('user_id'))->latest()->get();

        return view('lessons.index', compact('lessons'));
    }

    // --- CREATE (CREATE): Show the add form ---
    public function create(): View
    {
        return view('lessons.create');
    }

    // --- CREATE (STORE): Handle form submission and save to DB (UC003) ---
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation 
        $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|max:255',
            'content' => 'nullable|string', // Added content validation
            'duration' => 'nullable|integer|min:5',
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'url' => 'nullable|url',
        ]);

        $filePath = null;

        // 2. File Upload and Storage
        if ($request->hasFile('material_file')) {
            $storagePath = $request->file('material_file')->store('public/lessons');
            $filePath = str_replace('public/', 'storage/', $storagePath);
        }

        // 3. Create the Lesson Record in MySQL (Using validated data)
        Lesson::create([
            'title' => $request->title,
            'topic' => $request->topic,
            'content' => $request->input('content'), // Save content
            'material_path' => $filePath,
            'url' => $request->url,
            'duration' => $request->duration,
            'teacher_id' => session('user_id'), // Use session user_id
            'is_published' => true,
        ]);

        return redirect()->route('lessons.index')->with('success', __('Lesson saved successfully!'));
    }

    // --- READ (SHOW) - UC004: Display a single lesson for student view ---
    public function show(Lesson $lesson): View
    {
        // This is the student content consumption view
        return view('lessons.show', compact('lesson'));
    }

    // --- UPDATE (EDIT): Show the pre-filled form (UC003) ---
    public function edit(Lesson $lesson): View
    {
        // Authorization check
        if (session('user_id') != $lesson->teacher_id) {
            abort(403, __('Unauthorized.'));
        }
        return view('lessons.edit', compact('lesson'));
    }

    // --- UPDATE (UPDATE): Handle form submission and save changes (UC003) ---
    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        // Authorization check
        if (session('user_id') != $lesson->teacher_id) {
            abort(403, __('Unauthorized.'));
        }

        // 1. Validation (CRITICAL: Validation must be done before update)
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|max:255',
            'content' => 'nullable|string', // Added content validation
            'duration' => 'nullable|integer|min:5',
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'url' => 'nullable|url',
        ]);

        $filePath = $lesson->material_path;

        // 2. Handle File Replacement
        if ($request->hasFile('material_file')) {
            // Delete old file from storage
            if ($lesson->material_path) {
                Storage::delete(str_replace('storage/', 'public/', $lesson->material_path));
            }

            // Upload new file
            $storagePath = $request->file('material_file')->store('public/lessons');
            $filePath = str_replace('public/', 'storage/', $storagePath);
        }

        // 3. Update the Lesson Record (Using ONLY validated data + file path)
        $lesson->update([
            'title' => $validatedData['title'],
            'topic' => $validatedData['topic'],
            'content' => $validatedData['content'] ?? $lesson->content, // Update content
            'duration' => $validatedData['duration'],
            'material_path' => $filePath,
            'url' => $request->url ?? $lesson->url,
        ]);

        return redirect()->route('lessons.index')->with('success', __('Lesson updated successfully!'));
    }

    // --- DELETE (DESTROY): Handle deletion request (US003) ---
    public function destroy(Lesson $lesson): RedirectResponse
    {
        if (session('user_id') != $lesson->teacher_id) {
            abort(403, __('Unauthorized.'));
        }

        // 1. Delete the physical file from storage (if it exists)
        if ($lesson->material_path) {
            Storage::delete(str_replace('storage/', 'public/', $lesson->material_path));
        }

        // 2. Delete the database record
        $lesson->delete();

        return redirect()->route('lessons.index')->with('success', __('Lesson deleted successfully!'));
    }

    // --- STUDENT VIEW: List all published lessons ---
    public function studentIndex(): View
    {
        // 1. Fetch Lessons
        $lessons = Lesson::where('is_published', true)->get()->map(function($lesson) {
            $lesson->setAttribute('item_type', 'lesson');
            $lesson->setAttribute('sort_date', $lesson->created_at);
            return $lesson;
        });

        // 2. Fetch Activities Assigned to User's Classrooms
        $activities = collect();
        if (session('user_id')) {
            $user = \App\Models\User::find(session('user_id'));
            if ($user && $user->role === 'student') {
                $classroomIds = $user->enrolledClassrooms()->pluck('classes.id');
                
                $activities = \App\Models\ActivityAssignment::whereIn('classroom_id', $classroomIds)
                    ->with('activity')
                    ->get()
                    ->map(function($assignment) {
                        $activity = $assignment->activity;
                        // Attach assignment details to activity object for view
                        $activity->setAttribute('item_type', 'activity');
                        $activity->setAttribute('sort_date', $assignment->assigned_at ?? $assignment->created_at);
                        $activity->setAttribute('due_date', $assignment->due_date);
                        $activity->setAttribute('assignment_id', $assignment->id);
                        return $activity;
                    });
            }
        }

        // 3. Merge and Sort
        $items = $lessons->concat($activities)->sortByDesc('sort_date');

        return view('lessons.student-index', compact('items'));
    }

    // --- STUDENT VIEW: Show lesson content for students ---
    public function studentShow(Lesson $lesson): View
    {
        // Check if lesson is published
        if (!$lesson->is_published) {
            abort(404);
        }

        $submission = null;
        if (session('user_id')) {
            $submission = \App\Models\Submission::where('user_id', session('user_id'))
                ->where('lesson_id', $lesson->id)
                ->first();
        }

        return view('lessons.student-show', compact('lesson', 'submission'));
    }
}