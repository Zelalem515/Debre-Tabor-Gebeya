<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';

Auth::require_role('admin');
$current_language = 'en';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f9f9f9; }
        .btn { padding: 8px 12px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>Manage Users</h1>
        
        <?php
        $users = Database::fetch_all("SELECT id, email, full_name, role, is_active, created_at FROM users ORDER BY created_at DESC");
        ?>
        
        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                    <td><?php echo ucfirst($user['role']); ?></td>
                    <td><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="user-detail.php?id=<?php echo $user['id']; ?>" class="btn">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 20px; color: #666;">Total Users: <?php echo count($users); ?></p>
    </div>
</body>
</html>
