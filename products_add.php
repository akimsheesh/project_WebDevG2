<?php
// ================ File: product_add.php ==================
session_start();
include 'includes/header.php';
include 'includes/db.php';

// Sekat akses jika bukan admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $desc = $_POST["description"];
    $price = $_POST["price"];
    $image = $_FILES["image"]["name"];
    $tmp = $_FILES["image"]["tmp_name"];

    // Simpan fail gambar
    if ($image) {
        move_uploaded_file($tmp, "assets/images/" . $image);
    }

    $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $name, $desc, $price, $image);

    if ($stmt->execute()) {
        echo "<script>alert('Produk berjaya ditambah!'); window.location='product_list.php';</script>";
    } else {
        echo "<p style='color:red;'>Ralat: " . $stmt->error . "</p>";
    }
}
?>

<div class="card">
    <h2>Tambah Produk Baru</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Nama Produk" required><br><br>
        <textarea name="description" placeholder="Keterangan Produk" required></textarea><br><br>
        <input type="number" step="0.01" name="price" placeholder="Harga (RM)" required><br><br>
        <input type="file" name="image" accept="image/*" required><br><br>
        <button type="submit" class="btn">Tambah Produk</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
