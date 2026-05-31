<?php
session_start();
require_once 'includes/db_connect.php';
if (!isset($_SESSION['user_id'])) { header("Location: auth/login.php"); exit; }

$user_id = $_SESSION['user_id'];

// collect posted values (may be empty)
$posted = [
    'name' => trim($_POST['name'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'new_pass' => trim($_POST['new_pass'] ?? '')
];

// determine which columns exist in tbl_users
$cols = [];
try {
    $res = $pdo->query("DESCRIBE tbl_users")->fetchAll(PDO::FETCH_COLUMN);
    $cols = array_map('strtolower', $res);
} catch (Exception $e) {
    // If DESCRIBE fails, fallback to a safe set
    $cols = ['user_id','username','email','password','created_at'];
}

// handle avatar upload if provided
$avatar_path = null;
if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
    $dir = 'assets/uploads/users/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION) ?: 'jpg';
    $filename = $dir . 'user_' . $user_id . '_' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filename)) {
        $avatar_path = $filename;
    }
}

// build update dynamically based on existing columns
$set = [];
$params = [];

if (in_array('name', $cols) || in_array('username', $cols) || in_array('user_name', $cols)) {
    // prefer 'name' if present, otherwise 'username'
    if (in_array('name', $cols)) {
        $set[] = "name = ?";
        $params[] = $posted['name'];
    } elseif (in_array('username', $cols)) {
        $set[] = "username = ?";
        $params[] = $posted['name'];
    } elseif (in_array('user_name', $cols)) {
        $set[] = "user_name = ?";
        $params[] = $posted['name'];
    }
}

if (in_array('email', $cols)) {
    $set[] = "email = ?";
    $params[] = $posted['email'];
}

if (in_array('phone', $cols)) {
    $set[] = "phone = ?";
    $params[] = $posted['phone'];
}

if (in_array('address', $cols)) {
    $set[] = "address = ?";
    $params[] = $posted['address'];
}

if ($avatar_path) {
    // prefer avatar_path column name variations
    if (in_array('avatar_path', $cols)) {
        $set[] = "avatar_path = ?";
        $params[] = $avatar_path;
    } elseif (in_array('avatar', $cols)) {
        $set[] = "avatar = ?";
        $params[] = $avatar_path;
    } else {
        // if no column exists, skip saving path to DB (but file still uploaded)
    }
}

// password update: check columns
if (!empty($posted['new_pass'])) {
    $hash = password_hash($posted['new_pass'], PASSWORD_BCRYPT);
    if (in_array('password_hash', $cols)) {
        $set[] = "password_hash = ?";
        $params[] = $hash;
    } elseif (in_array('password', $cols)) {
        // some installs used the column 'password' to store hashed password
        $set[] = "password = ?";
        $params[] = $hash;
    } else {
        // no password column found — can't update
    }
}

// if nothing to update, redirect back
if (empty($set)) {
    header("Location: dashboard.php");
    exit;
}

// finalize query
$sql = "UPDATE tbl_users SET " . implode(", ", $set) . " WHERE user_id = ?";
$params[] = $user_id;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // update session username if appropriate
    if (!empty($posted['name'])) {
        $_SESSION['username'] = $posted['name'];
    }

    header("Location: dashboard.php");
    exit;
} catch (PDOException $e) {
    // Basic error output for debugging (you can log instead)
    echo "<h3>Update failed</h3><pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}
?>
