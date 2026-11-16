<?php

declare(strict_types=1);
function findActiveUserByEmail(PDO $p, string $e)
{
    $s = $p->prepare('SELECT user_id,full_name,email,password_hash,active FROM users WHERE email=:email AND active=1');
    $s->execute(['email' => $e]);
    return $s->fetch();
}

function findUserById(PDO $p, int $id)
{
    $s = $p->prepare('SELECT user_id,full_name,email,active,created_at FROM users WHERE user_id=:id');
    $s->execute(['id' => $id]);
    return $s->fetch();
}

function findUserWithHash(PDO $p, int $id)
{
    $s = $p->prepare('SELECT user_id,full_name,email,password_hash,active FROM users WHERE user_id=:id');
    $s->execute(['id' => $id]);
    return $s->fetch();
}
function loadRoles(PDO $p, int $id): array
{
    $s = $p->prepare('SELECT role FROM user_roles WHERE user_id=:id ORDER BY role');
    $s->execute(['id' => $id]);
    return array_column($s->fetchAll(), 'role');
}
function createUser(PDO $p, string $n, string $e, string $h, string $r): int
{
    $p->beginTransaction();
    try {
        $s = $p->prepare('INSERT INTO users(full_name,email,password_hash,active) VALUES(:n,:e,:h,1)');
        $s->execute(['n' => $n, 'e' => $e, 'h' => $h]);
        $id = (int)$p->lastInsertId();
        $s = $p->prepare('INSERT INTO user_roles(user_id,role) VALUES(:id,:r)');
        $s->execute(['id' => $id, 'r' => $r]);
        if ($r === 'student') {
            $s = $p->prepare('INSERT INTO students(student_name,student_email,user_id) VALUES(:n,:e,:id)');
            $s->execute(['n' => $n, 'e' => $e, 'id' => $id]);
        }
        $p->commit();
        return $id;
    } catch (Throwable $x) {
        if ($p->inTransaction()) $p->rollBack();
        throw $x;
    }
}
function updateOwnProfile(PDO $p, int $id, string $n, string $e): bool
{
    $p->beginTransaction();
    try {
        $s = $p->prepare('UPDATE users SET full_name=:n,email=:e WHERE user_id=:id');
        $s->execute(['n' => $n, 'e' => $e, 'id' => $id]);
        $changed = $s->rowCount();
        $s = $p->prepare('UPDATE students SET student_name=:n,student_email=:e WHERE user_id=:id');
        $s->execute(['n' => $n, 'e' => $e, 'id' => $id]);
        $p->commit();
        return $changed === 1;
    } catch (Throwable $x) {
        if ($p->inTransaction()) $p->rollBack();
        throw $x;
    }
}
function updatePasswordHash(PDO $p, int $id, string $h): bool
{
    $s = $p->prepare('UPDATE users SET password_hash=:h WHERE user_id=:id AND active=1');
    $s->execute(['h' => $h, 'id' => $id]);
    return $s->rowCount() === 1;
}
function searchUsers(PDO $p, string $q, string $r, string $status): array
{
    $sql = 'SELECT u.user_id,u.full_name,u.email,u.active,u.created_at,GROUP_CONCAT(ur.role) roles FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.user_id WHERE 1=1';
    $a = [];
    if ($q !== '') {
        $sql .= ' AND (u.full_name LIKE :q OR u.email LIKE :q)';
        $a['q'] = '%' . $q . '%';
    }
    if ($r !== '') {
        $sql .= ' AND EXISTS (SELECT 1 FROM user_roles x WHERE x.user_id=u.user_id AND x.role=:r)';
        $a['r'] = $r;
    }
    if ($status !== '') {
        $sql .= ' AND u.active=:a';
        $a['a'] = $status === 'active' ? 1 : 0;
    }
    $sql .= ' GROUP BY u.user_id ORDER BY u.full_name';
    $s = $p->prepare($sql);
    $s->execute($a);
    return $s->fetchAll();
}
function replaceUserRole(PDO $p, int $id, string $r): bool
{
    $p->beginTransaction();
    try {
        $s = $p->prepare('DELETE FROM user_roles WHERE user_id=:id');
        $s->execute(['id' => $id]);
        $s = $p->prepare('INSERT INTO user_roles(user_id,role) VALUES(:id,:r)');
        $s->execute(['id' => $id, 'r' => $r]);
        $p->commit();
        return true;
    } catch (Throwable $x) {
        if ($p->inTransaction()) $p->rollBack();
        throw $x;
    }
}
function setUserActive(PDO $p, int $id, bool $a): bool
{
    $s = $p->prepare('UPDATE users SET active=:a WHERE user_id=:id');
    $s->execute(['a' => $a ? 1 : 0, 'id' => $id]);
    return $s->rowCount() === 1;
}
function activeAdminCount(PDO $p): int
{
    return (int)$p->query("SELECT COUNT(*) FROM users u JOIN user_roles r ON r.user_id=u.user_id WHERE u.active=1 AND r.role='admin'")->fetchColumn();
}
function userHasTutoringRecords(PDO $p, int $id): bool
{
    $s = $p->prepare('SELECT EXISTS(SELECT 1 FROM tutoring_requests r JOIN students s ON s.student_id=r.student_id WHERE s.user_id=:id)');
    $s->execute(['id' => $id]);
    return (bool)$s->fetchColumn();
}
function deleteUserWithoutRequests(PDO $p, int $id): bool
{
    $s = $p->prepare('DELETE FROM users WHERE user_id=:id');
    $s->execute(['id' => $id]);
    return $s->rowCount() === 1;
}
