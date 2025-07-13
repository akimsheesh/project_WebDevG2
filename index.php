<?php
session_start();
require 'includes/db.php'; // Use your existing connection file

// Check user role
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$is_logged_in = isset($_SESSION['user_id']);

include 'includes/header.php';
?>

<div class="content-container">
    <?php if ($is_admin): ?>
        <!-- ADMIN DASHBOARD -->
        <div class="admin-dashboard">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            
            <div class="admin-stats">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <?php
                        $user_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
                        ?>
                        <h3>Total Users</h3>
                        <p><?= $user_count ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <?php
                        $product_count = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
                        ?>
                        <h3>Total Products</h3>
                        <p><?= $product_count ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <?php
                        $order_count = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
                        ?>
                        <h3>Total Orders</h3>
                        <p><?= $order_count ?></p>
                    </div>
                </div>
            </div>
            
            <div class="admin-actions">
                <a href="admin/products.php" class="btn btn-admin">
                    <i class="fas fa-box"></i> Manage Products
                </a>
                <a href="admin/users.php" class="btn btn-admin">
                    <i class="fas fa-users-cog"></i> Manage Users
                </a>
                <a href="admin/orders.php" class="btn btn-admin">
                    <i class="fas fa-clipboard-list"></i> View Orders
                </a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- CUSTOMER VIEW -->
        <div class="customer-view">
            <div class="hero-section">
                <h1>Welcome to MySport</h1>
                <p>Your one-stop shop for quality sports equipment</p>
                <?php if (!$is_logged_in): ?>
                    <div class="auth-buttons">
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn btn-secondary">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="featured-products">
                <h2><i class="fas fa-star"></i> Featured Products</h2>
                <div class="product-grid">
                    <?php
                    $products = $conn->query("SELECT * FROM products LIMIT 4");
                    while ($product = $products->fetch_assoc()):
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="images/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                            <?php else: ?>
                                <div class="no-image"><i class="fas fa-box-open"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                            <p class="price">RM <?= number_format($product['price'], 2) ?></p>
                            <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-view">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>