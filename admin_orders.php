<?php
// START SESSION AND CHECK LOGIN AT VERY TOP
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Now include header AFTER potential redirect
require 'includes/header.php';

// Status filter
$status = $_GET['status'] ?? '';
$where = $status ? "WHERE o.status = '$status'" : "";
?>

<div class="admin-container">
    <div class="filter-controls">
        <h1>Order Management</h1>
        
        <div class="filter-group">
            <span>Filter by Status:</span>
            <select onchange="window.location='admin_orders.php?status='+this.value" 
                    class="form-select">
                <option value="">All Orders</option>
                <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $status == 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Completed</option>
                <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="enhanced-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orders = $conn->query("
                    SELECT o.*, u.username, p.status as payment_status
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    LEFT JOIN payments p ON p.order_id = o.id
                    $where
                    ORDER BY o.order_date DESC
                ");
                
                while($order = $orders->fetch_assoc()):
                ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                    <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <span class="payment-status <?= strtolower($order['payment_status'] ?? 'unpaid') ?>">
                            <?= $order['payment_status'] ?? 'Unpaid' ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="update_order_status.php" class="status-form">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" onchange="this.form.submit()" 
                                    class="status-select <?= strtolower($order['status']) ?>">
                                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?= $order['id'] ?>" class="action-btn view">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="order_status_log.php?id=<?= $order['id'] ?>" class="action-btn history">
                            <i class="fas fa-history"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>