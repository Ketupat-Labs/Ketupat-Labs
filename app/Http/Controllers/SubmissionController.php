<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Submission;

class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure only teachers can access
        if ($user->role !== 'teacher') {
            abort(403, 'Unauthorized action.');
        }

        $query = Submission::with(['user', 'lesson']);

        $lesson = null;
        $classroom = null;

        if ($request->has('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
            $lesson = \App\Models\Lesson::find($request->lesson_id);
        }

        if ($request->has('classroom_id')) {
            $classroom = \App\Models\Classroom::find($request->classroom_id);
            if ($classroom) {
                // Filter submissions from users enrolled in this classroom
                $studentIds = $classroom->students()->pluck('user.id');
                $query->whereIn('user_id', $studentIds);
            }
        }

        $submissions = $query->latest()->get();

        return view('submission.index', compact('submissions', 'lesson', 'classroom'));
    }

    public function show(): View|RedirectResponse
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user) {
            return redirect()->route('login');
        }

        $submissions = Submission::with('lesson')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('submission.student-index', compact('submissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'submission_file' => 'nullable|file|mimes:html,zip,png,jpg,jpeg,pdf,doc,docx,txt|max:10240', // 10MB max
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $lesson = \App\Models\Lesson::find($request->lesson_id);

        // Check if user already submitted for this lesson
        $existingSubmission = Submission::where('user_id', $user->id)
            ->where('lesson_id', $request->lesson_id)
            ->first();

        if ($existingSubmission) {
            // Optional: Allow resubmission if not graded?
            // For now, just update the existing one or error
            if ($existingSubmission->status === 'Graded') {
                return redirect()->route('lesson.show', $request->lesson_id)
                    ->with('error', 'You have already been graded for this lesson.');
            }

            // Delete old file if exists
            if ($existingSubmission->file_path) {
                Storage::delete(str_replace('storage/', 'public/', $existingSubmission->file_path));
            }
        }

        // Store file
        $filePath = null;
        if ($request->hasFile('submission_file')) {
            $storagePath = $request->file('submission_file')->store('public/submissions');
            $filePath = str_replace('public/', 'storage/', $storagePath);
        }

        // Create or Update submission record
        Submission::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $request->lesson_id],
            [
                'assignment_name' => $lesson->title, // Use lesson title as assignment name
                'file_path' => $filePath,
                'file_name' => $request->hasFile('submission_file') ? $request->file('submission_file')->getClientOriginalName() : 'Marked as Completed',
                'status' => 'Submitted - Awaiting Grade',
            ]
        );

        // Update Enrollment Progress
        $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('lesson_id', $request->lesson_id)
            ->first();

        if ($enrollment) {
            $completedItems = $enrollment->completed_items ? json_decode($enrollment->completed_items, true) : [];

            // Add 'submission' to completed items if not present
            if (!in_array('submission', $completedItems)) {
                $completedItems[] = 'submission';
                // Re-index array
                $completedItems = array_values($completedItems);
                $enrollment->completed_items = json_encode($completedItems);

                // Calculate progress
                // Total items = content blocks count + 1 (submission)
                $blocksCount = isset($lesson->content_blocks['blocks']) ? count($lesson->content_blocks['blocks']) : 0;
                $totalItems = $blocksCount + 1;

                $progress = min(100, round((count($completedItems) / $totalItems) * 100));

                $enrollment->progress = $progress;

                // Update status if complete
                if ($progress == 100) {
                    $enrollment->status = 'completed';
                } elseif ($progress > 0) {
                    $enrollment->status = 'in_progress';
                }

                $enrollment->save();
            }
        }

        return redirect()->route('lesson.show', $request->lesson_id)
            ->with('success', 'Your file has been submitted and is awaiting teacher grade.');
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'grade' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission->update([
            'grade' => $request->grade,
            'feedback' => $request->feedback,
            'status' => 'Graded',
        ]);

        return redirect()->route('submission.index', ['lesson_id' => $submission->lesson_id])->with('success', 'Submission graded successfully.');
    }

    public function gradingView(Submission $submission): View
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user || $user->role !== 'teacher') {
            abort(403, 'Unauthorized action.');
        }

        return view('submission.grade', compact('submission'));
    }

    public function viewFile(Submission $submission)
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user) {
            abort(403);
        }

        // Allow if teacher OR if it's the student's own submission
        if ($user->role !== 'teacher' && $user->id !== $submission->user_id) {
            abort(403);
        }

        if (!$submission->file_path) {
            abort(404);
        }

        // Convert "storage/..." back to relative path if needed, or just use as is if stored relative
        // We stored it as "storage/submissions/..." in the submit method, but Storage::get expects relative to disk root
        // Let's rely on how we stored it.
        // In submit(): $filePath = str_replace('public/', 'storage/', $storagePath);
        // Original store was: $request->file(...)->store('public/submissions');

        // We need to map the stored "storage/..." path back to the actual storage disk path "public/..."
        // Or simply clean it. 
        $path = str_replace('storage/', 'public/', $submission->file_path);

        if (!Storage::exists($path)) {
            abort(404, 'File not found');
        }

        return Storage::response($path, $submission->file_name);
    }
}

