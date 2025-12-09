import React, { useState, useEffect, useRef } from 'react';
import ReactMarkdown from 'react-markdown';

/**
 * AI Text Highlighter Component
 * User Story US025: Highlight text and get AI explanations
 *
 * This component provides global text selection and AI explanation functionality
 *
 * USAGE INSTRUCTIONS FOR FUTURE PAGES:
 * ====================================
 * To add this feature to any new page, simply import and render this component:
 *
 * Example (in your entry point file like new-page.jsx):
 * ```jsx
 * import React from 'react';
 * import { createRoot } from 'react-dom/client';
 * import YourPageComponent from './YourPageComponent';
 * import AiTextHighlighter from './components/AiTextHighlighter';
 *
 * document.addEventListener('DOMContentLoaded', () => {
 *   const rootElement = document.getElementById('your-root');
 *   if (rootElement) {
 *     const root = createRoot(rootElement);
 *     root.render(
 *       <>
 *         <YourPageComponent />
 *         <AiTextHighlighter />
 *       </>
 *     );
 *   }
 * });
 * ```
 *
 * That's it! The highlighter will automatically:
 * - Detect text selection on the entire page
 * - Show "Ask AI" button when text is highlighted
 * - Fetch AI explanations from the backend API
 * - Display formatted explanations in a beautiful popup
 * - Allow users to copy explanations to clipboard
 *
 * NO ADDITIONAL CONFIGURATION NEEDED!
 */
export default function AiTextHighlighter() {
  const [selectedText, setSelectedText] = useState('');
  const [contextMenu, setContextMenu] = useState({ show: false, x: 0, y: 0 });
  const [explanation, setExplanation] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const popupRef = useRef(null);

  // Handle text selection
  useEffect(() => {
    const handleSelection = () => {
      const selection = window.getSelection();
      const text = selection.toString().trim();

      if (text.length > 0 && text.length <= 500) {
        setSelectedText(text);

        // Get selection position for context menu
        const range = selection.getRangeAt(0);
        const rect = range.getBoundingClientRect();

        setContextMenu({
          show: true,
          x: rect.left + (rect.width / 2),
          y: rect.bottom + window.scrollY + 5,
        });
      } else {
        setContextMenu({ show: false, x: 0, y: 0 });
      }
    };

    document.addEventListener('mouseup', handleSelection);
    document.addEventListener('touchend', handleSelection);

    return () => {
      document.removeEventListener('mouseup', handleSelection);
      document.removeEventListener('touchend', handleSelection);
    };
  }, []);

  // Close context menu when clicking outside
  useEffect(() => {
    const handleClickOutside = (e) => {
      // Don't close if clicking on the context menu itself
      if (contextMenu.show && !e.target.closest('.ai-context-menu')) {
        console.log('Clicking outside, closing menu');
        setContextMenu({ show: false, x: 0, y: 0 });
      }
    };

    // Use a slight delay to prevent immediate closing
    const timeoutId = setTimeout(() => {
      document.addEventListener('mousedown', handleClickOutside);
    }, 100);

    return () => {
      clearTimeout(timeoutId);
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, [contextMenu.show]);

  // Fetch AI explanation
  const handleAskAI = async () => {
    console.log('Ask Ketupats clicked! Selected text:', selectedText);
    setIsLoading(true);
    setError('');
    setContextMenu({ show: false, x: 0, y: 0 });

    // Get surrounding context (100 chars before and after)
    const selection = window.getSelection();
    if (!selection.rangeCount) {
      console.error('No selection range found');
      setError('Please select text again and try.');
      setIsLoading(false);
      return;
    }

    const range = selection.getRangeAt(0);
    const container = range.startContainer.parentElement;
    const fullText = container.textContent || '';
    const startIndex = Math.max(0, fullText.indexOf(selectedText) - 100);
    const endIndex = Math.min(fullText.length, fullText.indexOf(selectedText) + selectedText.length + 100);
    const context = fullText.substring(startIndex, endIndex);

    console.log('Sending request to:', '/api/ai-explain/explain');
    console.log('Request data:', { text: selectedText, context: context.substring(0, 50) + '...' });

    try {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
      if (!csrfToken) {
        console.error('CSRF token not found');
        setError('Security token missing. Please refresh the page.');
        setIsLoading(false);
        return;
      }

      const response = await fetch('/api/ai-explain/explain', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          text: selectedText,
          context: context,
        }),
      });

      console.log('Response status:', response.status);
      const data = await response.json();
      console.log('Response data:', data);

      if (data.success) {
        setExplanation({
          text: selectedText,
          content: data.explanation,
          timestamp: data.generated_at,
        });
        console.log('Explanation set successfully');
      } else {
        console.error('API returned error:', data.error);
        setError(data.error || 'Failed to generate explanation');
      }
    } catch (err) {
      console.error('AI Explanation Error:', err);
      setError(`Network error: ${err.message}. Please check your connection and try again.`);
    } finally {
      setIsLoading(false);
    }
  };

  // Copy explanation to clipboard
  const handleCopy = () => {
    if (explanation) {
      const textToCopy = `Term: ${explanation.text}\n\n${explanation.content}`;
      navigator.clipboard.writeText(textToCopy);

      // Show success feedback
      const copyBtn = document.querySelector('.copy-btn');
      if (copyBtn) {
        copyBtn.textContent = '✓ Copied!';
        setTimeout(() => {
          copyBtn.textContent = 'Copy';
        }, 2000);
      }
    }
  };

  // Close explanation popup
  const handleClose = () => {
    setExplanation(null);
    setError('');
    window.getSelection().removeAllRanges();
  };

  return (
    <>
      {/* Context Menu */}
      {contextMenu.show && (
        <div
          className="ai-context-menu fixed z-[9999] bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl shadow-2xl px-4 py-2 cursor-pointer hover:from-blue-700 hover:to-purple-700 transition-all transform hover:scale-105 animate-fadeIn"
          style={{
            left: `${contextMenu.x}px`,
            top: `${contextMenu.y}px`,
            transform: 'translateX(-50%)',
          }}
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            console.log('Button clicked!');
            handleAskAI();
          }}
          onMouseDown={(e) => {
            e.preventDefault();
            console.log('Button mouse down!');
          }}
        >
          <div className="flex items-center space-x-2 font-semibold" style={{ pointerEvents: 'none' }}>
            {/* Ketupat Icon */}
            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2L8 6L4 10L8 14L12 18L16 14L20 10L16 6L12 2ZM12 5L14.5 7.5L17 10L14.5 12.5L12 15L9.5 12.5L7 10L9.5 7.5L12 5ZM12 8L10.5 9.5L12 11L13.5 9.5L12 8Z"/>
              <path d="M11 1V5M13 1V5M11 15V19M13 15V19M5 11H1M5 13H1M19 11H23M19 13H23" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round"/>
            </svg>
            <span>Ask Ketupats</span>
          </div>
        </div>
      )}

      {/* Loading Overlay */}
      {isLoading && (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-[9998] flex items-center justify-center animate-fadeIn">
          <div className="bg-white rounded-2xl p-8 shadow-2xl max-w-sm">
            <div className="flex flex-col items-center space-y-4">
              <div className="relative">
                <div className="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                <div className="absolute inset-0 flex items-center justify-center">
                  {/* Ketupat Icon in Loading */}
                  <svg className="w-8 h-8 text-blue-600 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L8 6L4 10L8 14L12 18L16 14L20 10L16 6L12 2ZM12 5L14.5 7.5L17 10L14.5 12.5L12 15L9.5 12.5L7 10L9.5 7.5L12 5ZM12 8L10.5 9.5L12 11L13.5 9.5L12 8Z"/>
                  </svg>
                </div>
              </div>
              <p className="text-gray-700 font-semibold">Ketupats AI is thinking...</p>
              <p className="text-sm text-gray-500">Generating explanation</p>
            </div>
          </div>
        </div>
      )}

      {/* Explanation Popup */}
      {explanation && (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-[9998] flex items-center justify-center p-4 animate-fadeIn" onClick={handleClose}>
          <div
            ref={popupRef}
            className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden animate-slideUp"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div className="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white p-6">
              <div className="flex items-start justify-between">
                <div className="flex-1">
                  <div className="flex items-center space-x-2 mb-2">
                    {/* Ketupat Icon in Header */}
                    <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2L8 6L4 10L8 14L12 18L16 14L20 10L16 6L12 2ZM12 5L14.5 7.5L17 10L14.5 12.5L12 15L9.5 12.5L7 10L9.5 7.5L12 5ZM12 8L10.5 9.5L12 11L13.5 9.5L12 8Z"/>
                    </svg>
                    <h3 className="text-xl font-bold">Ketupats AI Explanation</h3>
                  </div>
                  <div className="bg-white bg-opacity-20 rounded-lg px-4 py-2 inline-block">
                    <p className="font-semibold">"{explanation.text}"</p>
                  </div>
                </div>
                <button
                  onClick={handleClose}
                  className="ml-4 w-8 h-8 flex items-center justify-center rounded-full hover:bg-white hover:bg-opacity-20 transition-all"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

            {/* Content */}
            <div className="p-6 overflow-y-auto max-h-[calc(80vh-200px)]">
              <div className="prose prose-blue max-w-none">
                <ReactMarkdown
                  components={{
                    h1: ({node, ...props}) => <h1 className="text-2xl font-bold text-gray-900 mb-4" {...props} />,
                    h2: ({node, ...props}) => <h2 className="text-xl font-bold text-gray-900 mb-3" {...props} />,
                    h3: ({node, ...props}) => <h3 className="text-lg font-bold text-gray-900 mb-2" {...props} />,
                    p: ({node, ...props}) => <p className="text-gray-700 mb-4 leading-relaxed" {...props} />,
                    strong: ({node, ...props}) => <strong className="font-bold text-gray-900" {...props} />,
                    ul: ({node, ...props}) => <ul className="list-disc list-inside mb-4 space-y-2" {...props} />,
                    ol: ({node, ...props}) => <ol className="list-decimal list-inside mb-4 space-y-2" {...props} />,
                    li: ({node, ...props}) => <li className="text-gray-700" {...props} />,
                    code: ({node, inline, ...props}) => inline
                      ? <code className="bg-gray-100 text-blue-600 px-2 py-1 rounded text-sm font-mono" {...props} />
                      : <code className="block bg-gray-100 text-gray-800 p-4 rounded-lg text-sm font-mono overflow-x-auto" {...props} />,
                  }}
                >
                  {explanation.content}
                </ReactMarkdown>
              </div>
            </div>

            {/* Footer */}
            <div className="border-t border-gray-200 p-4 bg-gray-50 flex items-center justify-between">
              <div className="text-sm text-gray-500">
                <svg className="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Generated by AI • {new Date(explanation.timestamp).toLocaleTimeString()}
              </div>
              <div className="flex space-x-2">
                <button
                  onClick={handleCopy}
                  className="copy-btn px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-semibold flex items-center space-x-2"
                >
                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                  </svg>
                  <span>Copy</span>
                </button>
                <button
                  onClick={handleClose}
                  className="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-all font-semibold"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Error Message */}
      {error && (
        <div className="fixed bottom-4 right-4 z-[9999] bg-red-500 text-white rounded-xl shadow-2xl p-4 max-w-md animate-slideUp">
          <div className="flex items-start space-x-3">
            <svg className="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div className="flex-1">
              <p className="font-semibold mb-1">Error</p>
              <p className="text-sm">{error}</p>
            </div>
            <button onClick={() => setError('')} className="text-white hover:text-gray-200">
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      )}

      {/* Custom CSS for animations */}
      <style jsx>{`
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        @keyframes slideUp {
          from {
            opacity: 0;
            transform: translateY(20px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .animate-fadeIn {
          animation: fadeIn 0.2s ease-out;
        }

        .animate-slideUp {
          animation: slideUp 0.3s ease-out;
        }
      `}</style>
    </>
  );
}
