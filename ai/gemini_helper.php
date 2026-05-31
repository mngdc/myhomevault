<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/offline_fallback.php';

function gemini_suggest($prompt) {
    global $GEMINI_API_KEY;

    // If offline or key missing, fallback
    if (!is_online() || empty($GEMINI_API_KEY)) {
        return offline_ai_response($prompt);
    }

    $model = "gemini-2.5-flash";
    $url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$GEMINI_API_KEY}";

    $data = [
        "contents" => [
            ["parts" => [["text" => $prompt]]]
        ]
    ];

    // --- Use cURL instead of file_get_contents ---
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || empty($response)) {
        // fallback to offline if cURL failed
        return "⚠️ Failed to connect to Gemini API. Using offline mode.\nError: {$error}\n\n" . offline_ai_response($prompt);
    }

    $result = json_decode($response, true);
    $ai_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

    if (empty($ai_text)) {
        return offline_ai_response($prompt);
    }

    return $ai_text;
}
?>
