<?php
// ================= File: products.php =================
session_start();
include 'includes/header.php';
include 'includes/db.php';

?>

<div class="card">
    <h2>Senarai Produk</h2>
    <div class="product-grid">
        <?php
        $result = $conn->query("SELECT * FROM products");
        while ($row = $result->fetch_assoc()):
        ?>
            <div class="product-card">
                <img src="assets/images/<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                <p>RM <?= number_format($row['price'], 2) ?></p>
                <a href="cart.php?add=<?= $row['id'] ?>" class="btn">Tambah ke Troli</a>
            </div>
        <?php endwhile; ?>
    </div>
</div>


<div class="form-group">
    <label>Category:</label>
    <select name="category_id">
        <option value="">-- No Category --</option>
        <?php 
        $categories = $conn->query("SELECT * FROM categories ORDER BY name");
        while ($cat = $categories->fetch_assoc()): 
        ?>
            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? null) == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>



<?php include 'includes/footer.php'; ?>
