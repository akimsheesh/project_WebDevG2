<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get stats
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM users) as users,
        (SELECT COUNT(*) FROM products) as products,
        (SELECT COUNT(*) FROM orders WHERE DATE(order_date) = CURDATE()) as today_orders,
        (SELECT SUM(total_amount) FROM orders WHERE DATE(order_date) = CURDATE()) as today_revenue
")->fetch_assoc();
?>

<div class="admin-container">
    <h1 style="margin-bottom: 20px;">Admin Dashboard</h1>
    
    <!-- Stats Cards -->
    <div class="dashboard-cards">
        <div class="dashboard-card">
            <div class="card-icon users">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-value"><?= $stats['users'] ?></div>
            <div class="card-title">Total Users</div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon products">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="card-value"><?= $stats['products'] ?></div>
            <div class="card-title">Total Products</div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon orders">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="card-value"><?= $stats['today_orders'] ?></div>
            <div class="card-title">Today's Orders</div>
        </div>
        
        <div class="dashboard-card">
            <div class="card-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="card-value">RM <?= number_format($stats['today_revenue'] ?? 0, 2) ?></div>
            <div class="card-title">Today's Revenue</div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div style="background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Recent Orders</h2>
            <a href="admin_orders.php" class="action-btn view">View All</a>
        </div>
        
        <table class="enhanced-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orders = $conn->query("
                    SELECT o.*, u.username 
                    FROM orders o
                    JOIN users u ON o.user_id = u.id
                    ORDER BY o.order_date DESC
                    LIMIT 5
                ");
                while($order = $orders->fetch_assoc()):
                ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                    <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?= $order['id'] ?>" class="action-btn view">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>