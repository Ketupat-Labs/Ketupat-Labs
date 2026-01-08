import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

const MemoryGameBlock = ({ block, onUpdate, onDelete }) => {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
    } = useSortable({ id: block.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className="group relative bg-white border border-gray-200 rounded-lg p-4 mb-4 hover:shadow-md transition-shadow"
        >
            {/* Drag Handle */}
            <div
                {...attributes}
                {...listeners}
                className="absolute left-2 top-1/2 -translate-y-1/2 cursor-grab text-gray-400 hover:text-gray-600 p-2"
            >
                ⋮⋮
            </div>

            <div className="ml-8">
                <div className="flex justify-between items-center mb-2">
                    <span className="text-xs font-semibold uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-1 rounded">
                        🎮 Memory Game
                    </span>
                    <button
                        onClick={() => onDelete(block.id)}
                        className="text-gray-400 hover:text-red-500 transition-colors p-1"
                    >
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>

                <div className="space-y-3">
                    <input
                        type="text"
                        value={block.content?.title || ''}
                        onChange={(e) => onUpdate(block.id, {
                            content: { ...block.content, title: e.target.value }
                        })}
                        placeholder="Game Title (e.g. Vocabulary Match)"
                        className="w-full text-lg font-medium border-0 border-b border-gray-200 focus:border-purple-500 focus:ring-0 px-0 py-2 placeholder-gray-300"
                    />

                    <div className="bg-gray-50 p-4 rounded-md border border-gray-200 text-center">
                        <p className="text-sm text-gray-500 mb-2">Game Configuration</p>
                        <div className="flex justify-center gap-4 text-sm">
                            <label className="flex items-center gap-2">
                                <span className="text-gray-600">Grid:</span>
                                <select
                                    value={block.content?.gridSize || '4x4'}
                                    onChange={(e) => onUpdate(block.id, {
                                        content: { ...block.content, gridSize: e.target.value }
                                    })}
                                    className="border-gray-300 rounded text-sm focus:ring-purple-500 focus:border-purple-500"
                                >
                                    <option value="4x4">4x4</option>
                                    <option value="6x6">6x6</option>
                                </select>
                            </label>

                            <label className="flex items-center gap-2">
                                <span className="text-gray-600">Theme:</span>
                                <select
                                    value={block.content?.theme || 'animals'}
                                    onChange={(e) => onUpdate(block.id, {
                                        content: { ...block.content, theme: e.target.value }
                                    })}
                                    className="border-gray-300 rounded text-sm focus:ring-purple-500 focus:border-purple-500"
                                >
                                    <option value="animals">Animals</option>
                                    <option value="fruits">Fruits</option>
                                    <option value="shapes">Shapes</option>
                                </select>
                            </label>
                        </div>
                        <p className="text-xs text-gray-400 mt-2">
                            * Custom pairs configuration required in Activity Editor. Start with a preset for quick games.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default MemoryGameBlock;
