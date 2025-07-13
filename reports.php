<?php
session_start();
require 'includes/db.php';
require 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<style>
    .card {
        background: #fff;
        border-radius: 12px;
        padding: 25px;
        max-width: 1100px;
        margin: 40px auto;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
    .admin-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #333;
    }
    .report-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
    }
    .report-card {
        background: #f9f9f9;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .chart-container {
        margin-top: 15px;
    }
    .bar-chart {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        height: 200px;
        border-left: 2px solid #ccc;
        border-bottom: 2px solid #ccc;
        padding: 10px;
    }
    .bar {
        background: #007bff;
        color: #fff;
        text-align: center;
        border-radius: 4px 4px 0 0;
        width: 50px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        transition: 0.3s;
    }
    .bar span {
        font-size: 12px;
        padding: 3px;
    }
    .bar label {
        margin-top: 5px;
        font-size: 12px;
        color: #333;
    }
    .stat-box {
        text-align: center;
        margin-top: 15px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: bold;
        color: #28a745;
    }
    .stat-label {
        font-size: 14px;
        color: #666;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .report-table th,
    .report-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    .report-table th {
        background-color: #007bff;
        color: white;
        text-align: left;
    }
</style>

<div class="card">
    <h2 class="admin-title"><i class="fas fa-chart-bar"></i> Laporan Sistem</h2>

    <div class="report-grid">
        <!-- Sales Report -->
        <div class="report-card">
            <h3>📈 Jualan Bulanan</h3>
            <?php
            $sales = $conn->query("
                SELECT MONTH(order_date) AS month, SUM(total_amount) AS total
                FROM orders
                WHERE total_amount IS NOT NULL
                GROUP BY MONTH(order_date)
                ORDER BY month DESC
                LIMIT 6
            ");
            ?>
            <div class="chart-container">
                <div class="bar-chart">
                    <?php while ($row = $sales->fetch_assoc()): ?>
                        <div class="bar" style="height: <?= max(20, ($row['total'] / 5)) ?>px;">
                            <span>RM<?= number_format($row['total'], 2) ?></span>
                            <label><?= date('M', mktime(0, 0, 0, $row['month'], 1)) ?></label>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="report-card">
            <h3>👥 Statistik Pengguna</h3>
            <div class="stat-box">
                <div class="stat-value">
                    <?= $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?>
                </div>
                <div class="stat-label">Jumlah Pengguna</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">
                    <?= $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetch_row()[0] ?>
                </div>
                <div class="stat-label">Admin</div>
            </div>
        </div>

        <!-- Product Statistics -->
        <div class="report-card">
            <h3>📦 Produk Terlaris</h3>
            <table class="report-table">
                <tr>
                    <th>Produk</th>
                    <th>Kuantiti</th>
                </tr>
                <?php
                $top_products = $conn->query("
                    SELECT p.product_name, SUM(oi.quantity) AS total_sold
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    GROUP BY oi.product_id
                    ORDER BY total_sold DESC
                    LIMIT 5
                ");
                while ($row = $top_products->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= $row['total_sold'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
