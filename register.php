<?php
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO tbl_users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);
	
	//API Key Generation
	$api_key = bin2hex(random_bytes(16)); // generate unique key
	$insertKey = $pdo->prepare("INSERT INTO api_keys (api_key, user_id) VALUES (?, ?)");
	$insertKey->execute([$api_key, $user_id]);
	$_SESSION['api_key'] = $api_key; // optional: store in session
	//End API Key Generation


    header("Location: login.php?registered=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - MyHomeVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">

</head>
<body class="bg-light">
<div class="container mt-5 col-md-4">
    <div class="card p-4 shadow">
        <h3 class="text-center mb-3">Create Account</h3>
        <form method="POST">
            <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            <button class="btn btn-primary w-100">Register</button>
        </form>
        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
