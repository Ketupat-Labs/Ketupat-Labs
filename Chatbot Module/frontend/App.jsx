import React, { useState, useEffect, useRef, useMemo } from 'react';
import LoadingScreen, { LoadingSpinner, SkeletonLoader } from './components/LoadingScreen.jsx';

// --- Configuration ---
// Resolve API URL from environment when available, falling back to same-origin uploader.
// Set VITE_UPLOAD_API_URL in your .env (Vite) to override.
const API_URL = import.meta?.env?.VITE_UPLOAD_API_URL || `${window.location.origin}/uploader/upload.php`;

// Client-side validation settings (mirrors backend)
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_FILE_TYPES = [
  'application/pdf', // .pdf
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
  'application/msword', // .doc
  'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
  'application/vnd.ms-powerpoint', // .ppt
];
const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'doc', 'pptx', 'ppt'];

// --- Helper Functions ---

/** Dedupe files by name (client-side convenience). */
const dedupeByName = (files) => {
  const map = new Map();
  for (const f of files) {
    if (!map.has(f.name)) map.set(f.name, f);
  }
  return Array.from(map.values());
};

/**
 * Safe JSON fetch with timeout and friendlier errors.
 * Throws Error with a user-friendly message on failure.
 */
const safeFetchJson = async (url, options = {}) => {
  const {
    method = 'GET',
    body = null,
    headers = {},
    timeout = 30000,
    signal,
  } = options;

  const controller = new AbortController();
  const signals = signal ?? controller.signal;
  const timeoutId = setTimeout(() => controller.abort(), timeout);

  try {
    const res = await fetch(url, { method, body, headers, signal: signals });

    const contentType = res.headers.get('content-type') || '';
    const isJson = contentType.includes('application/json');

    if (isJson) {
      const json = await res.json().catch(() => {
        throw new Error('Server returned invalid JSON.');
      });
      if (!res.ok) {
        const msg = json?.message || `Request failed with status ${res.status}`;
        throw new Error(msg);
      }
      return json;
    } else {
      const text = await res.text();
      if (!res.ok) {
        throw new Error(text?.slice(0, 200) || `Request failed with status ${res.status}`);
      }
      // Best-effort: try to parse if text is JSON-like
      try {
        return JSON.parse(text);
      } catch {
        return { success: true, raw: text };
      }
    }
  } catch (err) {
    if (err?.name === 'AbortError') {
      throw new Error('Request timed out. Please try again.');
    }
    throw new Error(err?.message || 'Network error.');
  } finally {
    clearTimeout(timeoutId);
  }
};

/**
 * Formats file size for display.
 * @param {number} bytes - File size in bytes.
 * @returns {string} - Formatted file size (e.g., "1.2 MB").
 */
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

/**
 * Validates a single file based on size and type.
 * @param {File} file - The file to validate.
 * @returns {string|null} - An error message string if invalid, or null if valid.
 */
const validateFile = (file) => {
  const fileExtension = file.name.split('.').pop().toLowerCase();

  if (file.size > MAX_FILE_SIZE) {
    return `${file.name} (${formatFileSize(file.size)}) is too large. Max size is ${formatFileSize(MAX_FILE_SIZE)}.`;
  }

  // Check both MIME type and file extension for robustness
  if (!ALLOWED_FILE_TYPES.includes(file.type) && !ALLOWED_EXTENSIONS.includes(fileExtension)) {
    return `${file.name} is an unauthorized file type. Allowed types: PDF, DOCX, PPT.`;
  }

  return null;
};

// --- React Components ---

/**
 * Main application component.
 */
export default function App() {
  // State for the list of documents shown in the UI
  const [uploadedFiles, setUploadedFiles] = useState([]);

  // State for the files selected in the input, ready for upload
  const [filesToUpload, setFilesToUpload] = useState([]);

  // Drag-and-drop and filters
  const [isDragging, setIsDragging] = useState(false);
  const [query, setQuery] = useState('');
  const [sortBy, setSortBy] = useState('name-asc');

  // State for loading indicators
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);
  const [isInitialLoading, setIsInitialLoading] = useState(true);

  // State for success/error messages
  const [message, setMessage] = useState({ type: '', text: '' });

  // Ref to the file input element
  const fileInputRef = useRef(null);
  // Ref to the upload section for scrolling
  const uploadSectionRef = useRef(null);

  // Initial loading effect
  useEffect(() => {
    const timer = setTimeout(() => {
      setIsInitialLoading(false);
    }, 1500);
    return () => clearTimeout(timer);
  }, []);

  // Fetch the list of already uploaded files on component mount
  useEffect(() => {
    if (!isInitialLoading) {
      fetchUploadedFiles();
    }
  }, [isInitialLoading]);

  // Derived: selection stats
  const selectionStats = useMemo(() => {
    const totalSize = filesToUpload.reduce((sum, f) => sum + (f?.size || 0), 0);
    return { count: filesToUpload.length, totalSize };
  }, [filesToUpload]);

  // Derived: filtered + sorted uploaded files
  const filteredFiles = useMemo(() => {
    let list = Array.isArray(uploadedFiles) ? [...uploadedFiles] : [];
    if (query.trim()) {
      const q = query.toLowerCase();
      list = list.filter(f => f?.name?.toLowerCase().includes(q));
    }
    const dir = sortBy.includes('desc') ? -1 : 1;
    if (sortBy.startsWith('name')) {
      list.sort((a, b) => (a.name || '').localeCompare(b.name || '') * dir);
    } else if (sortBy.startsWith('size')) {
      list.sort((a, b) => ((a.size || 0) - (b.size || 0)) * dir);
    }
    return list;
  }, [uploadedFiles, query, sortBy]);

  /**
   * Fetches the list of files from the backend.
   */
  const fetchUploadedFiles = async () => {
    setIsFetching(true);
    setMessage({ type: '', text: '' });
    try {
      const data = await safeFetchJson(`${API_URL}?action=list`, { timeout: 20000 });
      // Expected shape: { success: true, files: [...] }
      if (data && Array.isArray(data.files)) {
        setUploadedFiles(data.files);
      } else if (data && data.success && data.data && Array.isArray(data.data.files)) {
        // tolerate alternate nesting
        setUploadedFiles(data.data.files);
      } else {
        throw new Error('Unexpected data format from server.');
      }

    } catch (error) {
      setMessage({ type: 'error', text: error.message || 'Failed to fetch files. Please try again.' });
      console.error('Error fetching files:', error);
    } finally {
      setIsFetching(false);
    }
  };

  /**
   * Handles the file selection event.
   * Validates files and updates state.
   * @param {React.ChangeEvent<HTMLInputElement>} e - The file input change event.
   */
  const handleFileSelect = (e) => {
    setMessage({ type: '', text: '' });
    const selectedFiles = Array.from(e.target.files);
    addFilesToSelection(selectedFiles);
  };

  const addFilesToSelection = (files) => {
    let valid = [];
    let firstError = '';
    for (const file of files) {
      const err = validateFile(file);
      if (err) {
        if (!firstError) firstError = err;
      } else {
        valid.push(file);
      }
    }
    if (firstError) setMessage({ type: 'error', text: firstError });
    if (valid.length) {
      setFilesToUpload(prev => dedupeByName([...prev, ...valid]));
    }
  };

  /**
   * Handles the file upload process.
   * Sends selected files to the backend.
   */
  const handleUpload = async () => {
    if (filesToUpload.length === 0) {
      setMessage({ type: 'error', text: 'Please select at least one valid file to upload.' });
      return;
    }

    setIsLoading(true);
    setMessage({ type: '', text: '' });
    const formData = new FormData();
    filesToUpload.forEach((file) => {
      formData.append('files[]', file);
    });

    // Add action identifier for the PHP script
    formData.append('action', 'upload');

    try {
      const result = await safeFetchJson(API_URL, { method: 'POST', body: formData, timeout: 30000 });
      if (result.success) {
        setMessage({ type: 'success', text: result.message || 'Upload successful.' });
        fetchUploadedFiles();
        setFilesToUpload([]);
        if (fileInputRef.current) fileInputRef.current.value = '';
      } else {
        setMessage({ type: 'error', text: result.message || 'An unknown error occurred.' });
      }
    } catch (error) {
      setMessage({ type: 'error', text: error.message || 'File upload failed. Check your connection or file size.' });
      console.error('Error uploading files:', error);
    } finally {
      setIsLoading(false);
    }
  };

  /**
   * Handles the removal of a document.
   * Sends a request to the backend to delete the file.
   * @param {string} fileName - The unique name of the file to remove.
   */
  const handleRemove = async (fileName) => {
    // Optimistic UI update: remove from list immediately
    const originalFiles = [...uploadedFiles];
    setUploadedFiles((prevFiles) => prevFiles.filter((file) => file.name !== fileName));
    setMessage({ type: '', text: '' });

    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('file', fileName);

      const result = await safeFetchJson(API_URL, { method: 'POST', body: formData, timeout: 20000 });

      if (!result?.success) {
        throw new Error(result?.message || 'Failed to remove file.');
      }

      setMessage({ type: 'success', text: result.message || 'File removed.' });
    } catch (error) {
      // If removal fails, restore the file list
      setUploadedFiles(originalFiles);
      setMessage({ type: 'error', text: `Error: ${error.message}` });
      console.error('Error removing file:', error);
    }
  };

  // Drag & Drop handlers
  const onDragOver = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(true);
  };

  // Scroll to upload area and focus input
  const goToUpload = () => {
    if (uploadSectionRef.current) {
      uploadSectionRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    setTimeout(() => {
      if (fileInputRef.current) fileInputRef.current.focus();
    }, 500);
  };
  const onDragLeave = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  };
  const onDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
    const files = Array.from(e.dataTransfer.files || []);
    addFilesToSelection(files);
  };

  // Show loading screen on initial load
  if (isInitialLoading) {
    return <LoadingScreen message="Loading Document Library" />;
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 text-gray-800 font-sans p-4 sm:p-8">
      <div className="max-w-6xl mx-auto">

        {/* Animated Header with Logo */}
        <header className="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white rounded-2xl shadow-2xl p-8 mb-8 transform hover:scale-[1.01] transition-transform duration-300">
          <div className="flex items-center space-x-4">
            <div className="w-16 h-16 flex items-center justify-center animate-bounce">
              <img
                src="/images/ketupat-logo.svg"
                alt="Ketupat Logo"
                className="w-full h-full object-contain drop-shadow-lg"
              />
            </div>
            <div>
              <h1 className="text-3xl sm:text-4xl font-bold tracking-tight">📚 Document Library</h1>
              <p className="text-blue-100 mt-2 text-lg">Manage and distribute learning materials with ease</p>
            </div>
          </div>

          {/* Stats Bar */}
          <div className="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-bold">{uploadedFiles.length}</div>
              <div className="text-sm text-blue-100">Total Documents</div>
            </div>
            <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-bold">{filesToUpload.length}</div>
              <div className="text-sm text-blue-100">Ready to Upload</div>
            </div>
            <div className="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 hover:bg-white/20 transition-all">
              <div className="text-3xl font-bold">{formatFileSize(uploadedFiles.reduce((sum, f) => sum + (f?.size || 0), 0))}</div>
              <div className="text-sm text-blue-100">Total Storage</div>
            </div>
          </div>
        </header>

        <main className="space-y-8">

          {/* Section: Upload Document */}
          <section ref={uploadSectionRef} className="bg-white rounded-2xl shadow-xl p-6 sm:p-8 border border-gray-100 hover:shadow-2xl transition-all duration-300">
            <div className="flex items-center space-x-3 mb-6">
              <div className="w-10 h-10 bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                <span className="text-2xl">📤</span>
              </div>
              <h2 className="text-2xl font-bold text-gray-900">Upload New Document</h2>
            </div>

            {/* File Input Area with Animation */}
            <div
              className={`border-2 border-dashed rounded-2xl p-8 text-center transition-all duration-300 relative overflow-hidden ${
                isDragging
                  ? 'border-indigo-500 bg-gradient-to-br from-indigo-50 to-purple-50 scale-105 shadow-lg'
                  : 'border-gray-300 bg-gradient-to-br from-gray-50 to-blue-50 hover:border-indigo-400 hover:shadow-md'
              }`}
              onDragOver={onDragOver}
              onDragEnter={onDragOver}
              onDragLeave={onDragLeave}
              onDrop={onDrop}
            >
              {isDragging && (
                <div className="absolute inset-0 bg-indigo-500/10 backdrop-blur-sm flex items-center justify-center z-10">
                  <div className="text-6xl animate-bounce">📂</div>
                </div>
              )}

              <div className="text-6xl mb-4 animate-pulse">☁️</div>

              <input
                type="file"
                multiple
                ref={fileInputRef}
                onChange={handleFileSelect}
                className="block w-full text-sm text-gray-600 mb-4
                          file:mr-4 file:py-3 file:px-6
                          file:rounded-full file:border-0
                          file:text-sm file:font-semibold
                          file:bg-gradient-to-r file:from-indigo-500 file:to-purple-600
                          file:text-white
                          hover:file:from-indigo-600 hover:file:to-purple-700
                          file:cursor-pointer file:transition-all file:duration-300
                          file:shadow-lg hover:file:shadow-xl"
                aria-label="Upload document"
                accept=".pdf,.doc,.docx,.ppt,.pptx"
              />

              <div className="space-y-2">
                <p className="text-sm font-medium text-gray-700">
                  📄 Drag & drop files here or click to browse
                </p>
                <p className="text-xs text-gray-500">
                  ✅ Allowed: PDF, DOCX, PPTX • 📦 Max size: 10MB per file
                </p>
              </div>
            </div>

            {/* Selected Files Preview with Enhanced Design */}
            {filesToUpload.length > 0 && (
              <div className="mt-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-6 border-2 border-indigo-200 animate-fadeIn">
                <div className="flex items-center justify-between mb-4">
                  <h4 className="font-semibold text-gray-900 flex items-center space-x-2">
                    <span className="text-2xl">📋</span>
                    <span>Selected Files</span>
                  </h4>
                  <div className="px-4 py-2 bg-white rounded-full shadow-md">
                    <span className="text-sm font-bold text-indigo-600">{selectionStats.count} file(s)</span>
                    <span className="text-xs text-gray-500 ml-2">{formatFileSize(selectionStats.totalSize)}</span>
                  </div>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  {filesToUpload.map((file, index) => (
                    <div
                      key={index}
                      className="flex items-center justify-between p-3 rounded-lg bg-white border border-indigo-200 shadow-sm hover:shadow-md transition-all duration-200 hover:scale-105"
                    >
                      <div className="flex items-center space-x-3 min-w-0">
                        <div className="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                          {file.name.split('.').pop().toUpperCase().slice(0, 3)}
                        </div>
                        <div className="min-w-0 flex-1">
                          <p className="font-medium text-sm text-gray-900 truncate" title={file.name}>{file.name}</p>
                          <p className="text-xs text-gray-500">{formatFileSize(file.size)}</p>
                        </div>
                      </div>
                      <button
                        type="button"
                        onClick={() => setFilesToUpload(prev => prev.filter((_, i) => i !== index))}
                        className="flex-shrink-0 ml-2 p-1.5 rounded-full hover:bg-red-100 text-red-500 hover:text-red-700 transition-all"
                        title="Remove"
                      >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </button>
                    </div>
                  ))}
                </div>
                <div className="mt-4 flex gap-3">
                  <button
                    type="button"
                    onClick={() => setFilesToUpload([])}
                    className="px-4 py-2 text-sm rounded-lg border-2 border-gray-300 text-gray-700 hover:bg-gray-100 hover:border-gray-400 transition-all font-medium"
                  >
                    🗑️ Clear All
                  </button>
                </div>
              </div>
            )}

            {/* Upload Button with Enhanced Animation */}
            <div className="mt-6 flex justify-end">
              <button
                onClick={handleUpload}
                disabled={isLoading || filesToUpload.length === 0}
                className="group relative px-8 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-4 focus:ring-green-300 transition-all duration-300 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed disabled:shadow-none transform hover:scale-105 disabled:hover:scale-100"
              >
                {isLoading ? (
                  <div className="flex items-center space-x-2">
                    <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Uploading...</span>
                  </div>
                ) : (
                  <div className="flex items-center space-x-2">
                    <svg className="w-5 h-5 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <span>Upload Files</span>
                  </div>
                )}
              </button>
            </div>

            {/* Message Area with Enhanced Styling */}
            {message.text && (
              <div aria-live="polite" aria-atomic="true" className={`mt-6 p-5 rounded-xl text-sm font-medium shadow-lg border-2 animate-fadeIn
                ${message.type === 'success' && 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-800 border-green-300'}
                ${message.type === 'error' && 'bg-gradient-to-r from-red-50 to-rose-50 text-red-800 border-red-300'}
                ${message.type === 'warning' && 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-800 border-yellow-300'}
              `}>
                <div className="flex items-center space-x-3">
                  {message.type === 'success' && <span className="text-2xl">✅</span>}
                  {message.type === 'error' && <span className="text-2xl">❌</span>}
                  {message.type === 'warning' && <span className="text-2xl">⚠️</span>}
                  <span>{message.text}</span>
                </div>
              </div>
            )}
          </section>

          {/* Section: View Uploaded Documents */}
          <section className="bg-white rounded-2xl shadow-xl p-6 sm:p-8 border border-gray-100 hover:shadow-2xl transition-all duration-300">
            <div className="flex items-center space-x-3 mb-6">
              <div className="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                <span className="text-2xl">📁</span>
              </div>
              <h2 className="text-2xl font-bold text-gray-900">Document Library</h2>
            </div>

            {/* Enhanced Controls */}
            <div className="flex flex-col sm:flex-row gap-4 sm:items-center sm:justify-between mb-6">
              <div className="flex items-center gap-3 flex-1">
                <div className="relative flex-1 max-w-md">
                  <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                  </div>
                  <input
                    type="text"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="🔍 Search documents..."
                    className="w-full pl-11 pr-4 py-3 text-sm border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all hover:border-gray-300 bg-gray-50 focus:bg-white"
                  />
                  {query && (
                    <button
                      onClick={() => setQuery('')}
                      className="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                    >
                      <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  )}
                </div>

                <div className="relative">
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value)}
                    className="appearance-none pl-4 pr-10 py-3 text-sm border-2 border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all hover:border-gray-300 bg-gray-50 focus:bg-white font-medium cursor-pointer"
                  >
                    <option value="name-asc">📝 A → Z</option>
                    <option value="name-desc">📝 Z → A</option>
                    <option value="size-asc">📦 Small → Large</option>
                    <option value="size-desc">📦 Large → Small</option>
                  </select>
                  <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg className="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                  </div>
                </div>
              </div>

              <button
                type="button"
                onClick={fetchUploadedFiles}
                className="group inline-flex items-center gap-2 px-5 py-3 text-sm rounded-xl border-2 border-gray-200 text-gray-700 hover:bg-gradient-to-r hover:from-blue-500 hover:to-indigo-600 hover:text-white hover:border-transparent transition-all font-medium shadow-sm hover:shadow-md"
                title="Refresh list"
              >
                <svg className="h-5 w-5 group-hover:animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <polyline points="23 4 23 10 17 10"></polyline>
                  <polyline points="1 20 1 14 7 14"></polyline>
                  <path d="M3.51 9a9 9 0 0114.13-3.36L23 10M1 14l5.36 4.36A9 9 0 0020.49 15"></path>
                </svg>
                <span>Refresh</span>
              </button>
            </div>

            {/* Enhanced Loading Skeleton */}
            {isFetching && (
              <div className="space-y-4">
                <SkeletonLoader type="list" count={3} />
              </div>
            )}

            {/* File List with Enhanced Cards */}
            {!isFetching && filteredFiles.length > 0 && (
              <div className="grid grid-cols-1 gap-4">
                {filteredFiles.map((file, index) => (
                  <div
                    key={file.name}
                    className="animate-fadeIn"
                    style={{ animationDelay: `${index * 50}ms` }}
                  >
                    <FileItem file={file} onRemove={() => handleRemove(file.name)} />
                  </div>
                ))}
              </div>
            )}

            {/* Enhanced No Files Message */}
            {!isFetching && filteredFiles.length === 0 && (
              <div className="text-center py-16 bg-gradient-to-br from-gray-50 to-blue-50 rounded-2xl border-2 border-dashed border-gray-300">
                <div className="text-8xl mb-4 animate-bounce">📭</div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">No documents found</h3>
                <p className="text-gray-500 mb-6">
                  {query ? `No results for "${query}". Try a different search term.` : 'Start by uploading your first document!'}
                </p>
                {!query && (
                  <button
                    onClick={goToUpload}
                    className="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-indigo-600 hover:to-purple-700 transition-all transform hover:scale-105"
                  >
                    <span className="text-xl">📤</span>
                    <span>Upload Document</span>
                  </button>
                )}
              </div>
            )}
          </section>
        </main>
      </div>
    </div>
  );
}

/**
 * Component to render a single file item in the list with enhanced interactivity.
 * @param {object} props
 * @param {object} props.file - The file object.
 * @param {function} props.onRemove - Callback function to handle file removal.
 */
function FileItem({ file, onRemove }) {
  const [isRemoving, setIsRemoving] = useState(false);
  const [isHovered, setIsHovered] = useState(false);

  const handleRemoveClick = () => {
    if (window.confirm(`🗑️ Are you sure you want to remove "${file.name}"? This action cannot be undone.`)) {
      setIsRemoving(true);
      onRemove(file.name).finally(() => setIsRemoving(false));
    }
  };

  const extension = file.name.split('.').pop().toLowerCase();

  // Color schemes for different file types
  const getFileColor = () => {
    switch(extension) {
      case 'pdf': return 'from-red-400 to-rose-500';
      case 'doc':
      case 'docx': return 'from-blue-400 to-indigo-500';
      case 'ppt':
      case 'pptx': return 'from-orange-400 to-amber-500';
      default: return 'from-gray-400 to-slate-500';
    }
  };

  const getFileBgColor = () => {
    switch(extension) {
      case 'pdf': return 'from-red-50 to-rose-50';
      case 'doc':
      case 'docx': return 'from-blue-50 to-indigo-50';
      case 'ppt':
      case 'pptx': return 'from-orange-50 to-amber-50';
      default: return 'from-gray-50 to-slate-50';
    }
  };

  return (
    <div
      className={`group relative p-5 flex items-center justify-between space-x-4 bg-gradient-to-r ${getFileBgColor()} rounded-xl border-2 border-gray-200 hover:border-indigo-400 transition-all duration-300 hover:shadow-xl transform hover:scale-[1.02] ${isRemoving ? 'opacity-50' : ''}`}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* File Info */}
      <div className="flex items-center space-x-4 min-w-0 flex-1">
        <div className={`flex-shrink-0 w-14 h-14 bg-gradient-to-br ${getFileColor()} rounded-xl flex items-center justify-center text-white font-bold shadow-lg transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300`}>
          <FileIcon extension={extension} />
        </div>
        <div className="min-w-0 flex-1">
          <div className="flex items-center space-x-2 mb-1">
            <a
              href={file.url}
              download
              target="_blank"
              rel="noopener noreferrer"
              className="text-base font-semibold text-gray-900 hover:text-indigo-600 truncate group-hover:underline transition-colors"
              title={`Download ${file.name}`}
            >
              {file.name}
            </a>
            {isHovered && (
              <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 animate-fadeIn">
                Click to download
              </span>
            )}
          </div>
          <div className="flex items-center space-x-3 text-sm text-gray-600">
            <span className="flex items-center space-x-1">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
              </svg>
              <span>{file.size ? formatFileSize(file.size) : 'Unknown size'}</span>
            </span>
            <span className="text-gray-400">•</span>
            <span className="uppercase font-semibold text-xs px-2 py-0.5 bg-white rounded-full">
              {extension}
            </span>
          </div>
        </div>
      </div>

      {/* Action Buttons */}
      <div className="flex-shrink-0 flex items-center space-x-2">
        <a
          href={file.url}
          download
          className="p-3 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 shadow-md hover:shadow-lg transition-all transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
          title="Download file"
        >
          <svg className="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
          </svg>
        </a>
        <button
          onClick={handleRemoveClick}
          disabled={isRemoving}
          className="p-3 rounded-xl bg-gradient-to-r from-red-500 to-rose-600 text-white hover:from-red-600 hover:to-rose-700 shadow-md hover:shadow-lg transition-all transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
          title="Remove file"
        >
          {isRemoving ? (
            <svg className="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
              <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          ) : (
            <svg className="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          )}
        </button>
      </div>
    </div>
  );
}

/**
 * Component to render a file-type specific icon with emojis.
 * @param {object} props
 * @param {string} props.extension - The file extension.
 */
function FileIcon({ extension }) {
  const ext = extension.toLowerCase();
  let emoji;

  if (ext === 'pdf') {
    emoji = '📕';
  } else if (ext === 'docx' || ext === 'doc') {
    emoji = '📘';
  } else if (ext === 'pptx' || ext === 'ppt') {
    emoji = '📙';
  } else {
    emoji = '📄';
  }

  return <div className="text-3xl">{emoji}</div>;
}
