<?php
$pageTitle = 'Payment Voucher';
require_once __DIR__ . '/includes/header.php';

$vouchers = $pdo->query("SELECT * FROM payment_vouchers ORDER BY id DESC LIMIT 50")->fetchAll();
$showForm = isset($_GET['action']);

require_once __DIR__ . '/includes/sidebar.php';
?>

<?php if ($showForm): ?>
<div class="card-panel">
    <h5>New Payment Voucher</h5>
    <form action="backend/save_voucher.php" method="POST">
        <input type="hidden" name="type" value="payment">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="voucher_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label">Paid To</label>
                <input type="text" name="paid_to" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Amount (₹)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Purpose</label>
                <input type="text" name="purpose" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment Mode</label>
                <select name="payment_mode" class="form-select">
                    <option>Cash</option><option>Bank</option><option>UPI</option><option>Cheque</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            <a href="payment.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Payment Vouchers</h5>
        <a href="payment.php?action=add" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus"></i> New Payment</a>
    </div>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>Voucher No.</th><th>Date</th><th>Paid To</th><th>Amount</th><th>Purpose</th><th>Mode</th><th></th></tr></thead>
        <tbody>
        <?php if (count($vouchers) === 0): ?>
            <tr><td colspan="7" class="text-center text-muted py-3">No payments yet.</td></tr>
        <?php else: foreach ($vouchers as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['voucher_no']) ?></td>
                <td><?= $v['voucher_date'] ?></td>
                <td><?= htmlspecialchars($v['paid_to']) ?></td>
                <td>₹<?= number_format($v['amount'],2) ?></td>
                <td><?= htmlspecialchars($v['purpose']) ?></td>
                <td><?= $v['payment_mode'] ?></td>
                <td><a href="backend/print_voucher.php?type=payment&id=<?= $v['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i></a></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
