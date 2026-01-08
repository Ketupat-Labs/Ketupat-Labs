<?php

require __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
$model = $_ENV['OPENAI_MODEL'] ?? 'gpt-3.5-turbo';

echo "Testing OpenAI API Connection...\n";
echo "API Key: " . substr($apiKey, 0, 20) . "..." . substr($apiKey, -10) . "\n";
echo "Model: $model\n\n";

if (strlen($apiKey) < 20) {
    die("ERROR: OPENAI_API_KEY is too short or missing!\n");
}

$ch = curl_init('https://api.openai.com/v1/chat/completions');

$postData = json_encode([
    'model' => $model,
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
        ['role' => 'user', 'content' => 'Say "Hello, I am working!" in one sentence.']
    ],
    'temperature' => 0.7,
    'max_tokens' => 50,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

echo "Sending request to OpenAI...\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "\n--- RESPONSE ---\n";
echo "HTTP Code: $httpCode\n";

if ($curlError) {
    echo "cURL Error: $curlError\n";
    die();
}

echo "Response:\n";
echo $response . "\n\n";

$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['choices'][0]['message']['content'])) {
    echo "✓ SUCCESS! OpenAI API is working!\n";
    echo "AI Response: " . $data['choices'][0]['message']['content'] . "\n";
} else {
    echo "✗ FAILED!\n";
    if (isset($data['error'])) {
        echo "Error: " . json_encode($data['error'], JSON_PRETTY_PRINT) . "\n";
    }
}
