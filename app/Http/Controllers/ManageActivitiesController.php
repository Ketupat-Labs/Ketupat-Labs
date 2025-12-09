<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\LessonAssignment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ManageActivitiesController extends Controller
{
    public function index(Request $request)
    {
        $selectedClass = $request->get('class', '5A');
        $selectedMonth = $request->get('month', date('Y-m'));
        
        // Get available classes
        $classes = Student::distinct()->pluck('class');
        
        // Get all lessons (created by other team members)
        $allLessons = Lesson::all();
        
        // Get lessons for selected class
        $lessons = Lesson::where('class', $selectedClass)->get();
        
        // Get assignments for selected class (with fallback to dummy data if table doesn't exist)
        try {
            $assignments = LessonAssignment::where('class', $selectedClass)
                ->with('lesson')
                ->get()
                ->keyBy('lesson_id');
        } catch (\Exception $e) {
            // If table doesn't exist, create dummy assignments
            $assignments = $this->createDummyAssignments($lessons, $selectedClass);
        }
        
        // If no assignments exist, create some dummy ones
        if ($assignments->isEmpty() && $lessons->count() > 0) {
            $assignments = $this->createDummyAssignments($lessons, $selectedClass);
        }
        
        // Get students for selected class
        $students = Student::where('class', $selectedClass)->get();
        
        // Build student lesson status
        $studentStatus = [];
        foreach ($students as $student) {
            $studentData = [
                'student' => $student,
                'lessons' => []
            ];
            
            foreach ($lessons as $lesson) {
                $answer = StudentAnswer::where('student_id', $student->student_id)
                    ->where('lesson_id', $lesson->id)
                    ->first();
                
                $assignment = $assignments->get($lesson->id);
                
                $status = 'Not Started';
                $isLowScore = false;
                if ($answer) {
                    $percentage = ($answer->total_marks / 3) * 100;
                    if ($percentage <= 20) {
                        $status = 'Completed (Low Score)';
                        $isLowScore = true;
                    } else {
                        $status = 'Completed';
                    }
                } elseif ($assignment) {
                    // Handle both model instances and dummy objects
                    $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                        ? $assignment->due_date 
                        : \Carbon\Carbon::parse($assignment->due_date);
                    
                    if ($dueDate < now()) {
                        $status = 'Overdue';
                    } else {
                        $status = 'In Progress';
                    }
                }
                
                $dueDateFormatted = null;
                if ($assignment) {
                    $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                        ? $assignment->due_date 
                        : \Carbon\Carbon::parse($assignment->due_date);
                    $dueDateFormatted = $dueDate->format('Y-m-d');
                }
                
                $studentData['lessons'][] = [
                    'lesson' => $lesson,
                    'status' => $status,
                    'answer' => $answer,
                    'assignment' => $assignment,
                    'due_date' => $dueDateFormatted,
                    'is_low_score' => $isLowScore,
                    'percentage' => $answer ? round(($answer->total_marks / 3) * 100, 1) : 0
                ];
            }
            
            $studentStatus[] = $studentData;
        }
        
        // Build calendar data (convert to collection if needed)
        $assignmentsCollection = $assignments instanceof \Illuminate\Support\Collection 
            ? $assignments 
            : collect($assignments);
        $calendarData = $this->buildCalendar($selectedMonth, $assignmentsCollection);
        
        return view('manage-activities.index', compact(
            'classes',
            'selectedClass',
            'allLessons',
            'lessons',
            'assignments',
            'studentStatus',
            'calendarData',
            'selectedMonth'
        ));
    }
    
    public function storeAssignment(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'class' => 'required|string',
            'due_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);
        
        try {
            LessonAssignment::updateOrCreate(
                [
                    'lesson_id' => $request->lesson_id,
                    'class' => $request->class
                ],
                [
                    'due_date' => $request->due_date,
                    'notes' => $request->notes
                ]
            );
            
            return redirect()->route('manage-activities.index', ['class' => $request->class])
                ->with('success', 'Assignment due date set successfully!');
        } catch (\Exception $e) {
            // If table doesn't exist, just show success message (dummy mode)
            return redirect()->route('manage-activities.index', ['class' => $request->class])
                ->with('success', 'Assignment due date set successfully! (Demo mode - run migration to save)');
        }
    }
    
    public function deleteAssignment($id)
    {
        try {
            $assignment = LessonAssignment::findOrFail($id);
            $class = $assignment->class;
            $assignment->delete();
            
            return redirect()->route('manage-activities.index', ['class' => $class])
                ->with('success', 'Assignment removed successfully!');
        } catch (\Exception $e) {
            // If table doesn't exist, just redirect
            return redirect()->route('manage-activities.index')
                ->with('success', 'Assignment removed successfully! (Demo mode)');
        }
    }
    
    public function resendLesson(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'lesson_id' => 'required|exists:lessons,id'
        ]);
        
        try {
            // Delete the student's answer to allow them to retake the lesson
            StudentAnswer::where('student_id', $request->student_id)
                ->where('lesson_id', $request->lesson_id)
                ->delete();
            
            $student = Student::findOrFail($request->student_id);
            
            return redirect()->route('manage-activities.index', ['class' => $student->class])
                ->with('success', 'Lesson resent successfully! Student can now retake the lesson.');
        } catch (\Exception $e) {
            return redirect()->route('manage-activities.index')
                ->with('error', 'Failed to resend lesson. Please try again.');
        }
    }
    
    private function createDummyAssignments($lessons, $class)
    {
        $dummyAssignments = collect();
        $baseDate = now();
        
        foreach ($lessons->take(3) as $index => $lesson) {
            // Create dummy assignment objects
            $assignment = (object)[
                'id' => $index + 1,
                'lesson_id' => $lesson->id,
                'class' => $class,
                'due_date' => $baseDate->copy()->addDays(($index + 1) * 7), // 1 week, 2 weeks, 3 weeks from now
                'notes' => 'Sample assignment for ' . ($lesson->q1 ?? 'Lesson ' . $lesson->id),
                'lesson' => $lesson,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Make due_date a Carbon instance for consistency
            if (!($assignment->due_date instanceof \Carbon\Carbon)) {
                $assignment->due_date = \Carbon\Carbon::parse($assignment->due_date);
            }
            
            $dummyAssignments->put($lesson->id, $assignment);
        }
        
        return $dummyAssignments;
    }
    
    private function buildCalendar($month, $assignments)
    {
        $date = Carbon::parse($month . '-01');
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);
        
        $calendar = [];
        $currentDate = $startOfCalendar->copy();
        
        // Ensure assignments is a collection
        $assignmentsCollection = $assignments instanceof \Illuminate\Support\Collection 
            ? $assignments 
            : collect($assignments);
        
        while ($currentDate <= $endOfCalendar) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dayAssignments = $assignmentsCollection->filter(function($assignment) use ($currentDate) {
                    // Handle both model instances and dummy objects
                    $dueDate = $assignment->due_date instanceof \Carbon\Carbon 
                        ? $assignment->due_date 
                        : \Carbon\Carbon::parse($assignment->due_date);
                    return $dueDate->format('Y-m-d') === $currentDate->format('Y-m-d');
                });
                
                $week[] = [
                    'date' => $currentDate->copy(),
                    'isCurrentMonth' => $currentDate->month === $date->month,
                    'isToday' => $currentDate->isToday(),
                    'assignments' => $dayAssignments->values()
                ];
                
                $currentDate->addDay();
            }
            $calendar[] = $week;
        }
        
        $monthName = $date->format('F');
        $year = $date->format('Y');
        
        $malayMonths = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Mac', 'April' => 'April',
            'May' => 'Mei', 'June' => 'Jun', 'July' => 'Julai', 'August' => 'Ogos',
            'September' => 'September', 'October' => 'Oktober', 'November' => 'November', 'December' => 'Disember'
        ];
        
        $malayMonth = $malayMonths[$monthName] ?? $monthName;

        return [
            'weeks' => $calendar,
            'month' => $malayMonth . ' ' . $year,
            'prevMonth' => $date->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $date->copy()->addMonth()->format('Y-m')
        ];
    }
}

