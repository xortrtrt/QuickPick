const signUpButton = document.getElementById("signUp");
const signInButton = document.getElementById("signIn");
const container = document.getElementById("container");

// Check if we're on mobile
function isMobile() {
  return window.innerWidth <= 767;
}

// Handle mobile view toggling
function setupMobileView() {
  if (isMobile()) {
    container.classList.add("mobile-view");

    // Create mobile toggle buttons if they don't exist
    if (!document.querySelector(".mobile-toggle")) {
      const signInContainer = document.querySelector(".sign-in-container");
      const signUpContainer = document.querySelector(".sign-up-container");

      if (signInContainer && signUpContainer) {
        // Add "Show Sign Up" button to sign-in form
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

        // Add "Show Sign In" button to sign-up form
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

    // Remove mobile toggle buttons on desktop
    const mobileButtons = document.querySelectorAll(
      ".mobile-show-signup, .mobile-show-signin"
    );
    mobileButtons.forEach((btn) => btn.remove());
  }
}

// Desktop sliding animation
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

// Initialize on page load
setupMobileView();

// Handle window resize
let resizeTimer;
window.addEventListener("resize", () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    setupMobileView();
  }, 250);
});

// Password visibility toggle for sign up form
const toggleSignupPassword = document.getElementById("toggleSignupPassword");
const signupPasswordInput = document.getElementById("signup-password");
const toggleSignupConfirmPassword = document.getElementById(
  "toggleSignupConfirmPassword"
);
const signupConfirmPasswordInput = document.getElementById(
  "signup-confirm-password"
);

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

// Password strength validation
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
      // No strength - reset everything
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

    // If password is empty, reset all
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

    // Check each rule
    rules.forEach((rule) => {
      strength = checkRule(password, strength, rule);
    });
    makeProgressBar(strength);
  }

  signupPasswordInput.addEventListener("keyup", function () {
    checkStrength(this.value);
  });

  // Also listen to input event for better responsiveness
  signupPasswordInput.addEventListener("input", function () {
    checkStrength(this.value);
  });

  // Form validation before submit
  const signupForm = document.querySelector(".sign-up-container form");
  if (signupForm) {
    signupForm.addEventListener("submit", function (e) {
      const password = signupPasswordInput.value;
      const confirmPassword = signupConfirmPasswordInput.value;

      // Check if passwords match
      if (password !== confirmPassword) {
        e.preventDefault();
        alert("Passwords do not match!");
        return false;
      }

      // Check password strength (at least 3 rules met for average strength)
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
