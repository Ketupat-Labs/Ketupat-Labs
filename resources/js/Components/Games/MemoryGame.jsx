import React, { useState, useEffect } from 'react';

const MemoryGame = ({ config = {} }) => {
    const {
        mode = 'preset',
        theme = 'animals',
        gridSize = 4,
        customPairs = [],
    } = config;

    // Card themes
    const themes = {
        animals: ['🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼'],
        fruits: ['🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍒'],
        emojis: ['😀', '😎', '🤩', '🥳', '😍', '🤗', '😇', '🤠'],
    };

    const [cards, setCards] = useState([]);
    const [flipped, setFlipped] = useState([]);
    const [matched, setMatched] = useState([]);
    const [moves, setMoves] = useState(0);
    const [gameWon, setGameWon] = useState(false);
    const [actualGridSize, setActualGridSize] = useState(4);

    // Initialize game
    useEffect(() => {
        initializeGame();
    }, [mode, theme, gridSize, customPairs]);

    const initializeGame = () => {
        let cardPairs = [];
        let calculatedGridSize = gridSize;

        if (mode === 'custom' && customPairs && customPairs.length > 0) {
            // Filter out empty pairs
            const validPairs = customPairs.filter(p => p.card1.trim() && p.card2.trim());

            if (validPairs.length === 0) {
                // No valid pairs, show empty state
                setCards([]);
                return;
            }

            // Create card pairs from custom content
            validPairs.forEach((pair, index) => {
                cardPairs.push({ id: `${index}-a`, content: pair.card1, pairId: index });
                cardPairs.push({ id: `${index}-b`, content: pair.card2, pairId: index });
            });

            // Calculate grid size based on number of cards
            const totalCards = cardPairs.length;
            calculatedGridSize = Math.ceil(Math.sqrt(totalCards));

            // Shuffle cards
            cardPairs = cardPairs.sort(() => Math.random() - 0.5);
        } else {
            // Preset mode
            const icons = themes[theme] || themes.animals;
            const pairsNeeded = (gridSize * gridSize) / 2;
            const selectedIcons = icons.slice(0, pairsNeeded);

            // Create pairs and shuffle
            selectedIcons.forEach((icon, index) => {
                cardPairs.push({ id: `${index}-a`, content: icon, pairId: index });
                cardPairs.push({ id: `${index}-b`, content: icon, pairId: index });
            });

            cardPairs = cardPairs.sort(() => Math.random() - 0.5);
            calculatedGridSize = gridSize;
        }

        setCards(cardPairs);
        setActualGridSize(calculatedGridSize);
        setFlipped([]);
        setMatched([]);
        setMoves(0);
        setGameWon(false);
    };

    const handleCardClick = (index) => {
        // Ignore if card already flipped or matched
        if (flipped.includes(index) || matched.includes(index)) return;

        // Ignore if already 2 cards flipped
        if (flipped.length === 2) return;

        const newFlipped = [...flipped, index];
        setFlipped(newFlipped);

        // Check for match when 2 cards are flipped
        if (newFlipped.length === 2) {
            setMoves(moves + 1);
            const [first, second] = newFlipped;

            if (cards[first].pairId === cards[second].pairId) {
                // Match found!
                const newMatched = [...matched, first, second];
                setMatched(newMatched);
                setFlipped([]);

                // Check if game is won
                if (newMatched.length === cards.length) {
                    setGameWon(true);
                }
            } else {
                // No match - flip back after delay
                setTimeout(() => {
                    setFlipped([]);
                }, 1000);
            }
        }
    };

    // Empty state for custom mode with no valid pairs
    if (cards.length === 0) {
        return (
            <div className="memory-game-container p-6 bg-gradient-to-br from-purple-50 to-blue-50 rounded-lg border-2 border-purple-200">
                <div className="text-center py-12">
                    <p className="text-xl text-gray-600">
                        ⚠️ No valid card pairs configured. Please add matching pairs in the editor.
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="memory-game-container p-6 bg-gradient-to-br from-purple-50 to-blue-50 rounded-lg border-2 border-purple-200">
            {/* Header */}
            <div className="flex justify-between items-center mb-6">
                <h3 className="text-2xl font-bold text-purple-700">🎮 Memory Game</h3>
                <div className="flex gap-4">
                    <div className="bg-white px-4 py-2 rounded-lg shadow">
                        <span className="text-gray-600 text-sm">Moves:</span>
                        <span className="ml-2 font-bold text-purple-600">{moves}</span>
                    </div>
                    <button
                        onClick={initializeGame}
                        className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition"
                    >
                        🔄 Restart
                    </button>
                </div>
            </div>

            {/* Game Won Message */}
            {gameWon && (
                <div className="mb-4 p-4 bg-green-100 border-2 border-green-400 rounded-lg text-center">
                    <p className="text-2xl font-bold text-green-700">
                        🎉 Congratulations! You won in {moves} moves! 🎉
                    </p>
                </div>
            )}

            {/* Game Grid */}
            <div
                className="grid gap-3 mx-auto"
                style={{
                    gridTemplateColumns: `repeat(${actualGridSize}, minmax(0, 1fr))`,
                    maxWidth: `${actualGridSize * 100}px`,
                }}
            >
                {cards.map((card, index) => {
                    const isFlipped = flipped.includes(index) || matched.includes(index);
                    const isMatched = matched.includes(index);
                    const isCustom = mode === 'custom';

                    return (
                        <div
                            key={card.id}
                            onClick={() => handleCardClick(index)}
                            className={`
                aspect-square flex items-center justify-center
                rounded-lg cursor-pointer transition-all duration-300 transform
                ${isFlipped ? 'bg-white' : 'bg-purple-400 hover:bg-purple-500'}
                ${isMatched ? 'bg-green-200 border-2 border-green-400' : 'border-2 border-purple-300'}
                ${!isFlipped && !isMatched ? 'hover:scale-105' : ''}
                shadow-lg p-2
                ${isCustom ? 'text-sm font-semibold' : 'text-4xl'}
              `}
                            style={{
                                wordWrap: 'break-word',
                                overflow: 'hidden',
                                textAlign: 'center',
                            }}
                        >
                            {isFlipped ? card.content : '❓'}
                        </div>
                    );
                })}
            </div>

            {/* Instructions */}
            <div className="mt-6 p-4 bg-white rounded-lg border border-purple-200">
                <p className="text-sm text-gray-600 text-center">
                    Click on cards to flip them. Find matching pairs to win the game!
                </p>
            </div>
        </div>
    );
};

export default MemoryGame;
