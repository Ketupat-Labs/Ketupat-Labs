import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
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
    useSortable,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

// Import the CSS
import '../../../css/block-editor.css';

function SimpleBlockEditor({ initialBlocks = [], inputId = 'content_blocks_input' }) {
    const [blocks, setBlocks] = useState(initialBlocks);

    // Block types available
    const blockTypes = [
        { type: 'text', label: 'Teks', icon: '📝' },
        { type: 'heading', label: 'Tajuk (Header)', icon: '📌' },
        { type: 'image', label: 'Imej', icon: '🖼️' },
        { type: 'youtube', label: 'YouTube', icon: '▶️' },
        { type: 'game', label: 'Permainan Memori', icon: '🎮' },
        { type: 'quiz', label: 'Permainan Kuiz', icon: '❓' },
    ];

    // Add a new block
    const addBlock = (type) => {
        const defaultContent = type === 'game'
            ? JSON.stringify({ theme: 'animals', gridSize: 4 })
            : '';
        setBlocks([...blocks, { id: Date.now(), type, content: defaultContent }]);
    };

    // Update block content
    const updateBlock = (id, content) => {
        setBlocks(blocks.map(b => (b.id === id ? { ...b, content } : b)));
    };

    // Remove a block
    const deleteBlock = (id) => {
        setBlocks(blocks.filter(b => b.id !== id));
    };

    // Drag and drop sensors
    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    // Handle drag end
    const handleDragEnd = (event) => {
        const { active, over } = event;

        if (active.id !== over.id) {
            setBlocks((items) => {
                const oldIndex = items.findIndex((item) => item.id === active.id);
                const newIndex = items.findIndex((item) => item.id === over.id);
                return arrayMove(items, oldIndex, newIndex);
            });
        }
    };

    // Sync hidden input for form submission
    useEffect(() => {
        const input = document.getElementById(inputId);
        if (input) {
            input.value = JSON.stringify({ blocks });
        }
    }, [blocks, inputId]);

    // Render a single block based on type
    const renderBlock = (block) => {
        switch (block.type) {
            case 'heading':
                return (
                    <input
                        type="text"
                        value={block.content}
                        onChange={(e) => updateBlock(block.id, e.target.value)}
                        placeholder="Tajuk (Header)"
                        style={{ fontSize: '28px', fontWeight: '600' }}
                    />
                );
            case 'image':
                return (
                    <input
                        type="text"
                        value={block.content}
                        onChange={(e) => updateBlock(block.id, e.target.value)}
                        placeholder="URL Imej"
                    />
                );
            case 'youtube':
                return (
                    <input
                        type="text"
                        value={block.content}
                        onChange={(e) => updateBlock(block.id, e.target.value)}
                        placeholder="URL YouTube"
                    />
                );
            case 'game':
                const gameConfig = block.content ? JSON.parse(block.content) : {
                    mode: 'preset',
                    theme: 'animals',
                    gridSize: 4,
                    customPairs: []
                };

                // Ensure customPairs exists for backward compatibility
                if (!gameConfig.customPairs) {
                    gameConfig.customPairs = [];
                }

                // Ensure mode exists for backward compatibility  
                if (!gameConfig.mode) {
                    gameConfig.mode = 'preset';
                }

                const updateGameConfig = (updates) => {
                    updateBlock(block.id, JSON.stringify({ ...gameConfig, ...updates }));
                };

                const addPair = () => {
                    const newPairs = [...gameConfig.customPairs, {
                        id: Date.now(),
                        card1: '',
                        card2: ''
                    }];
                    updateGameConfig({ customPairs: newPairs });
                };

                const removePair = (id) => {
                    const newPairs = gameConfig.customPairs.filter(p => p.id !== id);
                    updateGameConfig({ customPairs: newPairs });
                };

                const updatePair = (id, field, value) => {
                    const newPairs = gameConfig.customPairs.map(p =>
                        p.id === id ? { ...p, [field]: value } : p
                    );
                    updateGameConfig({ customPairs: newPairs });
                };

                return (
                    <div className="game-config" style={{ paddingBottom: '20px' }}>
                        {/* Mode Selector */}
                        <div style={{ marginBottom: '15px' }}>
                            <label style={{ display: 'block', marginBottom: '5px', fontWeight: '600' }}>Mod:</label>
                            <select
                                value={gameConfig.mode || 'preset'}
                                onChange={(e) => updateGameConfig({ mode: e.target.value })}
                                style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #ddd' }}
                            >
                                <option value="preset">Tema Pratetap (Haiwan, Buah-buahan, Emoji)</option>
                                <option value="custom">Kad Tersuai (Kandungan Sendiri)</option>
                            </select>
                        </div>

                        {/* Preset Mode */}
                        {gameConfig.mode === 'preset' && (
                            <>
                                <div style={{ marginBottom: '10px' }}>
                                    <label style={{ display: 'block', marginBottom: '5px', fontWeight: '600' }}>Tema:</label>
                                    <select
                                        value={gameConfig.theme}
                                        onChange={(e) => updateGameConfig({ theme: e.target.value })}
                                        style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #ddd' }}
                                    >
                                        <option value="animals">Haiwan 🐶</option>
                                        <option value="fruits">Buah-buahan 🍎</option>
                                        <option value="emojis">Emoji 😀</option>
                                    </select>
                                </div>
                                <div>
                                    <label style={{ display: 'block', marginBottom: '5px', fontWeight: '600' }}>Saiz Grid:</label>
                                    <select
                                        value={gameConfig.gridSize}
                                        onChange={(e) => updateGameConfig({ gridSize: parseInt(e.target.value) })}
                                        style={{ width: '100%', padding: '8px', borderRadius: '4px', border: '1px solid #ddd' }}
                                    >
                                        <option value="4">4x4 (Mudah)</option>
                                        <option value="6">6x6 (Sederhana)</option>
                                    </select>
                                </div>
                            </>
                        )}

                        {/* Custom Mode */}
                        {gameConfig.mode === 'custom' && (
                            <div>
                                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                                    <label style={{ fontWeight: '600' }}>Pasangan Padanan:</label>
                                    <button
                                        type="button"
                                        onClick={addPair}
                                        style={{
                                            padding: '5px 10px',
                                            background: '#5FAD56',
                                            color: 'white',
                                            border: 'none',
                                            borderRadius: '4px',
                                            cursor: 'pointer',
                                            fontSize: '12px'
                                        }}
                                    >
                                        + Tambah Pasangan
                                    </button>
                                </div>

                                <div style={{
                                    height: gameConfig.customPairs.length >= 2 ? '150px' : 'auto',
                                    maxHeight: '200px',
                                    overflowY: gameConfig.customPairs.length >= 2 ? 'auto' : 'visible',
                                    border: '1px solid #e5e7eb',
                                    borderRadius: '4px',
                                    padding: '10px'
                                }}>
                                    {gameConfig.customPairs.length === 0 ? (
                                        <div style={{ textAlign: 'center', padding: '20px', color: '#9ca3af' }}>
                                            <p>Tiada pasangan lagi. Klik "+ Tambah Pasangan" untuk mencipta kad padanan.</p>
                                        </div>
                                    ) : (
                                        gameConfig.customPairs.map((pair, index) => (
                                            <div key={pair.id} style={{
                                                display: 'grid',
                                                gridTemplateColumns: '1fr 1fr auto',
                                                gap: '8px',
                                                marginBottom: '8px',
                                                padding: '8px',
                                                background: '#f9fafb',
                                                borderRadius: '4px'
                                            }}>
                                                <input
                                                    type="text"
                                                    value={pair.card1}
                                                    onChange={(e) => updatePair(pair.id, 'card1', e.target.value)}
                                                    placeholder={`Kad ${index + 1}A`}
                                                    style={{
                                                        padding: '6px',
                                                        border: '1px solid #ddd',
                                                        borderRadius: '4px',
                                                        fontSize: '14px'
                                                    }}
                                                />
                                                <input
                                                    type="text"
                                                    value={pair.card2}
                                                    onChange={(e) => updatePair(pair.id, 'card2', e.target.value)}
                                                    placeholder={`Kad ${index + 1}B`}
                                                    style={{
                                                        padding: '6px',
                                                        border: '1px solid #ddd',
                                                        borderRadius: '4px',
                                                        fontSize: '14px'
                                                    }}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => removePair(pair.id)}
                                                    style={{
                                                        padding: '6px 10px',
                                                        background: '#ef4444',
                                                        color: 'white',
                                                        border: 'none',
                                                        borderRadius: '4px',
                                                        cursor: 'pointer',
                                                        fontSize: '12px'
                                                    }}
                                                >
                                                    ✕
                                                </button>
                                            </div>
                                        ))
                                    )}
                                </div>

                                <p style={{ marginTop: '10px', marginBottom: '15px', fontSize: '11px', color: '#666' }}>
                                    💡 Tip: Setiap baris mencipta pasangan padanan. Pelajar perlu mencari kedua-dua kad.
                                </p>
                            </div>
                        )}

                        <p style={{ marginTop: '10px', fontSize: '12px', color: '#666', borderTop: '1px solid #e5e7eb', paddingTop: '10px' }}>
                            Pelajar akan melihat permainan memori interaktif
                        </p>
                    </div>
                );
            case 'quiz':
                const quizConfig = block.content ? JSON.parse(block.content) : {
                    questions: []
                };

                // Ensure questions array exists
                if (!quizConfig.questions) {
                    quizConfig.questions = [];
                }

                const updateQuizConfig = (updates) => {
                    updateBlock(block.id, JSON.stringify({ ...quizConfig, ...updates }));
                };

                const addQuestion = () => {
                    const newQuestions = [...quizConfig.questions, {
                        id: Date.now(),
                        question: '',
                        answers: ['', '', '', ''],
                        correctAnswer: 0
                    }];
                    updateQuizConfig({ questions: newQuestions });
                };

                const removeQuestion = (id) => {
                    const newQuestions = quizConfig.questions.filter(q => q.id !== id);
                    updateQuizConfig({ questions: newQuestions });
                };

                const updateQuestion = (id, field, value) => {
                    const newQuestions = quizConfig.questions.map(q =>
                        q.id === id ? { ...q, [field]: value } : q
                    );
                    updateQuizConfig({ questions: newQuestions });
                };

                const updateAnswer = (questionId, answerIndex, value) => {
                    const newQuestions = quizConfig.questions.map(q => {
                        if (q.id === questionId) {
                            const newAnswers = [...q.answers];
                            newAnswers[answerIndex] = value;
                            return { ...q, answers: newAnswers };
                        }
                        return q;
                    });
                    updateQuizConfig({ questions: newQuestions });
                };

                return (
                    <div className="quiz-config">
                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '15px' }}>
                            <label style={{ fontWeight: '600' }}>Soalan Kuiz:</label>
                            <button
                                type="button"
                                onClick={addQuestion}
                                style={{
                                    padding: '5px 10px',
                                    background: '#5FAD56',
                                    color: 'white',
                                    border: 'none',
                                    borderRadius: '4px',
                                    cursor: 'pointer',
                                    fontSize: '12px'
                                }}
                            >
                                + Tambah Soalan
                            </button>
                        </div>

                        <div style={{ maxHeight: '400px', overflowY: 'auto', border: '1px solid #e5e7eb', borderRadius: '4px', padding: '10px' }}>
                            {quizConfig.questions.length === 0 ? (
                                <div style={{ textAlign: 'center', padding: '20px', color: '#9ca3af' }}>
                                    <p>Tiada soalan lagi. Klik "+ Tambah Soalan" untuk mencipta soalan kuiz.</p>
                                </div>
                            ) : (
                                quizConfig.questions.map((question, qIndex) => (
                                    <div key={question.id} style={{
                                        marginBottom: '15px',
                                        padding: '12px',
                                        background: '#f9fafb',
                                        borderRadius: '4px',
                                        border: '1px solid #e5e7eb'
                                    }}>
                                        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' }}>
                                            <strong style={{ color: '#374151' }}>Soalan {qIndex + 1}</strong>
                                            <button
                                                type="button"
                                                onClick={() => removeQuestion(question.id)}
                                                style={{
                                                    padding: '4px 8px',
                                                    background: '#ef4444',
                                                    color: 'white',
                                                    border: 'none',
                                                    borderRadius: '4px',
                                                    cursor: 'pointer',
                                                    fontSize: '12px'
                                                }}
                                            >
                                                ✕ Padam
                                            </button>
                                        </div>

                                        <input
                                            type="text"
                                            value={question.question}
                                            onChange={(e) => updateQuestion(question.id, 'question', e.target.value)}
                                            placeholder="Masukkan soalan anda di sini..."
                                            style={{
                                                width: '100%',
                                                padding: '8px',
                                                marginBottom: '10px',
                                                border: '1px solid #ddd',
                                                borderRadius: '4px',
                                                fontSize: '14px',
                                                fontWeight: '500'
                                            }}
                                        />

                                        <div style={{ marginBottom: '10px' }}>
                                            <label style={{ display: 'block', fontSize: '12px', color: '#6b7280', marginBottom: '5px' }}>
                                                Pilihan Jawapan:
                                            </label>
                                            {question.answers.map((answer, aIndex) => (
                                                <div key={aIndex} style={{ display: 'flex', alignItems: 'center', marginBottom: '6px' }}>
                                                    <input
                                                        type="radio"
                                                        name={`correct-${question.id}`}
                                                        checked={question.correctAnswer === aIndex}
                                                        onChange={() => updateQuestion(question.id, 'correctAnswer', aIndex)}
                                                        style={{ marginRight: '8px', cursor: 'pointer' }}
                                                        title="Tandakan sebagai jawapan yang betul"
                                                    />
                                                    <span style={{ marginRight: '8px', fontWeight: '600', color: '#6b7280' }}>
                                                        {String.fromCharCode(65 + aIndex)}.
                                                    </span>
                                                    <input
                                                        type="text"
                                                        value={answer}
                                                        onChange={(e) => updateAnswer(question.id, aIndex, e.target.value)}
                                                        placeholder={`Jawapan ${String.fromCharCode(65 + aIndex)}`}
                                                        style={{
                                                            flex: 1,
                                                            padding: '6px',
                                                            border: '1px solid #ddd',
                                                            borderRadius: '4px',
                                                            fontSize: '13px'
                                                        }}
                                                    />
                                                </div>
                                            ))}
                                        </div>

                                        <p style={{ fontSize: '11px', color: '#6b7280', fontStyle: 'italic' }}>
                                            💡 Pilih butang radio bersebelahan dengan jawapan yang betul
                                        </p>
                                    </div>
                                ))
                            )}
                        </div>

                        <p style={{ marginTop: '10px', fontSize: '12px', color: '#666', borderTop: '1px solid #e5e7eb', paddingTop: '10px' }}>
                            Pelajar akan menjawab soalan-soalan ini dan melihat skor mereka
                        </p>
                    </div>
                );
            default:
                return (
                    <textarea
                        value={block.content}
                        onChange={(e) => updateBlock(block.id, e.target.value)}
                        placeholder="Tulis sesuatu..."
                        rows={3}
                    />
                );
        }
    };

    return (
        <div className="block-editor-container">
            {/* Sidebar */}
            <div className="block-editor-sidebar">
                <h3>Blok Kandungan</h3>
                {blockTypes.map((blockType) => (
                    <button
                        key={blockType.type}
                        type="button"
                        onClick={() => addBlock(blockType.type)}
                        className="block-type-btn"
                    >
                        <span className="icon">{blockType.icon}</span>
                        {blockType.label}
                    </button>
                ))}
            </div>

            {/* Editor Canvas */}
            <div className="block-editor-canvas">
                <div className="block-list">
                    {blocks.length === 0 ? (
                        <div className="editor-empty-state">
                            <h2>Mula mencipta</h2>
                            <p>Klik jenis blok dari bar sisi untuk bermula</p>
                        </div>
                    ) : (
                        <DndContext
                            sensors={sensors}
                            collisionDetection={closestCenter}
                            onDragEnd={handleDragEnd}
                        >
                            <SortableContext
                                items={blocks.map(b => b.id)}
                                strategy={verticalListSortingStrategy}
                            >
                                {blocks.map((block) => (
                                    <SortableBlock
                                        key={block.id}
                                        block={block}
                                        renderBlock={renderBlock}
                                        deleteBlock={deleteBlock}
                                    />
                                ))}
                            </SortableContext>
                        </DndContext>
                    )}
                </div>
            </div>
        </div>
    );
}

// Sortable Block Component
function SortableBlock({ block, renderBlock, deleteBlock }) {
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

    return (
        <div ref={setNodeRef} style={style} className="block-item">
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: '8px' }}>
                {/* Drag Handle */}
                <button
                    type="button"
                    {...attributes}
                    {...listeners}
                    style={{
                        cursor: 'grab',
                        background: 'transparent',
                        border: 'none',
                        padding: '4px',
                        color: '#9ca3af',
                        fontSize: '18px',
                        lineHeight: '1',
                        marginTop: '8px'
                    }}
                    title="Tarik untuk susun semula"
                >
                    ⋮⋮
                </button>

                {/* Block Content */}
                <div style={{ flex: 1 }}>
                    {renderBlock(block)}
                </div>
            </div>

            {/* Delete Button */}
            <div className="block-controls">
                <button
                    type="button"
                    onClick={() => deleteBlock(block.id)}
                    className="block-delete-btn"
                >
                    Padam
                </button>
            </div>
        </div>
    );
}

// Auto-mount when the DOM is ready
function initialize() {
    const container = document.getElementById('block-editor-root');
    if (!container) {
        console.warn('SimpleBlockEditor: #block-editor-root not found');
        return;
    }
    const initial = container.dataset.initialBlocks ? JSON.parse(container.dataset.initialBlocks) : [];
    const root = createRoot(container);
    root.render(<SimpleBlockEditor initialBlocks={initial} />);
    console.log('SimpleBlockEditor mounted');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}

export default SimpleBlockEditor;
