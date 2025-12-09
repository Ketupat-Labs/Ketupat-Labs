import React, { useState, useEffect } from 'react';
import LoadingScreen from './components/LoadingScreen.jsx';

export default function DashboardComponent({ user }) {
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    setTimeout(() => setIsLoading(false), 1500);
  }, []);

  if (isLoading) return <LoadingScreen message='Loading Dashboard...' />;

  return (
    <div className='py-12'>
      <div className='max-w-7xl mx-auto px-4 sm:px-6 lg:px-8'>
        <div className='bg-gradient-to-br from-white via-blue-50 to-purple-50 rounded-2xl shadow-xl p-8 mb-8 relative overflow-hidden'>
          <div className='absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-blue-200 to-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob'></div>
          <div className='relative z-10 flex items-center space-x-4'>
            <div className='w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg animate-bounce-slow'>
              <span className='text-3xl'>👋</span>
            </div>
            <div>
              <h1 className='text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent'>
                Welcome back, {user.name}!
              </h1>
              <p className='text-gray-600 mt-1'>Manage your documents and create AI-powered learning materials</p>
            </div>
          </div>
        </div>

        <div className='grid grid-cols-1 md:grid-cols-3 gap-6'>
          <a href='/documents' className='group bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all hover:scale-105'>
            <div className='bg-gradient-to-br from-blue-500 to-blue-600 p-4 rounded-2xl w-16 h-16 flex items-center justify-center mb-4 group-hover:rotate-6 transition-transform'>
              <svg className='w-8 h-8 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                <path strokeLinecap='round' strokeLinejoin='round' strokeWidth='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>
              </svg>
            </div>
            <h3 className='text-2xl font-bold mb-2'>Documents</h3>
            <p className='text-gray-600'>Upload and manage learning materials</p>
          </a>

          <a href='/ai-analyzer' className='group bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all hover:scale-105'>
            <div className='bg-gradient-to-br from-purple-500 to-pink-600 p-4 rounded-2xl w-16 h-16 flex items-center justify-center mb-4 group-hover:rotate-6 transition-transform'>
              <svg className='w-8 h-8 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                <path strokeLinecap='round' strokeLinejoin='round' strokeWidth='2' d='M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'></path>
              </svg>
            </div>
            <h3 className='text-2xl font-bold mb-2'>AI Analyzer</h3>
            <p className='text-gray-600'>Generate notes and quizzes with AI</p>
          </a>

          <a href='/profile' className='group bg-white rounded-2xl shadow-lg p-8 hover:shadow-2xl transition-all hover:scale-105'>
            <div className='bg-gradient-to-br from-green-500 to-emerald-600 p-4 rounded-2xl w-16 h-16 flex items-center justify-center mb-4 group-hover:rotate-6 transition-transform'>
              <svg className='w-8 h-8 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                <path strokeLinecap='round' strokeLinejoin='round' strokeWidth='2' d='M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'></path>
              </svg>
            </div>
            <h3 className='text-2xl font-bold mb-2'>Profile</h3>
            <p className='text-gray-600'>Update account settings</p>
          </a>
        </div>
      </div>
    </div>
  );
}
