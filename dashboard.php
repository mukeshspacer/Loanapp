<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Summary stats
$totalMembers = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
$activeLoans  = $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'Active'")->fetchColumn();
$todayCollections = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM receipt_vouchers WHERE receipt_date = CURDATE()")->fetchColumn();
$cashIn = $pdo->query("SELECT
    (SELECT COALESCE(SUM(amount),0) FROM receipt_vouchers) -
    (SELECT COALESCE(SUM(amount),0) FROM payment_vouchers) AS balance
")->fetchColumn();
$outstanding = $pdo->query("SELECT COALESCE(SUM(amount - paid_amount),0) FROM emi_payments WHERE status != 'Paid'")->fetchColumn();
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="icon" style="background:#2563eb"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="value"><?= number_format($totalMembers) ?></div>
                <div class="label">Total Members</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="icon" style="background:#16a34a"><i class="fa-solid fa-money-bill-wave"></i></div>
            <div>
                <div class="value"><?= number_format($activeLoans) ?></div>
                <div class="label">Active Loans</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="icon" style="background:#d97706"><i class="fa-solid fa-calendar-day"></i></div>
            <div>
                <div class="value">₹<?= number_format($todayCollections, 2) ?></div>
                <div class="label">Today's Collections</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="icon" style="background:#0891b2"><i class="fa-solid fa-vault"></i></div>
            <div>
                <div class="value">₹<?= number_format($cashIn, 2) ?></div>
                <div class="label">Cash in Hand</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="icon" style="background:#dc2626"><i class="fa-solid fa-hourglass-half"></i></div>
            <div>
                <div class="value">₹<?= number_format($outstanding, 2) ?></div>
                <div class="label">Outstanding Balance</div>
            </div>
        </div>
    </div>
</div>

<div class="card-panel">
    <h5>Quick Actions</h5>
    <div class="d-flex flex-wrap gap-2">
        <a href="members.php?action=add" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Add Member</a>
        <a href="loans.php?action=add" class="btn btn-success"><i class="fa-solid fa-file-signature"></i> New Loan</a>
        <a href="emi.php" class="btn btn-warning text-white"><i class="fa-solid fa-money-check-dollar"></i> Collect EMI</a>
        <a href="receipt.php?action=add" class="btn btn-info text-white"><i class="fa-solid fa-receipt"></i> Receipt Voucher</a>
        <a href="payment.php?action=add" class="btn btn-secondary"><i class="fa-solid fa-file-invoice-dollar"></i> Payment Voucher</a>
    </div>
</div>

<div class="card-panel">
    <h5>Recent Loans</h5>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>Loan No.</th><th>Member</th><th>Amount</th><th>EMI</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php
        $recent = $pdo->query("SELECT l.*, m.name FROM loans l JOIN members m ON l.member_id = m.id ORDER BY l.id DESC LIMIT 5");
        if ($recent->rowCount() === 0):
        ?>
            <tr><td colspan="6" class="text-center text-muted py-3">No loans yet. Click "New Loan" to add one.</td></tr>
        <?php else: foreach ($recent as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['loan_number']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>₹<?= number_format($row['loan_amount'], 2) ?></td>
                <td>₹<?= number_format($row['emi_amount'], 2) ?></td>
                <td><?= htmlspecialchars($row['due_date']) ?></td>
                <td><span class="badge-status badge-<?= strtolower($row['status']) ?>"><?= $row['status'] ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
