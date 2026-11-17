<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../includes/auth.php';
$pdo = openDatabase();
$user = requireLogin($pdo);
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    try {
        updateOwnAccount($pdo, (int)$user['user_id'], $_POST);
        $user = findUserById($pdo, (int)$user['user_id']);
        $success = 'Profile updated.';
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main class="container py-4">
    <h1>My profile</h1><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?><form method="post" class="col-md-7"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><label class="form-label">Full name</label><input class="form-control mb-3" name="full_name" maxlength="120" required value="<?= htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8') ?>"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" maxlength="254" required value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>">
        <p><strong>Role:</strong> <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?><br><strong>Status:</strong> <?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?></p><button class="btn btn-primary">Save changes</button> <a class="btn btn-secondary" href="change-password.php">Change password</a>
    </form>
</main><?php require __DIR__ . '/../includes/footer.php'; ?>