<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Lesson;

class EnrollmentController extends Controller
{
    public function index()
    {
        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user)
            return redirect()->route('login');

        // Get all published lessons
        $lessons = \App\Models\Lesson::where('is_published', true)->get();

        // Check enrollment status for each lesson
        foreach ($lessons as $lesson) {
            $enrollment = \App\Models\Enrollment::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();

            $lesson->enrolled = $enrollment ? true : false;
            // You might want to check for mandatory assignments here too if you have that logic
        }

        return view('enrollment.index', compact('lessons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lesson,id',
        ]);

        $user = session('user_id') ? \App\Models\User::find(session('user_id')) : null;
        if (!$user)
            return redirect()->route('login');

        // Check if already enrolled
        $exists = \App\Models\Enrollment::where('user_id', $user->id)
            ->where('lesson_id', $request->lesson_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'You are already enrolled in this lesson.');
        }

        \App\Models\Enrollment::create([
            'user_id' => $user->id,
            'lesson_id' => $request->lesson_id,
            'status' => 'enrolled',
            'progress' => 0
        ]);

        // Send Notification
        $lesson = Lesson::find($request->lesson_id);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'lesson_enrolled',
            'title' => 'Pelajaran Didaftar',
            'message' => 'Anda telah berjaya mendaftar untuk pelajaran "' . ($lesson->title ?? 'Unknown') . '"',
            'related_type' => 'lesson',
            'related_id' => $request->lesson_id,
            'is_read' => false,
        ]);

        return back()->with('success', 'Successfully enrolled in the lesson!');
    }

    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'item_id' => 'required',
            'status' => 'required|in:completed,incomplete',
            'total_items' => 'required|integer|min:1'
        ]);

        $enrollment = \App\Models\Enrollment::find($id);

        if (!$enrollment) {
            return response()->json(['success' => false, 'message' => 'Enrollment not found'], 404);
        }

        // Verify user ownership
        if ($enrollment->user_id != session('user_id')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $completedItems = $enrollment->completed_items;
        if (is_string($completedItems)) {
            $completedItems = json_decode($completedItems, true);
        }
        $completedItems = is_array($completedItems) ? $completedItems : [];
        $itemId = $request->item_id;

        if ($request->status === 'completed') {
            if (!in_array($itemId, $completedItems)) {
                $completedItems[] = $itemId;
            }
        } else {
            $completedItems = array_diff($completedItems, [$itemId]);
        }

        // Re-index array
        $completedItems = array_values($completedItems);

        $enrollment->completed_items = json_encode($completedItems);

        // Calculate percentage
        $totalItems = $request->total_items;
        $progress = min(100, round((count($completedItems) / $totalItems) * 100));

        $enrollment->progress = $progress;

        // Update status if complete
        $wasCompleted = $enrollment->status === 'completed';
        if ($progress == 100) {
            $enrollment->status = 'completed';
        } elseif ($progress > 0) {
            $enrollment->status = 'in_progress';
        }

        $enrollment->save();

        // Create notification when lesson is completed for the first time
        if ($progress == 100 && !$wasCompleted) {
            $lesson = Lesson::find($enrollment->lesson_id);
            if ($lesson) {
                Notification::create([
                    'user_id' => $enrollment->user_id,
                    'type' => 'lesson_completed',
                    'title' => 'Pelajaran Selesai!',
                    'message' => 'Tahniah! Anda telah menamatkan pelajaran "' . $lesson->title . '"',
                    'related_type' => 'lesson',
                    'related_id' => $lesson->id,
                    'is_read' => false,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed_items' => $completedItems
        ]);
    }
}
