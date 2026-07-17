<?php
$pageTitle = 'Receipt Voucher';
require_once __DIR__ . '/includes/header.php';

$members = $pdo->query("SELECT id, name, member_code FROM members ORDER BY name")->fetchAll();
$vouchers = $pdo->query("SELECT r.*, m.name FROM receipt_vouchers r JOIN members m ON r.member_id = m.id ORDER BY r.id DESC LIMIT 50")->fetchAll();
$showForm = isset($_GET['action']);

require_once __DIR__ . '/includes/sidebar.php';
?>

<?php if ($showForm): ?>
<div class="card-panel">
    <h5>New Receipt Voucher</h5>
    <form action="backend/save_voucher.php" method="POST">
        <input type="hidden" name="type" value="receipt">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="voucher_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label">Member</label>
                <select name="member_id" class="form-select" required>
                    <option value="">-- Select Member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['member_code'] . ' - ' . $m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Amount (₹)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                    <option>Cash</option><option>Bank</option><option>UPI</option><option>Cheque</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control">
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            <a href="receipt.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Receipt Vouchers</h5>
        <a href="receipt.php?action=add" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus"></i> New Receipt</a>
    </div>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>Receipt No.</th><th>Date</th><th>Member</th><th>Amount</th><th>Mode</th><th>Remarks</th><th></th></tr></thead>
        <tbody>
        <?php if (count($vouchers) === 0): ?>
            <tr><td colspan="7" class="text-center text-muted py-3">No receipts yet.</td></tr>
        <?php else: foreach ($vouchers as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['receipt_no']) ?></td>
                <td><?= $v['receipt_date'] ?></td>
                <td><?= htmlspecialchars($v['name']) ?></td>
                <td>₹<?= number_format($v['amount'],2) ?></td>
                <td><?= $v['payment_mode'] ?></td>
                <td><?= htmlspecialchars($v['remarks']) ?></td>
                <td><a href="backend/print_voucher.php?type=receipt&id=<?= $v['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i></a></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
