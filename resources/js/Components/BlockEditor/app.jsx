import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import BlockNoteEditor from './BlockNoteEditor';

function LessonEditorApp({ initialBlocks = [], inputId = 'content_blocks_input' }) {
    const [blocks, setBlocks] = useState(initialBlocks);

    // Update hidden input whenever blocks change
    useEffect(() => {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = JSON.stringify({ blocks });
        }
    }, [blocks, inputId]);

    return (
        <BlockNoteEditor
            initialBlocks={blocks}
            onChange={setBlocks}
        />
    );
}

// Initialize the editor
function initializeEditor() {
    const editorContainer = document.getElementById('block-editor-root');
    if (editorContainer) {
        console.log('Block editor container found, initializing React...');
        const initialBlocks = editorContainer.dataset.initialBlocks
            ? JSON.parse(editorContainer.dataset.initialBlocks)
            : [];

        const root = createRoot(editorContainer);
        root.render(<LessonEditorApp initialBlocks={initialBlocks} />);
        console.log('Block editor initialized successfully');
    } else {
        console.warn('Block editor container (#block-editor-root) not found');
    }
}

// Handle both cases: DOM already loaded or still loading
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEditor);
} else {
    // DOM already loaded, initialize immediately
    initializeEditor();
}

export default LessonEditorApp;
