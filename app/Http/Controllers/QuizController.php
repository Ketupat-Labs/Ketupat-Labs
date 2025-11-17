<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\QuizAttempt;

class QuizController extends Controller
{
    // Define quiz data (HCI Focus)
    private function getQuizData()
    {
        return [
            [
                'question' => 'Which phase of Interaction Design focuses on identifying the user\'s problems?',
                'options' => ['Prototype', 'Implementation', 'Empathise', 'Test'],
                'answer' => 'Empathise'
            ],
            [
                'question' => 'Which design principle suggests placing related items close together on a screen?',
                'options' => ['Consistency', 'Proximity', 'Feedback', 'Affordance'],
                'answer' => 'Proximity'
            ]
        ];
    }

    public function show(Request $request, $lesson = null): View
    {
        $quizData = $this->getQuizData();
        
        // Get user's current points
        $user = Auth::user();
        $points = $user->points ?? 0;
        
        // Check if user has already submitted
        $hasSubmitted = false;
        $lastAttempt = QuizAttempt::where('user_id', Auth::id())
            ->where('lesson_id', $lesson)
            ->latest()
            ->first();
            
        if ($lastAttempt && $lastAttempt->submitted) {
            $hasSubmitted = true;
        }
        
        return view('quiz.show', compact('quizData', 'points', 'hasSubmitted', 'lastAttempt', 'lesson'));
    }

    public function submit(Request $request): RedirectResponse
    {
        $quizData = $this->getQuizData();
        $totalQuestions = count($quizData);
        $correctAnswers = 0;
        
        // Calculate score
        foreach ($quizData as $index => $q) {
            $userAnswer = $request->input('q' . $index);
            if ($userAnswer === $q['answer']) {
                $correctAnswers++;
            }
        }
        
        $score = $correctAnswers;
        $percentage = ($score / $totalQuestions) * 100;
        
        // Point awarding logic (Gamification)
        $pointsPerCorrect = 50;
        $pointsAwarded = $score * $pointsPerCorrect;
        
        // Update user points
        $user = Auth::user();
        $user->points = ($user->points ?? 0) + $pointsAwarded;
        $user->save();
        
        // Save quiz attempt
        QuizAttempt::create([
            'user_id' => Auth::id(),
            'lesson_id' => $request->input('lesson_id'),
            'score' => $score,
            'total_questions' => $totalQuestions,
            'points_earned' => $pointsAwarded,
            'answers' => json_encode($request->except(['_token', 'lesson_id', 'submit_quiz'])),
            'submitted' => true,
        ]);
        
        return redirect()->route('quiz.show', ['lesson' => $request->input('lesson_id')])
            ->with('quiz_result', [
                'score' => $score,
                'total' => $totalQuestions,
                'percentage' => $percentage,
                'points_awarded' => $pointsAwarded,
                'total_points' => $user->points
            ]);
    }
}

