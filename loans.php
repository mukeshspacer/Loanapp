<?php
$pageTitle = 'Loans';
require_once __DIR__ . '/includes/header.php';

$members = $pdo->query("SELECT id, name, member_code FROM members WHERE status='Active' ORDER BY name")->fetchAll();
$loans = $pdo->query("SELECT l.*, m.name, m.member_code FROM loans l JOIN members m ON l.member_id = m.id ORDER BY l.id DESC")->fetchAll();
$showForm = isset($_GET['action']);

require_once __DIR__ . '/includes/sidebar.php';
?>

<?php if ($showForm): ?>
<div class="card-panel">
    <h5>New Loan</h5>
    <form action="backend/save_loan.php" method="POST" id="loanForm">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Member Name</label>
                <select name="member_id" class="form-select" required>
                    <option value="">-- Select Member --</option>
                    <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['member_code'] . ' - ' . $m['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Loan Amount (₹)</label>
                <input type="number" step="0.01" name="loan_amount" id="loan_amount" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Interest Rate (% per annum)</label>
                <input type="number" step="0.01" name="interest_rate" id="interest_rate" class="form-control" required value="12">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tenure (months)</label>
                <input type="number" name="tenure_months" id="tenure_months" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">EMI Amount (₹) <small class="text-muted">(auto / editable)</small></label>
                <input type="number" step="0.01" name="emi_amount" id="emi_amount" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Loan Date</label>
                <input type="date" name="loan_date" id="loan_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">First Due Date</label>
                <input type="date" name="due_date" id="due_date" class="form-control" required>
            </div>
        </div>
        <div class="mt-3">
            <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            <a href="loans.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
<script>
// Simple EMI auto-calculation: Principal + Flat Interest / Tenure
function calcEMI() {
    const p = parseFloat(document.getElementById('loan_amount').value) || 0;
    const r = parseFloat(document.getElementById('interest_rate').value) || 0;
    const n = parseInt(document.getElementById('tenure_months').value) || 0;
    if (p > 0 && n > 0) {
        const totalInterest = p * (r / 100) * (n / 12);
        const emi = (p + totalInterest) / n;
        document.getElementById('emi_amount').value = emi.toFixed(2);
    }
}
['loan_amount','interest_rate','tenure_months'].forEach(id => {
    document.getElementById(id).addEventListener('input', calcEMI);
});
// Default due date = loan date + 1 month
document.getElementById('loan_date').addEventListener('change', function() {
    const d = new Date(this.value);
    d.setMonth(d.getMonth() + 1);
    document.getElementById('due_date').value = d.toISOString().split('T')[0];
});
</script>
<?php endif; ?>

<div class="card-panel">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Loans List</h5>
        <a href="loans.php?action=add" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus"></i> New Loan</a>
    </div>
    <div class="table-responsive">
    <table class="app-table">
        <thead><tr><th>Loan No.</th><th>Member</th><th>Amount</th><th>Rate</th><th>Tenure</th><th>EMI</th><th>Due Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (count($loans) === 0): ?>
            <tr><td colspan="9" class="text-center text-muted py-3">No loans found.</td></tr>
        <?php else: foreach ($loans as $l): ?>
            <tr>
                <td><?= htmlspecialchars($l['loan_number']) ?></td>
                <td><?= htmlspecialchars($l['name']) ?> <span class="text-muted small">(<?= htmlspecialchars($l['member_code']) ?>)</span></td>
                <td>₹<?= number_format($l['loan_amount'], 2) ?></td>
                <td><?= $l['interest_rate'] ?>%</td>
                <td><?= $l['tenure_months'] ?> mo</td>
                <td>₹<?= number_format($l['emi_amount'], 2) ?></td>
                <td><?= $l['due_date'] ?></td>
                <td><span class="badge-status badge-<?= strtolower($l['status']) ?>"><?= $l['status'] ?></span></td>
                <td>
                    <a href="backend/print_agreement.php?id=<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i></a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
