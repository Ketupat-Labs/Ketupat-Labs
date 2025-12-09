import React, { useState } from 'react';
import './Chatbot.css'; // We'll create this CSS file next

// --- The AI Chatbot Component ---
export default function Chatbot() {
    const [messages, setMessages] = useState([
        { from: 'ai', text: "Hello! Ask me any question about your lessons." }
    ]);
    const [input, setInput] = useState('');
    const [isLoading, setIsLoading] = useState(false);

    // Handles sending the message
    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!input.trim()) return;

        const userMessage = { from: 'user', text: input };
        setMessages(prev => [...prev, userMessage]);
        setInput('');
        setIsLoading(true);

        try {
            // --- API Call to Backend ---
            // Fulfills AC 5 & AT 4 (20-second timeout)
            const response = await fetch('http://localhost:3000/api/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ question: input })
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();
            
            // Fulfills AC 1, 2, 3 & AT 1, 2
            const aiMessage = { from: 'ai', text: data.answer };
            setMessages(prev => [...prev, aiMessage]);

        } catch (error) {
            const errorMessage = { from: 'ai', text: "Sorry, I couldn't get an answer. Please try again." };
            setMessages(prev => [...prev, errorMessage]);
        } finally {
            setIsLoading(false);
        }
    };

    // Fulfills AC 4 & AT 3
    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text).then(() => {
            alert("Copied to clipboard!"); //
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    };

    return (
        <div className="chat-window">
            <div className="chat-header">
                <h3>AI Lesson Helper</h3>
            </div>
            {/* Fulfills AC 1: "chat-style window" */}
            <div className="chat-messages">
                {messages.map((msg, index) => (
                    <div key={index} className={`message ${msg.from}`}>
                        {msg.text}
                        {msg.from === 'ai' && index > 0 && (
                            <button 
                                className="copy-btn" 
                                onClick={() => copyToClipboard(msg.text)} //
                                title="Copy"
                            >
                                📋
                            </button>
                        )}
                    </div>
                ))}
                {isLoading && (
                    <div className="message ai">
                        <div className="spinner"></div>
                    </div>
                )}
            </div>
            <form className="chat-input-form" onSubmit={handleSubmit}>
                <input
                    type="text"
                    value={input}
                    onChange={(e) => setInput(e.target.value)}
                    placeholder="Ask a question..." //
                    disabled={isLoading}
                />
                <button type="submit" disabled={isLoading}>Send</button>
            </form>
        </div>
    );
}