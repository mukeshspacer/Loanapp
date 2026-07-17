<?php
$pageTitle = 'Members';
require_once __DIR__ . '/includes/header.php';

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header('Location: members.php');
    exit;
}

// Search
$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE name LIKE ? OR member_code LIKE ? OR mobile LIKE ? ORDER BY id DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM members ORDER BY id DESC");
}
$members = $stmt->fetchAll();

// Editing an existing member?
$editMember = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editMember = $stmt->fetch();
}
$showForm = isset($_GET['action']) || $editMember;

require_once __DIR__ . '/includes/sidebar.php';
?>

<?php if ($showForm): ?>
<div class="card-panel">
    <h5><?= $editMember ? 'Edit Member' : 'Add Member' ?></h5>
    <form action="backend/save_member.php" method="POST" enctype="multipart/form-data">
        <?php if ($editMember): ?>
            <input type="hidden" name="id" value="<?= $editMember['id'] ?>">
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editMember['name'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Mobile Number</label>
                <input type="text" name="mobile" class="form-control" required value="<?= htmlspecialchars($editMember['mobile'] ?? '') ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($editMember['address'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Aadhaar Number</label>
                <input type="text" name="aadhaar" class="form-control" value="<?= htmlspecialchars($editMember['aadhaar'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Active" <?= ($editMember['status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= ($editMember['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            <a href="members.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-panel">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0">Members List</h5>
        <div class="d-flex gap-2">
            <form class="d-flex" method="GET">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / mobile / ID" value="<?= htmlspecialchars($search) ?>">
                <button class="btn btn-sm btn-outline-secondary ms-1"><i class="fa-solid fa-search"></i></button>
            </form>
            <a href="members.php?action=add" class="btn btn-sm btn-primary text-nowrap"><i class="fa-solid fa-plus"></i> Add Member</a>
        </div>
    </div>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>Photo</th><th>Member ID</th><th>Name</th><th>Mobile</th><th>Aadhaar</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (count($members) === 0): ?>
            <tr><td colspan="7" class="text-center text-muted py-3">No members found.</td></tr>
        <?php else: foreach ($members as $m): ?>
            <tr>
                <td>
                    <?php if (!empty($m['photo'])): ?>
                        <img src="assets/images/<?= htmlspecialchars($m['photo']) ?>" width="36" height="36" style="border-radius:50%;object-fit:cover">
                    <?php else: ?>
                        <i class="fa-solid fa-circle-user fa-xl text-secondary"></i>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($m['member_code']) ?></td>
                <td><?= htmlspecialchars($m['name']) ?></td>
                <td><?= htmlspecialchars($m['mobile']) ?></td>
                <td><?= htmlspecialchars($m['aadhaar']) ?></td>
                <td><span class="badge-status badge-<?= strtolower($m['status']) ?>"><?= $m['status'] ?></span></td>
                <td>
                    <a href="members.php?edit=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                    <a href="members.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this member?')"><i class="fa-solid fa-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
