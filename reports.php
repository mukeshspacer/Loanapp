<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';

$report = $_GET['report'] ?? 'daily_collection';

$data = [];
$columns = [];

switch ($report) {
    case 'monthly_collection':
        $columns = ['Date', 'Receipts (₹)'];
        $rows = $pdo->query("SELECT receipt_date, SUM(amount) total FROM receipt_vouchers
                              WHERE receipt_date BETWEEN DATE_FORMAT(CURDATE(),'%Y-%m-01') AND LAST_DAY(CURDATE())
                              GROUP BY receipt_date ORDER BY receipt_date")->fetchAll();
        foreach ($rows as $r) $data[] = [$r['receipt_date'], number_format($r['total'],2)];
        break;

    case 'outstanding_loans':
        $columns = ['Loan No.', 'Member', 'Loan Amount', 'Balance'];
        $rows = $pdo->query("SELECT l.loan_number, m.name, l.loan_amount,
                              (l.loan_amount - COALESCE((SELECT SUM(paid_amount) FROM emi_payments WHERE loan_id=l.id),0)) AS balance
                              FROM loans l JOIN members m ON l.member_id=m.id WHERE l.status='Active'")->fetchAll();
        foreach ($rows as $r) $data[] = [$r['loan_number'], $r['name'], number_format($r['loan_amount'],2), number_format($r['balance'],2)];
        break;

    case 'overdue_emi':
        $columns = ['Loan No.', 'Member', 'Due Date', 'Amount Due'];
        $rows = $pdo->query("SELECT l.loan_number, m.name, e.due_date, (e.amount - e.paid_amount) AS due
                              FROM emi_payments e JOIN loans l ON e.loan_id=l.id JOIN members m ON l.member_id=m.id
                              WHERE e.status != 'Paid' AND e.due_date < CURDATE()")->fetchAll();
        foreach ($rows as $r) $data[] = [$r['loan_number'], $r['name'], $r['due_date'], number_format($r['due'],2)];
        break;

    case 'profit_loss':
        $columns = ['Item', 'Amount (₹)'];
        $totalInterest = $pdo->query("SELECT COALESCE(SUM(loan_amount * interest_rate/100 * tenure_months/12),0) FROM loans")->fetchColumn();
        $totalExpense = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payment_vouchers")->fetchColumn();
        $data[] = ['Total Interest Income', number_format($totalInterest,2)];
        $data[] = ['Total Expenses (Payments)', number_format($totalExpense,2)];
        $data[] = ['Net Profit', number_format($totalInterest - $totalExpense,2)];
        break;

    case 'cash_book':
        header('Location: ledger.php'); exit;

    case 'member_ledger':
    case 'cash_ledger':
    case 'bank_ledger':
    case 'expense_ledger':
        $columns = ['Date', 'Description', 'Amount'];
        if ($report === 'expense_ledger') {
            $rows = $pdo->query("SELECT voucher_date d, purpose desc_, amount FROM payment_vouchers ORDER BY voucher_date DESC")->fetchAll();
        } else {
            $rows = $pdo->query("SELECT r.receipt_date d, m.name desc_, r.amount FROM receipt_vouchers r JOIN members m ON r.member_id=m.id ORDER BY r.receipt_date DESC")->fetchAll();
        }
        foreach ($rows as $r) $data[] = [$r['d'], htmlspecialchars($r['desc_']), number_format($r['amount'],2)];
        break;

    case 'member_statement':
        $columns = ['Member', 'Total Loans', 'Total Paid', 'Balance'];
        $rows = $pdo->query("SELECT m.name,
                              COALESCE(SUM(l.loan_amount),0) total_loan,
                              COALESCE((SELECT SUM(paid_amount) FROM emi_payments e JOIN loans l2 ON e.loan_id=l2.id WHERE l2.member_id=m.id),0) total_paid
                              FROM members m LEFT JOIN loans l ON l.member_id = m.id GROUP BY m.id")->fetchAll();
        foreach ($rows as $r) $data[] = [$r['name'], number_format($r['total_loan'],2), number_format($r['total_paid'],2), number_format($r['total_loan']-$r['total_paid'],2)];
        break;

    case 'daily_collection':
    default:
        $columns = ['Receipt No.', 'Member', 'Amount', 'Mode'];
        $rows = $pdo->query("SELECT r.receipt_no, m.name, r.amount, r.payment_mode FROM receipt_vouchers r JOIN members m ON r.member_id=m.id WHERE r.receipt_date = CURDATE()")->fetchAll();
        foreach ($rows as $r) $data[] = [$r['receipt_no'], $r['name'], number_format($r['amount'],2), $r['payment_mode']];
        $report = 'daily_collection';
        break;
}

$reportLabels = [
    'daily_collection' => 'Daily Collection',
    'monthly_collection' => 'Monthly Collection',
    'outstanding_loans' => 'Outstanding Loans',
    'overdue_emi' => 'Overdue EMI',
    'profit_loss' => 'Profit & Loss',
    'member_statement' => 'Member Statement',
    'cash_ledger' => 'Cash Ledger',
    'bank_ledger' => 'Bank Ledger',
    'member_ledger' => 'Member Ledger',
    'expense_ledger' => 'Expense Ledger',
];

require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="card-panel">
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($reportLabels as $key => $label): ?>
            <a href="reports.php?report=<?= $key ?>" class="btn btn-sm <?= $report==$key?'btn-primary':'btn-outline-secondary' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <h5><?= $reportLabels[$report] ?? 'Report' ?></h5>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><?php foreach ($columns as $c): ?><th><?= $c ?></th><?php endforeach; ?></tr></thead>
        <tbody>
        <?php if (count($data) === 0): ?>
            <tr><td colspan="<?= count($columns) ?>" class="text-center text-muted py-3">No data available.</td></tr>
        <?php else: foreach ($data as $row): ?>
            <tr><?php foreach ($row as $cell): ?><td><?= $cell ?></td><?php endforeach; ?></tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
