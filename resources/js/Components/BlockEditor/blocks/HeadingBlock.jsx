import React from 'react';

export default function HeadingBlock({ block, onUpdate }) {
    const handleContentChange = (e) => {
        onUpdate(block.id, { content: e.target.value });
    };

    const handleLevelChange = (e) => {
        onUpdate(block.id, { level: parseInt(e.target.value) });
    };

    const level = block.level || 2;
    const HeadingTag = `h${level}`;

    return (
        <div>
            <div className="flex items-center gap-3 mb-2">
                <select
                    value={level}
                    onChange={handleLevelChange}
                    className="px-3 py-1 border border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                >
                    <option value="1">H1</option>
                    <option value="2">H2</option>
                    <option value="3">H3</option>
                </select>
                <span className="text-xs text-gray-500">Heading Level</span>
            </div>

            <input
                type="text"
                value={block.content || ''}
                onChange={handleContentChange}
                placeholder="Enter heading text..."
                className="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                style={{
                    fontSize: level === 1 ? '2rem' : level === 2 ? '1.5rem' : '1.25rem',
                    fontWeight: 'bold'
                }}
            />
        </div>
    );
}
