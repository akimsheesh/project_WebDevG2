<?php
// ========================== File: login.php ==========================

session_start();
include 'includes/db.php';

// Pastikan role ditetapkan dari URL
$role = $_GET['role'] ?? 'user'; // default ke 'user'

// Jika admin, pastikan admin_verified wujud
if ($role === 'admin' && !isset($_SESSION['admin_verified'])) {
    header("Location: admin_passcode.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cari pengguna berdasarkan emel dan role
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Simpan data dalam session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Bersihkan sesi admin_verified jika bukan admin
            if ($role !== 'admin') {
                unset($_SESSION['admin_verified']);
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Kata laluan salah.";
        }
    } else {
        $error = "Pengguna tidak dijumpai untuk peranan ini.";
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Log Masuk <?= ucfirst($role) ?></title>
    <link rel="stylesheet" href="assets.css">
</head>
<body>
<div class="card">
    <h2>Log Masuk <?= $role === 'admin' ? 'Admin' : 'Pengguna' ?></h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Emel" required><br><br>
        <input type="password" name="password" placeholder="Kata Laluan" required><br><br>
        <button type="submit" name="login" class="btn">Log Masuk</button>
    </form>

    <p>
        Belum ada akaun
        <a href="register.php?role=<?= $role ?>">Daftar sebagai <?= $role === 'admin' ? 'Admin' : 'Pengguna' ?></a><br>
        <a href="welcome.php">← Kembali ke Laman Utama</a>
    </p>
