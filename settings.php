<?php
$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

$users = $pdo->query("SELECT id, username, full_name, role FROM users")->fetchAll();

require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="card-panel">
    <h5>Company Settings</h5>
    <form action="backend/save_settings.php" method="POST" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($settings['company_name']) ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Company Logo</label>
                <input type="file" name="company_logo" class="form-control" accept="image/*">
            </div>
            <div class="col-md-4">
                <label class="form-label">Default Interest Rate (%)</label>
                <input type="number" step="0.01" name="default_interest_rate" class="form-control" value="<?= $settings['default_interest_rate'] ?? 12 ?>">
            </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Settings</button>
    </form>
</div>

<div class="card-panel">
    <h5>User Management</h5>
    <table class="app-table mb-3">
        <thead><tr><th>Username</th><th>Full Name</th><th>Role</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr><td><?= htmlspecialchars($u['username']) ?></td><td><?= htmlspecialchars($u['full_name']) ?></td><td><?= $u['role'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <form action="backend/add_user.php" method="POST" class="row g-2">
        <div class="col-md-3"><input type="text" name="username" class="form-control form-control-sm" placeholder="Username" required></div>
        <div class="col-md-3"><input type="text" name="full_name" class="form-control form-control-sm" placeholder="Full Name"></div>
        <div class="col-md-3"><input type="password" name="password" class="form-control form-control-sm" placeholder="Password" required></div>
        <div class="col-md-2">
            <select name="role" class="form-select form-select-sm">
                <option value="staff">Staff</option><option value="admin">Admin</option>
            </select>
        </div>
        <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-plus"></i></button></div>
    </form>
</div>

<div class="card-panel">
    <h5>Backup & Restore</h5>
    <p class="text-muted small">Download a full backup of your database, or restore from a previously saved .sql file.</p>
    <a href="backend/backup_db.php" class="btn btn-outline-primary"><i class="fa-solid fa-download"></i> Download Backup (.sql)</a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
