import React, { useState, useEffect } from 'react';
import LoadingScreen from './components/LoadingScreen.jsx';

export default function ProfileComponent({ user: initialUser, errors: initialErrors = {} }) {
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [user, setUser] = useState(initialUser || { name: '', email: '' });
  const [successMessage, setSuccessMessage] = useState('');

  useEffect(() => { setTimeout(() => setIsLoading(false), 1000); }, []);

  if (isLoading) return <LoadingScreen message='Loading Profile...' />;

  return (
    <div className='py-12'>
      <div className='max-w-7xl mx-auto px-4 space-y-8'>
        <div className='bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl p-8 text-white'>
          <h1 className='text-4xl font-bold'>Profile Settings</h1>
        </div>
        {successMessage && <div className='bg-green-50 border-2 border-green-300 rounded-xl p-4'><p className='text-green-800 font-semibold'>{successMessage}</p></div>}
        <div className='bg-white rounded-2xl shadow-xl p-8'>
          <h2 className='text-2xl font-bold mb-6'>Profile Information</h2>
          <form className='space-y-6'>
            <div>
              <label className='block text-sm font-semibold mb-2'>Name</label>
              <input type='text' value={user.name} onChange={(e) => setUser({ ...user, name: e.target.value })} className='w-full px-4 py-3 border-2 rounded-xl' required />
            </div>
            <div>
              <label className='block text-sm font-semibold mb-2'>Email</label>
              <input type='email' value={user.email} onChange={(e) => setUser({ ...user, email: e.target.value })} className='w-full px-4 py-3 border-2 rounded-xl' required />
            </div>
            <button type='submit' disabled={isSaving} className='px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:scale-105 transition-all'>Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  );
}
