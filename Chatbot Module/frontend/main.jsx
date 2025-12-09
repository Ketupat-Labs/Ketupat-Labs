import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App.jsx'; // Import your component
import AiTextHighlighter from './components/AiTextHighlighter';

// Find the 'root' div and render your App component inside it
ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <>
      <App />
      <AiTextHighlighter />
    </>
  </React.StrictMode>
);
