<?php
session_start();
require 'includes/db.php';

// Check if order ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = (int)$_GET['id'];

// Verify order belongs to logged in user
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get order details
$order_stmt = $conn->prepare("
    SELECT o.id, o.order_date, s.method, s.fee as shipping_fee
    FROM orders o
    JOIN shipping s ON o.shipping_id = s.id
    WHERE o.id = ? AND o.user_id = ?
");
$order_stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$order = $order_result->fetch_assoc();

// Get order items - UPDATED to use product_name instead of name
$items_stmt = $conn->prepare("
    SELECT oi.*, p.product_name, p.image
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Calculate total
$total = 0;
$items = [];
while ($item = $items_result->fetch_assoc()) {
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $items[] = $item;
}
$total += $order['shipping_fee'];

require 'includes/header.php';
?>

<div class="checkout-container">
    <div class="order-confirmation">
        <div class="confirmation-header">
            <i class="fas fa-check-circle"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been received.</p>
        </div>

        <div class="order-summary">
            <div class="summary-card">
                <h2><i class="fas fa-receipt"></i> Order Summary</h2>
                
                <div class="order-details">
                    <div class="detail-row">
                        <span>Order Number:</span>
                        <strong>#<?= $order['id'] ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Date:</span>
                        <strong><?= date('F j, Y', strtotime($order['order_date'])) ?></strong>
                    </div>
                    <div class="detail-row">
                        <span>Shipping Method:</span>
                        <strong><?= htmlspecialchars($order['method']) ?></strong>
                    </div>
                </div>

                <div class="order-items">
                    <h3>Your Items</h3>
                    <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div class="item-image">
                            <?php if (!empty($item['image'])): ?>
                            <img src="images/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                            <?php else: ?>
                            <div class="no-image"><i class="fas fa-box-open"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="item-info">
                            <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                            <div class="item-meta">
                                <span><?= $item['quantity'] ?> × RM <?= number_format($item['price'], 2) ?></span>
                                <span class="item-subtotal">RM <?= number_format($item['subtotal'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>RM <?= number_format($total - $order['shipping_fee'], 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>RM <?= number_format($order['shipping_fee'], 2) ?></span>
                    </div>
                    <div class="total-row grand-total">
                        <span>Total:</span>
                        <span>RM <?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-continue">
                <i class="fas fa-home"></i> Back to Home
            </a>
            <a href="order_history.php" class="btn btn-view-orders">
                <i class="fas fa-list"></i> View All Orders
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>