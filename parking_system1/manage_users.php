<?php
require_once 'security.php';
require_once 'connect.php';

requireAdmin();
startSecureSession();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId <= 0) {
        setFlash('error', 'Invalid user selected.');
        header('Location: manage_users.php');
        exit();
    }

    if ($action === 'delete') {
        if ($userId === (int)$_SESSION['user_id']) {
            setFlash('error', 'You cannot delete your own admin account.');
            header('Location: manage_users.php');
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);

        if ($stmt->execute()) {
            setFlash('success', 'User deleted successfully.');
        } else {
            setFlash('error', 'Failed to delete user.');
        }

        header('Location: manage_users.php');
        exit();
    }

    if ($action === 'change_role') {
        $newRole = $_POST['role'] ?? 'user';

        if (!in_array($newRole, ['admin', 'user'], true)) {
            setFlash('error', 'Invalid role selected.');
            header('Location: manage_users.php');
            exit();
        }

        if ($userId === (int)$_SESSION['user_id'] && $newRole !== 'admin') {
            setFlash('error', 'You cannot remove your own admin role.');
            header('Location: manage_users.php');
            exit();
        }

        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $newRole, $userId);

        if ($stmt->execute()) {
            setFlash('success', 'User role updated successfully.');
        } else {
            setFlash('error', 'Failed to update user role.');
        }

        header('Location: manage_users.php');
        exit();
    }
}

$flash = getFlash();
$users = $conn->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background:#eef4ff; padding:30px; color:#1f2d3d; }
        .container { max-width:1200px; margin:0 auto; }
        .card { background:#fff; border-radius:14px; padding:28px; box-shadow:0 10px 30px rgba(64,112,244,0.12); }
        h1 { margin-bottom:10px; }
        p { color:#5f6f81; margin-bottom:18px; }
        .topbar { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:18px; }
        .btn, button {
            display:inline-block; text-decoration:none; border:none; cursor:pointer;
            background:#4070f4; color:#fff; padding:10px 16px; border-radius:8px; font-size:14px;
        }
        .danger { background:#d64545; }
        .secondary { background:#5f6f81; }
        .success { background:#1f9d55; }
        table { width:100%; border-collapse:collapse; margin-top:18px; }
        th, td { padding:12px; border-bottom:1px solid #e4eaf5; text-align:left; vertical-align:middle; }
        th { background:#f7f9fe; }
        .message { padding:14px 16px; border-radius:10px; margin-bottom:16px; }
        .message.success { background:#e9f8ef; color:#216e39; }
        .message.error { background:#fff1f1; color:#b42318; }
        .action-group { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
        select { padding:8px 10px; border:1px solid #d6deeb; border-radius:8px; }
        .badge {
            display:inline-block; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:600;
            background:#edf2ff; color:#365ad8;
        }
        .badge.admin { background:#fff1f1; color:#b42318; }
        .badge.user { background:#e9f8ef; color:#216e39; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Manage Users</h1>
        <p>View users, change roles, and remove accounts if needed.</p>

        <?php if ($flash): ?>
            <div class="message <?php echo e($flash['type']); ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <div class="topbar">
            <a class="btn secondary" href="admin_dashboard.php">Back to Dashboard</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th width="280">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo e($row['id']); ?></td>
                        <td><?php echo e($row['name']); ?></td>
                        <td><?php echo e($row['email']); ?></td>
                        <td><?php echo e($row['phone']); ?></td>
                        <td>
                            <span class="badge <?php echo e($row['role']); ?>">
                                <?php echo e(strtoupper($row['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo e($row['created_at']); ?></td>
                        <td>
                            <div class="action-group">
                                <form method="POST" style="display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?php echo e($row['id']); ?>">
                                    <select name="role">
                                        <option value="user" <?php echo $row['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $row['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit" class="success">Update Role</button>
                                </form>

                                <form method="POST" onsubmit="return confirm('Delete this user?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo e($row['id']); ?>">
                                    <button type="submit" class="danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>