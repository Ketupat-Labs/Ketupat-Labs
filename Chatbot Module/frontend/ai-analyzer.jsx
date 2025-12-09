import React from 'react';
import { createRoot } from 'react-dom/client';
import AiDocumentAnalyzer from './AiDocumentAnalyzer';
import AiTextHighlighter from './components/AiTextHighlighter';

const container = document.getElementById('ai-analyzer-root');
if (container) {
    const root = createRoot(container);
    root.render(
      <>
        <AiDocumentAnalyzer />
        <AiTextHighlighter />
      </>
    );
}
