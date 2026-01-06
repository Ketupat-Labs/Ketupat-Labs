import React from 'react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

const QuizBlock = ({ block, onUpdate, onDelete }) => {
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

    const addQuestion = () => {
        const currentQuestions = block.content?.questions || [];
        const newQuestions = [
            ...currentQuestions,
            { id: Date.now(), question: '', answers: ['', '', '', ''], correct: 0 }
        ];
        onUpdate(block.id, { content: { ...block.content, questions: newQuestions } });
    };

    const updateQuestion = (qIndex, field, value) => {
        const questions = [...(block.content?.questions || [])];
        questions[qIndex] = { ...questions[qIndex], [field]: value };
        onUpdate(block.id, { content: { ...block.content, questions } });
    };

    const updateAnswer = (qIndex, aIndex, value) => {
        const questions = [...(block.content?.questions || [])];
        const answers = [...questions[qIndex].answers];
        answers[aIndex] = value;
        questions[qIndex] = { ...questions[qIndex], answers };
        onUpdate(block.id, { content: { ...block.content, questions } });
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
                    <span className="text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                        ❓ Quiz Game
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

                <div className="space-y-4">
                    <input
                        type="text"
                        value={block.content?.title || ''}
                        onChange={(e) => onUpdate(block.id, {
                            content: { ...block.content, title: e.target.value }
                        })}
                        placeholder="Quiz Title (e.g. Pop Quiz 1)"
                        className="w-full text-lg font-medium border-0 border-b border-gray-200 focus:border-indigo-500 focus:ring-0 px-0 py-2 placeholder-gray-300"
                    />

                    {/* Simple Question List Preview (Simplified for Block View) */}
                    <div className="space-y-2">
                        {(block.content?.questions || []).map((q, idx) => (
                            <div key={q.id} className="bg-gray-50 p-3 rounded border border-gray-200">
                                <input
                                    type="text"
                                    value={q.question}
                                    onChange={(e) => updateQuestion(idx, 'question', e.target.value)}
                                    placeholder={`Question ${idx + 1}`}
                                    className="w-full text-sm bg-transparent border-none focus:ring-0 p-0 mb-2 font-medium"
                                />
                                <div className="grid grid-cols-2 gap-2">
                                    {q.answers.map((ans, aIdx) => (
                                        <input
                                            key={aIdx}
                                            type="text"
                                            value={ans}
                                            onChange={(e) => updateAnswer(idx, aIdx, e.target.value)}
                                            placeholder={`Option ${aIdx + 1}`}
                                            className={`w-full text-xs rounded border-gray-200 px-2 py-1 ${q.correct === aIdx ? 'border-green-400 bg-green-50' : ''}`}
                                        />
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>

                    <button
                        type="button"
                        onClick={addQuestion}
                        className="w-full py-2 border-2 border-dashed border-gray-300 rounded text-sm text-gray-500 hover:border-indigo-300 hover:text-indigo-600 transition-colors"
                    >
                        + Add Question
                    </button>
                </div>
            </div>
        </div>
    );
};

export default QuizBlock;
