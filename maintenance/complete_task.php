<?php
session_start();
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = intval($_POST['task_id'] ?? 0);
$notes = $_POST['notes'] ?? '';

if ($task_id <= 0) {
    header('Location: list.php?error=invalid');
    exit;
}

// Confirm ownership
$stmt = $pdo->prepare("SELECT * FROM tbl_maintenance_tasks WHERE task_id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header('Location: list.php?error=notfound');
    exit;
}

// Prevent double completion
if ($task['status'] === 'Completed') {
    header('Location: list.php?error=already_completed');
    exit;
}

// Mark as completed
$upd = $pdo->prepare("
    UPDATE tbl_maintenance_tasks 
    SET status = 'Completed', completed_date = CURDATE() 
    WHERE task_id = ? AND user_id = ?
");
$upd->execute([$task_id, $user_id]);

// Optional: maintenance log file
$logdir = __DIR__ . '/../assets/uploads/maintenance_logs/';
if (!is_dir($logdir)) mkdir($logdir, 0755, true);
$logfile = $logdir . 'item_maintenance_history_' . ($task['item_id'] ?? 'general') . '.txt';
$entry = date('Y-m-d H:i:s') . " | Task ID: {$task_id} | User: {$user_id} | Notes: " . str_replace(["\r","\n"], [' ', ' '], $notes) . PHP_EOL;
file_put_contents($logfile, $entry, FILE_APPEND);

// Handle recurring task generation
$freq = strtolower($task['frequency'] ?? '');
if (in_array($freq, ['monthly', 'quarterly', 'yearly'])) {
    $next_due = null;
    if ($freq === 'monthly') $next_due = (new DateTime($task['due_date'] ?: 'now'))->modify('+1 month')->format('Y-m-d');
    elseif ($freq === 'quarterly') $next_due = (new DateTime($task['due_date'] ?: 'now'))->modify('+3 months')->format('Y-m-d');
    elseif ($freq === 'yearly') $next_due = (new DateTime($task['due_date'] ?: 'now'))->modify('+1 year')->format('Y-m-d');

    // Avoid duplicate next task
    $check = $pdo->prepare("
        SELECT COUNT(*) FROM tbl_maintenance_tasks 
        WHERE user_id = ? AND item_id = ? AND task_description = ? AND status = 'Pending'
    ");
    $check->execute([$user_id, $task['item_id'], $task['task_description']]);
    $exists = $check->fetchColumn();

    if (!$exists && $next_due) {
        $stmt2 = $pdo->prepare("
            INSERT INTO tbl_maintenance_tasks 
            (user_id, item_id, task_description, due_date, frequency, status)
            VALUES (?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt2->execute([
            $user_id,
            $task['item_id'],
            $task['task_description'],
            $next_due,
            $freq
        ]);
    }
}

// ✅ Redirect with confirmation message
header('Location: list.php?completed=1');
exit;
?>
