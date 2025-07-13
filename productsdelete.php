<?php
// ============== File: productsdelete.php ==============
session_start();
include 'includes/db.php';

// Sekat akses jika bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: product_list.php");
    } else {
        echo "<p style='color:red;'>Ralat: " . $stmt->error . "</p>";
    }
} else {
    echo "<p>ID produk tidak sah.</p>";
}
?>
