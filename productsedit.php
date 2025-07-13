<?php
// ============== File: productsedit.php ==============
session_start();
include 'includes/header.php';
include 'includes/db.php';

// Sekat akses jika bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Semak parameter ID
if (!isset($_GET['id'])) {
    echo "<p>ID produk tidak sah.</p>";
    include 'includes/footer.php';
    exit();
}

$id = $_GET['id'];
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $desc = $_POST["description"];
    $price = $_POST["price"];

    // Kemas kini dengan atau tanpa gambar baru
    if (!empty($_FILES["image"]["name"])) {
        $image = $_FILES["image"]["name"];
        $tmp = $_FILES["image"]["tmp_name"];
        move_uploaded_file($tmp, "assets/images/" . $image);
        $stmt = $conn->prepare("UPDATE products SET product_name=?, description=?, price=?, image=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $desc, $price, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, description=?, price=? WHERE id=?");
        $stmt->bind_param("ssdi", $name, $desc, $price, $id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Produk berjaya dikemaskini.'); window.location='product_list.php';</script>";
    } else {
        echo "<p style='color:red;'>Ralat: " . $stmt->error . "</p>";
    }
}
?>

<div class="card">
    <h2>Edit Produk</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" value="<?= htmlspecialchars($product['product_name']) ?>" required><br><br>
        <textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea><br><br>
        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br><br>
        <img src="assets/images/<?= $product['image'] ?>" width="100"><br><br>
        <input type="file" name="image" accept="image/*"><br><br>
        <button type="submit" class="btn">Kemaskini</button>
    </form>
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
