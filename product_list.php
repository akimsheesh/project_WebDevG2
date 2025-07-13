<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$category_id = $_GET['category'] ?? 0;
$where = $category_id ? "WHERE category_id = $category_id" : "";
$products = $conn->query("SELECT * FROM products $where ORDER BY created_at DESC");
?>

<div class="admin-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Product Management</h1>
        <a href="products_add.php" class="action-btn edit">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
    
    <!-- Category Filter -->
    <div class="filter-controls">
        <div class="filter-group">
            <span>Filter by Category:</span>
            <select onchange="window.location='product_list.php?category='+this.value" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                <option value="0">All Categories</option>
                <?php
                $categories = $conn->query("SELECT * FROM categories");
                while($cat = $categories->fetch_assoc()):
                ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
    
    <!-- Product Grid -->
    <div class="product-grid">
        <?php while($product = $products->fetch_assoc()): ?>
        <div class="product-card">
            <div class="product-img">
                <?php if($product['image']): ?>
                <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
                <?php else: ?>
                <i class="fas fa-box-open" style="font-size: 40px; color: #ddd;"></i>
                <?php endif; ?>
            </div>
            <div class="product-body">
                <h3 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h3>
                <div class="product-price">RM <?= number_format($product['price'], 2) ?></div>
                <div class="product-stock <?= $product['stock_quantity'] < 10 ? 'stock-low' : 'stock-ok' ?>">
                    Stock: <?= $product['stock_quantity'] ?>
                </div>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <a href="productsedit.php?id=<?= $product['id'] ?>" class="action-btn edit">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="productsdelete.php?id=<?= $product['id'] ?>" class="action-btn delete" onclick="return confirm('Delete this product?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>