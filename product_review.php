<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

$product_id = intval($_GET['id']);
$product = $conn->query("SELECT * FROM products WHERE id = $product_id")->fetch_assoc();

if (!$product) {
    header("Location: products.php");
    exit;
}
?>

<div class="product-container">
    <!-- Product Details Section -->
    <div class="product-details">
        <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
        <h1><?= htmlspecialchars($product['product_name']) ?></h1>
        <p class="price">RM <?= number_format($product['price'], 2) ?></p>
        <p><?= htmlspecialchars($product['description']) ?></p>
        
        <!-- Add to Cart Form -->
        <form action="cart.php" method="GET">
            <input type="hidden" name="add" value="<?= $product['id'] ?>">
            <button type="submit" class="btn-cart">Add to Cart</button>
        </form>
    </div>

    <!-- Reviews Section -->
    <div class="reviews-section">
        <h2><i class="fas fa-star"></i> Customer Reviews</h2>
        
        <?php
        $reviews = $conn->query("
            SELECT r.*, u.username 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = $product_id AND r.is_approved = TRUE
            ORDER BY r.created_at DESC
            LIMIT 5
        ");
        
        if ($reviews->num_rows > 0): 
            $total_rating = 0;
            $review_count = 0;
        ?>
            <div class="rating-summary">
                <?php while($review = $reviews->fetch_assoc()): 
                    $total_rating += $review['rating'];
                    $review_count++;
                ?>
                <div class="review-card">
                    <div class="review-header">
                        <span class="reviewer"><?= htmlspecialchars($review['username']) ?></span>
                        <span class="rating-stars">
                            <?= str_repeat('★', $review['rating']) ?><?= str_repeat('☆', 5 - $review['rating']) ?>
                        </span>
                        <span class="review-date"><?= date('M j, Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <p class="review-content"><?= htmlspecialchars($review['comment']) ?></p>
                </div>
                <?php endwhile; ?>
                
                <div class="average-rating">
                    Average Rating: 
                    <strong><?= number_format($total_rating / $review_count, 1) ?></strong>/5 
                    (<?= $review_count ?> reviews)
                </div>
            </div>
        <?php else: ?>
            <p class="no-reviews">No reviews yet. Be the first to review this product!</p>
        <?php endif; ?>

        <!-- Review Submission Form -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="review-form">
            <h3>Write a Review</h3>
            <form action="submit_review.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                
                <div class="form-group">
                    <label>Your Rating:</label>
                    <select name="rating" class="rating-select" required>
                        <option value="">Select Rating</option>
                        <option value="5">★★★★★ Excellent</option>
                        <option value="4">★★★★☆ Very Good</option>
                        <option value="3">★★★☆☆ Good</option>
                        <option value="2">★★☆☆☆ Fair</option>
                        <option value="1">★☆☆☆☆ Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Your Review:</label>
                    <textarea name="comment" rows="4" required></textarea>
                </div>
                
                <button type="submit" class="btn-submit">Submit Review</button>
            </form>
        </div>
        <?php else: ?>
        <div class="login-prompt">
            <a href="login.php" class="btn-login">Log in</a> to leave a review
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>