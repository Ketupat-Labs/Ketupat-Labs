import React, { useState } from 'react';

const QuizGame = ({ config = {}, onFinish }) => {
    const { questions = [] } = config;

    const [currentQuestion, setCurrentQuestion] = useState(0);
    const [selectedAnswers, setSelectedAnswers] = useState({});
    const [showResults, setShowResults] = useState(false);
    const [score, setScore] = useState(0);

    // Filter out incomplete questions - must have question text and at least one answer
    const validQuestions = questions.filter(q =>
        q.question && q.question.trim() &&
        q.answers && q.answers.filter(a => a && a.trim()).length > 0
    );

    const handleAnswerSelect = (answerIndex) => {
        setSelectedAnswers({
            ...selectedAnswers,
            [currentQuestion]: answerIndex
        });
    };

    const handleNext = () => {
        if (currentQuestion < validQuestions.length - 1) {
            setCurrentQuestion(currentQuestion + 1);
        }
    };

    const handlePrevious = () => {
        if (currentQuestion > 0) {
            setCurrentQuestion(currentQuestion - 1);
        }
    };

    const handleSubmit = () => {
        let correctCount = 0;
        const breakdown = validQuestions.map((q, index) => {
            const isCorrect = selectedAnswers[index] === q.correctAnswer;
            if (isCorrect) correctCount++;
            return {
                question: q.question,
                isCorrect: isCorrect,
                userAnswer: q.answers[selectedAnswers[index]],
                correctAnswer: q.answers[q.correctAnswer]
            };
        });

        setScore(correctCount);
        setShowResults(true);

        const percentage = Math.round((correctCount / validQuestions.length) * 100);

        if (onFinish) {
            onFinish({
                score: correctCount,
                total: validQuestions.length,
                percentage: percentage,
                breakdown: breakdown
            });
        }
    };

    const handleRestart = () => {
        setCurrentQuestion(0);
        setSelectedAnswers({});
        setShowResults(false);
        setScore(0);
    };

    // Empty state
    if (validQuestions.length === 0) {
        return (
            <div className="quiz-game-container p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200">
                <div className="text-center py-12">
                    <p className="text-xl text-gray-600">
                        ⚠️ No valid questions configured. Please add questions in the editor.
                    </p>
                </div>
            </div>
        );
    }

    const currentQ = validQuestions[currentQuestion];
    const isAnswered = selectedAnswers.hasOwnProperty(currentQuestion);
    const allAnswered = validQuestions.every((_, index) => selectedAnswers.hasOwnProperty(index));

    return (
        <div className="quiz-game-container p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border-2 border-blue-200">
            {/* Header */}
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-2xl font-bold text-blue-700">📝 Quiz Game</h3>
                <div className="bg-white px-4 py-2 rounded-lg shadow">
                    <span className="text-gray-600 text-sm">Question:</span>
                    <span className="ml-2 font-bold text-blue-600">
                        {currentQuestion + 1} / {validQuestions.length}
                    </span>
                </div>
            </div>

            {!showResults ? (
                <>
                    {/* Question */}
                    <div className="bg-white p-6 rounded-lg shadow-md mb-6">
                        <h4 className="text-xl font-semibold text-gray-800 mb-4">
                            {currentQ.question}
                        </h4>

                        {/* Answer Options */}
                        <div className="space-y-3">
                            {currentQ.answers.map((answer, index) => {
                                if (!answer.trim()) return null;

                                const isSelected = selectedAnswers[currentQuestion] === index;

                                return (
                                    <button
                                        key={index}
                                        onClick={() => handleAnswerSelect(index)}
                                        className={`
                      w-full text-left p-4 rounded-lg border-2 transition-all duration-200
                      ${isSelected
                                                ? 'border-blue-500 bg-blue-100 shadow-md'
                                                : 'border-gray-300 bg-white hover:border-blue-300 hover:bg-blue-50'
                                            }
                    `}
                                    >
                                        <div className="flex items-center">
                                            <div className={`
                        w-6 h-6 rounded-full border-2 mr-3 flex items-center justify-center
                        ${isSelected ? 'border-blue-500 bg-blue-500' : 'border-gray-400'}
                      `}>
                                                {isSelected && (
                                                    <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                                    </svg>
                                                )}
                                            </div>
                                            <span className="font-medium text-gray-700">
                                                {String.fromCharCode(65 + index)}. {answer}
                                            </span>
                                        </div>
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {/* Navigation */}
                    <div className="flex justify-between items-center">
                        <button
                            onClick={handlePrevious}
                            disabled={currentQuestion === 0}
                            className={`
                px-6 py-2 rounded-lg font-semibold transition
                ${currentQuestion === 0
                                    ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    : 'bg-gray-500 hover:bg-gray-600 text-white'
                                }
              `}
                        >
                            ← Previous
                        </button>

                        {currentQuestion === validQuestions.length - 1 ? (
                            <button
                                onClick={handleSubmit}
                                disabled={!allAnswered}
                                className={`
                  px-6 py-2 rounded-lg font-semibold transition
                  ${!allAnswered
                                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                        : 'bg-green-600 hover:bg-green-700 text-white'
                                    }
                `}
                            >
                                Submit Quiz
                            </button>
                        ) : (
                            <button
                                onClick={handleNext}
                                className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition"
                            >
                                Next →
                            </button>
                        )}
                    </div>

                    {/* Progress Indicator */}
                    <div className="mt-4">
                        <div className="flex gap-2 justify-center">
                            {validQuestions.map((_, index) => (
                                <div
                                    key={index}
                                    className={`
                    w-3 h-3 rounded-full transition-all
                    ${index === currentQuestion ? 'bg-blue-600 scale-125' : ''}
                    ${selectedAnswers.hasOwnProperty(index) ? 'bg-blue-400' : 'bg-gray-300'}
                  `}
                                />
                            ))}
                        </div>
                    </div>
                </>
            ) : (
                /* Results - Hidden, user will see details in Lihat Prestasi */
                <div className="text-center py-12">
                    <div className="bg-gradient-to-br from-green-50 to-blue-50 p-8 rounded-2xl shadow-lg mb-6 border-2 border-green-200">
                        <h3 className="text-3xl font-bold text-gray-800 mb-4">
                            🎉 Quiz Complete!
                        </h3>
                        <div className="text-6xl font-bold text-green-600 mb-2">
                            {score} / {validQuestions.length}
                        </div>
                        <p className="text-xl text-gray-700 mb-4">
                            {score === validQuestions.length
                                ? 'Perfect Score! 🌟'
                                : score >= validQuestions.length * 0.7
                                    ? 'Great Job! 👏'
                                    : score >= validQuestions.length * 0.5
                                        ? 'Good Effort! 💪'
                                        : 'Keep Practicing! 📚'
                            }
                        </p>
                        <p className="text-sm text-gray-600">
                            View detailed results in <strong>Lihat Prestasi</strong> dashboard
                        </p>
                    </div>

                    <button
                        onClick={handleRestart}
                        className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-full font-bold transition text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1"
                    >
                        🔄 Try Again
                    </button>
                </div>
            )}
        </div>
    );
};

export default QuizGame;
