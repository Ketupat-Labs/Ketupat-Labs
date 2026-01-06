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
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:25600',
            'url' => 'nullable|url',
            'is_public' => 'nullable|in:0,1',
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
            'content' => $request->input('content'), // Legacy field
            'content_blocks' => $contentBlocks, // New block-based content
            'material_path' => $filePath,
            'url' => $request->url,
            'duration' => $request->duration,
            'teacher_id' => session('user_id'), // Use session user_id
            'is_published' => true,
            'is_public' => $request->input('is_public', '1') == '1', // Default to public (1) if not specified
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
            'material_file' => 'nullable|file|mimes:pdf,doc,docx|max:25600',
            'url' => 'nullable|url',
            'is_public' => 'nullable|in:0,1',
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
            'is_public' => $request->has('is_public') ? ($request->input('is_public') == '1') : $lesson->is_public, // Preserve existing if not provided
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
            $classroomIds = $user->enrolledClassrooms()->pluck('class.id');
        }

        $lessons = Lesson::where('is_published', true)
            ->where(function ($query) use ($classroomIds, $user) {
                // Show if Public
                $query->where('is_public', true);

                // OR if assigned to one of the student's classrooms via lesson_assignment table
                if ($classroomIds->isNotEmpty()) {
                    $query->orWhereHas('assignments', function ($q) use ($classroomIds) {
                        $q->whereIn('classroom_id', $classroomIds);
                    });
                }

                // OR if user is a teacher and this is their own lesson
                if ($user && $user->role === 'teacher') {
                    $query->orWhere('teacher_id', $user->id);
                }
            })
            ->latest()
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
        if ($user) {
            $classroomIds = collect();
            if ($user->role === 'student') {
                $classroomIds = $user->enrolledClassrooms()->pluck('class.id');
            } elseif ($user->role === 'teacher') {
                // Teachers can see activities from their own classrooms
                $classroomIds = \App\Models\Classroom::where('teacher_id', $user->id)->pluck('id');
            }

            if ($classroomIds->isNotEmpty()) {
                $classActivities = \App\Models\ActivityAssignment::whereIn('classroom_id', $classroomIds)
                    ->with('activity')
                    ->get()
                    ->filter(function ($assignment) {
                        return $assignment->activity !== null; // Filter out null activities
                    })
                    ->map(function ($assignment) use ($activitySubmissions, $user) {
                        $activity = $assignment->activity;
                        if (!$activity)
                            return null;

                        // Attach assignment details to activity object for view
                        $activity->setAttribute('item_type', 'activity');
                        $activity->setAttribute('sort_date', $assignment->assigned_at ?? $assignment->created_at);
                        $activity->setAttribute('due_date', $assignment->due_date);
                        $activity->setAttribute('assignment_id', $assignment->id);

                        // Check if completed
                        if ($user && $user->role === 'student') {
                            $isCompleted = $activitySubmissions->has($assignment->id) ||
                                \App\Models\ActivitySubmission::where('activity_id', $activity->id)
                                    ->where('user_id', $user->id)
                                    ->whereNotNull('completed_at')
                                    ->exists();
                        } else {
                            $isCompleted = false;
                        }
                        $activity->setAttribute('is_completed', $isCompleted);
                        return $activity;
                    })
                    ->filter(); // Remove any null values
            }
        }

        // Fetch Public Activities (exclude those already in classActivities to avoid duplicates)
        $assignedActivityIds = $classActivities->pluck('id')->toArray();
        $publicActivities = \App\Models\Activity::where('is_public', true)
            ->whereNotIn('id', $assignedActivityIds) // Exclude already assigned activities
            ->latest()
            ->get()
            ->map(function ($activity) use ($user) {
                $activity->setAttribute('item_type', 'activity');
                $activity->setAttribute('sort_date', $activity->created_at);
                $activity->setAttribute('due_date', null);
                $activity->setAttribute('assignment_id', 'public_' . $activity->id);

                // Check if completed (for public activities, check by activity_id)
                $isCompleted = false;
                if ($user && $user->role === 'student') {
                    $isCompleted = \App\Models\ActivitySubmission::where('activity_id', $activity->id)
                        ->where('user_id', $user->id)
                        ->whereNotNull('completed_at')
                        ->exists();
                }
                $activity->setAttribute('is_completed', $isCompleted);

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

            // Auto-enroll if not exists (ensure tracking works)
            $enrollment = \App\Models\Enrollment::firstOrCreate(
                [
                    'user_id' => session('user_id'),
                    'lesson_id' => $lesson->id
                ],
                [
                    'status' => 'enrolled',
                    'progress' => 0,
                    'completed_items' => [] // Pass array, let Cast handle serialization
                ]
            );

            // SYNC PROGRESS: Ensure completed items are valid for current lesson blocks
            if ($enrollment) {
                // completed_items is cast to array in Model, but handle string fallback just in case
                $completedItems = $enrollment->completed_items ?? [];
                if (is_string($completedItems)) {
                    $completedItems = json_decode($completedItems, true) ?? [];
                }
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

                if (count($completedItems) !== $originalCount) {
                    // Update DB if changes found
                    $enrollment->completed_items = json_encode($completedItems);

                    // Recalculate Progress
                    $totalItems = count($validItemIds); // Blocks + 1
                    $progress = ($totalItems > 0) ? min(100, round((count($completedItems) / $totalItems) * 100)) : 0;

                    $enrollment->progress = $progress;

                    if ($progress == 100) {
                        $enrollment->status = 'completed';
                    } elseif ($progress > 0) {
                        $enrollment->status = 'in_progress';
                    } else {
                        $enrollment->status = 'enrolled'; // Reset if 0
                    }

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:25600', // Max 25MB
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
    // --- CLONE: Allow teachers to save (clone) a lesson from another teacher ---
    public function clone(Lesson $lesson): RedirectResponse
    {
        $user = Auth::user();

        // 1. Basic Check: Ensure user is a teacher
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized. Only teachers can clone lessons.');
        }

        // 2. Prevent cloning own lessons (optional, but good UX to just redirect or allow duplicate)
        // If clonging own lesson, it's just a duplicate. If cloning another's, it's an import.

        // 3. Create the new Lesson record
        $newLesson = Lesson::create([
            'title' => 'Copy of ' . $lesson->title,
            'topic' => $lesson->topic,
            'content' => $lesson->content,
            'content_blocks' => $lesson->content_blocks,
            'teacher_id' => $user->id, // Assigned to current user
            'duration' => $lesson->duration,
            'material_path' => $lesson->material_path, // Shared file path (could duplicate file to be safe, but shared reference ok for now)
            'url' => $lesson->url,
            'is_published' => false, // Start as draft
            'is_public' => false, // Start as private
            'original_lesson_id' => $lesson->id, // Track provenance
            'original_author_id' => $lesson->teacher_id, // Track original author
        ]);

        return redirect()->route('lessons.edit', $newLesson->id)
            ->with('success', __('Lesson saved to your inventory successfully!'));
    }
}