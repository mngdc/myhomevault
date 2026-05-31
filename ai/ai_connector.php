<?php
// ai/ai_connector.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/cache_manager.php';
require_once __DIR__ . '/offline_fallback.php';
require_once __DIR__ . '/config.php'; // must define $GEMINI_API_KEY
require_once __DIR__ . '/../includes/db_connect.php'; // only for logging if desired

// Use global key only (Option A)
$api_key = $GEMINI_API_KEY ?? null;
if (empty($api_key)) {
    echo json_encode(["error" => "No global Gemini API key configured (config.php)."]);
    exit;
}

$user_input = trim($_POST['prompt'] ?? '');
if ($user_input === '') {
    echo json_encode(["error" => "⚠️ No input provided."]);
    exit;
}

// 🔍 Check cache
$cached = ai_cache_get($user_input);
if ($cached !== null) {
    echo json_encode([
        "mode" => "cached",
        "response" => $cached
    ]);
    exit;
}

// Model + endpoint
$model = "gemini-2.5-flash";
$url = "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$api_key}";
$data = [
    "contents" => [
        ["parts" => [["text" => $user_input]]]
    ]
];

// retry loop for transient failures
$maxAttempts = 3;
$timeoutSeconds = 25;
$response = false;
$error = '';
$httpCode = 0;

for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => $timeoutSeconds
    ]);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    @file_put_contents(__DIR__ . '/ai_debug.log',
        date('c') . " Attempt:$attempt HTTP:$httpCode CURL_ERR:'$error' RAW:" . ($response === false ? '[false]' : $response) . PHP_EOL,
        FILE_APPEND
    );

    // success if 2xx and non-empty body
    if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
        break;
    }

    // small backoff for next attempt (if not last)
    if ($attempt < $maxAttempts) sleep(pow(2, $attempt - 1));
}

// handle failure -> offline fallback
if ($response === false || empty($response) || $httpCode >= 500) {
    $offlineResponse = offline_ai_response($user_input);
    echo json_encode([
        "mode" => "offline",
        "response" => $offlineResponse,
        "http_code" => $httpCode,
        "curl_error" => $error
    ]);
    exit;
}

// decode response
$result = json_decode($response, true);
$jsonErr = json_last_error() !== JSON_ERROR_NONE ? json_last_error_msg() : null;
if ($jsonErr) {
    @file_put_contents(__DIR__ . '/ai_debug.log', date('c') . " JSON_ERROR: {$jsonErr}\n", FILE_APPEND);
}

// helper: find first text content in the typical Gemini structure
function find_text_in_response($data) {
    if (is_array($data)) {
        // candidates -> content -> parts -> text
        if (isset($data['candidates']) && is_array($data['candidates'])) {
            foreach ($data['candidates'] as $cand) {
                if (isset($cand['content']['parts']) && is_array($cand['content']['parts'])) {
                    foreach ($cand['content']['parts'] as $part) {
                        if (!empty($part['text'])) return $part['text'];
                    }
                }
            }
        }
        // fallback scan:
        foreach ($data as $v) {
            $found = find_text_in_response($v);
            if ($found !== null) return $found;
        }
    } elseif (is_string($data) && trim($data) !== '') {
        return $data;
    }
    return null;
}

$ai_text = find_text_in_response($result);

// If still empty, fallback offline
if (empty($ai_text)) {
    $offlineResponse = offline_ai_response($user_input);
    echo json_encode([
        "mode" => "offline",
        "response" => $offlineResponse,
        "decoded_response" => $result,
        "json_error" => $jsonErr
    ]);
    exit;
}

// Success
echo json_encode([
    "mode" => "online",
    "response" => $ai_text,
    "http_code" => $httpCode
]);

ai_cache_set($user_input, $ai_text);
exit;
?>
