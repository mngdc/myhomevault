<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert the new user first
    $stmt = $pdo->prepare("INSERT INTO tbl_users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);
    $user_id = $pdo->lastInsertId();

    // Generate and assign API key
    $api_key = bin2hex(random_bytes(16)); // 32-character unique key
    $insertKey = $pdo->prepare("INSERT INTO api_keys (api_key, user_id) VALUES (?, ?)");
    $insertKey->execute([$api_key, $user_id]);

    // Store the key temporarily in session (optional)
    $_SESSION['api_key'] = $api_key;

    header("Location: login.php?registered=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MyHomeVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="icon" type="image/png" href="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_LARGE.png">
    <style>
        .bg {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        
        .register-card {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
            border: none;
            border-radius: 12px;
        }
        
        .logo-colored {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .logo-colored img {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }
        
        .logo-colored h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #555;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }
        
        .form-control:focus {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 0.2rem rgba(74, 108, 247, 0.25);
        }
        
        .btn-primary {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            background-color: #4a6cf7;
            border: none;
            margin-top: 0.5rem;
        }
        
        .btn-primary:hover {
            background-color: #3a5ce5;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .login-link a {
            color: #4a6cf7;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            margin-top: -0.5rem;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: #666;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .bg {
                padding: 15px;
            }
            
            .register-card {
                padding: 1.5rem !important;
            }
            
            .logo-colored h2 {
                font-size: 1.6rem;
            }
            
            h3 {
                font-size: 1.4rem;
            }
        }
        
        @media (max-width: 480px) {
            .logo-colored img {
                width: 70px;
            }
            
            .logo-colored h2 {
                font-size: 1.5rem;
            }
            
            h3 {
                font-size: 1.3rem;
            }
            
            .form-control {
                padding: 0.65rem 0.9rem;
            }
            
            .password-requirements {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body class="bg">
    <div class="container">
        <div class="card register-card p-4 shadow">
            <div class="logo-colored">
                <img src="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_MEDIUM.png" alt="MyHomeVault Logo">
                <h2>MyHomeVault</h2>
            </div>
            
            <h3 class="text-center mb-4">Create Your Account!</h3>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name:</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Full Name" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    <div class="password-requirements">
                            
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">SIGN UP</button>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
