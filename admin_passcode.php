<?php
// ========================== File: admin_passcode.php ==========================

session_start();
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_code = $_POST['passcode'] ?? '';

    // Ini adalah kata laluan rahsia admin
    $admin_code = "admin123";

    if ($entered_code === $admin_code) {
        // Berikan akses kepada admin
        $_SESSION['admin_verified'] = true;
        header("Location: login.php?role=admin");
        echo "Redirecting... Session set. <a href='login.php?role=admin'>Click here if not redirected.</a>";
        exit();
    } else {
        $error = "Kata laluan rahsia tidak sah.";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Kata Laluan Admin</title>
    <link rel="stylesheet" href="assets.css">
</head>
<body>
<div class="card">
    <h2>Akses Admin 🔐</h2>
    <p>Sila masukkan kata laluan rahsia untuk akses admin.</p>

    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="password" name="passcode" placeholder="Kata Laluan Rahsia" required><br><br>
        <button type="submit" class="btn">Sahkan</button>
    </form>

    <p><a href="welcome.php">← Kembali</a></p>
</div>
</body>
</html>
