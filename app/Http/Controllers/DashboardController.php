<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index(Request $request): View|RedirectResponse
    {
        $userId = session('user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        // Award "Pendatang Baru" badge on first login (students only)
        if ($user->role === 'student' && !$user->first_login_at) {
            $this->awardNewcomerBadge($user);
            $user->first_login_at = now();
            $user->save();
        }

        // Redirect based on role
        if ($user->role === 'teacher') {
            $classrooms = \App\Models\Classroom::where('teacher_id', $user->id)->withCount('students')->get();
            return view('dashboard.teacher', [
                'user' => $user,
                'classrooms' => $classrooms
            ]);
        } else {
            // Eager load enrolled classrooms for student dashboard
            $user->load('enrolledClassrooms');

            // Fetch recent feedback (last graded submission)
            $recentFeedback = \App\Models\Submission::where('user_id', $user->id)
                ->where('status', 'Graded')
                ->with('lesson')
                ->latest('updated_at')
                ->first();

            // Fetch recent class assignments (Lessons)
            $recentAssignments = \App\Models\LessonAssignment::whereIn('classroom_id', $user->enrolledClassrooms->pluck('id'))
                ->with('lesson', 'classroom')
                ->latest('assigned_at')
                ->take(5)
                ->get();

            // Fetch recent class assignments (Activity)
            $activityAssignments = \App\Models\ActivityAssignment::whereIn('classroom_id', $user->enrolledClassrooms->pluck('id'))
                ->with('activity', 'classroom')
                ->get();

            // Fetch public activities
            $publicActivities = \App\Models\Activity::where('is_public', true)
                ->latest('updated_at')
                ->take(5)
                ->get()
                ->map(function ($activity) {
                    $assignment = new \App\Models\ActivityAssignment();
                    $assignment->id = 'public_' . $activity->id; // Pseudo ID
                    $assignment->activity = $activity;
                    $assignment->activity_id = $activity->id;
                    $assignment->classroom = null;
                    $assignment->assigned_at = $activity->updated_at;
                    $assignment->due_date = null; 
                    return $assignment;
                });
            
            // Merge and sort
            $recentActivities = $activityAssignments->concat($publicActivities)
                ->sortByDesc('assigned_at')
                ->take(5);
            
            // Merge and sort by date descending
            $mixedTimeline = $recentAssignments->concat($recentActivities)
                ->sortByDesc('assigned_at')
                ->take(10); // Limit total items

            // Get earned badges (not yet redeemed)
            $earnedBadges = \App\Models\Badge::whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->where('user_badge.status', 'earned');
            })->take(6)->get();

            return view('dashboard.student', [
                'user' => $user,
                'recentFeedback' => $recentFeedback,
                'recentAssignments' => $recentAssignments, // Keep this if used elsewhere
                'mixedTimeline' => $mixedTimeline,
                'earnedBadges' => $earnedBadges
            ]);
        }
    }

    /**
     * Award "Pendatang Baru" badge to new students
     */
    private function awardNewcomerBadge($user)
    {
        // Check for Newcomer badge
        $badge = \App\Models\Badge::where('code', 'newcomer')->first();
        
        if ($badge) {
            // Check if badge not already awarded
            $hasBadge = DB::table('user_badge')
                ->where('user_id', $user->id)
                ->where('badge_code', 'newcomer')
                ->exists();
                
            if (!$hasBadge) {
                DB::table('user_badge')->insert([
                    'user_id' => $user->id,
                    'badge_code' => 'newcomer',
                    'status' => 'earned',
                    'earned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
