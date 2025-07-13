<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

$product_id = $_GET['id'] ?? 0;

// Validate product_id
if (!$product_id || !is_numeric($product_id)) {
    echo "<div class='content-container'><p>Invalid product ID.</p></div>";
    include 'includes/footer.php';
    exit();
}

// Fetch product from database
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo "<div class='content-container'><p>Product not found.</p></div>";
    include 'includes/footer.php';
    exit();
}
?>

<div class="content-container">
    <div class="product-detail">
        <div class="product-detail-left">
            <?php if (!empty($product['image'])): ?>
                <img src="images/products/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-detail-image">
            <?php else: ?>
                <div class="no-image"><i class="fas fa-box-open fa-5x" style="color: #ccc;"></i></div>
            <?php endif; ?>
        </div>

        <div class="product-detail-right">
            <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
            <p class="product-price">RM <?= number_format($product['price'], 2) ?></p>

            <?php if ($product['category_name']): ?>
                <p class="product-meta"><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
            <?php endif; ?>

            <p class="product-meta">
                <strong>Stock:</strong>
                <span class="<?= $product['stock_quantity'] < 10 ? 'stock-low' : 'stock-ok' ?>">
                    <?= $product['stock_quantity'] ?> available
                </span>
            </p>

            <p class="product-description">
                <?= nl2br(htmlspecialchars($product['description'] ?? 'No description.')) ?>
            </p>

            <!-- Optional: Add to cart (for user side only) -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'user'): ?>
                <form method="POST" action="cart_add.php" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>" required class="qty-input">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
