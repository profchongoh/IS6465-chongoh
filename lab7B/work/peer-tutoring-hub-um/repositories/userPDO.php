<?php

function findUserByEmail($pdo, $email)
{
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, password_hash, role, status, created_at FROM users WHERE email = :email');
    $stmt->execute(array('email' => $email));
    return $stmt->fetch();
}

function findUserById($pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT user_id, full_name, email, password_hash, role, status, created_at FROM users WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $userId));
    return $stmt->fetch();
}

function insertUser($pdo, $fullName, $email, $passwordHash, $role)
{
    $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, status) VALUES (:full_name, :email, :password_hash, :role, \'Active\')');
    $stmt->execute(array('full_name' => $fullName, 'email' => $email, 'password_hash' => $passwordHash, 'role' => $role));
    return (int) $pdo->lastInsertId();
}

function listUsers($pdo, $search, $role, $status)
{
    $sql = 'SELECT user_id, full_name, email, role, status, created_at FROM users WHERE 1 = 1';
    $params = array();
    if ($search !== '') {
        $sql .= ' AND (full_name LIKE :search OR email LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }
    if ($role !== '') {
        $sql .= ' AND role = :role';
        $params['role'] = $role;
    }
    if ($status !== '') {
        $sql .= ' AND status = :status';
        $params['status'] = $status;
    }
    $sql .= ' ORDER BY full_name, user_id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function updateOwnUser($pdo, $userId, $fullName, $email)
{
    $stmt = $pdo->prepare('UPDATE users SET full_name = :full_name, email = :email WHERE user_id = :user_id');
    $stmt->execute(array('full_name' => $fullName, 'email' => $email, 'user_id' => $userId));
    return $stmt->rowCount() === 1;
}

function updateUserPasswordHash($pdo, $userId, $passwordHash)
{
    $stmt = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id');
    return $stmt->execute(array('password_hash' => $passwordHash, 'user_id' => $userId));
}

function updateUserRole($pdo, $userId, $role)
{
    $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE user_id = :user_id');
    return $stmt->execute(array('role' => $role, 'user_id' => $userId));
}

function updateUserStatus($pdo, $userId, $status)
{
    $stmt = $pdo->prepare('UPDATE users SET status = :status WHERE user_id = :user_id');
    return $stmt->execute(array('status' => $status, 'user_id' => $userId));
}

function countActiveAdministrators($pdo)
{
    return (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'Administrator' AND status = 'Active'")->fetchColumn();
}

function findStudentForUser($pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT student_id, student_name, student_email, user_id FROM students WHERE user_id = :user_id');
    $stmt->execute(array('user_id' => $userId));
    return $stmt->fetch();
}

function linkStudentToUser($pdo, $studentId, $userId, $fullName, $email)
{
    $stmt = $pdo->prepare('UPDATE students SET user_id = :user_id, student_name = :student_name, student_email = :student_email WHERE student_id = :student_id');
    return $stmt->execute(array('user_id' => $userId, 'student_name' => $fullName, 'student_email' => $email, 'student_id' => $studentId));
}

function unlinkStudentFromUser($pdo, $userId)
{
    $stmt = $pdo->prepare('UPDATE students SET user_id = NULL WHERE user_id = :user_id');
    return $stmt->execute(array('user_id' => $userId));
}

function deleteUser($pdo, $userId)
{
    $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = :user_id');
    return $stmt->execute(array('user_id' => $userId));
}

function countRequestsForUser($pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM tutoring_requests r JOIN students s ON r.student_id = s.student_id WHERE s.user_id = :user_id');
    $stmt->execute(array('user_id' => $userId));
    return (int) $stmt->fetchColumn();
}
