// In one of your .js files
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('question-form');
    const input = document.getElementById('question-input');
    const answerArea = document.getElementById('answer-area');

    form.addEventListener('submit', (event) => {
        event.preventDefault(); // Stop the page from reloading

        const questionText = input.value;
        if (questionText.trim() === '') return;

        // 1. Display the user's question
        displayUserMessage(questionText);

        // 2. Send the question to your Laravel backend (API)
        sendQuestionToApi(questionText);

        // 3. Clear the input
        input.value = '';
    });

    function displayUserMessage(message) {
        // Code to add the user's question to the 'answer-area'
        answerArea.innerHTML += `
            <div class="flex justify-end">
                <div class="bg-gray-200 text-gray-800 p-3 rounded-lg max-w-xs">
                    <p>${message}</p>
                </div>
            </div>`;
        // Scroll to bottom
        answerArea.scrollTop = answerArea.scrollHeight;
    }

    async function sendQuestionToApi(question) {
        // This is just an example. You need to create this API route in Laravel.
        const response = await fetch('/api/ask-question', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // Important for Laravel
            },
            body: JSON.stringify({ question: question })
        });

        const data = await response.json();

        // 3. Display the AI's answer
        displayAiMessage(data.answer);
    }

    function displayAiMessage(message) {
        // Code to add the AI's answer to the 'answer-area'
         answerArea.innerHTML += `
            <div class="flex">
                <div class="bg-blue-500 text-white p-3 rounded-lg max-w-xs">
                    <p>${message}</p>
                </div>
            </div>`;
        // Scroll to bottom
        answerArea.scrollTop = answerArea.scrollHeight;
    }
});
