<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    header("Location: admin_users.php");
    exit;
}

// Prevent self-deletion
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = "You cannot delete your own account";
    header("Location: admin_users.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $_SESSION['message'] = "User deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting user: " . $conn->error;
}

header("Location: admin_users.php");
exit;