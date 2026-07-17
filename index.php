<?php
require_once __DIR__ . '/backend/db.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
$stmt = $pdo->query("SELECT company_name FROM settings LIMIT 1");
$company = $stmt->fetchColumn() ?: 'Loan Finance System';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | <?= htmlspecialchars($company) ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <h2><i class="fa-solid fa-building-columns text-primary"></i> <?= htmlspecialchars($company) ?></h2>
        <p class="subtitle">Sign in to manage loans &amp; collections</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger py-2">Invalid username or password.</div>
        <?php endif; ?>

        <form action="backend/login_process.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
            <div class="text-center mt-3">
                <a href="#" class="text-decoration-none small">Forgot Password?</a>
            </div>
        </form>
        <p class="text-center text-muted small mt-4 mb-0">Default: admin / admin123</p>
    </div>
</div>
</body>
</html>
