<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Password verified successfully
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username']; // Matches DB
        header("Location: ../dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyHomeVault</title>
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
        
        .login-card {
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
        
        .login {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 8px;
            background-color: #4a6cf7;
            border: none;
            margin-top: 0.5rem;
        }
        
        .login:hover {
            background-color: #3a5ce5;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
        }
        
        .forgot-password {
            color: #4a6cf7;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .create-account {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .create-account a {
            color: #4a6cf7;
            text-decoration: none;
            font-weight: 500;
        }
        
        .create-account a:hover {
            text-decoration: underline;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .bg {
                padding: 15px;
            }
            
            .login-card {
                padding: 1.5rem !important;
            }
            
            .logo-colored h2 {
                font-size: 1.6rem;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
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
        }
    </style>
</head>
<body class="bg">
    <div class="container">
        <div class="card login-card p-4 shadow">
            <div class="logo-colored">
                <img src="../assets/MyHomeVault Assets/LOGO/COLORED/mhvLOGO_C_MEDIUM.png" alt="MyHomeVault Logo">
                <h2>MyHomeVault</h2>
            </div>
            
            <h3 class="text-center mb-4">Welcome Back!</h3>
            
            <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary login">LOG IN</button>
            </form>
            
            <div class="create-account">
                Don't have an Account? <a href="register.php">Create One!</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
