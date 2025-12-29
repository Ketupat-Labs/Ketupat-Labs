import React from 'react';
import { createRoot } from 'react-dom/client';
import MemoryGame from './Components/Games/MemoryGame';
import QuizGame from './Components/Games/QuizGame';

console.log('🎮 Game loader script loaded!');

// Game component mapping
const gameComponents = {
    'memory': MemoryGame,
    'quiz': QuizGame,
};

// Function to initialize games
function initializeGames() {
    const gameContainers = document.querySelectorAll('[data-game-block]');
    console.log('Game loader: Found', gameContainers.length, 'game containers');

    gameContainers.forEach(container => {
        const gameType = container.dataset.gameType;
        const gameConfig = JSON.parse(container.dataset.gameConfig || '{}');

        console.log('Loading game:', gameType, gameConfig);

        const GameComponent = gameComponents[gameType];
        if (GameComponent) {
            const root = createRoot(container);
            root.render(<GameComponent config={gameConfig} />);
            console.log('✓ Game loaded successfully:', gameType);
        } else {
            console.warn('✗ Unknown game type:', gameType);
        }
    });

    if (gameContainers.length === 0) {
        console.warn('No game containers found on page');
    }
}

// Initialize games when DOM is ready
console.log('🎮 Checking document ready state:', document.readyState);
if (document.readyState === 'loading') {
    console.log('🎮 DOM still loading, adding event listener');
    document.addEventListener('DOMContentLoaded', initializeGames);
} else {
    console.log('🎮 DOM already loaded, running immediately');
    initializeGames();
}
