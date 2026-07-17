<?php
$pageTitle = 'EMI Collection';
require_once __DIR__ . '/includes/header.php';

$search = trim($_GET['q'] ?? '');
$loan = null;
$schedule = [];

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT l.*, m.name, m.member_code FROM loans l JOIN members m ON l.member_id = m.id
                            WHERE l.loan_number LIKE ? OR m.name LIKE ? ORDER BY l.id DESC LIMIT 1");
    $like = "%$search%";
    $stmt->execute([$like, $like]);
    $loan = $stmt->fetch();

    if ($loan) {
        $stmt2 = $pdo->prepare("SELECT * FROM emi_payments WHERE loan_id = ? ORDER BY installment_no");
        $stmt2->execute([$loan['id']]);
        $schedule = $stmt2->fetchAll();
    }
}

require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="card-panel">
    <h5>Search Loan</h5>
    <form method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control" placeholder="Search by Member Name or Loan Number" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary text-nowrap"><i class="fa-solid fa-search"></i> Search</button>
    </form>
</div>

<?php if ($search !== '' && !$loan): ?>
    <div class="alert alert-warning">No loan found for "<?= htmlspecialchars($search) ?>".</div>
<?php endif; ?>

<?php if ($loan):
    $totalLoan = $loan['loan_amount'];
    $paidAmount = array_sum(array_column($schedule, 'paid_amount'));
    $balance = $totalLoan - $paidAmount;
    $todayDue = 0;
    foreach ($schedule as $s) {
        if ($s['status'] !== 'Paid' && $s['due_date'] <= date('Y-m-d')) {
            $todayDue += ($s['amount'] - $s['paid_amount']);
        }
    }
?>
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card"><div><div class="value">₹<?= number_format($totalLoan,2) ?></div><div class="label">Total Loan</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div><div class="value">₹<?= number_format($paidAmount,2) ?></div><div class="label">Paid Amount</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div><div class="value">₹<?= number_format($balance,2) ?></div><div class="label">Balance</div></div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card"><div><div class="value">₹<?= number_format($todayDue,2) ?></div><div class="label">Today's Due</div></div></div>
    </div>
</div>

<div class="card-panel">
    <h5><?= htmlspecialchars($loan['name']) ?> — Loan #<?= htmlspecialchars($loan['loan_number']) ?></h5>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>#</th><th>Due Date</th><th>Amount</th><th>Paid</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($schedule as $s): ?>
            <tr>
                <td><?= $s['installment_no'] ?></td>
                <td><?= $s['due_date'] ?></td>
                <td>₹<?= number_format($s['amount'],2) ?></td>
                <td>₹<?= number_format($s['paid_amount'],2) ?></td>
                <td><span class="badge-status badge-<?= strtolower($s['status']) ?>"><?= $s['status'] ?></span></td>
                <td>
                    <?php if ($s['status'] !== 'Paid'): ?>
                    <form action="backend/collect_emi.php" method="POST" class="d-flex gap-1">
                        <input type="hidden" name="emi_id" value="<?= $s['id'] ?>">
                        <input type="hidden" name="loan_id" value="<?= $loan['id'] ?>">
                        <input type="hidden" name="loan_search" value="<?= htmlspecialchars($search) ?>">
                        <input type="number" step="0.01" name="amount" class="form-control form-control-sm" style="width:110px" value="<?= $s['amount'] - $s['paid_amount'] ?>" required>
                        <button class="btn btn-sm btn-success"><i class="fa-solid fa-check"></i></button>
                    </form>
                    <?php else: ?>
                        <span class="text-muted small">Paid on <?= $s['paid_date'] ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
