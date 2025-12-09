// Example: DocumentViewer.js
import React, { useState, useEffect } from 'react';
import HighlightTooltip from './highlightToolTip';
import AIModal from './AiModel.jsx';

function DocumentViewer({ documentText }) {
    const [selectedText, setSelectedText] = useState('');
    const [position, setPosition] = useState({ x: 0, y: 0 });
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [aiResponse, setAiResponse] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    // This runs when the user lifts their mouse
    const handleMouseUp = () => {
        const text = window.getSelection().toString().trim();
        if (text.length > 0) {
            const range = window.getSelection().getRangeAt(0);
            const rect = range.getBoundingClientRect();

            setSelectedText(text);
            setPosition({ x: rect.left + (rect.width / 2), y: rect.top + window.scrollY - 10 });
        } else {
            setSelectedText(''); // Hide tooltip if no text is selected
        }
    };

    // This is called when the "Ask AI" button is clicked
    const handleAskAI = async () => {
        setIsModalOpen(true);
        setIsLoading(true);
        setSelectedText(''); // Hide the tooltip

        // Your real API call to Laravel
        try {
            const response = await fetch('/api/ask-ai', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: selectedText })
            });
            const data = await response.json();

            setAiResponse(data.explanation); // Set the answer from Laravel
        } catch (error) {
            setAiResponse('Error: Could not fetch explanation.');
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div onMouseUp={handleMouseUp}>
            {/* The "Ask AI" button that appears on highlight */}
            <HighlightTooltip
                text={selectedText}
                position={position}
                onAskAI={handleAskAI}
            />

            {/* The modal that shows the answer */}
            <AIModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                content={aiResponse}
                isLoading={isLoading}
            />

            {/* Your document content */}
            <h1>Biology 101</h1>
            <p>{documentText}</p>
        </div>
    );
}
