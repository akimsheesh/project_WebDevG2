<?php
session_start();
require 'includes/db.php';

// Validate session and cart
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Validate shipping method
if (!isset($_POST['shipping_method'])) {
    $_SESSION['error'] = "Please select a shipping method";
    header("Location: checkout.php");
    exit();
}

$shipping_id = (int)$_POST['shipping_method'];

// Verify shipping method exists and is active
$shipping_stmt = $conn->prepare("SELECT id, fee FROM shipping WHERE id = ? AND is_active = 1");
$shipping_stmt->bind_param("i", $shipping_id);
$shipping_stmt->execute();
$shipping_result = $shipping_stmt->get_result();

if ($shipping_result->num_rows === 0) {
    $_SESSION['error'] = "Invalid shipping method selected";
    header("Location: checkout.php");
    exit();
}

$shipping = $shipping_result->fetch_assoc();

// Start transaction
$conn->begin_transaction();

try {
    // 1. Insert the order record
    $order_stmt = $conn->prepare("INSERT INTO orders (user_id, shipping_id) VALUES (?, ?)");
    $order_stmt->bind_param("ii", $_SESSION['user_id'], $shipping_id);
    
    if (!$order_stmt->execute()) {
        throw new Exception("Failed to create order");
    }
    
    $order_id = $conn->insert_id;
    
    // 2. Insert order items and calculate total
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        // Get product price
        $product_stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $product_stmt->bind_param("i", $product_id);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();
        
        if ($product_result->num_rows === 0) {
            throw new Exception("Product ID $product_id not found");
        }
        
        $product = $product_result->fetch_assoc();
        $price = $product['price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
        
        // Insert order item
        $item_stmt = $conn->prepare("INSERT INTO order_items 
            (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)");
        $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to add order item for product $product_id");
        }
    }
    
    // 3. Add shipping fee to total
    $total += $shipping['fee'];
    
    // 4. Update order with total (if you add this column later)
    // $update_stmt = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
    // $update_stmt->bind_param("di", $total, $order_id);
    // $update_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Clear cart
    unset($_SESSION['cart']);
    
    // Redirect to confirmation
    header("Location: order_confirmation.php?id=$order_id");
    exit();
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    $_SESSION['error'] = "Order processing failed: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}