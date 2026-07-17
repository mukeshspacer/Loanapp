<?php
$pageTitle = 'Cash Book / Ledger';
require_once __DIR__ . '/includes/header.php';

$filter = $_GET['filter'] ?? 'today';
$customDate = $_GET['date'] ?? date('Y-m-d');

if ($filter === 'today') {
    $dateCond = "= CURDATE()";
} elseif ($filter === 'month') {
    $dateCond = "BETWEEN DATE_FORMAT(CURDATE(),'%Y-%m-01') AND LAST_DAY(CURDATE())";
} else {
    $dateCond = "= " . $pdo->quote($customDate);
}

$receipts = $pdo->query("SELECT receipt_date AS date, amount, 'Receipt' AS type, remarks AS note FROM receipt_vouchers WHERE receipt_date $dateCond")->fetchAll();
$payments = $pdo->query("SELECT voucher_date AS date, amount, 'Payment' AS type, purpose AS note FROM payment_vouchers WHERE voucher_date $dateCond")->fetchAll();

$entries = array_merge($receipts, $payments);
usort($entries, fn($a, $b) => strcmp($a['date'], $b['date']));

$runningBalance = 0;
foreach ($entries as &$e) {
    $runningBalance += $e['type'] === 'Receipt' ? $e['amount'] : -$e['amount'];
    $e['balance'] = $runningBalance;
}
unset($e);

require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="card-panel">
    <h5>Cash Book</h5>
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="ledger.php?filter=today" class="btn btn-sm <?= $filter=='today'?'btn-primary':'btn-outline-secondary' ?>">Today</a>
        <a href="ledger.php?filter=month" class="btn btn-sm <?= $filter=='month'?'btn-primary':'btn-outline-secondary' ?>">This Month</a>
        <form method="GET" class="d-flex gap-1">
            <input type="hidden" name="filter" value="custom">
            <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($customDate) ?>">
            <button class="btn btn-sm btn-outline-secondary">Custom Date</button>
        </form>
    </div>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>Date</th><th>Type</th><th>Note</th><th>Receipt</th><th>Payment</th><th>Balance</th></tr></thead>
        <tbody>
        <?php if (count($entries) === 0): ?>
            <tr><td colspan="6" class="text-center text-muted py-3">No entries for this period.</td></tr>
        <?php else: foreach ($entries as $e): ?>
            <tr>
                <td><?= $e['date'] ?></td>
                <td><?= $e['type'] ?></td>
                <td><?= htmlspecialchars($e['note']) ?></td>
                <td><?= $e['type']==='Receipt' ? '₹'.number_format($e['amount'],2) : '' ?></td>
                <td><?= $e['type']==='Payment' ? '₹'.number_format($e['amount'],2) : '' ?></td>
                <td>₹<?= number_format($e['balance'],2) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card-panel">
    <h5>General Ledger</h5>
    <div class="d-flex flex-wrap gap-2 mb-2">
        <a href="reports.php?report=cash_ledger" class="btn btn-sm btn-outline-primary">Cash Ledger</a>
        <a href="reports.php?report=bank_ledger" class="btn btn-sm btn-outline-primary">Bank Ledger</a>
        <a href="reports.php?report=member_ledger" class="btn btn-sm btn-outline-primary">Member Ledger</a>
        <a href="reports.php?report=expense_ledger" class="btn btn-sm btn-outline-primary">Expense Ledger</a>
    </div>
    <div class="d-flex gap-2">
        <span class="text-muted small align-self-center">Export:</span>
        <a href="backend/export_ledger.php?format=pdf&filter=<?= $filter ?>&date=<?= $customDate ?>" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-file-pdf"></i> PDF</a>
        <a href="backend/export_ledger.php?format=excel&filter=<?= $filter ?>&date=<?= $customDate ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-file-excel"></i> Excel</a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
