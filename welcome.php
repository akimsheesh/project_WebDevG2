<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang ke MySport</title>
    <link rel="stylesheet" href="assets.css">
</head>
<body>

<div class="card">
    <h1>🏅 Selamat Datang ke MySport</h1>
    <p>Platform sehenti anda untuk membeli peralatan sukan berkualiti dengan harga berpatutan.</p>
    
    <div class="role-selection">
        <a href="admin_passcode.php" class="role-btn">
            <div class="role-icon">🛠</div>
            <span>Admin</span>
        </a>
        <a href="login.php?role=user" class="role-btn">
            <div class="role-icon">👤</div>
            <span>Pengguna</span>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>