<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../includes/auth.php';
startSecureSession();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $user = authenticateUser(openDatabase(), isset($_POST['email']) ? $_POST['email'] : '', isset($_POST['password']) ? $_POST['password'] : '');
    if (!$user) $error = 'Invalid email or password.';
    else {
        loginSession($user);
        header('Location: profile.php', true, 303);
        exit;
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main class="container py-4">
    <h1>Log in</h1><?php if (isset($_GET['registered'])): ?><div class="alert alert-success">Account created. You can now log in.</div><?php endif; ?><?php if (isset($_GET['logged_out'])): ?><div class="alert alert-info">You have been logged out.</div><?php endif; ?><?php if (isset($_GET['expired'])): ?><div class="alert alert-warning">Your session expired. Please log in again.</div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?><form method="post" class="col-md-6"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required><button class="btn btn-primary">Log in</button></form>
</main><?php require __DIR__ . '/../includes/footer.php'; ?>