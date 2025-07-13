<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Get stats based on user role
if ($role === 'admin') {
    $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
    $total_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
    $total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
    $recent_orders = $conn->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 5");
} else {
    // Modified to work without 'status' column
    $total_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE user_id = $user_id")->fetch_row()[0];
    $recent_orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC LIMIT 3");
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Selamat Datang, <?= htmlspecialchars($username) ?></h1>
        <p>Anda log masuk sebagai <span class="role-badge <?= $role ?>"><?= ucfirst($role) ?></span></p>
    </div>

    <div class="dashboard-stats">
        <?php if ($role === 'admin'): ?>
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>Total Pengguna</h3>
                    <p><?= $total_users ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <h3>Total Produk</h3>
                    <p><?= $total_products ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🧾</div>
                <div class="stat-info">
                    <h3>Total Pesanan</h3>
                    <p><?= $total_orders ?></p>
                </div>
            </div>
        <?php else: ?>
            <div class="stat-card">
                <div class="stat-icon">🧾</div>
                <div class="stat-info">
                    <h3>Pesanan Saya</h3>
                    <p><?= $total_orders ?></p>
                </div>
            </div>
            <!-- Removed pending orders stat since we don't have status column -->
        <?php endif; ?>
    </div>

    <div class="dashboard-sections">
        <section class="recent-orders">
            <h2><i class="fas fa-history"></i> Pesanan Terkini</h2>
            <div class="orders-list">
                <?php if ($recent_orders->num_rows > 0): ?>
                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                        <div class="order-item">
                            <div class="order-id">#<?= $order['id'] ?></div>
                            <div class="order-date"><?= date('d/m/Y', strtotime($order['order_date'])) ?></div>
                            <div class="order-amount">RM <?= number_format($order['total_amount'], 2) ?></div>
                            <a href="order_detail.php?order_id=<?= $order['id'] ?>" class="btn-view">Lihat</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-orders">Tiada pesanan terkini.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="quick-actions">
            <h2><i class="fas fa-bolt"></i> Tindakan Pantas</h2>
            <div class="action-buttons">
                <?php if ($role === 'admin'): ?>
                    <a href="admin_users.php" class="action-btn">
                        <i class="fas fa-users"></i>
                        <span>Urus Pengguna</span>
                    </a>
                    <a href="product_list.php" class="action-btn">
                        <i class="fas fa-boxes"></i>
                        <span>Urus Produk</span>
                    </a>
                    <a href="admin_orders.php" class="action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Lihat Pesanan</span>
                    </a>
                <?php else: ?>
                    <a href="products.php" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Beli Produk</span>
                    </a>
                    <a href="order_history.php" class="action-btn">
                        <i class="fas fa-history"></i>
                        <span>Sejarah Pesanan</span>
                    </a>
                    <a href="profile.php" class="action-btn">
                        <i class="fas fa-user"></i>
                        <span>Profil Saya</span>
                    </a>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<?php include 'includes/footer.php'; ?>