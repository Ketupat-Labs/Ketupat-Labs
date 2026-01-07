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

// Function to initialize games (Exposed globally for manual triggers)
window.initializeGames = function () {
    const gameContainers = document.querySelectorAll('[data-game-block]');
    console.log('Game loader: Found', gameContainers.length, 'game containers');

    gameContainers.forEach(container => {
        // Prevent double initialization if root already exists
        if (container._reactRoot) {
            console.log('Game already initialized for this container, skipping re-creation.');
            // Optional: If you want to force re-render with new config, you could do:
            // container._reactRoot.render(...) here.
            // For now, we assume config doesn't change on the fly.
            return;
        }

        const gameType = container.dataset.gameType;
        const blockId = container.dataset.id; // Capture the block ID
        let gameConfig = {};
        
        try {
            gameConfig = JSON.parse(container.dataset.gameConfig || '{}');
        } catch (e) {
            console.error('Failed to parse game config:', e);
            return;
        }

        console.log('Loading game:', gameType, gameConfig, 'Block:', blockId);

        const GameComponent = gameComponents[gameType];
        if (GameComponent) {
            try {
                const root = createRoot(container);
                // Store root on element to prevent duplicate initialization
                container._reactRoot = root; 
                
                root.render(
                    <GameComponent
                        config={gameConfig}
                        onFinish={(results) => {
                             // Pass blockId to the handler
                            if (window.handleGameScore) {
                                window.handleGameScore(results, blockId);
                            }
                        }}
                    />
                );
                console.log('✓ Game loaded successfully:', gameType);
            } catch (err) {
                console.error('Error rendering game:', err);
            }
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
