import React, { useState } from 'react';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import BlockRenderer from './BlockRenderer';
import BlockSidebar from './BlockSidebar';

export default function BlockEditor({ initialBlocks = [], onChange }) {
    const [blocks, setBlocks] = useState(initialBlocks.length > 0 ? initialBlocks : [
        { id: '1', type: 'text', content: '' }
    ]);

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    const handleDragEnd = (event) => {
        const { active, over } = event;

        if (active.id !== over.id) {
            setBlocks((items) => {
                const oldIndex = items.findIndex(item => item.id === active.id);
                const newIndex = items.findIndex(item => item.id === over.id);
                const newBlocks = arrayMove(items, oldIndex, newIndex);
                onChange(newBlocks);
                return newBlocks;
            });
        }
    };

    const addBlock = (type) => {
        const newBlock = {
            id: `block-${Date.now()}`,
            type,
            let content = '';
            if(type === 'heading') content = '';
        else if (type === 'youtube') content = '';
    else if (type === 'image') content = '';
    else if (type === 'memory') content = { title: '', gridSize: '4x4', theme: 'animals' };
    else if (type === 'quiz') content = { title: '', questions: [] };

    const newBlock = {
        id: `block-${Date.now()}`,
        type,
        content,
    };

    const newBlocks = [...blocks, newBlock];
    setBlocks(newBlocks);
    onChange(newBlocks);
};

const updateBlock = (id, updates) => {
    const newBlocks = blocks.map(block =>
        block.id === id ? { ...block, ...updates } : block
    );
    setBlocks(newBlocks);
    onChange(newBlocks);
};

const deleteBlock = (id) => {
    const newBlocks = blocks.filter(block => block.id !== id);
    setBlocks(newBlocks);
    onChange(newBlocks);
};

return (
    <div className="flex gap-6">
        {/* Main Editor Area */}
        <div className="flex-1 bg-white rounded-lg border-2 border-gray-200 p-6 min-h-[600px]">
            <DndContext
                sensors={sensors}
                collisionDetection={closestCenter}
                onDragEnd={handleDragEnd}
            >
                <SortableContext
                    items={blocks.map(b => b.id)}
                    strategy={verticalListSortingStrategy}
                >
                    <div className="space-y-4">
                        {blocks.map((block) => (
                            <BlockRenderer
                                key={block.id}
                                block={block}
                                onUpdate={updateBlock}
                                onDelete={deleteBlock}
                            />
                        ))}
                    </div>
                </SortableContext>
            </DndContext>

            {blocks.length === 0 && (
                <div className="text-center text-gray-400 py-12">
                    <p className="text-lg">Start building your lesson</p>
                    <p className="text-sm">Drag blocks from the sidebar to get started</p>
                </div>
            )}
        </div>

        {/* Sidebar with Block Types */}
        <BlockSidebar onAddBlock={addBlock} />
    </div>
);
}
