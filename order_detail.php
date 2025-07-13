<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

// Validate order ID
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$order_id) {
    die('<div class="card error-card">
        <h3><i class="fas fa-exclamation-triangle"></i> Invalid Order</h3>
        <p>No valid order ID was specified in the URL.</p>
        <a href="admin_orders.php" class="btn">Back to Orders</a>
    </div>');
}

// Fetch order details
try {
    $stmt = $conn->prepare("
        SELECT o.*, u.username, u.email 
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        throw new Exception("Order not found");
    }

    // Fetch order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.product_name, p.image
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $items = $stmt->get_result();
} catch (Exception $e) {
    die('<div class="card error-card">
        <h3><i class="fas fa-exclamation-triangle"></i> Error Loading Order</h3>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <a href="admin_orders.php" class="btn">Back to Orders</a>
    </div>');
}
?>

<div class="card order-detail-card">
    <div class="card-header">
        <h2><i class="fas fa-receipt"></i> Order #<?= $order_id ?></h2>
        <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
            <?= ucfirst(htmlspecialchars($order['status'])) ?>
        </span>
    </div>

    <div class="order-content">
        <div class="order-meta">
            <div class="meta-section">
                <h3><i class="fas fa-user"></i> Customer Information</h3>
                <div class="meta-item">
                    <span>Name:</span>
                    <strong><?= htmlspecialchars($order['username']) ?></strong>
                </div>
                <div class="meta-item">
                    <span>Email:</span>
                    <strong><?= htmlspecialchars($order['email']) ?></strong>
                </div>
            </div>
            
            <div class="meta-section">
                <h3><i class="fas fa-calendar-alt"></i> Order Information</h3>
                <div class="meta-item">
                    <span>Date:</span>
                    <strong><?= date('F j, Y \a\t g:i A', strtotime($order['order_date'])) ?></strong>
                </div>
                <div class="meta-item total">
                    <span>Total Amount:</span>
                    <strong>RM <?= number_format($order['total_amount'], 2) ?></strong>
                </div>
            </div>
        </div>

        <div class="order-items">
            <h3><i class="fas fa-box-open"></i> Order Items</h3>
            
            <?php if ($items->num_rows > 0): ?>
                <div class="items-list">
                    <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="item">
                        <div class="item-image">
                            <?php if (!empty($item['image'])): ?>
                                <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>">
                            <?php else: ?>
                                <div class="no-image"><i class="fas fa-box-open"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                            <div class="item-meta">
                                <span>Price: RM <?= number_format($item['price'], 2) ?></span>
                                <span>Qty: <?= $item['quantity'] ?></span>
                                <span class="subtotal">Subtotal: RM <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-items">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>No items found in this order</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="order-actions">
        <a href="admin_orders.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="order_status_log.php?id=<?= $order_id ?>" class="btn btn-history">
            <i class="fas fa-history"></i> View History
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>