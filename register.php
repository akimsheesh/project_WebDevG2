<?php
// ========================== File: register.php ==========================

session_start();
include 'includes/db.php';

$role = $_GET['role'] ?? 'user'; // default ke user

// Jika admin, pastikan admin_verified wujud
if ($role === 'admin' && !isset($_SESSION['admin_verified'])) {
    header("Location: admin_passcode.php");
    exit();
}

$error = "";
$success = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Semak jika emel sudah didaftarkan
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Emel sudah digunakan.";
    } else {
        // Masukkan pengguna ke dalam DB
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $role);

        if ($stmt->execute()) {
            $success = "Pendaftaran berjaya. <a href='login.php?role=$role'>Log masuk di sini</a>";
            // Hapus sesi admin_verified selepas pendaftaran
            if ($role === 'admin') {
                unset($_SESSION['admin_verified']);
            }
        } else {
            $error = "Ralat semasa mendaftar: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Daftar <?= ucfirst($role) ?></title>
    <link rel="stylesheet" href="assets.css">
</head>
<body>
<div class="card">
    <h2>Daftar <?= $role === 'admin' ? 'Admin' : 'Pengguna' ?></h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color: green;"><?= $success ?></p>
    <?php else: ?>
    <form method="post">
        <input type="text" name="username" placeholder="Nama Pengguna" required><br><br>
        <input type="email" name="email" placeholder="Emel" required><br><br>
        <input type="password" name="password" placeholder="Kata Laluan" required><br><br>
        <button type="submit" name="register" class="btn">Daftar</button>
    </form>
    <p>
        Sudah ada akaun?
        <a href="login.php?role=<?= $role ?>">Log masuk</a><br>
        <a href="welcome.php">← Kembali ke Laman Utama</a>
    </p>
    <?php endif; ?>
</div>
</body>
</html>
