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

        // Fetch activities (Games and Quizzes) for the second tab
        $games = \App\Models\Activity::where('teacher_id', session('user_id'))->where('type', 'Game')->latest()->get();
        $quizzes = \App\Models\Activity::where('teacher_id', session('user_id'))->where('type', 'Quiz')->latest()->get();

        return view('lessons.index', compact('lessons', 'games', 'quizzes'));
    }

    // --- CREATE (CREATE): Show the add form ---
    public function create(): View
    {
        return view('lessons.create-new');
    }

    // --- CREATE (STORE): Handle form submission and save to DB (UC003) ---
    public function store(Request $request): RedirectResponse
    {
        // 1. Validation 
        $request->validate([
            'title' => 'required|string|max:255',
            'topic' => 'required|string|max:255',
            'content' => 'nullable|string', // Old field for backward compatibility
            'content_blocks' => 'nullable|json', // New block-based content
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

        // 3. Parse content_blocks if provided
        $contentBlocks = null;
        if ($request->has('content_blocks')) {
            $contentBlocks = json_decode($request->content_blocks, true);
        }

        // 4. Create the Lesson Record in MySQL (Using validated data)
        Lesson::create([
            'title' => $request->title,
            'topic' => $request->topic,
            'is_public' => false, // Default to Private
            'is_published' => true, // Ready by default, but restricted visibility checking is_public
            'content' => $request->input('content'), // Legacy field
            'content_blocks' => $contentBlocks, // New block-based content
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
            'content_blocks' => 'nullable|json', // Block editor data
            'content' => 'nullable|string', // Backward compatibility
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

        // 3. Prepare content_blocks data
        $contentBlocks = null;
        if ($request->has('content_blocks')) {
            $contentBlocks = json_decode($request->content_blocks, true);
        }

        // 4. Update the Lesson Record (Using ONLY validated data + file path)
        $lesson->update([
            'title' => $validatedData['title'],
            'topic' => $validatedData['topic'],
            'content_blocks' => $contentBlocks, // New block editor data
            'content' => $validatedData['content'] ?? $lesson->content, // Backward compatibility
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
        // 2. Fetch Activity Submissions for Completion Check
        $user = auth()->user();
        $activitySubmissions = collect();
        $lessonEnrollments = collect();

        if ($user) {
            $activitySubmissions = \App\Models\ActivitySubmission::where('user_id', $user->id)
                ->get()
                ->pluck('completed_at', 'activity_assignment_id');
            
            $lessonEnrollments = \App\Models\Enrollment::where('user_id', $user->id)
                ->get()
                ->pluck('status', 'lesson_id');
        }

        // 1. Fetch Lessons
        $classroomIds = collect();
        if ($user && $user->role === 'student') {
             $classroomIds = $user->enrolledClassrooms()->pluck('classes.id');
        }

        $lessons = Lesson::where('is_published', true)
            ->where(function($query) use ($classroomIds) {
                // Show if Public
                $query->where('is_public', true)
                // OR if assigned to one of the student's classrooms
                      ->orWhereHas('classrooms', function($q) use ($classroomIds) {
                          $q->whereIn('classroom_id', $classroomIds);
                      });
            })
            ->get()
            ->map(function ($lesson) use ($lessonEnrollments) {
            $lesson->setAttribute('item_type', 'lesson');
            $lesson->setAttribute('sort_date', $lesson->created_at);
            // Check completion
            $isCompleted = $lessonEnrollments->get($lesson->id) === 'completed';
            $lesson->setAttribute('is_completed', $isCompleted);
            return $lesson;
        });

        // 2. Fetch Activities (Class Assigned + Public)
        $classActivities = collect();
        if ($user && $user->role === 'student') {
            $classroomIds = $user->enrolledClassrooms()->pluck('classes.id');

            $classActivities = \App\Models\ActivityAssignment::whereIn('classroom_id', $classroomIds)
                ->with('activity')
                ->get()
                ->map(function ($assignment) use ($activitySubmissions) {
                    $activity = $assignment->activity;
                    // Attach assignment details to activity object for view
                    $activity->setAttribute('item_type', 'activity');
                    $activity->setAttribute('sort_date', $assignment->assigned_at ?? $assignment->created_at);
                    $activity->setAttribute('due_date', $assignment->due_date);
                    $activity->setAttribute('assignment_id', $assignment->id);
                    // Check if completed
                    $activity->setAttribute('is_completed', $activitySubmissions->has($assignment->id));
                    return $activity;
                });
        }

        // Fetch Public Activities
        $publicActivities = \App\Models\Activity::where('is_public', true)
             ->latest()
             ->get()
             ->map(function ($activity) {
                 $activity->setAttribute('item_type', 'activity');
                 $activity->setAttribute('sort_date', $activity->created_at);
                 $activity->setAttribute('due_date', null);
                 $activity->setAttribute('assignment_id', 'public_' . $activity->id);
                 $activity->setAttribute('is_completed', false); 
                 return $activity;
             });

        $activities = $classActivities->concat($publicActivities);

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
        $enrollment = null;

        if (session('user_id')) {
            // Check if user is a teacher - if so, show them the Teacher View (Manage Lesson View)
            $user = \App\Models\User::find(session('user_id'));
            if ($user && $user->role === 'teacher') {
                return view('lessons.show', compact('lesson'));
            }

            $submission = \App\Models\Submission::where('user_id', session('user_id'))
                ->where('lesson_id', $lesson->id)
                ->first();

            $enrollment = \App\Models\Enrollment::where('user_id', session('user_id'))
                ->where('lesson_id', $lesson->id)
                ->first();

            // SYNC PROGRESS: Ensure completed items are valid for current lesson blocks
            if ($enrollment) {
                $completedItems = $enrollment->completed_items ? json_decode($enrollment->completed_items, true) : [];
                $originalCount = count($completedItems);

                // Get all valid Item IDs from lesson blocks
                $validItemIds = [];
                if (isset($lesson->content_blocks['blocks'])) {
                    foreach ($lesson->content_blocks['blocks'] as $index => $block) {
                        $validItemIds[] = $block['id'] ?? 'block_' . $index;
                    }
                }

                // Add submission to valid IDs if it exists
                // We always allow 'submission' as a valid tracking item for progress
                $validItemIds[] = 'submission';

                // Filter out invalid items (orphaned from deleted blocks?)
                $completedItems = array_values(array_intersect($completedItems, $validItemIds));

                // Also check if 'submission' is marked complete but no submission exists? 
                // (Optional: strict check, but maybe overkill. Let's trust the flag if submission was deleted manually but progress kept? No, let's strictly check submission existence too if needed. But for now, just syncing block IDs is enough to fix the 67% ghost issue.)

                if (count($completedItems) !== $originalCount) {
                    // Update DB if changes found
                    $enrollment->completed_items = json_encode($completedItems);

                    // Recalculate Progress
                    $totalItems = count($validItemIds); // Blocks + 1
                    $progress = ($totalItems > 0) ? min(100, round((count($completedItems) / $totalItems) * 100)) : 0;

                    $enrollment->progress = $progress;

                    if ($progress == 100)
                        $enrollment->status = 'completed';
                    elseif ($progress > 0)
                        $enrollment->status = 'in_progress';
                    else
                        $enrollment->status = 'enrolled'; // Reset to enrolled if 0

                    $enrollment->save();
                }
            }
        }

        return view('lessons.student-show', compact('lesson', 'submission', 'enrollment'));
    }

    // --- API: Upload image for block editor ---
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('image')) {
            $storagePath = $request->file('image')->store('public/lessons/images');
            $url = asset(str_replace('public/', 'storage/', $storagePath));

            return response()->json([
                'success' => true,
                'url' => $url,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No image file provided',
        ], 400);
    }
}