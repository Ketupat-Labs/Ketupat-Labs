<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get classrooms based on role
        if ($user->role === 'teacher') {
            $classrooms = \App\Models\Classroom::where('teacher_id', $user->id)->get();
        } else {
            // Assume student or others see enrolled classes (Consistency with Performance)
             $classrooms = $user->enrolledClassrooms;
        }

        // Determine selected classroom (default to first one if exists)
        $selectedClassId = $request->get('class_id', $classrooms->first()->id ?? null);
        
        // Ensure selected class is in the allowed list
        $selectedClass = $classrooms->find($selectedClassId);

        if (!$selectedClass) {
            return view('progress.index', [
                'classrooms' => $classrooms,
                'selectedClass' => null,
                'progressData' => [],
                'lessons' => [],
                'summary' => ['totalStudents' => 0, 'totalLessons' => 0, 'lessonCompletion' => []]
            ]);
        }

        // Get students enrolled in the selected classroom
        $students = \App\Models\User::whereHas('enrolledClassrooms', function ($query) use ($selectedClassId) {
            $query->where('class.id', $selectedClassId);
        })->get();

        // Get lessons assigned to this classroom through lesson_assignments pivot table
        $lessons = $selectedClass->lessons;

        // Get Activities assigned to this classroom
        $activities = \App\Models\Activity::whereHas('assignments', function($q) use ($selectedClassId) {
            $q->where('classroom_id', $selectedClassId);
        })->get();

        // Build progress data
        $progressData = [];

        foreach ($students as $student) {
            $studentProgress = [
                'student' => $student,
                'lessons' => [],
                'activities' => [], // New
                'completedCount' => 0,
                'totalLessons' => $lessons->count(),
                'totalActivities' => $activities->count(), // New
                'completionPercentage' => 0
            ];

            $totalProgressSum = 0;

            // Process Lessons
            foreach ($lessons as $lesson) {
                $enrollment = \App\Models\Enrollment::where('user_id', $student->id)
                    ->where('lesson_id', $lesson->id)
                    ->first();

                $progressValue = $enrollment ? $enrollment->progress : 0;
                $totalProgressSum += $progressValue;

                if ($progressValue == 100) {
                    $status = 'Completed';
                } elseif ($progressValue > 0) {
                    $status = 'In Progress';
                } else {
                    $status = 'Not Started';
                }

                $studentProgress['completedCount'] += ($progressValue == 100 ? 1 : 0);

                $studentProgress['lessons'][] = [
                    'lesson' => $lesson,
                    'status' => $status,
                    'progress' => $progressValue
                ];
            }
            
            // Process Activities
            foreach ($activities as $activity) {
                 $submission = \App\Models\ActivitySubmission::where('user_id', $student->id)
                        ->whereHas('assignment', function($q) use ($activity, $selectedClassId) {
                            $q->where('activity_id', $activity->id)
                              ->where('classroom_id', $selectedClassId);
                        })->first();
                
                $isCompleted = $submission && $submission->completed_at;
                
                $studentProgress['activities'][] = [
                    'activity' => $activity,
                    'status' => $isCompleted ? 'Completed' : 'Not Started',
                    'score' => $submission ? $submission->score . '%' : '-'
                ];
                
                if ($isCompleted) {
                    // Add 100% for completed activity
                    $totalProgressSum += 100;
                    $studentProgress['completedCount']++; // Increment total completed items count
                }
            }

            // Calculate completion percentage (Average of all lesson AND activity progresses)
            $totalItems = $studentProgress['totalLessons'] + $studentProgress['totalActivities'];
            
            if ($totalItems > 0) {
                $studentProgress['completionPercentage'] = round(
                    $totalProgressSum / $totalItems,
                    1
                );
            }

            $progressData[] = $studentProgress;
        }

        // Calculate summary statistics
        $totalCompletionSum = 0;
        $hundredPercentCount = 0;
        foreach ($progressData as $pData) {
            $totalCompletionSum += $pData['completionPercentage'];
            if ($pData['completionPercentage'] == 100) {
                $hundredPercentCount++;
            }
        }

        $classAverage = count($progressData) > 0 ? round($totalCompletionSum / count($progressData), 1) : 0;

        $summary = [
            'totalStudents' => $students->count(),
            'totalLessons' => $lessons->count() + $activities->count(), // Combined per request
            'totalActivities' => $activities->count(),
            'classAverage' => $classAverage,
            'hundredPercentCount' => $hundredPercentCount,
            'lessonCompletion' => []
        ];

        foreach ($lessons as $lesson) {
            $completedCount = \App\Models\Enrollment::where('lesson_id', $lesson->id)
                ->whereIn('user_id', $students->pluck('id'))
                ->where('progress', 100)
                ->count();

            $summary['lessonCompletion'][$lesson->id] = [
                'lesson' => $lesson,
                'completed' => $completedCount,
                'total' => $students->count(),
                'percentage' => $students->count() > 0 ? round(($completedCount / $students->count()) * 100, 1) : 0
            ];
        }

        return view('progress.index', compact(
            'classrooms',
            'selectedClass',
            'progressData',
            'lessons',
            'activities', // Pass to view
            'summary'
        ));
    }
}


