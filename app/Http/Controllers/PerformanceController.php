<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Lesson;
use App\Models\StudentAnswer;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $classrooms = collect();
        $selectedClass = null;
        $selectedClassId = null;
        $selectedLessonId = $request->get('lesson_id', 'all');
        $lessons = collect();
        $activities = collect();
        $filterItems = collect();
        $selectedFilter = $request->get('filter_id', 'all');
        $data = [];
        $mode = 'all';
        $students = collect();

        if ($user->role === 'teacher') {
             $classrooms = Classroom::where('teacher_id', $user->id)->get();
             $selectedClassId = $request->get('class_id', $classrooms->first()->id ?? null);
             $selectedClass = $classrooms->find($selectedClassId);
             
             // Reset filter if class changed or no class selected
             if (!$selectedClass) {
                 $selectedFilter = 'all';
             }
             
             if ($selectedClass) {
                 $students = $selectedClass->students;
                 
                 // Fallback if relation not standard
                 if(!$students->count()){
                     $students = \App\Models\User::whereHas('enrollments', function($q) use ($selectedClassId){
                         $q->where('classroom_id', $selectedClassId);
                     })->get(); 
                 }
                 
                 $activities = \App\Models\Activity::whereHas('assignments', function($q) use ($selectedClass) {
                     $q->where('classroom_id', $selectedClass->id);
                 })->get();
             }
        } else {
             // STUDENT VIEW
             $classrooms = $user->enrolledClassrooms;
             $selectedClass = $classrooms->first();
             $selectedClassId = $selectedClass ? $selectedClass->id : null;
             $students = collect([$user]);
             
             if ($selectedClass) {
                 $activities = \App\Models\Activity::whereHas('assignments', function($q) use ($selectedClass) {
                     $q->where('classroom_id', $selectedClass->id);
                 })->get();
             }
        }

        // Fetch Lessons - Only show lessons assigned to the selected class
        if ($selectedClass) {
            if ($user->role === 'teacher') {
                // For teachers: Only show lessons assigned to the selected class
                $lessons = Lesson::where('is_published', true)
                    ->whereHas('assignments', function($q) use ($selectedClass) {
                        $q->where('classroom_id', $selectedClass->id);
                    })->get();
            } else {
                // For students: Show public lessons OR lessons assigned to their enrolled classes
                $lessons = Lesson::where('is_published', true)
                    ->where(function($q) use ($user, $selectedClass) {
                         $q->where('is_public', true)
                           ->orWhereHas('assignments', function($q2) use ($selectedClass) {
                                $q2->where('classroom_id', $selectedClass->id);
                           });
                    })->get();
            }
        } else {
            // No class selected - show empty
            $lessons = collect();
        }

        // Combine for Dropdown - Only if class is selected
        if ($selectedClass) {
            foreach($lessons as $l) $filterItems->push(['id' => 'lesson-'.$l->id, 'name' => $l->title]);
            foreach($activities as $a) $filterItems->push(['id' => 'activity-'.$a->id, 'name' => $a->title]);
        }
        $filterItems = $filterItems->values();

        // Determine Mode
        if (str_starts_with($selectedFilter, 'activity-')) {
            $activityId = (int) str_replace('activity-', '', $selectedFilter);
            $activities = $activities->where('id', $activityId);
            $lessons = collect();
            $mode = 'activity_detail';
        } elseif (str_starts_with($selectedFilter, 'lesson-')) {
            $lessonId = (int) str_replace('lesson-', '', $selectedFilter);
            $selectedLessonId = $lessonId;
            $mode = 'lesson_detail';
        } else {
            $mode = 'all';
        }

        if ($mode === 'all') {
            // View Mode A: Summary (Used for 'All')
            foreach ($students as $student) {
                $studentRow = [
                    'student' => $student,
                    'grades' => [],
                    'activity_grades' => [], 
                    'total_score' => 0,
                    'max_score' => 0,
                    'average' => 0,
                    'activity_average' => 0
                ];

                // Process Lessons
                foreach ($lessons as $lesson) {
                    // Check Completion (Enrollment)
                    $enrollment = \App\Models\Enrollment::where('user_id', $student->id)
                        ->where('lesson_id', $lesson->id)
                        ->first();
                    $isCompleted = $enrollment && $enrollment->status === 'completed';

                    // Check Quiz
                    $quizAttempt = QuizAttempt::where('user_id', $student->id)
                        ->where('lesson_id', $lesson->id)
                        ->where('submitted', true)
                        ->first();
                    
                    if ($isCompleted) {
                        $score = 100;
                        $max = 100; 
                    } else {
                        $score = $quizAttempt ? $quizAttempt->score : 0;
                        $max = $quizAttempt ? $quizAttempt->total_questions : 0;
                    }

                    $studentRow['grades'][$lesson->id] = [
                        'score' => $score,
                        'max' => $max,
                        'display' => $isCompleted ? '100%' : ($quizAttempt ? "$score/$max" : '-')
                    ];

                    if ($isCompleted || $quizAttempt) {
                        $percentage = ($max > 0) ? ($score / $max) * 100 : 0;
                        if($isCompleted) $percentage = 100;
                        $studentRow['total_score'] += $percentage;
                        $studentRow['max_score'] += 100; 
                    }
                }
                
                // Process Activities logic... (Same as before)
                $studentSubmissions = \App\Models\ActivitySubmission::where('user_id', $student->id)
                    ->with(['assignment.activity'])
                    ->get()
                    ->keyBy(function($submission) {
                        return $submission->assignment->activity_id;
                    });

                $totalActivityScore = 0;
                $activityCount = 0;
                
                foreach ($activities as $activity) {
                     $submission = $studentSubmissions->get($activity->id);
                     $score = $submission ? $submission->score : 0;
                     $studentRow['activity_grades'][$activity->id] = [
                        'score' => $score,
                        'display' => $submission ? $score : '-'
                     ];
                     if ($submission) {
                         $totalActivityScore += $score;
                         $activityCount++;
                     }
                }
                
                // Averages logic...
                if ($studentRow['max_score'] > 0) {
                    $attemptedCount = count(array_filter($studentRow['grades'], fn($g) => $g['display'] !== '-'));
                    if ($attemptedCount > 0) $studentRow['average'] = round($studentRow['total_score'] / $attemptedCount, 1);
                }
                if ($activityCount > 0) $studentRow['activity_average'] = round($totalActivityScore / $activityCount, 1);

                // Add row to data
                $data[] = $studentRow;
            }
            
            // Statistics for ALL mode
            $totalAvg = count($data) > 0 ? collect($data)->avg('average') : 0;
            // Count students with 100% average
            $hundredPercentCount = collect($data)->where('average', 100)->count();

            $statistics = [
                'total_students' => $students->count(),
                'total_items' => $lessons->count() + $activities->count(),
                'class_average' => round($totalAvg, 1),
                'hundred_percent_count' => $hundredPercentCount
            ];
            request()->merge(['statistics' => $statistics]);

        } elseif ($mode === 'activity_detail') { // NEW: Activity Detail Mode
             // Determine question count
             $activity = $activities->first(); // Filtered above
             $content = json_decode($activity->content, true);
             $questions = $content['questions'] ?? [];
             $questionCount = count($questions) > 0 ? count($questions) : 3; // Fallback to 3 if missing

             $completedCount = 0;
             $hundredPercentCount = 0;
             $totalScoreSum = 0;
             $submissionCount = 0;

             foreach ($students as $student) {
                 // Try to find submission via assignment first
                 $submission = \App\Models\ActivitySubmission::where('user_id', $student->id)
                    ->where('activity_id', $activity->id)
                    ->first();
                 
                 $score = $submission ? $submission->score : 0;
                 if ($submission) {
                     $completedCount++;
                     $submissionCount++;
                     $totalScoreSum += $score;
                     
                     // Check for perfect score
                     if ($score == 100 || ($questionCount > 0 && $score == $questionCount)) {
                         $hundredPercentCount++;
                     }
                 }
                 
                 // Use ACTUAL results if available
                 $answers = [];
                 
                 if ($submission && $submission->results) {
                     // Check if results is already an array (Laravel JSON casting) or needs decoding
                     $results = is_array($submission->results) 
                         ? $submission->results 
                         : json_decode($submission->results, true);
                     
                     // Check if it's Quiz format with breakdown
                     if (isset($results['breakdown']) && is_array($results['breakdown'])) {
                         foreach ($results['breakdown'] as $item) {
                             $answers[] = $item['isCorrect'] ? '✓' : '✗';
                         }
                     } else {
                         // Fallback to inferred logic
                         $answers = $this->inferAnswers($score, $questionCount);
                     }
                 } else {
                     // No submission or no results - infer
                     $answers = $this->inferAnswers($submission ? $score : null, $questionCount);
                 }

                 $data[] = [
                    'student' => $student,
                    'answers' => $answers,
                    'total_marks' => $submission ? $score : '-'
                 ];
             }
             // Pass questions to view for headers
             request()->merge(['activity_questions' => $questions]);
             
             // Statistics Activity
             $statistics = [
                 'total_students' => $students->count(),
                 'completed_count' => $completedCount,
                 'not_completed_count' => $students->count() - $completedCount,
                 'hundred_percent_count' => $hundredPercentCount,
                 'average_score' => $submissionCount > 0 ? round($totalScoreSum / $submissionCount, 1) : 0
             ];
             request()->merge(['statistics' => $statistics]);

        } else {
            // View Mode B: Specific Lesson Breakdown (Refined)
            $selectedLesson = $lessons->firstWhere('id', $selectedLessonId);
            
            $completedCount = 0;
            $hundredPercentCount = 0;
            $totalScoreSum = 0;
            $gradedCount = 0;

            if ($selectedLesson) {
                foreach ($students as $student) {
                    // Check Enrollment
                    $enrollment = \App\Models\Enrollment::where('user_id', $student->id)
                        ->where('lesson_id', $selectedLesson->id)
                        ->first();
                    $isCompleted = $enrollment && $enrollment->status === 'completed';

                    // Check Teacher Grade
                    $submission = Submission::where('user_id', $student->id)
                        ->where('lesson_id', $selectedLesson->id)
                        ->first();
                    
                    $teacherGrade = $submission ? $submission->grade : null;
                    
                    // Final Display Grade
                    // If Teacher Grade exists, it overrides everything.
                    // If not, use 100 if completed.
                    // If neither, 0 (or '-').
                    if ($teacherGrade !== null) {
                        $displayGrade = $teacherGrade;
                    } else {
                        $displayGrade = $isCompleted ? 100 : '-';
                    }
                    
                    if ($displayGrade !== '-') {
                        $gradedCount++;
                        $totalScoreSum += $displayGrade;
                        if ($displayGrade == 100) $hundredPercentCount++;
                    }
                    if ($isCompleted) $completedCount++;

                    $data[] = [
                        'student' => $student,
                        'grade' => $displayGrade,
                        'is_completed' => $isCompleted,
                        'submission_id' => $submission ? $submission->id : null, // Needed for update
                        'user_id' => $student->id,
                        'lesson_id' => $selectedLesson->id
                    ];
                }
            }
            
            // Statistics Lesson
             $statistics = [
                 'total_students' => $students->count(),
                 'completed_count' => $completedCount, // Enrolled & Completed
                 'not_completed_count' => $students->count() - $completedCount,
                 'hundred_percent_count' => $hundredPercentCount,
                 'average_score' => $gradedCount > 0 ? round($totalScoreSum / $gradedCount, 1) : 0
             ];
             request()->merge(['statistics' => $statistics]);
            if (!$selectedLesson) {
                $mode = 'none';
            }
        }


        // Fetch Badges for Header Display (Identical Logic to ProfileController)
        $badges = \Illuminate\Support\Facades\DB::table('badge')->get();
        
        $userBadges = \Illuminate\Support\Facades\DB::table('user_badge')
            ->where('user_id', $user->id)
            ->whereIn('status', ['earned', 'redeemed'])
            ->get()
            ->keyBy('badge_code');
            
        // Map visibility
        $visibleBadgeCodes = [];
        if (!empty($user->visible_badge_codes)) {
            $decoded = json_decode($user->visible_badge_codes, true);
            if (is_array($decoded) && !empty($decoded)) {
                $visibleBadgeCodes = $decoded;
            }
        }
        // Fallback
        if (empty($visibleBadgeCodes)) {
            $visibleBadgeCodes = $userBadges->keys()->toArray();
        }

        $formattedBadges = $badges->map(function($badge) use ($userBadges, $visibleBadgeCodes) {
            $userBadge = $userBadges->get($badge->code);
            $badge->is_earned = $userBadge ? true : false;
            $badge->is_visible = $badge->is_earned && in_array($badge->code, $visibleBadgeCodes);
            return $badge;
        });

        // Pass $formattedBadges as $badges variable to view
        return view('performance.index', compact(
            'classrooms',
            'lessons',
            'activities',
            'selectedClass',
            'selectedLessonId', 
            'filterItems',
            'selectedFilter',
            'data',
            'mode',
            'formattedBadges' // Renamed to avoid confusion, but passed as 'badges' in compact if we used array, but here just passing variable
        ))->with('badges', $formattedBadges);
    }
    
    // NEW: Update Lesson Grade
    public function updateLessonGrade(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'lesson_id' => 'required',
            'grade' => 'required|numeric|min:0|max:100'
        ]);
        
        // Find or Create Submission
        \App\Models\Submission::updateOrCreate(
            ['user_id' => $request->user_id, 'lesson_id' => $request->lesson_id],
            ['grade' => $request->grade]
        );
        
        return back()->with('success', 'Gred berjaya dikemaskini.');
    }
    
    /**
     * Helper method to infer answer breakdown from score when detailed results are unavailable
     */
    private function inferAnswers($score, $questionCount)
    {
        $answers = [];
        
        if ($score === null) {
            // No submission
            for($i=0; $i<$questionCount; $i++) {
                $answers[] = '-';
            }
        } else {
            // Determine number of correct answers
            $correctCount = 0;
            if ($score > $questionCount && $score <= 100) {
                // Percentage Logic
                $correctCount = round(($score / 100) * $questionCount);
            } else {
                // Raw Score Logic
                $correctCount = min($score, $questionCount);
            }

            for($i=0; $i<$questionCount; $i++) {
                if ($i < $correctCount) {
                    $answers[] = '✓';
                } else {
                    $answers[] = '✗';
                }
            }
        }
        
        return $answers;
    }
}
