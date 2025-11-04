<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
        crossorigin="anonymous" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <title>QuickPick</title>
</head>

<style>
    @import url("https://fonts.googleapis.com/css?family=Nunito:wght@400;800&display=swap");

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    :root {
        --main-color: #6dd5ed;
        --secondary-color: #2193b0;
        --gradient: linear-gradient(135deg, var(--main-color), var(--secondary-color));
    }

    body {
        font-family: "Nunito", sans-serif;
        overflow-x: hidden;
    }

    .page-wrapper {
        position: relative;
        width: 100%;
        min-height: 100vh;
        background: #f5f5f5;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    /* Wavy blue section */
    .blue-section {
        position: absolute;
        right: 0;
        top: 0;
        width: 65%;
        height: 100%;
        background: #0099ff;
        clip-path: path("M 300 0 C 250 50, 200 100, 250 150 C 300 200, 200 250, 220 300 C 240 350, 280 400, 200 450 C 150 500, 250 550, 230 600 C 210 650, 180 700, 250 750 C 300 800, 220 850, 240 900 C 260 950, 280 980, 300 1000 L 2000 1000 L 2000 0 Z");
        z-index: 1;
    }

    .blue-section::before {
        content: "";
        position: absolute;
        left: -80px;
        top: 0;
        width: 150px;
        height: 100%;
        background: #0099ff;
        filter: blur(30px);
        opacity: 0.6;
    }

    /* Navigation */
    .navbar {
        position: absolute;
        top: 40px;
        right: 60px;
        z-index: 3;
    }

    .navbar .nav {
        display: flex;
        align-items: center;
        gap: 0;
    }

    .navbar .nav-link {
        color: white !important;
        font-size: 14px;
        font-weight: 500;
        letter-spacing: 0.5px;
        margin-left: 30px;
        transition: opacity 0.3s;
        white-space: nowrap;
    }

    .navbar .nav-link:hover {
        opacity: 0.8;
    }

    /* Mobile hamburger menu */
    .navbar-toggle {
        display: none;
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid white;
        color: white;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 16px;
        transition: all 0.3s;
    }

    .navbar-toggle:hover {
        background: rgba(255, 255, 255, 0.4);
    }

    .navbar.mobile-active .nav {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: 100%;
        right: 0;
        background: rgba(0, 153, 255, 0.95);
        padding: 15px;
        border-radius: 5px;
        min-width: 150px;
        gap: 10px;
        margin-top: 10px;
    }

    .navbar.mobile-active .nav-link {
        margin-left: 0;
        font-size: 13px;
        padding: 8px 10px;
        display: block;
    }

    /* Home tab */
    .home-tab {
        position: absolute;
        top: 0;
        left: 60px;
        background: #0099ff;
        color: white;
        padding: 20px 30px;
        clip-path: polygon(0 0, 100% 0, 100% 80%, 50% 100%, 0 80%);
        z-index: 3;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-decoration: none;
    }

    /* Login Container */
    .container {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);
        position: relative;
        overflow: hidden;
        width: 768px;
        max-width: 100%;
        min-height: 480px;
        z-index: 2;
    }

    .form-container {
        position: absolute;
        top: 0;
        height: 100%;
        transition: all 0.6s ease-in-out;
    }

    .sign-in-container {
        left: 0;
        width: 50%;
        z-index: 2;
    }

    .container.right-panel-active .sign-in-container {
        transform: translateX(100%);
    }

    .sign-up-container {
        left: 0;
        width: 50%;
        opacity: 0;
        z-index: 1;
    }

    .container.right-panel-active .sign-up-container {
        transform: translateX(100%);
        opacity: 1;
        z-index: 5;
        animation: show 0.6s;
    }

    @keyframes show {
        0%, 49.99% {
            opacity: 0;
            z-index: 1;
        }
        50%, 100% {
            opacity: 1;
            z-index: 5;
        }
    }

    .overlay-container {
        position: absolute;
        top: 0;
        left: 50%;
        width: 50%;
        height: 100%;
        overflow: hidden;
        transition: transform 0.6s ease-in-out;
        z-index: 100;
    }

    .container.right-panel-active .overlay-container {
        transform: translateX(-100%);
    }

    .overlay {
        background: var(--secondary-color);
        background: var(--gradient);
        background-repeat: no-repeat;
        background-size: cover;
        background-position: 0 0;
        color: #fff;
        position: relative;
        left: -100%;
        height: 100%;
        width: 200%;
        transform: translateX(0);
        transition: transform 0.6s ease-in-out;
    }

    .container.right-panel-active .overlay {
        transform: translateX(50%);
    }

    .overlay-panel {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 0 40px;
        text-align: center;
        top: 0;
        height: 100%;
        width: 50%;
        transform: translateX(0);
        transition: transform 0.6s ease-in-out;
    }

    .overlay-left {
        transform: translateX(-20%);
    }

    .container.right-panel-active .overlay-left {
        transform: translateX(0);
    }

    .overlay-right {
        right: 0;
        transform: translateX(0);
    }

    .container.right-panel-active .overlay-right {
        transform: translateX(20%);
    }

    form {
        background: #fff;
        display: flex;
        flex-direction: column;
        padding: 0 50px;
        height: 100%;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    h1 {
        font-weight: bold;
        margin: 0;
    }

    p {
        font-size: 14px;
        font-weight: 100;
        line-height: 20px;
        letter-spacing: 0.5px;
        margin: 20px 0 30px;
    }

    span {
        font-size: 12px;
    }

    a {
        color: #333;
        font-size: 14px;
        text-decoration: none;
        margin: 15px 0;
    }

    button {
        cursor: pointer;
        border-radius: 20px;
        border: 1px solid var(--main-color);
        background: var(--main-color);
        color: #fff;
        font-size: 12px;
        font-weight: bold;
        padding: 12px 45px;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: transform 80ms ease-out;
    }

    button:hover {
        background: var(--secondary-color);
    }

    button:active {
        transform: scale(0.95);
    }

    button:focus {
        outline: none;
    }

    button.ghost {
        background: transparent;
        border-color: #fff;
    }

    button.ghost:hover {
        background: #fff;
        color: var(--main-color);
    }

    input {
        background: #eee;
        border: none;
        padding: 12px 15px;
        margin: 8px 0;
        width: 100%;
        font-family: inherit;
        border-radius: 5px;
    }

    body.verify-active .sign-up-container {
        opacity: 1 !important;
        z-index: 5 !important;
        width: 100% !important;
        left: 0 !important;
        transform: none !important;
    }

    body.verify-active .overlay-container {
        display: none !important;
    }

    .password-input-wrapper {
        position: relative;
        width: 100%;
        margin: 8px 0;
    }

    .password-input-wrapper input {
        margin: 0;
        padding-right: 40px;
        width: 100%;
    }

    .password-toggle-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #666;
        font-size: 16px;
        transition: color 0.3s;
    }

    .password-toggle-icon:hover {
        color: var(--main-color);
    }

    .password-strength {
        display: flex;
        gap: 0.4rem;
        flex-direction: column;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
        padding: 0.6rem;
        border-radius: 8px;
        font-size: 0.7rem;
        background: #f8f8f8;
        width: 100%;
    }

    .password-strength-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
    }

    .password-strength-items {
        display: flex;
        flex-direction: column;
        list-style: none;
        font-size: 10px;
        gap: 0.2rem;
        margin: 0;
        padding: 0;
    }

    .password-strength-items li {
        display: flex;
        gap: 0.3rem;
        align-items: center;
        color: #999;
        font-size: 10px;
        transition: color 0.3s;
    }

    .password-strength-items li::before {
        content: "✗";
        color: #ccc;
        font-weight: bold;
        font-size: 12px;
        min-width: 12px;
    }

    .password-strength-items li.valid {
        color: #00d084;
    }

    .password-strength-items li.valid::before {
        content: "✓";
        color: #00d084;
    }

    .progress {
        height: 3px;
        width: 100%;
        background: #e0e0e0;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 3px;
        transition: all 0.3s ease-in-out;
        width: 0%;
    }

    .progress-bar-danger {
        background: #ff4757;
    }

    .progress-bar-warning {
        background: #ffa502;
    }

    .progress-bar-success {
        background: #00d084;
    }

    .label-danger {
        color: #ff4757;
        font-weight: bold;
        font-size: 10px;
    }

    .label-warning {
        color: #ffa502;
        font-weight: bold;
        font-size: 10px;
    }

    .label-success {
        color: #00d084;
        font-weight: bold;
        font-size: 10px;
    }

    @media (max-width: 992px) {
        .blue-section {
            width: 100%;
            height: 50%;
            top: 50%;
            clip-path: path("M 0 150 C 100 100, 200 120, 300 100 S 500 90, 600 110 S 800 100, 900 120 S 1100 110, 1200 100 S 1400 90, 1500 100 L 1500 1000 L 0 1000 Z");
        }

        .navbar {
            top: auto;
            bottom: 40px;
            right: 50%;
            transform: translateX(50%);
        }

        .navbar .nav-link {
            margin-left: 15px;
            margin-right: 15px;
        }

        .home-tab {
            left: 30px;
        }

        .container {
            width: 90%;
            max-width: 700px;
        }

        form {
            padding: 0 30px;
        }
    }

    @media (max-width: 767px) {
        .container {
            width: 100%;
            min-height: auto;
            border-radius: 15px;
        }

        .overlay-container {
            display: none;
        }

        .form-container {
            position: relative !important;
            width: 100% !important;
            height: auto !important;
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .sign-in-container {
            position: relative !important;
            left: 0 !important;
            width: 100% !important;
            z-index: 2 !important;
            margin-bottom: 20px;
        }

        .sign-up-container {
            position: relative !important;
            left: 0 !important;
            width: 100% !important;
            opacity: 1 !important;
            z-index: 2 !important;
            transform: none !important;
        }

        .sign-in-container::after {
            content: "";
            display: block;
            width: 80%;
            height: 2px;
            background: linear-gradient(to right, transparent, #ddd, transparent);
            margin: 20px auto;
        }

        form {
            padding: 30px 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        input {
            padding: 14px 15px;
            font-size: 16px;
        }

        button {
            padding: 14px 40px;
            font-size: 14px;
        }

        .mobile-view .sign-up-container {
            display: none;
        }

        .mobile-view.show-signup .sign-up-container {
            display: block;
        }

        .mobile-view.show-signup .sign-in-container {
            display: none;
        }

        .navbar {
            bottom: 20px;
            right: 20px;
            transform: none;
        }

        .navbar .nav {
            display: none;
        }

        .navbar.mobile-active .nav {
            display: flex;
        }

        .navbar-toggle {
            display: block;
        }

        .navbar .nav-link {
            font-size: 12px;
            margin-left: 0;
            margin-right: 0;
        }

        .password-strength {
            padding: 0.5rem;
            gap: 0.3rem;
        }

        .password-strength-items li {
            font-size: 9px;
        }

        .password-toggle-icon {
            font-size: 14px;
        }

        .home-tab {
            font-size: 12px;
            padding: 15px 20px;
            left: 10px;
        }
    }

    .mobile-show-signup,
    .mobile-show-signin {
        background: #6dd5ed !important;
        color: #fff !important;
        border: 1px solid #6dd5ed !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

</style>

<body>
    <div class="page-wrapper">
        <!-- Home Tab -->
        <a class="home-tab" href="/index.php">HOME</a>

        <!-- Blue wavy background -->
        <div class="blue-section"></div>

        <!-- Navigation -->
        <nav class="navbar" id="navbar">
            <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav" id="navMenu">
                <li class="nav-item">
                    <a class="nav-link" href="/aboutus.html">ABOUT US</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">CONTACT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/faqs.html">FAQ</a>
                </li>
            </ul>
        </nav>

        <!-- Login Container -->
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

                        <div class="password-input-wrapper">
                            <input id="signup-password" type="password" name="password" placeholder="Password" required />
                            <i class="fas fa-eye password-toggle-icon" id="toggleSignupPassword"></i>
                        </div>

                        <div class="password-strength">
                            <div class="password-strength-header">
                                <span style="font-size: 11px;">Password Strength:</span>
                                <span id="signup-strength-label"></span>
                            </div>
                            <div class="progress">
                                <div id="signup-password-strength" class="progress-bar" role="progressbar"></div>
                            </div>
                            <ul class="password-strength-items">
                                <li class="signup-low-upper-case">Lowercase &amp; Uppercase</li>
                                <li class="signup-one-number">Number (0-9)</li>
                                <li class="signup-one-special-char">Special character</li>
                                <li class="signup-eight-character">8 characters min</li>
                            </ul>
                        </div>

                        <div class="password-input-wrapper">
                            <input id="signup-confirm-password" type="password" name="confirm" placeholder="Confirm Password" required />
                            <i class="fas fa-eye password-toggle-icon" id="toggleSignupConfirmPassword"></i>
                        </div>

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
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile navbar toggle
        const navbarToggle = document.getElementById("navbarToggle");
        const navbar = document.getElementById("navbar");
        const navMenu = document.getElementById("navMenu");
        const navLinks = navMenu.querySelectorAll(".nav-link");

        navbarToggle.addEventListener("click", () => {
            navbar.classList.toggle("mobile-active");
        });

        // Close menu when a link is clicked
        navLinks.forEach(link => {
            link.addEventListener("click", () => {
                navbar.classList.remove("mobile-active");
            });
        });

        // Close menu when clicking outside
        document.addEventListener("click", (e) => {
            if (!navbar.contains(e.target)) {
                navbar.classList.remove("mobile-active");
            }
        });

        const signUpButton = document.getElementById("signUp");
        const signInButton = document.getElementById("signIn");
        const container = document.getElementById("container");

        function isMobile() {
            return window.innerWidth <= 767;
        }

        function setupMobileView() {
            if (isMobile()) {
                container.classList.add("mobile-view");

                if (!document.querySelector(".mobile-toggle")) {
                    const signInContainer = document.querySelector(".sign-in-container");
                    const signUpContainer = document.querySelector(".sign-up-container");

                    if (signInContainer && signUpContainer) {
                        const signInForm = signInContainer.querySelector("form");
                        if (signInForm && !signInForm.querySelector(".mobile-show-signup")) {
                            const showSignUpBtn = document.createElement("button");
                            showSignUpBtn.textContent = "Create Account";
                            showSignUpBtn.type = "button";
                            showSignUpBtn.className = "ghost mobile-show-signup";
                            showSignUpBtn.style.marginTop = "15px";
                            showSignUpBtn.onclick = () => {
                                container.classList.add("show-signup");
                            };
                            signInForm.appendChild(showSignUpBtn);
                        }

                        const signUpForm = signUpContainer.querySelector("form");
                        if (signUpForm && !signUpForm.querySelector(".mobile-show-signin")) {
                            const showSignInBtn = document.createElement("button");
                            showSignInBtn.textContent = "Already have an account?";
                            showSignInBtn.type = "button";
                            showSignInBtn.className = "ghost mobile-show-signin";
                            showSignInBtn.style.marginTop = "15px";
                            showSignInBtn.onclick = () => {
                                container.classList.remove("show-signup");
                            };
                            signUpForm.appendChild(showSignInBtn);
                        }
                    }
                }
            } else {
                container.classList.remove("mobile-view", "show-signup");

                const mobileButtons = document.querySelectorAll(
                    ".mobile-show-signup, .mobile-show-signin"
                );
                mobileButtons.forEach((btn) => btn.remove());
            }
        }

        if (signUpButton && signInButton && container) {
            signUpButton.addEventListener("click", () => {
                if (!isMobile()) {
                    container.classList.add("right-panel-active");
                }
            });

            signInButton.addEventListener("click", () => {
                if (!isMobile()) {
                    container.classList.remove("right-panel-active");
                }
            });
        }

        setupMobileView();

        let resizeTimer;
        window.addEventListener("resize", () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                setupMobileView();
            }, 250);
        });

        const toggleSignupPassword = document.getElementById("toggleSignupPassword");
        const signupPasswordInput = document.getElementById("signup-password");
        const toggleSignupConfirmPassword = document.getElementById("toggleSignupConfirmPassword");
        const signupConfirmPasswordInput = document.getElementById("signup-confirm-password");

        if (toggleSignupPassword && signupPasswordInput) {
            toggleSignupPassword.addEventListener("click", function () {
                const type =
                    signupPasswordInput.getAttribute("type") === "password"
                        ? "text"
                        : "password";
                signupPasswordInput.setAttribute("type", type);
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        }

        if (toggleSignupConfirmPassword && signupConfirmPasswordInput) {
            toggleSignupConfirmPassword.addEventListener("click", function () {
                const type =
                    signupConfirmPasswordInput.getAttribute("type") === "password"
                        ? "text"
                        : "password";
                signupConfirmPasswordInput.setAttribute("type", type);
                this.classList.toggle("fa-eye");
                this.classList.toggle("fa-eye-slash");
            });
        }

        const passwordStrengthBar = document.querySelector("#signup-password-strength");
        const passwordStrengthLabel = document.getElementById("signup-strength-label");

        if (signupPasswordInput && passwordStrengthBar && passwordStrengthLabel) {
            const rules = [
                {
                    name: "signup-low-upper-case",
                    pattern: /(?=.*[a-z])(?=.*[A-Z])/,
                },
                {
                    name: "signup-one-number",
                    pattern: /[0-9]/,
                },
                {
                    name: "signup-one-special-char",
                    pattern: /[!@#\$%\^&\*\(\)\[\]\-\+_=;:'",.<>\/\?\\|`~]/,
                },
                {
                    name: "signup-eight-character",
                    pattern: /.{8,}/,
                },
            ];

            const checkRule = (password, strength, { pattern, name }) => {
                const listItem = document.querySelector(`.${name}`);
                if (!listItem) return strength;

                if (password.match(pattern)) {
                    strength = strength + 1;
                    listItem.classList.add("valid");
                } else {
                    listItem.classList.remove("valid");
                }
                return strength;
            };

            const passwordStrengthProgressRule = [
                { maxStrength: 1, width: "25%", class: "danger", label: "Weak" },
                { maxStrength: 3, width: "60%", class: "warning", label: "Average" },
                { maxStrength: 4, width: "100%", class: "success", label: "Strong" },
            ];

            const makeProgressBar = (strength) => {
                if (strength === 0) {
                    passwordStrengthBar.className = "progress-bar";
                    passwordStrengthBar.style.width = "0%";
                    passwordStrengthLabel.innerText = "";
                    passwordStrengthLabel.className = "";
                } else {
                    const rule = passwordStrengthProgressRule.find(
                        (r) => strength <= r.maxStrength
                    );
                    if (rule) {
                        passwordStrengthBar.className =
                            "progress-bar progress-bar-" + rule.class;
                        passwordStrengthBar.style.width = rule.width;
                        passwordStrengthLabel.innerText = rule.label;
                        passwordStrengthLabel.className = "label-" + rule.class;
                    }
                }
            };

            function checkStrength(password) {
                let strength = 0;

                if (!password || password.length === 0) {
                    rules.forEach((rule) => {
                        const listItem = document.querySelector(`.${rule.name}`);
                        if (listItem) {
                            listItem.classList.remove("valid");
                        }
                    });
                    makeProgressBar(0);
                    return;
                }

                rules.forEach((rule) => {
                    strength = checkRule(password, strength, rule);
                });
                makeProgressBar(strength);
            }

            signupPasswordInput.addEventListener("keyup", function () {
                checkStrength(this.value);
            });

            signupPasswordInput.addEventListener("input", function () {
                checkStrength(this.value);
            });

            const signupForm = document.querySelector(".sign-up-container form");
            if (signupForm) {
                signupForm.addEventListener("submit", function (e) {
                    const password = signupPasswordInput.value;
                    const confirmPassword = signupConfirmPasswordInput.value;

                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert("Passwords do not match!");
                        return false;
                    }

                    let strength = 0;
                    rules.forEach((rule) => {
                        if (password.match(rule.pattern)) {
                            strength++;
                        }
                    });

                    if (strength < 3) {
                        e.preventDefault();
                        alert(
                            "Password is too weak. Please meet at least 3 of the requirements."
                        );
                        return false;
                    }
                });
            }
        }
    </script>

    <body class="<?php echo isset($_GET['verify']) ? 'verify-active' : ''; ?>">
    </body>

</html>