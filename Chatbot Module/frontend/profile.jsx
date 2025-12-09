import React from 'react';
import { createRoot } from 'react-dom/client';
import ProfileComponent from './ProfileComponent.jsx';
import AiTextHighlighter from './components/AiTextHighlighter';

document.addEventListener('DOMContentLoaded', () => {
  const rootElement = document.getElementById('profile-root');
  if (rootElement) {
    const userData = JSON.parse(rootElement.dataset.user);
    const errors = JSON.parse(rootElement.dataset.errors || '{}');
    const root = createRoot(rootElement);
    root.render(
      <>
        <ProfileComponent user={userData} errors={errors} />
        <AiTextHighlighter />
      </>
    );
  }
});
