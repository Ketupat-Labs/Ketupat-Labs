import React from 'react';

export default function TextBlock({ block, onUpdate }) {
    const handleChange = (e) => {
        onUpdate(block.id, { content: e.target.value });
    };

    return (
        <div>
            <textarea
                value={block.content || ''}
                onChange={handleChange}
                placeholder="Start typing your text here..."
                className="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 resize-y"
                style={{ fontFamily: 'inherit' }}
            />
            <div className="text-xs text-gray-400 mt-1">
                Text Block • {(block.content || '').length} characters
            </div>
        </div>
    );
}
