<?php
function findUserByEmail(PDO $pdo, string $email) {
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}
function insertRegisteredUser(PDO $pdo, string $name, string $email, string $hash, string $role): int {
    $stmt = $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,status) VALUES (:name,:email,:hash,:role,'Active')");
    $stmt->execute([':name'=>$name, ':email'=>$email, ':hash'=>$hash, ':role'=>$role]);
    return (int)$pdo->lastInsertId();
}
