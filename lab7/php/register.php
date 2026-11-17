<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
require_once __DIR__ . '/../includes/auth.php';
startSecureSession();
$error = '';
$values = array('full_name' => '', 'email' => '', 'role' => 'Student');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken($_POST);
    $values = array('full_name' => (string)(isset($_POST['full_name']) ? $_POST['full_name'] : ''), 'email' => (string)(isset($_POST['email']) ? $_POST['email'] : ''), 'role' => (string)(isset($_POST['role']) ? $_POST['role'] : ''));
    try {
        $id = registerUser(openDatabase(), $_POST);
        header('Location: login.php?registered=1', true, 303);
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to create the account.';
    }
}
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php'; ?>
<main class="container py-4">
    <h1>Create account</h1><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post" class="col-md-7"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><label class="form-label">Full name</label><input class="form-control mb-3" name="full_name" maxlength="120" required value="<?= htmlspecialchars($values['full_name'], ENT_QUOTES, 'UTF-8') ?>"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" maxlength="254" required value="<?= htmlspecialchars($values['email'], ENT_QUOTES, 'UTF-8') ?>"><label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" minlength="12" required><label class="form-label">Confirm password</label><input class="form-control mb-3" type="password" name="confirm_password" minlength="12" required><label class="form-label">Account type</label><select class="form-select mb-3" name="role">
            <option value="Student" <?= $values['role'] === 'Student' ? 'selected' : '' ?>>Student</option>
            <option value="Tutor" <?= $values['role'] === 'Tutor' ? 'selected' : '' ?>>Tutor</option>
        </select><button class="btn btn-primary">Create account</button>
        </form>
</main><?php require __DIR__ . '/../includes/footer.php'; ?>