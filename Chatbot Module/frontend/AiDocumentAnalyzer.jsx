import React, { useState, useRef, useEffect } from 'react';
import LoadingScreen, { LoadingSpinner } from './components/LoadingScreen.jsx';

/**
 * Ketupats Document Analyzer Component
 * User Story US024: AI support to generate notes and quiz
 */
export default function AiDocumentAnalyzer({ classId = null, onContentGenerated = null }) {
  const [isInitialLoading, setIsInitialLoading] = useState(true);
  const [files, setFiles] = useState([]);
  const [outputType, setOutputType] = useState('summary_notes');
  const [questionType, setQuestionType] = useState('mcq'); // mcq, structured, mixed
  const [questionCount, setQuestionCount] = useState(10);
  const [separateAnswers, setSeparateAnswers] = useState(false);
  const [title, setTitle] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);
  const [processingStatus, setProcessingStatus] = useState('');
  const [generatedContent, setGeneratedContent] = useState(null);
  const [isEditing, setIsEditing] = useState(false);
  const [editedContent, setEditedContent] = useState(null);
  const [message, setMessage] = useState({ type: '', text: '' });
  const [showExportMenu, setShowExportMenu] = useState(false);
  const fileInputRef = useRef(null);

  // Initial page load animation
  useEffect(() => {
    const timer = setTimeout(() => {
      setIsInitialLoading(false);
    }, 1500);

    return () => clearTimeout(timer);
  }, []);

  // AC: "Ability to select one or more files"
  const handleFileSelect = (e) => {
    const selectedFiles = Array.from(e.target.files);

    // AC: "file size up to 10MB"
    const validFiles = selectedFiles.filter(file => {
      const maxSize = 10 * 1024 * 1024; // 10MB
      if (file.size > maxSize) {
        setMessage({ type: 'error', text: `${file.name} exceeds 10MB limit` });
        return false;
      }

      // AC: "PDF, DOCX, PPT"
      const validTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
      if (!validTypes.includes(file.type)) {
        setMessage({ type: 'error', text: `${file.name} must be PDF, DOCX, or PPTX` });
        return false;
      }

      return true;
    });

    setFiles(validFiles);
    setMessage({ type: '', text: '' });
  };

  // AC: "Ability to select the 'Analyze Document' button"
  const handleAnalyze = async () => {
    if (files.length === 0) {
      setMessage({ type: 'error', text: 'Please select at least one document' });
      return;
    }

    if (!title.trim()) {
      setMessage({ type: 'error', text: 'Please provide a title' });
      return;
    }

    setIsProcessing(true);
    // AC: "show processing status"
    setProcessingStatus('Analyzing document...');
    setMessage({ type: '', text: '' });

    const formData = new FormData();
    files.forEach(file => formData.append('documents[]', file));
    formData.append('output_type', outputType);
    formData.append('title', title);
    if (classId) formData.append('class_id', classId);

    // Only send quiz-related fields when output type is quiz
    if (outputType === 'quiz') {
      formData.append('question_type', questionType);
      formData.append('question_count', questionCount);
      formData.append('separate_answers', separateAnswers ? '1' : '0');
    }

    try {
      const response = await fetch('/api/ai-content/analyze', {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      console.log('Response status:', response.status);
      console.log('Response headers:', Object.fromEntries(response.headers.entries()));

      // Check if response is JSON before parsing
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const textResponse = await response.text();
        console.error('Non-JSON response received:', textResponse.substring(0, 500));
        throw new Error(`Server returned HTML instead of JSON. Status: ${response.status}. This usually means there's a server error or authentication issue.`);
      }

      const data = await response.json();
      console.log('Response data:', data);

      if (data.success) {
        // AC: "A success message is displayed immediately"
        setMessage({ type: 'success', text: data.message });
        setGeneratedContent(data.data);
        setEditedContent(data.data.content);
        setIsEditing(false);

        if (onContentGenerated) {
          onContentGenerated(data.data);
        }
      } else {
        // AC: "clear error message after generation"
        const errorMessage = data.message || 'Failed to analyze document';
        console.error('Error from server:', data);
        setMessage({ type: 'error', text: errorMessage });
      }
    } catch (error) {
      console.error('Network error:', error);
      setMessage({ type: 'error', text: `Failed to analyze document: ${error.message}. Check console for details.` });
    } finally {
      setIsProcessing(false);
      setProcessingStatus('');
    }
  };

  // AC: "Ability to edit the AI-generated content before saving"
  const handleEdit = () => {
    setIsEditing(true);
  };

  const handleSaveEdit = async () => {
    if (!generatedContent) return;

    try {
      const response = await fetch(`/api/ai-content/${generatedContent.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          content: editedContent,
          title: title,
        }),
      });

      const data = await response.json();

      if (data.success) {
        setMessage({ type: 'success', text: 'Changes saved successfully!' });
        setGeneratedContent(data.data);
        setIsEditing(false);
      } else {
        setMessage({ type: 'error', text: data.message });
      }
    } catch (error) {
      setMessage({ type: 'error', text: 'Failed to save changes' });
    }
  };

  const handleCancelEdit = () => {
    setEditedContent(generatedContent.content);
    setIsEditing(false);
  };

  // AC: "Ability to save and share it with students"
  const handleShare = async (targetClassId) => {
    if (!generatedContent) return;

    try {
      const response = await fetch(`/api/ai-content/${generatedContent.id}/share`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({ class_id: targetClassId }),
      });

      const data = await response.json();

      if (data.success) {
        setMessage({ type: 'success', text: 'Content shared with students!' });
        setGeneratedContent(data.data);
      } else {
        setMessage({ type: 'error', text: data.message });
      }
    } catch (error) {
      setMessage({ type: 'error', text: 'Failed to share content' });
    }
  };

  // AC: "Ability to remove generated content"
  const handleDelete = async () => {
    if (!generatedContent || !confirm('Are you sure you want to delete this content?')) return;

    try {
      const response = await fetch(`/api/ai-content/${generatedContent.id}`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
      });

      const data = await response.json();

      if (data.success) {
        setMessage({ type: 'success', text: 'Content deleted successfully!' });
        setGeneratedContent(null);
        setEditedContent(null);
        setFiles([]);
        setTitle('');
      } else {
        setMessage({ type: 'error', text: data.message });
      }
    } catch (error) {
      setMessage({ type: 'error', text: 'Failed to delete content' });
    }
  };

  const handleReset = () => {
    setFiles([]);
    setTitle('');
    setOutputType('summary_notes');
    setQuestionType('mcq');
    setGeneratedContent(null);
    setEditedContent(null);
    setIsEditing(false);
    setMessage({ type: '', text: '' });
    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  // Export functionality
  const handleExport = async (format) => {
    if (!generatedContent) return;

    setShowExportMenu(false);
    setProcessingStatus(`Generating ${format.toUpperCase()} file...`);
    setIsProcessing(true);

    try {
      const response = await fetch(`/api/ai-content/${generatedContent.id}/export`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        body: JSON.stringify({
          format,
          separate_answers: separateAnswers
        }),
      });

      if (response.ok) {
        const contentType = response.headers.get('content-type');

        // Check if response is a zip file (for separate answers)
        if (contentType && contentType.includes('application/zip')) {
          const blob = await response.blob();
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `${generatedContent.title}.zip`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
          setMessage({ type: 'success', text: `Files downloaded as ZIP archive!` });
        } else {
          // Single file download
          const blob = await response.blob();
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `${generatedContent.title}.${format}`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
          setMessage({ type: 'success', text: `File downloaded as ${format.toUpperCase()}!` });
        }
      } else {
        setMessage({ type: 'error', text: 'Export failed. Please try again.' });
      }
    } catch (error) {
      setMessage({ type: 'error', text: 'Export failed. Please try again.' });
    } finally {
      setIsProcessing(false);
      setProcessingStatus('');
    }
  };

  // Render MCQ Quiz content
  const renderQuizContent = (content, editing = false) => {
    if (!content || !content.questions) return null;

    return (
      <div className="space-y-6">
        <div className="bg-blue-50 p-4 rounded-lg">
          <p className="text-sm font-medium text-blue-900">
            Total Questions: {content.total_questions || content.questions.length}
          </p>
        </div>

        {content.questions.map((q, idx) => (
          <div key={q.id || idx} className="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
            {editing ? (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Question {idx + 1}
                  </label>
                  <textarea
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    rows="2"
                    value={q.question}
                    onChange={(e) => {
                      const newContent = { ...editedContent };
                      newContent.questions[idx].question = e.target.value;
                      setEditedContent(newContent);
                    }}
                  />
                </div>

                <div className="space-y-2">
                  <label className="block text-sm font-medium text-gray-700">Options</label>
                  {Object.entries(q.options).map(([key, value]) => (
                    <div key={key} className="flex items-center space-x-2">
                      <span className="font-semibold text-gray-700 w-8">{key}:</span>
                      <input
                        type="text"
                        className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        value={value}
                        onChange={(e) => {
                          const newContent = { ...editedContent };
                          newContent.questions[idx].options[key] = e.target.value;
                          setEditedContent(newContent);
                        }}
                      />
                      <input
                        type="radio"
                        name={`correct-${idx}`}
                        checked={q.correct_answer === key}
                        onChange={() => {
                          const newContent = { ...editedContent };
                          newContent.questions[idx].correct_answer = key;
                          setEditedContent(newContent);
                        }}
                        className="w-4 h-4 text-green-600"
                      />
                    </div>
                  ))}
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Explanation
                  </label>
                  <textarea
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                    rows="2"
                    value={q.explanation || ''}
                    onChange={(e) => {
                      const newContent = { ...editedContent };
                      newContent.questions[idx].explanation = e.target.value;
                      setEditedContent(newContent);
                    }}
                  />
                </div>
              </div>
            ) : (
              <div className="space-y-3">
                <h4 className="font-semibold text-gray-900">Question {idx + 1}</h4>
                <p className="text-gray-800">{q.question}</p>

                <div className="space-y-2 mt-4">
                  {Object.entries(q.options).map(([key, value]) => (
                    <div
                      key={key}
                      className={`px-4 py-2 rounded-md ${
                        key === q.correct_answer
                          ? 'bg-green-50 border-2 border-green-500'
                          : 'bg-gray-50 border border-gray-200'
                      }`}
                    >
                      <span className="font-semibold">{key}:</span> {value}
                      {key === q.correct_answer && (
                        <span className="ml-2 text-green-600 font-medium">✓ Correct</span>
                      )}
                    </div>
                  ))}
                </div>

                {q.explanation && (
                  <div className="mt-4 p-3 bg-blue-50 rounded-md">
                    <p className="text-sm text-blue-900">
                      <span className="font-semibold">Explanation:</span> {q.explanation}
                    </p>
                  </div>
                )}
              </div>
            )}
          </div>
        ))}
      </div>
    );
  };

  // Render Structured Questions content
  const renderStructuredContent = (content, editing = false) => {
    if (!content || !content.questions) return null;

    return (
      <div className="space-y-6">
        <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border-2 border-green-300">
          <p className="text-sm font-medium text-green-900 flex items-center space-x-2">
            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd"></path>
            </svg>
            <span>Total Questions: {content.questions.length}</span>
          </p>
        </div>

        {content.questions.map((q, idx) => (
          <div key={q.id || idx} className="bg-white p-6 rounded-2xl border-2 border-gray-200 shadow-lg hover:shadow-xl transition-all duration-300">
            {editing ? (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-bold text-gray-700 mb-2 flex items-center space-x-2">
                    <span className="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-full flex items-center justify-center text-sm font-bold">
                      {idx + 1}
                    </span>
                    <span>Question</span>
                  </label>
                  <textarea
                    className="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-200 focus:border-green-500 transition-all duration-300"
                    rows="3"
                    value={q.question}
                    onChange={(e) => {
                      const newContent = { ...editedContent };
                      newContent.questions[idx].question = e.target.value;
                      setEditedContent(newContent);
                    }}
                  />
                </div>

                <div>
                  <label className="block text-sm font-bold text-gray-700 mb-2">
                    Marks: {q.marks || 10}
                  </label>
                  <input
                    type="number"
                    className="w-24 px-4 py-2 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-200 focus:border-green-500"
                    value={q.marks || 10}
                    min="1"
                    max="100"
                    onChange={(e) => {
                      const newContent = { ...editedContent };
                      newContent.questions[idx].marks = parseInt(e.target.value);
                      setEditedContent(newContent);
                    }}
                  />
                </div>

                <div>
                  <label className="block text-sm font-bold text-gray-700 mb-2">
                    Model Answer / Key Points
                  </label>
                  <textarea
                    className="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-200 focus:border-green-500 transition-all duration-300"
                    rows="4"
                    value={q.model_answer || ''}
                    placeholder="Enter the expected answer or key points..."
                    onChange={(e) => {
                      const newContent = { ...editedContent };
                      newContent.questions[idx].model_answer = e.target.value;
                      setEditedContent(newContent);
                    }}
                  />
                </div>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="flex items-start space-x-3">
                  <span className="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 text-white rounded-xl flex items-center justify-center text-lg font-bold shadow-lg">
                    {idx + 1}
                  </span>
                  <div className="flex-1">
                    <h4 className="font-bold text-gray-900 text-lg mb-2">{q.question}</h4>
                    <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-orange-100 to-red-100 text-orange-700 border border-orange-300">
                      {q.marks || 10} marks
                    </span>
                  </div>
                </div>

                {q.model_answer && (
                  <div className="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-200">
                    <p className="text-sm font-bold text-green-900 mb-2 flex items-center space-x-2">
                      <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"></path>
                      </svg>
                      <span>Model Answer:</span>
                    </p>
                    <p className="text-gray-800 whitespace-pre-wrap">{q.model_answer}</p>
                  </div>
                )}

                {q.marking_scheme && (
                  <div className="mt-3 p-3 bg-blue-50 rounded-xl border border-blue-200">
                    <p className="text-xs font-semibold text-blue-900 mb-1">Marking Scheme:</p>
                    <p className="text-sm text-blue-800">{q.marking_scheme}</p>
                  </div>
                )}
              </div>
            )}
          </div>
        ))}
      </div>
    );
  };

  // Render Summary Notes content
  const renderNotesContent = (content, editing = false) => {
    if (!content || !content.key_points) return null;

    return (
      <div className="space-y-6">
        {content.summary && (
          <div className="bg-blue-50 p-4 rounded-lg">
            {editing ? (
              <textarea
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                rows="4"
                value={content.summary}
                onChange={(e) => {
                  const newContent = { ...editedContent };
                  newContent.summary = e.target.value;
                  setEditedContent(newContent);
                }}
              />
            ) : (
              <p className="text-gray-800">{content.summary}</p>
            )}
          </div>
        )}

        <div>
          <h4 className="font-semibold text-gray-900 mb-4">Key Points</h4>
          <ul className="space-y-3">
            {content.key_points.map((point, idx) => (
              <li key={idx} className="flex items-start space-x-3">
                <span className="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-medium">
                  {idx + 1}
                </span>
                {editing ? (
                  <input
                    type="text"
                    className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                    value={point}
                    onChange={(e) => {
                      const newContent = { ...editedContent };
                      newContent.key_points[idx] = e.target.value;
                      setEditedContent(newContent);
                    }}
                  />
                ) : (
                  <span className="flex-1 text-gray-700">{point}</span>
                )}
              </li>
            ))}
          </ul>
        </div>

        {content.word_count && (
          <div className="text-sm text-gray-500 text-right">
            Source document word count: {content.word_count}
          </div>
        )}
      </div>
    );
  };

  // Show initial loading screen
  if (isInitialLoading) {
    return <LoadingScreen message="Loading AI Document Analyzer..." />;
  }

  // Show full-screen loading when processing
  if (isProcessing) {
    return <LoadingScreen message={processingStatus || "Analyzing your documents..."} showProgress={false} />;
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 p-6">
      <div className="max-w-6xl mx-auto">
        <div className="bg-white rounded-3xl shadow-2xl p-8 border border-gray-100 hover:shadow-3xl transition-all duration-500">
          {/* Animated Header with Logo */}
          <div className="flex items-center justify-between mb-8 pb-6 border-b-2 border-gray-100">
            <div className="flex items-center space-x-4">
              <div className="w-20 h-20 flex items-center justify-center animate-bounce">
                <img
                  src="/images/ketupat-logo.svg"
                  alt="Ketupat Logo"
                  className="w-full h-full object-contain drop-shadow-2xl hover:scale-110 transition-transform duration-300"
                />
              </div>
              <div>
                <h2 className="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
                  Ketupats Document Analyzer
                </h2>
                <p className="text-gray-600 mt-2 text-lg flex items-center space-x-2">
                  <span>✨</span>
                  <span>Transform your documents into engaging learning materials</span>
                </p>
              </div>
            </div>

            {/* Quick Stats Badge */}
            {generatedContent && (
              <div className="bg-gradient-to-r from-green-400 to-emerald-500 text-white px-6 py-3 rounded-full shadow-lg animate-fadeIn">
                <div className="flex items-center space-x-2">
                  <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"></path>
                  </svg>
                  <span className="font-bold">Content Generated!</span>
                </div>
              </div>
            )}
          </div>

          {/* Enhanced Message Display */}
          {message.text && (
            <div
              className={`mb-6 p-5 rounded-2xl shadow-xl animate-fadeIn transform hover:scale-[1.02] transition-all duration-300 ${
                message.type === 'success'
                  ? 'bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 text-green-800 border-2 border-green-400'
                  : 'bg-gradient-to-r from-red-50 via-rose-50 to-pink-50 text-red-800 border-2 border-red-400'
              }`}
              role="alert"
            >
              <div className="flex items-center space-x-3">
                {message.type === 'success' ? (
                  <div className="flex-shrink-0 w-10 h-10 bg-green-500 rounded-full flex items-center justify-center animate-bounce">
                    <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"></path>
                    </svg>
                  </div>
                ) : (
                  <div className="flex-shrink-0 w-10 h-10 bg-red-500 rounded-full flex items-center justify-center animate-pulse">
                    <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd"></path>
                    </svg>
                  </div>
                )}
                <span className="font-semibold text-lg">{message.text}</span>
              </div>
            </div>
          )}

        {/* Upload Section - AC: "Ability to select source files" */}
        {!generatedContent && (
          <div className="space-y-6">
            <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-200">
              <label className="block text-sm font-semibold text-gray-700 mb-3 flex items-center space-x-2">
                <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span>Title *</span>
              </label>
              <input
                type="text"
                className="w-full px-5 py-3 border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-blue-200 focus:border-blue-500 transition-all duration-300 text-lg"
                placeholder="e.g., Chapter 5 Quiz, Week 3 Summary"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                disabled={isProcessing}
              />
            </div>

            <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-200 hover:border-blue-400 transition-all duration-300">
              <label className="block text-sm font-semibold text-gray-700 mb-3 flex items-center space-x-2">
                <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <span>Upload Documents (PDF, DOCX, PPTX - Max 10MB each) *</span>
              </label>
              <div className="relative">
                <input
                  ref={fileInputRef}
                  type="file"
                  multiple
                  accept=".pdf,.docx,.pptx"
                  onChange={handleFileSelect}
                  disabled={isProcessing}
                  className="w-full px-5 py-4 border-2 border-dashed border-gray-300 rounded-xl focus:ring-4 focus:ring-purple-200 focus:border-purple-500 transition-all duration-300 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-purple-500 file:to-blue-500 file:text-white hover:file:from-purple-600 hover:file:to-blue-600 file:shadow-lg cursor-pointer hover:border-purple-400"
                />
              </div>
              {files.length > 0 && (
                <div className="mt-4 space-y-2">
                  {files.map((file, idx) => (
                    <div key={idx} className="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-4 shadow-md transform hover:scale-102 transition-all duration-200">
                      <div className="flex items-center space-x-3">
                        <div className="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                          <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                          </svg>
                        </div>
                        <div className="flex-1">
                          <p className="text-sm font-bold text-gray-800">{file.name}</p>
                          <p className="text-xs text-gray-600">{(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* AC: "Ability to choose desired output" */}
            <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-gray-200">
              <label className="block text-sm font-bold text-gray-700 mb-4 flex items-center space-x-2">
                <svg className="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                <span>Choose Content Type *</span>
              </label>
              <div className="grid grid-cols-2 gap-6">
                <button
                  type="button"
                  onClick={() => setOutputType('summary_notes')}
                  disabled={isProcessing}
                  className={`group relative px-6 py-6 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-105 ${
                    outputType === 'summary_notes'
                      ? 'bg-gradient-to-br from-blue-500 to-blue-700 text-white shadow-2xl ring-4 ring-blue-300'
                      : 'bg-white text-gray-700 hover:shadow-xl border-2 border-gray-200 hover:border-blue-400'
                  }`}
                >
                  <div className="flex flex-col items-center space-y-3">
                    <svg className={`w-10 h-10 ${outputType === 'summary_notes' ? 'text-white' : 'text-blue-500'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span className="text-lg">Summary Notes</span>
                    <span className={`text-xs ${outputType === 'summary_notes' ? 'text-blue-100' : 'text-gray-500'}`}>
                      📝 Key points & overview
                    </span>
                  </div>
                  {outputType === 'summary_notes' && (
                    <div className="absolute top-2 right-2">
                      <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"></path>
                      </svg>
                    </div>
                  )}
                </button>
                <button
                  type="button"
                  onClick={() => setOutputType('quiz')}
                  disabled={isProcessing}
                  className={`group relative px-6 py-6 rounded-2xl font-semibold transition-all duration-300 transform hover:scale-105 ${
                    outputType === 'quiz'
                      ? 'bg-gradient-to-br from-purple-500 to-purple-700 text-white shadow-2xl ring-4 ring-purple-300'
                      : 'bg-white text-gray-700 hover:shadow-xl border-2 border-gray-200 hover:border-purple-400'
                  }`}
                >
                  <div className="flex flex-col items-center space-y-3">
                    <svg className={`w-10 h-10 ${outputType === 'quiz' ? 'text-white' : 'text-purple-500'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span className="text-lg">Quiz Questions</span>
                    <span className={`text-xs ${outputType === 'quiz' ? 'text-purple-100' : 'text-gray-500'}`}>
                      ❓ Test knowledge
                    </span>
                  </div>
                  {outputType === 'quiz' && (
                    <div className="absolute top-2 right-2">
                      <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"></path>
                      </svg>
                    </div>
                  )}
                </button>
              </div>
            </div>

            {/* Question Type Selection - Only show when quiz is selected */}
            {outputType === 'quiz' && (
              <div className="bg-white rounded-2xl shadow-lg p-6 border-2 border-purple-200 animate-fadeIn">
                <label className="block text-sm font-bold text-gray-700 mb-4 flex items-center space-x-2">
                  <svg className="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <span>Select Question Type *</span>
                </label>
                <div className="grid grid-cols-3 gap-4">
                  <button
                    type="button"
                    onClick={() => setQuestionType('mcq')}
                    disabled={isProcessing}
                    className={`px-5 py-4 rounded-xl font-bold transition-all duration-300 transform hover:scale-105 ${
                      questionType === 'mcq'
                        ? 'bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-lg ring-4 ring-pink-300'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-2 border-gray-300'
                    }`}
                  >
                    <div className="flex flex-col items-center space-y-2">
                      <span className="text-2xl">🎯</span>
                      <span className="text-sm">MCQ Only</span>
                    </div>
                  </button>
                  <button
                    type="button"
                    onClick={() => setQuestionType('structured')}
                    disabled={isProcessing}
                    className={`px-5 py-4 rounded-xl font-bold transition-all duration-300 transform hover:scale-105 ${
                      questionType === 'structured'
                        ? 'bg-gradient-to-br from-green-500 to-emerald-600 text-white shadow-lg ring-4 ring-green-300'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-2 border-gray-300'
                    }`}
                  >
                    <div className="flex flex-col items-center space-y-2">
                      <span className="text-2xl">📋</span>
                      <span className="text-sm">Structured</span>
                    </div>
                  </button>
                  <button
                    type="button"
                    onClick={() => setQuestionType('mixed')}
                    disabled={isProcessing}
                    className={`px-5 py-4 rounded-xl font-bold transition-all duration-300 transform hover:scale-105 ${
                      questionType === 'mixed'
                        ? 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg ring-4 ring-indigo-300'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-2 border-gray-300'
                    }`}
                  >
                    <div className="flex flex-col items-center space-y-2">
                      <span className="text-2xl">🎲</span>
                      <span className="text-sm">MCQ + Structure</span>
                    </div>
                  </button>
                </div>
                <div className="mt-4 p-4 bg-purple-50 rounded-xl border border-purple-200">
                  <p className="text-xs text-purple-800">
                    <span className="font-bold">Tip:</span> {
                      questionType === 'mcq' ? '🎯 Multiple choice questions with 4 options each' :
                      questionType === 'structured' ? '📋 Essay-type questions requiring detailed answers' :
                      '🎲 A combination of both MCQ and structured questions'
                    }
                  </p>
                </div>

                {/* Question Count Selector */}
                <div className="mt-4 p-4 bg-white rounded-xl border-2 border-purple-200">
                  <label className="block text-sm font-bold text-gray-700 mb-3 flex items-center space-x-2">
                    <svg className="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                    </svg>
                    <span>Number of Questions</span>
                  </label>
                  <div className="flex items-center space-x-4">
                    <input
                      type="range"
                      min="3"
                      max="20"
                      value={questionCount}
                      onChange={(e) => setQuestionCount(parseInt(e.target.value))}
                      className="flex-1 h-3 bg-gradient-to-r from-purple-200 to-indigo-200 rounded-lg appearance-none cursor-pointer"
                      style={{
                        background: `linear-gradient(to right, rgb(168, 85, 247) 0%, rgb(168, 85, 247) ${((questionCount - 3) / 17) * 100}%, rgb(229, 231, 235) ${((questionCount - 3) / 17) * 100}%, rgb(229, 231, 235) 100%)`
                      }}
                    />
                    <div className="flex items-center justify-center w-20 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 text-white rounded-xl font-bold text-xl shadow-lg">
                      {questionCount}
                    </div>
                  </div>
                  <div className="mt-2 flex justify-between text-xs text-gray-500">
                    <span>Min: 3</span>
                    <span>Max: 20</span>
                  </div>
                </div>

                {/* Separate Answers Checkbox */}
                <div className="mt-4 p-4 bg-white rounded-xl border-2 border-purple-200">
                  <label className="flex items-center space-x-3 cursor-pointer group">
                    <div className="relative">
                      <input
                        type="checkbox"
                        checked={separateAnswers}
                        onChange={(e) => setSeparateAnswers(e.target.checked)}
                        className="sr-only"
                      />
                      <div className={`w-14 h-7 rounded-full transition-all duration-300 ${
                        separateAnswers
                          ? 'bg-gradient-to-r from-green-500 to-emerald-600'
                          : 'bg-gray-300'
                      }`}>
                        <div className={`absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full shadow-md transform transition-transform duration-300 ${
                          separateAnswers ? 'translate-x-7' : 'translate-x-0'
                        }`}></div>
                      </div>
                    </div>
                    <div className="flex-1">
                      <div className="flex items-center space-x-2">
                        <svg className={`w-5 h-5 ${separateAnswers ? 'text-green-600' : 'text-gray-500'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span className="text-sm font-bold text-gray-700 group-hover:text-gray-900">
                          Generate answers in separate file
                        </span>
                      </div>
                      <p className="text-xs text-gray-500 mt-1 ml-7">
                        {separateAnswers
                          ? '✅ Questions and answers will be exported as 2 separate files'
                          : '📄 Questions and answers will be in the same file'}
                      </p>
                    </div>
                  </label>
                </div>
              </div>
            )}

            <div className="flex space-x-4 mt-8">
              <button
                onClick={handleAnalyze}
                disabled={isProcessing || files.length === 0 || !title.trim()}
                className="group relative flex-1 px-10 py-5 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white rounded-2xl font-bold text-xl shadow-2xl hover:shadow-3xl disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-500 transform hover:scale-105 disabled:hover:scale-100 overflow-hidden"
              >
                {/* Animated Background Layer */}
                <div className="absolute inset-0 bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-500 animate-pulse"></div>

                {/* Shine Effect */}
                <div className="absolute inset-0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

                {/* Button Content */}
                <span className="relative z-10 flex items-center justify-center space-x-3">
                  {isProcessing ? (
                    <>
                      <svg className="w-7 h-7 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                      </svg>
                      <span className="animate-pulse">Analyzing Magic in Progress...</span>
                    </>
                  ) : (
                    <>
                      <svg className="w-7 h-7 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>
                      <span>⚡ Generate Content</span>
                    </>
                  )}
                </span>

                {/* Glow Effect on Hover */}
                <div className="absolute inset-0 opacity-0 group-hover:opacity-50 blur-xl bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 transition-opacity duration-500"></div>
              </button>
            </div>
          </div>
        )}

        {/* Generated Content Display - AC: "Ability to view the generated notes or quiz" */}
        {generatedContent && (
          <div className="space-y-6">
            <div className="bg-white rounded-2xl shadow-xl p-6 border-2 border-gray-200">
              <div className="flex items-center justify-between pb-4 border-b-2 border-gray-200">
                <div className="flex items-center space-x-4">
                  <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${
                    generatedContent.content_type === 'mcq_quiz'
                      ? 'bg-gradient-to-br from-purple-500 to-purple-700'
                      : 'bg-gradient-to-br from-blue-500 to-blue-700'
                  }`}>
                    {generatedContent.content_type === 'mcq_quiz' ? (
                      <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                      </svg>
                    ) : (
                      <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    )}
                  </div>
                  <div>
                    <h3 className="text-2xl font-bold text-gray-900">{generatedContent.title}</h3>
                    <p className="text-sm text-gray-500 mt-1 flex items-center space-x-2">
                      <span className={`px-3 py-1 rounded-full text-xs font-semibold ${
                        generatedContent.content_type === 'mcq_quiz'
                          ? 'bg-purple-100 text-purple-700'
                          : 'bg-blue-100 text-blue-700'
                      }`}>
                        {generatedContent.content_type === 'mcq_quiz' ? 'MCQ Quiz' : 'Summary Notes'}
                      </span>
                    </p>
                  </div>
                </div>
                <div className="flex space-x-3">
                  {!isEditing ? (
                    <>
                      {/* Enhanced Export Button with Dropdown */}
                      <div className="relative">
                        <button
                          onClick={() => setShowExportMenu(!showExportMenu)}
                          className="group px-6 py-3 bg-gradient-to-r from-cyan-500 via-blue-500 to-indigo-600 text-white rounded-xl font-bold hover:from-cyan-600 hover:via-blue-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl flex items-center space-x-2 relative overflow-hidden"
                        >
                          <div className="absolute inset-0 bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                          <svg className="w-5 h-5 relative z-10 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                          </svg>
                          <span className="relative z-10">📥 Export</span>
                          <svg className={`w-4 h-4 relative z-10 transition-transform duration-300 ${showExportMenu ? 'rotate-180' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                          </svg>
                        </button>

                        {/* Enhanced Export Dropdown Menu */}
                        {showExportMenu && (
                          <div className="absolute top-full mt-3 left-0 bg-white rounded-2xl shadow-2xl border-2 border-gray-200 py-3 min-w-[220px] z-50 animate-fadeIn">
                            <div className="px-4 py-2 border-b border-gray-200 mb-2">
                              <p className="text-xs font-bold text-gray-500 uppercase tracking-wide">Choose Format</p>
                            </div>
                            <button
                              onClick={() => handleExport('pdf')}
                              className="w-full px-5 py-3 text-left hover:bg-gradient-to-r hover:from-red-50 hover:to-rose-50 transition-all flex items-center space-x-3 text-gray-700 hover:text-red-600 font-semibold group"
                            >
                              <div className="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span className="text-2xl">📕</span>
                              </div>
                              <div className="flex-1">
                                <div className="font-bold">PDF Document</div>
                                <div className="text-xs text-gray-500">Portable format</div>
                              </div>
                            </button>
                            <button
                              onClick={() => handleExport('docx')}
                              className="w-full px-5 py-3 text-left hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all flex items-center space-x-3 text-gray-700 hover:text-blue-600 font-semibold group"
                            >
                              <div className="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span className="text-2xl">📘</span>
                              </div>
                              <div className="flex-1">
                                <div className="font-bold">Word Document</div>
                                <div className="text-xs text-gray-500">Editable format</div>
                              </div>
                            </button>
                            <button
                              onClick={() => handleExport('pptx')}
                              className="w-full px-5 py-3 text-left hover:bg-gradient-to-r hover:from-orange-50 hover:to-amber-50 transition-all flex items-center space-x-3 text-gray-700 hover:text-orange-600 font-semibold group"
                            >
                              <div className="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span className="text-2xl">📙</span>
                              </div>
                              <div className="flex-1">
                                <div className="font-bold">PowerPoint</div>
                                <div className="text-xs text-gray-500">Presentation format</div>
                              </div>
                            </button>
                            <button
                              onClick={() => handleExport('txt')}
                              className="w-full px-5 py-3 text-left hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 transition-all flex items-center space-x-3 text-gray-700 hover:text-gray-900 font-semibold border-t border-gray-200 group"
                            >
                              <div className="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                <span className="text-2xl">📄</span>
                              </div>
                              <div className="flex-1">
                                <div className="font-bold">Plain Text</div>
                                <div className="text-xs text-gray-500">Simple format</div>
                              </div>
                            </button>
                          </div>
                        )}
                      </div>

                      <button
                        onClick={handleEdit}
                        className="group px-6 py-3 bg-gradient-to-r from-yellow-400 via-orange-400 to-orange-500 text-white rounded-xl font-bold hover:from-yellow-500 hover:via-orange-500 hover:to-orange-600 transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl flex items-center space-x-2 relative overflow-hidden"
                      >
                        <div className="absolute inset-0 bg-gradient-to-r from-orange-600 via-orange-500 to-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <svg className="w-5 h-5 relative z-10 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        <span className="relative z-10">✏️ Edit</span>
                      </button>
                      {classId && !generatedContent.is_shared && (
                        <button
                          onClick={() => handleShare(classId)}
                          className="group px-6 py-3 bg-gradient-to-r from-green-500 via-emerald-500 to-teal-600 text-white rounded-xl font-bold hover:from-green-600 hover:via-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl flex items-center space-x-2 relative overflow-hidden"
                        >
                          <div className="absolute inset-0 bg-gradient-to-r from-teal-600 via-emerald-600 to-green-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                          <svg className="w-5 h-5 relative z-10 group-hover:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                          </svg>
                          <span>Share</span>
                        </button>
                      )}
                      <button
                        onClick={handleDelete}
                        className="group px-5 py-3 bg-gradient-to-r from-red-500 to-rose-600 text-white rounded-xl font-semibold hover:from-red-600 hover:to-rose-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center space-x-2"
                      >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span>Delete</span>
                      </button>
                    </>
                  ) : (
                    <>
                      <button
                        onClick={handleSaveEdit}
                        className="group px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl font-bold hover:from-green-600 hover:to-emerald-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center space-x-2"
                      >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Save Changes</span>
                      </button>
                      <button
                        onClick={handleCancelEdit}
                        className="group px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl font-bold hover:from-gray-600 hover:to-gray-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center space-x-2"
                      >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span>Cancel</span>
                      </button>
                    </>
                  )}
                </div>
              </div>

            {generatedContent.is_shared && (
              <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-4 shadow-md">
                <div className="flex items-center space-x-3">
                  <svg className="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"></path>
                  </svg>
                  <p className="text-sm font-semibold text-green-800">
                    This content is shared with students
                  </p>
                </div>
              </div>
            )}

            <div className="mt-6">
              {generatedContent.content_type === 'summary_notes' ? (
                renderNotesContent(isEditing ? editedContent : generatedContent.content, isEditing)
              ) : (
                // For quiz content, check question type
                <>
                  {(generatedContent.question_type === 'mcq' || (!generatedContent.question_type && generatedContent.content.questions && generatedContent.content.questions[0]?.options)) && (
                    renderQuizContent(isEditing ? editedContent : generatedContent.content, isEditing)
                  )}
                  {generatedContent.question_type === 'structured' && (
                    renderStructuredContent(isEditing ? editedContent : generatedContent.content, isEditing)
                  )}
                  {generatedContent.question_type === 'mixed' && (
                    <div className="space-y-8">
                      <div className="bg-gradient-to-r from-pink-50 to-purple-50 p-4 rounded-xl border-2 border-pink-300">
                        <h3 className="font-bold text-lg text-gray-900 flex items-center space-x-2">
                          <span className="text-2xl">🎯</span>
                          <span>Multiple Choice Questions</span>
                        </h3>
                      </div>
                      {renderQuizContent({ questions: (isEditing ? editedContent : generatedContent.content).mcq_questions || [] }, isEditing)}

                      <div className="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-xl border-2 border-green-300 mt-8">
                        <h3 className="font-bold text-lg text-gray-900 flex items-center space-x-2">
                          <span className="text-2xl">📋</span>
                          <span>Structured Questions</span>
                        </h3>
                      </div>
                      {renderStructuredContent({ questions: (isEditing ? editedContent : generatedContent.content).structured_questions || [] }, isEditing)}
                    </div>
                  )}
                </>
              )}
            </div>
            </div>

            <div className="bg-white rounded-2xl shadow-xl p-6 border-2 border-gray-200">
              <button
                onClick={handleReset}
                className="w-full group px-8 py-4 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-xl font-bold text-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center space-x-3"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Generate New Content</span>
              </button>
            </div>
          </div>
        )}
        </div>
      </div>
    </div>
  );
}
