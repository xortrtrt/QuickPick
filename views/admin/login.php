<?php
session_start();
if (isset($_SESSION['error'])):
    echo '<div class="alert alert-danger">';
    echo htmlspecialchars($_SESSION['error']);
    echo '</div>';
    unset($_SESSION['error']);
endif;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick Admin Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 1000px;
            width: 90%;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .login-image {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 40px;
        }

        .login-image-content {
            text-align: center;
        }

        .login-image-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .login-image h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .login-image p {
            font-size: 14px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-form {
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-form h1 {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .login-form-subtitle {
            font-size: 14px;
            color: #718096;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #718096;
            background: none;
            border: none;
            font-size: 18px;
        }

        .toggle-password:hover {
            color: #2d3748;
        }

        .login-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .remember-forgot a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s;
        }

        .remember-forgot a:hover {
            color: #764ba2;
        }

        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
            }

            .login-image {
                display: none;
            }

            .login-form {
                padding: 40px 20px;
            }

            .login-form h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Image -->
        <div class="login-image">
            <div class="login-image-content">
                <div class="login-image-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h2>Admin Portal</h2>
                <p>Manage QuickPick with powerful tools and analytics</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="login-form">
            <h1>Welcome Back</h1>
            <p class="login-form-subtitle">Sign in to your admin account</p>


            <form method="POST" action="/controllers/admin/admin_login.php">
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="admin@quickpick.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="remember-forgot">
                    <label style="margin-bottom: 0;">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button type="submit" class="login-button">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div style="text-align: center; margin-top: 20px; color: #718096; font-size: 12px;">
                <p>Demo Credentials: admin@quickpick.com / password123</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = event.target.closest('.toggle-password');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<i class="fas fa-eye"></i>';
            }
        }
    </script>
</body>

</html>