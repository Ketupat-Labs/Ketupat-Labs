import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import TextBlock from './blocks/TextBlock';
import HeadingBlock from './blocks/HeadingBlock';
import YouTubeBlock from './blocks/YouTubeBlock';
import ImageBlock from './blocks/ImageBlock';

export default function BlockRenderer({ block, onUpdate, onDelete }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: block.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    const renderBlock = () => {
        switch (block.type) {
            case 'text':
                return <TextBlock block={block} onUpdate={onUpdate} />;
            case 'heading':
                return <HeadingBlock block={block} onUpdate={onUpdate} />;
            case 'youtube':
                return <YouTubeBlock block={block} onUpdate={onUpdate} />;
            case 'image':
                return <ImageBlock block={block} onUpdate={onUpdate} />;
            default:
                return <div className="text-red-500">Unknown block type: {block.type}</div>;
        }
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className="group relative bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 transition-all"
        >
            {/* Drag Handle */}
            <div
                {...attributes}
                {...listeners}
                className="absolute left-2 top-2 cursor-move opacity-0 group-hover:opacity-100 transition-opacity"
            >
                <svg className="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"></path>
                </svg>
            </div>

            {/* Delete Button */}
            <button
                onClick={() => onDelete(block.id)}
                className="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity text-red-500 hover:text-red-700 p-1"
                title="Delete block"
            >
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>

            {/* Block Content */}
            <div className="pl-6">
                {renderBlock()}
            </div>
        </div>
    );
}
