<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = intval($_POST['review_id']);
    
    if (isset($_POST['approve_review'])) {
        $conn->query("UPDATE reviews SET is_approved = TRUE WHERE id = $review_id");
        $_SESSION['success'] = "Review approved successfully";
    } elseif (isset($_POST['delete_review'])) {
        $conn->query("DELETE FROM reviews WHERE id = $review_id");
        $_SESSION['success'] = "Review deleted successfully";
    }
}

// Get all reviews
$reviews = $conn->query("
    SELECT r.*, u.username, p.product_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    JOIN products p ON r.product_id = p.id
    ORDER BY r.is_approved ASC, r.created_at DESC
");
?>

<div class="admin-container">
    <h1><i class="fas fa-star"></i> Review Management</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <div class="review-filters">
        <a href="?filter=all" class="btn-filter active">All Reviews</a>
        <a href="?filter=pending" class="btn-filter">Pending Approval</a>
        <a href="?filter=approved" class="btn-filter">Approved</a>
    </div>
    
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>User</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($review = $reviews->fetch_assoc()): ?>
            <tr>
                <td><?= $review['id'] ?></td>
                <td><?= htmlspecialchars($review['product_name']) ?></td>
                <td><?= htmlspecialchars($review['username']) ?></td>
                <td class="rating-cell">
                    <?= str_repeat('★', $review['rating']) ?>
                </td>
                <td class="review-content">
                    <?= htmlspecialchars(substr($review['comment'], 0, 50)) ?>
                    <?= strlen($review['comment']) > 50 ? '...' : '' ?>
                </td>
                <td><?= date('M j, Y', strtotime($review['created_at'])) ?></td>
                <td>
                    <span class="status-badge <?= $review['is_approved'] ? 'approved' : 'pending' ?>">
                        <?= $review['is_approved'] ? 'Approved' : 'Pending' ?>
                    </span>
                </td>
                <td class="actions">
                    <?php if (!$review['is_approved']): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <button type="submit" name="approve_review" class="btn-action approve">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                        <button type="submit" name="delete_review" class="btn-action delete" 
                                onclick="return confirm('Delete this review permanently?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>