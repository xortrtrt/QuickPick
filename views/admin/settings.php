<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

$stmt = $pdo->prepare("SELECT * FROM admins WHERE adminID = ?");
$stmt->execute([$_SESSION['adminID']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$adminName = $_SESSION['adminName'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
</head>

<body>
    <div style="display: flex;">
        <!-- Sidebar -->
        <?php include("../../includes/admin-sidebar.php"); ?>


        <!-- Main Content -->
        <div class="main-content" style="flex: 1;">
            <div class="top-bar">
                <h1 class="page-title">Admin Settings</h1>
            </div>

            <!-- Settings Form -->
            <div class="form-card" style="max-width: 600px;">
                <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 25px;">Account Information</h3>

                <form id="settingsForm">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['phoneNumber']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Last Login</label>
                        <input type="text" class="form-control"
                            value="<?php echo $admin['lastLogin'] ? date('M d, Y H:i', strtotime($admin['lastLogin'])) : 'Never'; ?>"
                            disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Account Status</label>
                        <input type="text" class="form-control"
                            value="<?php echo $admin['isActive'] ? '✓ Active' : '✗ Inactive'; ?>"
                            disabled>
                    </div>

                    <hr style="margin: 30px 0;">

                    <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px;">Change Password</h3>

                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" placeholder="Enter current password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" placeholder="Enter new password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password">
                    </div>

                    <button type="button" class="btn btn-primary" onclick="changePassword()">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function changePassword() {
            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;

            if (!current || !newPass || !confirm) {
                alert('All fields are required');
                return;
            }

            if (newPass.length < 8) {
                alert('New password must be at least 8 characters');
                return;
            }

            if (newPass !== confirm) {
                alert('Passwords do not match');
                return;
            }

            fetch('/controllers/admin/change_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `currentPassword=${encodeURIComponent(current)}&newPassword=${encodeURIComponent(newPass)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Password changed successfully');
                        document.getElementById('currentPassword').value = '';
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>