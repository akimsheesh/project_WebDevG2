<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Edit user
    if (isset($_POST['edit_user'])) {
        $user_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        
        $stmt = $conn->prepare("UPDATE users SET username=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $username, $email, $role, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "User updated successfully";
        } else {
            $_SESSION['error'] = "Error updating user: " . $conn->error;
        }
    }
    // Delete user
    elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "User deleted successfully";
            } else {
                $_SESSION['error'] = "Error deleting user: " . $conn->error;
            }
        }
    }
    
    header("Location: admin_users.php");
    exit;
}

// Get all users
$result = $conn->query("SELECT id, username, email, role FROM users ORDER BY id");
?>

<div class="card admin-users-card">
    <h2>👥 User Management</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="role-badge <?= $row['role'] ?>"><?= ucfirst($row['role']) ?></span>
                    </td>
                    <td class="actions">
                        <button class="btn-edit" onclick="openEditModal(
                            <?= $row['id'] ?>, 
                            '<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>',
                            '<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>',
                            '<?= $row['role'] ?>'
                        )">Edit</button>
                        
                        <form method="POST" class="delete-form">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn-delete" name="delete_user" onclick="return confirm('Are you sure you want to delete this user?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h3>Edit User</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" id="edit_username" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" id="edit_email" required>
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role" id="edit_role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" name="edit_user" class="btn-save">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openEditModal(id, username, email, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;
    document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('editModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>