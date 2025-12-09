import React, { useState, useEffect } from 'react';

/**
 * AI Detector / Human Verification Component
 * A fun and secure CAPTCHA-like system to verify users are human
 * @param {boolean} autoShow - If true, automatically show verification on mount
 * @param {function} onVerified - Callback when verification succeeds
 * @param {function} onFailed - Callback when verification fails
 */
export default function HumanVerification({ autoShow = false, onVerified, onFailed }) {
  const [showChallenge, setShowChallenge] = useState(false);
  const [challengeType, setChallengeType] = useState('');
  const [question, setQuestion] = useState('');
  const [answer, setAnswer] = useState('');
  const [userAnswer, setUserAnswer] = useState('');
  const [attempts, setAttempts] = useState(0);
  const [verified, setVerified] = useState(false);
  const [error, setError] = useState('');

  // Check if user is already verified in this session
  useEffect(() => {
    const isVerified = sessionStorage.getItem('humanVerified');
    const verificationTime = sessionStorage.getItem('verificationTime');

    // Clear verification if it's a new login (check if verification is older than 1 minute)
    if (verificationTime) {
      const timeSinceVerification = Date.now() - new Date(verificationTime).getTime();
      const oneMinute = 60 * 1000;

      // If verification is older than 1 minute, clear it (assumes new login)
      if (timeSinceVerification > oneMinute) {
        sessionStorage.removeItem('humanVerified');
        sessionStorage.removeItem('verificationTime');
      }
    }

    const stillVerified = sessionStorage.getItem('humanVerified');

    if (stillVerified === 'true') {
      setVerified(true);
      if (onVerified) onVerified();
    } else if (autoShow) {
      // Auto-show challenge after a short delay if not verified
      setTimeout(() => {
        startChallenge();
      }, 1500);
    }
  }, [autoShow]);

  const challenges = {
    math: [
      { q: 'What is 5 + 3?', a: '8' },
      { q: 'What is 10 - 4?', a: '6' },
      { q: 'What is 2 × 6?', a: '12' },
      { q: 'What is 15 ÷ 3?', a: '5' },
      { q: 'What is 7 + 8?', a: '15' },
    ],
    logic: [
      { q: 'What comes after Monday?', a: 'tuesday' },
      { q: 'How many days in a week?', a: '7' },
      { q: 'What color is the sky on a clear day?', a: 'blue' },
      { q: 'What is the opposite of hot?', a: 'cold' },
      { q: 'How many wheels does a car have?', a: '4' },
    ],
    pattern: [
      { q: 'Complete: 2, 4, 6, 8, __', a: '10' },
      { q: 'Complete: A, B, C, D, __', a: 'e' },
      { q: 'Complete: red, orange, yellow, green, __', a: 'blue' },
      { q: 'What letter comes before Z?', a: 'y' },
      { q: 'Complete: 5, 10, 15, 20, __', a: '25' },
    ],
  };

  const startChallenge = () => {
    const types = ['math', 'logic', 'pattern'];
    const selectedType = types[Math.floor(Math.random() * types.length)];
    const challengeSet = challenges[selectedType];
    const challenge = challengeSet[Math.floor(Math.random() * challengeSet.length)];

    setChallengeType(selectedType);
    setQuestion(challenge.q);
    setAnswer(challenge.a);
    setUserAnswer('');
    setError('');
    setShowChallenge(true);
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    const isCorrect = userAnswer.toLowerCase().trim() === answer.toLowerCase().trim();

    if (isCorrect) {
      setVerified(true);
      sessionStorage.setItem('humanVerified', 'true');
      sessionStorage.setItem('verificationTime', new Date().toISOString());
      setShowChallenge(false);
      if (onVerified) onVerified();
    } else {
      setAttempts(attempts + 1);
      setError('❌ Incorrect answer. Please try again.');
      setUserAnswer('');

      if (attempts >= 2) {
        setShowChallenge(false);
        if (onFailed) onFailed();
        setTimeout(() => {
          setAttempts(0);
          alert('Too many failed attempts. Please refresh the page and try again.');
        }, 500);
      }
    }
  };

  const getChallengeIcon = () => {
    switch (challengeType) {
      case 'math': return '🔢';
      case 'logic': return '🧠';
      case 'pattern': return '🔤';
      default: return '🤔';
    }
  };

  if (verified) {
    return (
      <div className="inline-flex items-center space-x-2 text-green-600 bg-green-50 px-4 py-2 rounded-lg">
        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
        </svg>
        <span className="font-semibold">✓ Verified Human</span>
      </div>
    );
  }

  return (
    <>
      {/* Verification Button */}
      {!showChallenge && (
        <button
          onClick={startChallenge}
          className="group relative inline-flex items-center space-x-3 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-lg font-semibold shadow-lg transition-all duration-300 transform hover:scale-105"
        >
          <svg className="w-6 h-6 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clipRule="evenodd" />
          </svg>
          <span>🤖 Are You a Human? Click to Verify</span>
        </button>
      )}

      {/* Challenge Modal */}
      {showChallenge && (
        <div className="fixed inset-0 z-[10000] bg-black bg-opacity-70 flex items-center justify-center p-4 animate-fadeIn">
          <div className="bg-white rounded-2xl shadow-2xl max-w-md w-full animate-slideUp">
            {/* Header */}
            <div className="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white p-6 rounded-t-2xl">
              <div className="flex items-center justify-center space-x-3 mb-2">
                <span className="text-4xl">{getChallengeIcon()}</span>
                <h2 className="text-2xl font-bold">Human Verification</h2>
              </div>
              <p className="text-center text-sm text-white text-opacity-90">
                Please answer this question to prove you're human
              </p>
            </div>

            {/* Challenge Content */}
            <form onSubmit={handleSubmit} className="p-6 space-y-6">
              {/* Question */}
              <div className="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 border-2 border-blue-200">
                <div className="text-center">
                  <div className="text-6xl mb-4">🤔</div>
                  <p className="text-xl font-bold text-gray-800 mb-2">
                    {question}
                  </p>
                  <p className="text-sm text-gray-600">
                    Type your answer below
                  </p>
                </div>
              </div>

              {/* Input */}
              <div>
                <input
                  type="text"
                  value={userAnswer}
                  onChange={(e) => setUserAnswer(e.target.value)}
                  placeholder="Your answer..."
                  autoFocus
                  className="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all text-lg"
                  required
                />
              </div>

              {/* Error Message */}
              {error && (
                <div className="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                  <p className="text-red-700 font-medium">{error}</p>
                  <p className="text-sm text-red-600 mt-1">
                    Attempts remaining: {3 - attempts}
                  </p>
                </div>
              )}

              {/* Buttons */}
              <div className="flex space-x-3">
                <button
                  type="button"
                  onClick={() => {
                    setShowChallenge(false);
                    setAttempts(0);
                    setError('');
                  }}
                  className="flex-1 px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="flex-1 px-6 py-3 text-white bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 rounded-lg font-medium shadow-lg transition-all"
                >
                  ✓ Verify
                </button>
              </div>

              {/* Info */}
              <div className="text-center">
                <p className="text-xs text-gray-500">
                  🔒 This helps us prevent automated access
                </p>
              </div>
            </form>
          </div>
        </div>
      )}
    </>
  );
}
