<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $name = trim($_POST['name']);
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $category_id);
    $stmt->execute();
    header("Location: admin_categories.php");
    exit;
}

$category = $conn->query("SELECT * FROM categories WHERE id = $category_id")->fetch_assoc();

if (!$category) {
    header("Location: admin_categories.php");
    exit;
}
?>

<div class="card admin-card">
    <div class="card-header">
        <h2><i class="fas fa-edit"></i> Edit Category</h2>
    </div>

    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label>Category Name:</label>
                <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="update_category" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="admin_categories.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>