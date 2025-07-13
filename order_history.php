<?php
// ================= File: order_history.php =================
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Semak login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<div class="card">
    <h2>🛒 Sejarah Pesanan Anda</h2>

    <table border="1" cellpadding="10" cellspacing="0" width="100%">
        <tr>
            <th>ID Pesanan</th>
            <th>Tarikh</th>
            <th>Jumlah Bayaran (RM)</th>
            <th>Butiran</th>
        </tr>

        <?php
        $stmt = $conn->prepare("SELECT id, order_date, total_amount FROM orders WHERE user_id = ? ORDER BY order_date DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0):
            while ($order = $result->fetch_assoc()):
        ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= $order['order_date'] ?></td>
                <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                <td><a href="order_detail.php?order_id=<?= $order['id'] ?>" class="btn">Lihat</a></td>
            </tr>
        <?php
            endwhile;
        else:
            echo "<tr><td colspan='4'>Tiada pesanan ditemui.</td></tr>";
        endif;
        ?>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
