<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../services/userService.php';
if (empty($_SESSION['registration_csrf'])) $_SESSION['registration_csrf'] = bin2hex(random_bytes(32));
$errors = []; $success = false; $values = ['full_name'=>'','email'=>'','role'=>'Student'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = ['full_name'=>(string)($_POST['full_name'] ?? ''), 'email'=>(string)($_POST['email'] ?? ''), 'role'=>(string)($_POST['role'] ?? '')];
    if (!hash_equals($_SESSION['registration_csrf'], (string)($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Form expired. Refresh and try again.';
    } else {
        try {
            $pdo = openDatabase();
            $result = registerUser($pdo, $_POST);
            $success = $result['ok']; $errors = $result['errors'];
            if ($success) $_SESSION['registration_csrf'] = bin2hex(random_bytes(32));
        } catch (Throwable $e) {
            error_log('UM01 registration error: ' . $e->getMessage());
            $errors[] = 'Registration is temporarily unavailable. Please try again later.';
        }
    }
}
function esc($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/nav.php';
?>
<main class="container py-4" style="max-width:650px">
<h1>Create user account</h1>
<?php if ($success): ?>
<div class="alert alert-success" role="status">Your account has been created and is Active. You can log in when login is implemented.</div>
<?php else: ?>
<?php if ($errors): ?><div class="alert alert-danger" role="alert"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="register.php">
<input type="hidden" name="csrf_token" value="<?= esc($_SESSION['registration_csrf']) ?>">
<div class="mb-3"><label class="form-label" for="full_name">Full name</label><input class="form-control" id="full_name" name="full_name" maxlength="120" required value="<?= esc($values['full_name']) ?>"></div>
<div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" name="email" type="email" maxlength="254" required value="<?= esc($values['email']) ?>"></div>
<div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" name="password" type="password" minlength="12" autocomplete="new-password" required><div class="form-text">At least 12 characters (provisional classroom rule).</div></div>
<div class="mb-3"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required></div>
<fieldset class="mb-3"><legend class="fs-6">Account type</legend>
<?php foreach (['Student','Tutor'] as $role): ?><div class="form-check"><input class="form-check-input" type="radio" name="role" id="role_<?= $role ?>" value="<?= $role ?>" <?= $values['role'] === $role ? 'checked' : '' ?> required><label class="form-check-label" for="role_<?= $role ?>"><?= $role ?></label></div><?php endforeach; ?></fieldset>
<button class="btn btn-primary" type="submit">Create Account</button>
</form>
<?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
