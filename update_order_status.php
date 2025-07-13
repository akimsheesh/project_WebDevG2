<?php
session_start();
require 'includes/db.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if POST data is available
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status'];
    $admin_id = $_SESSION['user_id'];

    // Validate status
    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled', 'shipped', 'delivered'];
    if (!in_array($new_status, $allowed_statuses)) {
        die('Invalid status.');
    }

    // Get old status
    $stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->bind_result($old_status);
    if ($stmt->fetch()) {
        $stmt->close();

        // Update order status
        $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update->bind_param("si", $new_status, $order_id);
        $update->execute();
        $update->close();

        // Log the status change
        $log = $conn->prepare("INSERT INTO order_status_log (order_id, old_status, new_status, changed_by) VALUES (?, ?, ?, ?)");
        $log->bind_param("issi", $order_id, $old_status, $new_status, $admin_id);
        $log->execute();
        $log->close();
    } else {
        $stmt->close();
        die("Order not found.");
    }

    // Redirect back with same filter if any
    $redirectUrl = 'admin_orders.php';
    if (!empty($_GET['status'])) {
        $redirectUrl .= '?status=' . urlencode($_GET['status']);
    }
    header("Location: $redirectUrl");
    exit();
} else {
    die('Invalid request.');
}
