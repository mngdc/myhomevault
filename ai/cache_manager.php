<?php
function ai_cache_get($prompt)
{
    $hash = md5($prompt);
    $file = __DIR__ . "/cache/{$hash}.json";

    if (!file_exists($file)) return null;

    $data = json_decode(file_get_contents($file), true);

    // cache expires in 24 hours
    if (time() - $data['time'] > 86400) return null;

    return $data['response'];
}

function ai_cache_set($prompt, $response)
{
    $hash = md5($prompt);
    $file = __DIR__ . "/cache/{$hash}.json";

    file_put_contents($file, json_encode([
        'time' => time(),
        'response' => $response
    ], JSON_PRETTY_PRINT));
}
?>
