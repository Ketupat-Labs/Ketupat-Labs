import React, { useEffect } from 'react';
import { useCreateBlockNote } from '@blocknote/react';
import { BlockNoteView } from '@blocknote/mantine';
import '@blocknote/mantine/style.css';

export default function BlockNoteEditor({ initialBlocks = [], onChange }) {
    const editor = useCreateBlockNote({
        initialContent: initialBlocks.length ? initialBlocks : [
            { type: 'paragraph', content: 'Welcome to BlockNote!' },
            { type: 'paragraph', content: "Type '/' for commands or drag blocks to reorder." },
        ],
    });

    // Sync editor content to parent whenever it changes
    useEffect(() => {
        const handleChange = () => {
            const content = editor.document.getBlocks();
            if (onChange) {
                onChange(content);
            }
        };
        editor.onChange(handleChange);
        return () => editor.offChange(handleChange);
    }, [editor, onChange]);

    return <BlockNoteView editor={editor} />;
}
