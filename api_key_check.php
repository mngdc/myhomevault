<?php
include 'db_connect.php';

$api_key = $_GET['api_key'] ?? '';

if (!$api_key) {
    echo "API key missing!";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ?");
    $stmt->execute([$api_key]);
    if ($stmt->rowCount() === 0) {
        echo "Invalid API key!";
        exit;
    }
    echo "Valid API key!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
