<?php
require_once __DIR__ . '/../Controller/common.php';

if (isLoggedIn()) {
    $user = getCurrentUser();
    redirectToRoleDashboard($user['role']);
}

$form = $_SESSION['form'] ?? [];
$error = $_SESSION['error'] ?? '';
unset($_SESSION['form'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - University Event Management</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 18px;
            color: #666;
        }
        .toggle-password:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-card">
            <div class="auth-header" style="display: flex; align-items: center; justify-content: center; gap: 15px; text-align: left; margin-bottom: 25px;">
                <img src="https://portal.aiub.edu/Content/Images/aiub_logo_92x92.png" alt="AIUB Logo" style="width: 85px; height: auto;">
                <div>
                    <h2 style="font-family: sans-serif; color: #005a9c; font-size: 14px; font-weight: 600; margin: 0; line-height: 1.2; text-transform: uppercase;">
                        American International<br>University-Bangladesh
                    </h2>
                    <p style="margin: 4px 0 0 0; color: #666; font-size: 10px; font-style: italic;">
                        — where leaders are created.
                    </p>
                    <p style="margin: 8px 0 0 0; color: #444; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                        Club and Event Management System
                    </p>
                </div>
            </div>

            <form method="POST" action="../Controller/signUpValidation.php" enctype="multipart/form-data">
                <h2>Register</h2>
                
                <?php if ($error): ?>
                <div class="alert alert-error" style="display: block;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($form['full_name'] ?? ''); ?>"
                           placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($form['username'] ?? ''); ?>"
                           placeholder="Choose a username">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($form['email'] ?? ''); ?>"
                           placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($form['phone'] ?? ''); ?>"
                           inputmode="numeric"
                           pattern="\d{11}"
                           maxlength="11"
                           placeholder="Enter your phone number">
                </div>

                <div class="form-group">
                    <label for="profile_pic">Profile Picture (Optional)</label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="user_role">I am a</label>
                    <select id="user_role" name="user_role">
                        <option value="">Select your role</option>
                        <option value="student" <?php echo ($form['role'] ?? '') === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="organizer" <?php echo ($form['role'] ?? '') === 'organizer' ? 'selected' : ''; ?>>Event Organizer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" 
                               placeholder="Create a password">
                        <span class="toggle-password" onclick="togglePassword('password', 'passwordToggleIcon')" title="Show/Hide password">
                            <img id="passwordToggleIcon" src="https://img.icons8.com/?size=100&id=85137&format=png&color=000000" alt="Show/Hide" width="18" height="18">
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm your password">
                        <span class="toggle-password" onclick="togglePassword('confirm_password', 'confirmPasswordToggleIcon')" title="Show/Hide password">
                            <img id="confirmPasswordToggleIcon" src="https://img.icons8.com/?size=100&id=85137&format=png&color=000000" alt="Show/Hide" width="18" height="18">
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>

                <div class="form-footer">
                    <p>Already have an account? <a href="login.php" class="link">Login here</a></p>
                </div>
            </form>
        </div>
    </div>
    <script>
        function togglePassword(fieldId, iconImgId) {
            const field = document.getElementById(fieldId);
            const img = document.getElementById(iconImgId);

            // Per your icons: unhide=85137, hide=85130
            const showIcon = 'https://img.icons8.com/?size=100&id=85137&format=png&color=000000';
            const hideIcon = 'https://img.icons8.com/?size=100&id=85130&format=png&color=000000';

            if (field.type === 'password') {
                field.type = 'text';
                if (img) img.src = hideIcon;
            } else {
                field.type = 'password';
                if (img) img.src = showIcon;
            }
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const role = document.getElementById('user_role').value;
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const phone = document.getElementById('phone').value.trim();
            
            let errors = [];

            if (fullName.length < 3) errors.push("Full name must be at least 3 characters");
            if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) errors.push("Username: 3-20 characters, letters/numbers/underscore only");
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push("Invalid email format");
            if (phone && (!/^\d{11}$/.test(phone))) errors.push("Phone must be 11 digits");
            if (!role) errors.push("Please select a valid role");
            
            // Password complexity
            if (password.length < 8) errors.push("Password must be at least 8 characters");
            if (!/[A-Z]/.test(password)) errors.push("Password needs at least one uppercase letter");
            if (!/[a-z]/.test(password)) errors.push("Password needs at least one lowercase letter");
            if (!/[0-9]/.test(password)) errors.push("Password needs at least one number");
            if (!/[@#$%^&*!]/.test(password)) errors.push("Password needs at least one special character (@#$%^&*!)");
            
            if (password !== confirm) errors.push("Passwords do not match");

            if (errors.length > 0) {
                e.preventDefault();
                let alertBox = document.querySelector('.alert-error');
                if (!alertBox) {
                    alertBox = document.createElement('div');
                    alertBox.className = 'alert alert-error';
                    alertBox.style.display = 'block';
                    document.querySelector('form').insertBefore(alertBox, document.querySelector('h2').nextSibling);
                }
                alertBox.innerHTML = errors.join('<br>');
                alertBox.style.display = 'block';
                window.scrollTo(0, 0);
            }
        });
    </script>
</body>
</html>
