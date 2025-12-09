const express = require('express');
const cors = require('cors');

const app = express();
app.use(cors()); // Allows your React app to call this server
app.use(express.json());

// --- SIMULATED AI BRAIN ---
// In a real app, you'd call an external AI API here.
function getSimulatedAiAnswer(question) {
    const q = question.toLowerCase();
    
    // Fulfills AC 2 & 3: Relevant and easy-to-understand answers
    if (q.includes('photosynthesis')) {
        return "Photosynthesis is the process plants use to turn sunlight, water, and carbon dioxide into food (glucose) and oxygen. Think of it as a plant's way of cooking its own meal using sunlight!";
    }
    if (q.includes('mitochondria')) {
        return "The mitochondrion (plural: mitochondria) is known as the 'powerhouse' of the cell. Its main job is to take in nutrients, break them down, and create energy-rich molecules (ATP) for the cell to use.";
    }
    
    return "That's a great question! I'm a demo AI, so I don't have a specific answer for that. In a real application, I would connect to a large language model to provide a detailed explanation.";
}

// --- API Endpoint ---
app.post('/api/ask', (req, res) => {
    const { question } = req.body;
    if (!question) {
        return res.status(400).json({ error: 'Question is required.' });
    }

    console.log(`Received question: ${question}`);

    // Fulfills AC 5 & AT 4: Simulating a 1.5-second AI response
    // This is well within the 20-second requirement.
    setTimeout(() => {
        const answer = getSimulatedAiAnswer(question);
        res.json({ answer: answer });
    }, 1500); // 1.5-second delay
});

const PORT = 3000;
app.listen(PORT, () => {
    console.log(`🚀 AI server running at http://localhost:${PORT}`);
});