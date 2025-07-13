<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name']);
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    } elseif (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];
        // First set products in this category to NULL
        $conn->query("UPDATE products SET category_id = NULL WHERE category_id = $id");
        // Then delete the category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<div class="card admin-card">
    <div class="card-header">
        <h2><i class="fas fa-tags"></i> Product Categories</h2>
    </div>

    <div class="card-body">
        <!-- Add Category Form -->
        <div class="category-form">
            <h3>Add New Category</h3>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Category Name" required>
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
            </form>
        </div>

        <!-- Categories List -->
        <div class="categories-list">
            <h3>Existing Categories</h3>
            <?php if ($categories->num_rows > 0): ?>
                <table class="categories-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($category = $categories->fetch_assoc()): 
                            $product_count = $conn->query("SELECT COUNT(*) FROM products WHERE category_id = {$category['id']}")->fetch_row()[0];
                        ?>
                        <tr>
                            <td><?= $category['id'] ?></td>
                            <td><?= htmlspecialchars($category['name']) ?></td>
                            <td><?= $product_count ?></td>
                            <td class="actions">
                                <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                    <button type="submit" name="delete_category" class="btn btn-delete" 
                                            onclick="return confirm('Delete this category? Products will be uncategorized.')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-categories">No categories found. Add your first category above.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>