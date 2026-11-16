<?php
require_once __DIR__ . '/../repositories/userPDO.php';
function registerUser(PDO $pdo, array $input): array {
    $name = trim((string)($input['full_name'] ?? ''));
    $email = strtolower(trim((string)($input['email'] ?? '')));
    $password = (string)($input['password'] ?? '');
    $confirm = (string)($input['confirm_password'] ?? '');
    $role = (string)($input['role'] ?? '');
    $errors = [];
    if ($name === '' || mb_strlen($name) > 120) $errors[] = 'Enter a name of up to 120 characters.';
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 12) $errors[] = 'Password must contain at least 12 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (!in_array($role, ['Student','Tutor'], true)) $errors[] = 'Select Student or Tutor.';
    if ($errors) return ['ok'=>false,'errors'=>$errors];
    if (findUserByEmail($pdo, $email)) return ['ok'=>false,'errors'=>['Email is already registered.']];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if ($hash === false) throw new RuntimeException('Password hashing failed.');
    try {
        $id = insertRegisteredUser($pdo, $name, $email, $hash, $role);
    } catch (PDOException $e) {
        // SQLite unique constraint also protects against concurrent submissions.
        if (stripos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
            return ['ok'=>false,'errors'=>['Email is already registered.']];
        }
        throw $e;
    }
    return ['ok'=>true,'user_id'=>$id,'errors'=>[]];
}
