<?php
// ============= File: cart.php =============
session_start();
include 'includes/header.php';
include 'includes/db.php';

// ========== LOGIC: Tambah, Kurang, Padam ==========
if (isset($_GET['add'])) {
    $product_id = $_GET['add'];
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
}

if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]--;
        if ($_SESSION['cart'][$product_id] <= 0) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    unset($_SESSION['cart'][$product_id]);
}
?>

<div class="card">
    <h2>🛒 Troli Anda</h2>

    <?php
    $total = 0;

    if (!empty($_SESSION['cart'])) {
        echo "<table border='1' cellpadding='10' cellspacing='0' width='100%'>";
        echo "<tr><th>Produk</th><th>Kuantiti</th><th>Harga Seunit (RM)</th><th>Jumlah (RM)</th><th>Tindakan</th></tr>";

        foreach ($_SESSION['cart'] as $id => $qty) {
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!$result) continue; // Jika produk telah dipadam

            $subtotal = $qty * $result['price'];
            $total += $subtotal;

            echo "<tr>
                    <td>" . htmlspecialchars($result['product_name']) . "</td>
                    <td>$qty</td>
                    <td>RM " . number_format($result['price'], 2) . "</td>
                    <td>RM " . number_format($subtotal, 2) . "</td>
                    <td>
                        <a href='cart.php?add=$id' class='btn'>+</a>
                        <a href='cart.php?remove=$id' class='btn'>-</a>
                        <a href='cart.php?delete=$id' class='btn btn-red'>Padam</a>
                    </td>
                  </tr>";
        }

        echo "</table>";
        echo "<h3 style='text-align:right;'>Jumlah Keseluruhan: RM " . number_format($total, 2) . "</h3>";
        echo "<div style='text-align:right;'><a href='checkout.php' class='btn'>Teruskan ke Pembayaran</a></div>";
    } else {
        echo "<p style='text-align:center;'>Troli anda kosong 😅</p>";
    }
    ?>
</div>

<?php include 'includes/footer.php'; ?>
