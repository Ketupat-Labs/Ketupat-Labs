import React from 'react';

const BLOCK_TYPES = [
    { type: 'text', label: 'Text', icon: '📝', description: 'Rich text paragraph' },
    { type: 'heading', label: 'Heading', icon: '📌', description: 'Section heading' },
    { type: 'youtube', label: 'YouTube', icon: '🎥', description: 'Embed video' },
    { type: 'image', label: 'Image', icon: '🖼️', description: 'Upload or link image' },
];

export default function BlockSidebar({ onAddBlock }) {
    return (
        <div className="w-64 bg-white rounded-lg border-2 border-gray-200 p-4 sticky top-4 h-fit">
            <h3 className="text-lg font-bold text-gray-800 mb-4">Add Blocks</h3>
            <div className="space-y-2">
                {BLOCK_TYPES.map((blockType) => (
                    <button
                        key={blockType.type}
                        onClick={() => onAddBlock(blockType.type)}
                        className="w-full text-left p-3 rounded-lg border border-gray-300 hover:border-blue-500 hover:bg-blue-50 transition-all group"
                    >
                        <div className="flex items-start gap-3">
                            <span className="text-2xl">{blockType.icon}</span>
                            <div className="flex-1">
                                <div className="font-medium text-gray-800 group-hover:text-blue-600">
                                    {blockType.label}
                                </div>
                                <div className="text-xs text-gray-500">
                                    {blockType.description}
                                </div>
                            </div>
                        </div>
                    </button>
                ))}
            </div>

            <div className="mt-6 pt-6 border-t border-gray-200">
                <p className="text-xs text-gray-500">
                    💡 <strong>Tip:</strong> Drag blocks to reorder them
                </p>
            </div>
        </div>
    );
}
