<?php
session_start();
require 'includes/db.php';

// Redirect if cart is empty or user not logged in
if (empty($_SESSION['cart']) || !isset($_SESSION['user_id'])) {
    header("Location: cart.php");
    exit();
}

// Get active shipping methods
$shipping_query = "SELECT * FROM shipping WHERE is_active = 1";
$shipping_methods = $conn->query($shipping_query);

require 'includes/header.php';
?>

<div class="checkout-container">
    <h1>Checkout</h1>
    
    <div class="checkout-steps">
        <div class="step active">Shipping</div>
        <div class="step">Payment</div>
        <div class="step">Confirmation</div>
    </div>

    <form action="process_order.php" method="POST">
        <div class="shipping-section">
            <h2><i class="fas fa-truck"></i> Shipping Method</h2>
            
            <?php if ($shipping_methods->num_rows > 0): ?>
                <div class="shipping-options">
                    <?php while($method = $shipping_methods->fetch_assoc()): ?>
                    <div class="shipping-option">
                        <input type="radio" name="shipping_method" 
                               id="method_<?= $method['id'] ?>" 
                               value="<?= $method['id'] ?>" required
                               <?= ($method['is_default'] ?? false) ? 'checked' : '' ?>>
                        
                        <label for="method_<?= $method['id'] ?>">
                            <span class="method-name"><?= htmlspecialchars($method['method']) ?></span>
                            <span class="method-fee">RM <?= number_format($method['fee'], 2) ?></span>
                            <?php if (!empty($method['estimated_days'])): ?>
                            <span class="method-estimate">
                                <i class="fas fa-clock"></i> 
                                <?= $method['estimated_days'] ?> business days
                            </span>
                            <?php endif; ?>
                        </label>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-methods">No shipping methods available. Please contact us.</p>
            <?php endif; ?>
        </div>

        <div class="checkout-actions">
            <a href="cart.php" class="btn-back">Back to Cart</a>
            <button type="submit" class="btn-continue">Continue to Payment</button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>