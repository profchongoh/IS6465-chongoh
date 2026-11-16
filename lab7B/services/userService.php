<?php

require_once dirname(__DIR__) . '/repositories/userPDO.php';
require_once dirname(__DIR__) . '/repositories/studentPDO.php';

function userRoles()
{
    return array('Student', 'Tutor', 'Administrator');
}

function userStatuses()
{
    return array('Active', 'Inactive');
}

function validateAccountDetails($input, $includePassword)
{
    $errors = array();
    $fullName = trim((string) (isset($input['full_name']) ? $input['full_name'] : ''));
    $email = strtolower(trim((string) (isset($input['email']) ? $input['email'] : '')));
    if ($fullName === '' || mb_strlen($fullName) > 120) {
        $errors[] = 'Enter a name of 1 to 120 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($includePassword) {
        $password = (string) (isset($input['password']) ? $input['password'] : '');
        $confirmation = (string) (isset($input['confirm_password']) ? $input['confirm_password'] : '');
        if (strlen($password) < 12) {
            $errors[] = 'Use a password of at least 12 characters.';
        }
        if ($password !== $confirmation) {
            $errors[] = 'Password confirmation does not match.';
        }
    }
    return array($errors, array('full_name' => $fullName, 'email' => $email));
}

function registerUser($pdo, $input)
{
    list($errors, $data) = validateAccountDetails($input, true);
    $role = isset($input['role']) ? (string) $input['role'] : '';
    if (!in_array($role, array('Student', 'Tutor'), true)) {
        $errors[] = 'Choose Student or Tutor.';
    }
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }
    if (findUserByEmail($pdo, $data['email'])) {
        throw new InvalidArgumentException('An account already uses this email address.');
    }
    $pdo->beginTransaction();
    try {
        $userId = insertUser($pdo, $data['full_name'], $data['email'], password_hash($input['password'], PASSWORD_DEFAULT), $role);
        if ($role === 'Student') {
            $student = findStudentByEmail($pdo, $data['email']);
            if ($student) {
                linkStudentToUser($pdo, (int) $student['student_id'], $userId, $data['full_name'], $data['email']);
            } else {
                insertStudentForUser($pdo, $data['full_name'], $data['email'], $userId);
            }
        }
        $pdo->commit();
        return $userId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function updateOwnAccount($pdo, $userId, $input)
{
    list($errors, $data) = validateAccountDetails($input, false);
    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }
    $current = findUserById($pdo, $userId);
    if (!$current) {
        throw new RuntimeException('Account not found.');
    }
    $other = findUserByEmail($pdo, $data['email']);
    if ($other && (int) $other['user_id'] !== (int) $userId) {
        throw new InvalidArgumentException('An account already uses this email address.');
    }
    $pdo->beginTransaction();
    try {
        updateOwnUser($pdo, $userId, $data['full_name'], $data['email']);
        if ($current['role'] === 'Student') {
            $student = findStudentForUser($pdo, $userId);
            if ($student) {
                linkStudentToUser($pdo, (int) $student['student_id'], $userId, $data['full_name'], $data['email']);
            }
        }
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function changePassword($pdo, $userId, $input)
{
    $user = findUserById($pdo, $userId);
    $current = isset($input['current_password']) ? (string) $input['current_password'] : '';
    $new = isset($input['new_password']) ? (string) $input['new_password'] : '';
    $confirm = isset($input['confirm_password']) ? (string) $input['confirm_password'] : '';
    if (!$user || !password_verify($current, $user['password_hash'])) {
        throw new InvalidArgumentException('Current password is incorrect.');
    }
    if (strlen($new) < 12) {
        throw new InvalidArgumentException('Use a new password of at least 12 characters.');
    }
    if ($new !== $confirm) {
        throw new InvalidArgumentException('Password confirmation does not match.');
    }
    updateUserPasswordHash($pdo, $userId, password_hash($new, PASSWORD_DEFAULT));
}

function changeUserRole($pdo, $actorId, $targetId, $newRole)
{
    $target = findUserById($pdo, $targetId);
    if (!$target || !in_array($newRole, userRoles(), true)) {
        throw new InvalidArgumentException('Select a valid account and role.');
    }
    if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && $newRole !== 'Administrator' && countActiveAdministrators($pdo) <= 1) {
        throw new InvalidArgumentException('The last active administrator cannot lose the Administrator role.');
    }
    updateUserRole($pdo, $targetId, $newRole);
}

function changeUserStatus($pdo, $actorId, $targetId, $newStatus)
{
    $target = findUserById($pdo, $targetId);
    if (!$target || !in_array($newStatus, userStatuses(), true)) {
        throw new InvalidArgumentException('Select a valid account and status.');
    }
    if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && $newStatus === 'Inactive' && countActiveAdministrators($pdo) <= 1) {
        throw new InvalidArgumentException('The last active administrator cannot be deactivated.');
    }
    updateUserStatus($pdo, $targetId, $newStatus);
}

function removeUserAccount($pdo, $actorId, $targetId)
{
    $target = findUserById($pdo, $targetId);
    if (!$target) {
        throw new InvalidArgumentException('Account not found.');
    }
    if ($target['role'] === 'Administrator' && $target['status'] === 'Active' && countActiveAdministrators($pdo) <= 1) {
        throw new InvalidArgumentException('The last active administrator cannot be deleted.');
    }
    if (countRequestsForUser($pdo, $targetId) > 0) {
        throw new InvalidArgumentException('Account deletion is blocked because tutoring requests must be retained.');
    }
    $pdo->beginTransaction();
    try {
        unlinkStudentFromUser($pdo, $targetId);
        deleteUser($pdo, $targetId);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function authenticateUser($pdo, $email, $password)
{
    $user = findUserByEmail($pdo, strtolower(trim($email)));
    if (!$user || $user['status'] !== 'Active' || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        updateUserPasswordHash($pdo, (int) $user['user_id'], password_hash($password, PASSWORD_DEFAULT));
    }
    return $user;
}
