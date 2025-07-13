<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please log in to submit a review";
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Invalid rating value";
        header("Location: product.php?id=$product_id");
        exit;
    }

    // Check for existing review
    $existing = $conn->query("SELECT id FROM reviews WHERE user_id = $user_id AND product_id = $product_id");
    
    if ($existing->num_rows > 0) {
        $_SESSION['error'] = "You've already reviewed this product";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment, is_approved) VALUES (?, ?, ?, ?, ?)");
        $approved = $_SESSION['role'] === 'admin' ? 1 : 0; // Auto-approve admin reviews
        $stmt->bind_param("iiisi", $user_id, $product_id, $rating, $comment, $approved);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = $approved ? "Review submitted successfully!" : "Review submitted for approval";
        } else {
            $_SESSION['error'] = "Error submitting review: " . $conn->error;
        }
    }
}

header("Location: product.php?id=$product_id");
exit;