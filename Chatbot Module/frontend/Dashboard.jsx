import React from 'react';
import { createRoot } from 'react-dom/client';
import DashboardComponent from './DashboardComponent.jsx';
import AiTextHighlighter from './components/AiTextHighlighter';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  const rootElement = document.getElementById('dashboard-root');

  if (rootElement) {
    const userData = JSON.parse(rootElement.dataset.user);
    const root = createRoot(rootElement);
    root.render(
      <>
        <DashboardComponent user={userData} />
        <AiTextHighlighter />
      </>
    );
  }
});
