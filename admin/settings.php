<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password === $confirm_password) {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($current_password, $user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['admin_id']]);
                $success_message = 'Password updated successfully';
            } else {
                $error_message = 'Current password is incorrect';
            }
        } catch(PDOException $e) {
            $error_message = 'Error updating password';
            error_log('Password update error: ' . $e->getMessage());
        }
    } else {
        $error_message = 'New passwords do not match';
    }
}

// Handle new agent creation
if (isset($_POST['create_agent'])) {
    $agent_username = htmlspecialchars(trim($_POST['agent_username']), ENT_QUOTES, 'UTF-8');
    $agent_password = $_POST['agent_password'];
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = ?");
        $stmt->execute([$agent_username]);
        if ($stmt->fetchColumn() > 0) {
            $error_message = 'Username already exists';
        } else {
            // Create new agent account
            $hashed_password = password_hash($agent_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, role) VALUES (?, ?, 'agent')");
            $stmt->execute([$agent_username, $hashed_password]);
            $success_message = 'Agent account created successfully';
        }
    } catch(PDOException $e) {
        $error_message = 'Error creating agent account';
        error_log('Agent creation error: ' . $e->getMessage());
    }
}

// Fetch all agents
try {
    $stmt = $pdo->prepare("SELECT id, username, created_at FROM admin_users WHERE role = 'agent'");
    $stmt->execute();
    $agents = $stmt->fetchAll();
} catch(PDOException $e) {
    error_log('Error fetching agents: ' . $e->getMessage());
    $agents = [];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعدادات الحساب</title>
    <link rel="icon" type="image/png" href="../logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="settings-container">
        <div class="admin-header">
            <h1>إعدادات الحساب</h1>
            <div class="admin-nav">
                <nav>
                    <ul>
                        <li><a href="dashboard.php">لوحة التحكم</a></li>
                        <li><a href="logout.php">تسجيل الخروج</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="settings-section">
            <h2>تغيير كلمة المرور</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">كلمة المرور الحالية:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">كلمة المرور الجديدة:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">تأكيد كلمة المرور الجديدة:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="change_password">تحديث كلمة المرور</button>
            </form>
        </div>

        <?php if ($_SESSION['admin_role'] === 'admin'): ?>
        <div class="settings-section">
            <h2>إضافة وكيل جديد</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="agent_username">اسم المستخدم:</label>
                    <input type="text" id="agent_username" name="agent_username" required>
                </div>
                
                <div class="form-group">
                    <label for="agent_password">كلمة المرور:</label>
                    <input type="password" id="agent_password" name="agent_password" required>
                </div>
                
                <button type="submit" name="create_agent">إنشاء حساب وكيل</button>
            </form>

            <div class="agents-list">
                <h3>الوكلاء الحاليون</h3>
                <table>
                    <thead>
                        <tr>
                            <th>اسم المستخدم</th>
                            <th>تاريخ الإنشاء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($agent['username']); ?></td>
                                <td><?php echo htmlspecialchars($agent['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>