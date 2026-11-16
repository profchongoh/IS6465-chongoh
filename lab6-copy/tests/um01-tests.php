<?php
require_once __DIR__ . '/../services/userService.php';
$passed = 0; $failed = 0;
function checkUM(bool $condition, string $label): void {
    global $passed,$failed;
    if ($condition) { $passed++; echo "PASS: $label\n"; }
    else { $failed++; echo "FAIL: $label\n"; }
}
try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec(file_get_contents(__DIR__ . '/../database/um01-users.sql'));
    $base = ['full_name'=>'Alex Morgan','email'=>'alex@example.edu','password'=>'long-secret-password','confirm_password'=>'long-secret-password','role'=>'Student'];
    $a = registerUser($pdo,$base);
    checkUM($a['ok'], 'UM01-R01 Student registration');
    $row = $pdo->query('SELECT * FROM users LIMIT 1')->fetch();
    checkUM($row && $row['status']==='Active' && $row['role']==='Student', 'UM01-R08 Active Student');
    checkUM($row && $row['password_hash'] !== $base['password'] && password_verify($base['password'],$row['password_hash']), 'UM01-R07 password hash');
    $b = $base; $b['email']='tutor@example.edu'; $b['role']='Tutor';
    checkUM(registerUser($pdo,$b)['ok'], 'UM01-R02 Tutor registration');
    checkUM(!registerUser($pdo,$base)['ok'], 'UM01-R05 duplicate email rejected');
    $bad = $base; $bad['email']='not-an-email';
    checkUM(!registerUser($pdo,$bad)['ok'], 'UM01-R03 invalid email rejected');
    $bad = $base; $bad['email']='mismatch@example.edu'; $bad['confirm_password']='different';
    checkUM(!registerUser($pdo,$bad)['ok'], 'UM01-R04 password mismatch rejected');
    $bad = $base; $bad['email']='admin@example.edu'; $bad['role']='Administrator';
    checkUM(!registerUser($pdo,$bad)['ok'], 'UM01-R06 Administrator registration rejected');
    $bad = $base; $bad['email']='empty@example.edu'; $bad['full_name']='';
    checkUM(!registerUser($pdo,$bad)['ok'], 'UM01-R03 empty name rejected');
    $bad = $base; $bad['email']='short@example.edu'; $bad['password']='short'; $bad['confirm_password']='short';
    checkUM(!registerUser($pdo,$bad)['ok'], 'UM01 password length rejected');
    $bad = $base; $bad['email']='CASE@EXAMPLE.EDU';
    checkUM(!registerUser($pdo,$bad)['ok'], 'UM01 duplicate email case insensitive');
    checkUM((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 2, 'UM01-R10 invalid attempts create no records');
    $bad = $base; $bad['email']='quote@example.edu'; $bad['full_name']="O'Connor <Tutor>";
    checkUM(registerUser($pdo,$bad)['ok'], 'UM01 quoted name stored safely');
    checkUM((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 3, 'UM01 no unintended writes');
} catch (Throwable $e) {
    $failed++; echo 'FAIL: setup or runtime error: ' . $e->getMessage() . "\n";
}
echo "Results: $passed passed, $failed failed\n";
exit($failed ? 1 : 0);
