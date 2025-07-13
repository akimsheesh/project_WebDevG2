<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$order_id = $_GET['id'] ?? 0;
$logs = $conn->query("
    SELECT l.*, u.username 
    FROM order_status_log l
    JOIN users u ON l.changed_by = u.id
    WHERE l.order_id = $order_id
    ORDER BY l.changed_at DESC
");
?>

<div class="card">
    <h2>🔄 Status History for Order #<?= $order_id ?></h2>
    <a href="admin_orders.php" class="btn-back">← Back to Orders</a>
    
    <div class="log-container">
        <table class="log-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Changed By</th>
                    <th>From</th>
                    <th>To</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($log = $logs->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($log['changed_at'])) ?></td>
                    <td><?= htmlspecialchars($log['username']) ?></td>
                    <td><span class="status-badge <?= $log['old_status'] ?>"><?= $log['old_status'] ? ucfirst($log['old_status']) : 'N/A' ?></span></td>
                    <td><span class="status-badge <?= $log['new_status'] ?>"><?= ucfirst($log['new_status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>