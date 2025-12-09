// Example: HighlightTooltip.js
import React from 'react';

// Receives props from DocumentViewer.js
function HighlightToolTip({ text, position, onAskAI }) {
    if (!text) return null;

    const style = {
        position: 'absolute',
        top: `${position.y}px`,
        left: `${position.x}px`,
        transform: 'translateX(-50%) translateY(-100%)',
        /* Add your CSS from the demo */
        background: '#2c3e50',
        color: 'white',
        padding: '8px 15px',
        borderRadius: '6px',
        cursor: 'pointer',
        zIndex: 1000,
    };

    return (
        <div style={style} onClick={onAskAI}>
            Ask AI
        </div>
    );
}
