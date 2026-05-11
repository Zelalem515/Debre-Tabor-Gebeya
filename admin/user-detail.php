<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../php/auth.php';
require_once __DIR__ . '/../php/db.php';

Auth::require_role('admin');

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header('Location: users.php');
    exit;
}

$user = Database::fetch_one("SELECT * FROM users WHERE id = ?", [$user_id]);
if (!$user) {
    header('Location: users.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .info-group { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        .info-group label { font-weight: bold; color: #2c3e50; }
        .info-group p { margin: 5px 0; }
        .btn { padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        .btn:hover { background: #5568d3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <a href="users.php" class="back-link">← Back to Users</a>
        <h1>User Details</h1>
        
        <div class="info-group">
            <label>Email:</label>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        
        <div class="info-group">
            <label>Name:</label>
            <p><?php echo htmlspecialchars($user['full_name']); ?></p>
        </div>
        
        <div class="info-group">
            <label>Role:</label>
            <p><?php echo ucfirst($user['role']); ?></p>
        </div>
        
        <div class="info-group">
            <label>Phone:</label>
            <p><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></p>
        </div>
        
        <div class="info-group">
            <label>Address:</label>
            <p><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></p>
        </div>
        
        <div class="info-group">
            <label>Status:</label>
            <p><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></p>
        </div>
        
        <div class="info-group">
            <label>Created:</label>
            <p><?php echo date('M d, Y H:i', strtotime($user['created_at'])); ?></p>
        </div>
        
        <div style="margin-top: 30px;">
            <button class="btn" onclick="alert('Edit functionality coming soon')">Edit User</button>
            <button class="btn btn-danger" onclick="if(confirm('Are you sure?')) alert('Deactivate functionality coming soon')">Deactivate User</button>
        </div>
    </div>
</body>
</html>
