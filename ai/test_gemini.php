<?php
require_once __DIR__ . '/config.php';

$url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key={$GEMINI_API_KEY}";

$payload = [
    "contents" => [
        ["parts" => [["text" => "Say hello from MyHomeVault in one short line."]]]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($response === false) {
    echo "CURL error: $error";
} else {
    echo "<pre>";
    echo htmlspecialchars($response);
    echo "</pre>";
}
?>
