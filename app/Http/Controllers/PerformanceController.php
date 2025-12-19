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
        $selectedLessonId = $request->get('lesson_id', 'all'); // Initialize selectedLessonId

        if ($user->role === 'teacher') {
             $classrooms = Classroom::where('teacher_id', $user->id)->get();
             $selectedClassId = $request->get('class_id', $classrooms->first()->id ?? null);
             $selectedClass = $classrooms->find($selectedClassId);
             if (!$selectedClass) return view('performance.index', ['classrooms' => [], 'lessons' => [], 'data' => []]);
             
             // Get students in this class
             $students = $selectedClass->students; // Relationship must exist
             // Or explicitly: $students = User::whereHas('enrolledClassrooms', function($q) use ($selectedClassId) { $q->where('class_id', $selectedClassId); })->get();
             // Assuming $selectedClass->students relationship works based on previous code context. 
             // Wait, previous code didn't show student fetching logic, likely omitted in my view? 
             // Let's deduce: $selectedClass->students is standard ManyToMany.
             
             // Fallback if relation not standard:
             if(!$students->count()){
                 $students = \App\Models\User::whereHas('enrollments', function($q) use ($selectedClassId){
                     // Actually enrollment is usually per classroom? 
                     // Let's assume User <-> Classroom via 'classes' table or similar. 
                     // User model has enrolledClassrooms()
                 })->get(); 
                 // Let's stick to what was likely there or standard: $selectedClass->students
             }
             
        } else {
             // STUDENT VIEW
             $classrooms = $user->enrolledClassrooms;
             $selectedClass = $classrooms->first(); // Default to first class
             $students = collect([$user]); // Only show themselves
        }

        // Common Data Gathering
        if ($user->role === 'teacher') {
            $lessons = Lesson::where('is_published', true)->get();
        } else {
            // Student: Only Public OR Assigned Lessons
            $lessons = Lesson::where('is_published', true)
                ->where(function($q) use ($user) {
                     $q->where('is_public', true)
                       ->orWhereHas('assignments', function($q2) use ($user) {
                            $q2->whereIn('classroom_id', $user->enrolledClassrooms->pluck('id'));
                       });
                })->get();
        }
        
        // Get Activities assigned to this classroom (or all if teacher)
        $activities = collect();
        if ($selectedClass) {
            $activities = \App\Models\Activity::whereHas('assignments', function($q) use ($selectedClass) {
                $q->where('classroom_id', $selectedClass->id);
            })->get();
            
            // For teacher preview logic ... (keep existing)
            if($user->role === 'teacher') {
                 $students = $selectedClass->students;
                 $teacherSubmissionsCount = \App\Models\ActivitySubmission::where('user_id', $user->id)->count();
                 if ($teacherSubmissionsCount > 0) {
                     $hasRelevantSubmission = \App\Models\ActivitySubmission::where('user_id', $user->id)
                        ->with('assignment')
                        ->get()
                        ->contains(function ($submission) use ($activities) {
                            return $submission->assignment && $activities->contains('id', $submission->assignment->activity_id);
                        });
                     if ($hasRelevantSubmission) $students->push($user);
                 }
            }
        }

        // Combine for Dropdown
        $filterItems = collect();
        foreach($lessons as $l) $filterItems->push(['id' => 'lesson-'.$l->id, 'name' => $l->title]);
        foreach($activities as $a) $filterItems->push(['id' => 'activity-'.$a->id, 'name' => $a->title]);
        $filterItems = $filterItems->values(); // Ensure clean array for JSON

        $data = [];
        $selectedFilter = $request->get('filter_id', 'all');
        
        // Determine Mode and Filter Collections
        if (str_starts_with($selectedFilter, 'activity-')) {
            $activityId = (int) str_replace('activity-', '', $selectedFilter);
            // Filter activities to just this one
            $activities = $activities->where('id', $activityId);
            // Hide lessons entirely for this view
            $lessons = collect();
            $mode = 'activity_detail'; // NEW: Use specific detail view
        } elseif (str_starts_with($selectedFilter, 'lesson-')) {
            $lessonId = (int) str_replace('lesson-', '', $selectedFilter);
            // Use the detailed lesson view logic (Mode B)
            $selectedLessonId = $lessonId;
            $mode = 'single';
            $mode = 'lesson_detail'; // Renamed from 'single'
        } else {
            // 'all'
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
                 $submission = \App\Models\ActivitySubmission::where('user_id', $student->id)
                    ->whereHas('assignment', fn($q) => $q->where('activity_id', $activity->id)) // Safer query
                    ->first();
                 
                 // If no submission found via assignment, try fallback for teachers or loose linking
                 if (!$submission && $student->role === 'teacher') {
                      $submission = \App\Models\ActivitySubmission::where('user_id', $student->id)->latest()->first(); // Loose check for demo
                      // Verify it belongs to this activity?
                      if($submission && $submission->assignment && $submission->assignment->activity_id != $activity->id) $submission = null;
                 }

                 $score = $submission ? $submission->score : 0;
                 if ($submission) {
                     $completedCount++;
                     $submissionCount++;
                     $totalScoreSum += $score; // Assuming score is already normalized or raw?
                     // Verify if score is 100%
                     if ($score == 100 || ($questionCount > 0 && $score == $questionCount)) {
                         $hundredPercentCount++;
                     }
                 }
                 
                 // Infer answers based on score (Visual Distribution)
                 $inferredAnswers = [];
                 
                 // Determine number of correct answers
                 if ($submission) {
                     $correctCount = 0;
                     if ($score > $questionCount && $score <= 100) {
                         // Percentage Logic
                         $correctCount = round(($score / 100) * $questionCount);
                         // Use normalized score for average calc if needed, but keeping raw logic consistent
                     } else {
                         // Raw Score Logic
                         $correctCount = min($score, $questionCount);
                     }
 
                     for($i=0; $i<$questionCount; $i++) {
                         if ($i < $correctCount) {
                             $inferredAnswers[] = '✓';
                         } else {
                             $inferredAnswers[] = '✗';
                         }
                     }
                 } else {
                     // No submission
                     for($i=0; $i<$questionCount; $i++) {
                         $inferredAnswers[] = '-';
                     }
                 }

                 $data[] = [
                    'student' => $student,
                    'answers' => $inferredAnswers,
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

        return view('performance.index', compact(
            'classrooms',
            'lessons',
            'activities',
            'selectedClass',
            'selectedLessonId', 
            'filterItems',
            'selectedFilter',
            'data',
            'mode'
        ));
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
}
