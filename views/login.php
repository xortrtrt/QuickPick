<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
        crossorigin="anonymous" />
    <link rel="stylesheet" href="/assets/css/login.css" />
    <title>QuickPick</title>
</head>

<body>
    <div class="container" id="container">

        <!-- SIGN UP FORM -->
        <?php if (!isset($_GET['verify'])): ?>
            <div class="form-container sign-up-container">
                <form method="POST" action="/controllers/auth/register.php">
                    <h1>Sign Up</h1>
                    <input type="text" name="name" placeholder="Name" required />

                    <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                        <input type="text" name="countryCode" value="+63" maxlength="4"
                            style="width: 60px; text-align: center;" readonly />
                        <input type="text" name="phone" placeholder="9XXXXXXXXX" maxlength="10"
                            pattern="9[0-9]{9}" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>

                    <input type="password" name="password" placeholder="Password" required />
                    <input type="password" name="confirm" placeholder="Confirm Password" required />
                    <input type="hidden" name="action" value="signup" />
                    <button type="submit">Sign Up</button>
                </form>
            </div>

            <!-- SIGN IN FORM -->
            <div class="form-container sign-in-container">
                <form method="POST" action="/controllers/auth/login.php">
                    <h1>Sign In</h1>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                        <input type="text" name="countryCode" value="+63" maxlength="4"
                            style="width: 60px; text-align: center;" readonly />
                        <input type="text" name="phone" placeholder="9XXXXXXXXX" maxlength="10"
                            pattern="9[0-9]{9}" required
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>

                    <input type="password" name="password" placeholder="Password" required />
                    <input type="hidden" name="action" value="signin" />
                    <a href="#">Forgot your password?</a>
                    <button type="submit">Sign In</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- VERIFY OTP FORM -->
        <?php if (isset($_GET['verify'])): ?>
            <div class="form-container sign-up-container" style="text-align:center;">
                <form method="POST" action="/controllers/auth/verify_otp.php">
                    <h1>Verify OTP</h1>
                    <p style="color:#666;">Please enter the 6-digit code sent to your phone.</p>

                    <input
                        type="text"
                        name="otp"
                        maxlength="6"
                        pattern="\d{6}"
                        required
                        placeholder="Enter OTP"
                        style="text-align:center; font-size:18px; letter-spacing:3px;" />

                    <input type="hidden" name="action" value="verify_otp" />
                    <button type="submit">Verify</button>
                </form>

                <form method="POST" action="/controllers/auth/register.php" style="margin-top:10px;">
                    <button type="submit" class="ghost" style="background:#6c757d;">Resend OTP</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- OVERLAY -->
        <?php if (!isset($_GET['verify'])): ?>
            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1>Welcome Back!</h1>
                        <p>Please login with your personal info</p>
                        <button class="ghost" id="signIn">Sign In</button>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1>Hello, Friend!</h1>
                        <p>Enter your personal details and start your journey with us</p>
                        <button class="ghost" id="signUp">Sign Up</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
    <script src="../assets/js/login.js"></script>

    <body class="<?php echo isset($_GET['verify']) ? 'verify-active' : ''; ?>">
    </body>

</html>