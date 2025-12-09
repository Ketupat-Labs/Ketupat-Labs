import React from 'react';

/**
 * Interactive Loading Screen Component
 * Features: Animated ketupat logo, pulsing effects, progress indicators
 */
export default function LoadingScreen({ message = "Loading...", showProgress = false, progress = 0 }) {
  return (
    <div className="fixed inset-0 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 z-50 flex items-center justify-center">
      {/* Animated Background Patterns */}
      <div className="absolute inset-0 overflow-hidden">
        <div className="absolute top-20 left-20 w-32 h-32 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div className="absolute top-40 right-20 w-32 h-32 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div className="absolute bottom-20 left-40 w-32 h-32 bg-pink-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
      </div>

      {/* Main Loading Content */}
      <div className="relative z-10 flex flex-col items-center space-y-8">
        {/* Animated Ketupat Logo */}
        <div className="relative">
          {/* Outer Spinning Ring */}
          <div className="absolute inset-0 -m-8">
            <div className="w-40 h-40 border-4 border-transparent border-t-blue-500 border-r-purple-500 rounded-full animate-spin"></div>
          </div>

          {/* Middle Pulsing Ring */}
          <div className="absolute inset-0 -m-4">
            <div className="w-32 h-32 border-4 border-transparent border-b-pink-500 border-l-indigo-500 rounded-full animate-spin-reverse"></div>
          </div>

          {/* Ketupat Logo with Bounce */}
          <div className="relative w-24 h-24 bg-white rounded-2xl shadow-2xl flex items-center justify-center animate-bounce-slow">
            <img
              src="/images/ketupat-logo.svg"
              alt="Loading"
              className="w-20 h-20 object-contain animate-pulse"
            />
          </div>

          {/* Glowing Effect */}
          <div className="absolute inset-0 -m-2 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 rounded-full opacity-30 blur-2xl animate-pulse"></div>
        </div>

        {/* Loading Text */}
        <div className="text-center space-y-3">
          <h2 className="text-2xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent animate-pulse">
            {message}
          </h2>

          {/* Animated Dots */}
          <div className="flex items-center justify-center space-x-2">
            <div className="w-3 h-3 bg-blue-500 rounded-full animate-bounce"></div>
            <div className="w-3 h-3 bg-purple-500 rounded-full animate-bounce animation-delay-200"></div>
            <div className="w-3 h-3 bg-pink-500 rounded-full animate-bounce animation-delay-400"></div>
          </div>
        </div>

        {/* Progress Bar (Optional) */}
        {showProgress && (
          <div className="w-80 bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
            <div
              className="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-full transition-all duration-500 ease-out animate-shimmer"
              style={{ width: `${progress}%` }}
            ></div>
          </div>
        )}

        {/* Fun Loading Messages */}
        <p className="text-sm text-gray-600 animate-pulse">
          ✨ Preparing something amazing...
        </p>
      </div>
    </div>
  );
}

/**
 * Mini Loading Spinner for inline use
 */
export function LoadingSpinner({ size = "md", color = "blue" }) {
  const sizeClasses = {
    sm: "w-4 h-4",
    md: "w-8 h-8",
    lg: "w-12 h-12",
    xl: "w-16 h-16"
  };

  const colorClasses = {
    blue: "text-blue-600",
    purple: "text-purple-600",
    pink: "text-pink-600",
    green: "text-green-600",
    indigo: "text-indigo-600"
  };

  return (
    <div className="flex items-center justify-center">
      <svg
        className={`animate-spin ${sizeClasses[size]} ${colorClasses[color]}`}
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
      >
        <circle
          className="opacity-25"
          cx="12"
          cy="12"
          r="10"
          stroke="currentColor"
          strokeWidth="4"
        ></circle>
        <path
          className="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
    </div>
  );
}

/**
 * Skeleton Loader for content placeholders
 */
export function SkeletonLoader({ type = "card", count = 1 }) {
  const skeletons = Array(count).fill(0);

  if (type === "card") {
    return (
      <>
        {skeletons.map((_, i) => (
          <div key={i} className="bg-white rounded-xl p-6 shadow-lg animate-pulse">
            <div className="flex items-center space-x-4 mb-4">
              <div className="w-12 h-12 bg-gradient-to-br from-gray-200 to-gray-300 rounded-xl"></div>
              <div className="flex-1 space-y-2">
                <div className="h-4 bg-gradient-to-r from-gray-200 to-gray-300 rounded w-3/4"></div>
                <div className="h-3 bg-gradient-to-r from-gray-200 to-gray-300 rounded w-1/2"></div>
              </div>
            </div>
            <div className="space-y-2">
              <div className="h-3 bg-gradient-to-r from-gray-200 to-gray-300 rounded"></div>
              <div className="h-3 bg-gradient-to-r from-gray-200 to-gray-300 rounded w-5/6"></div>
            </div>
          </div>
        ))}
      </>
    );
  }

  if (type === "list") {
    return (
      <>
        {skeletons.map((_, i) => (
          <div key={i} className="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg animate-pulse">
            <div className="w-10 h-10 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg"></div>
            <div className="flex-1 space-y-2">
              <div className="h-4 bg-gradient-to-r from-gray-200 to-gray-300 rounded w-3/4"></div>
              <div className="h-3 bg-gradient-to-r from-gray-200 to-gray-300 rounded w-1/2"></div>
            </div>
          </div>
        ))}
      </>
    );
  }

  return null;
}
