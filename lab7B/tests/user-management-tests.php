<?php
require_once dirname(__DIR__) . '/services/userService.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/setup-database.sql'));
$passed = 0; $failed = 0;
function checkUserManagement($condition, $label) { global $passed, $failed; if ($condition) { $passed++; echo 'PASS: ' . $label . PHP_EOL; } else { $failed++; echo 'FAIL: ' . $label . PHP_EOL; } }

$student = array('full_name'=>'Jordan Lee','email'=>'jordan@example.edu','password'=>'CorrectHorseBattery1','confirm_password'=>'CorrectHorseBattery1','role'=>'Student');
$studentId = registerUser($pdo, $student); $studentRow = findUserById($pdo, $studentId);
checkUserManagement($studentRow['status'] === 'Active' && $studentRow['role'] === 'Student', 'UM-01 creates an active Student account');
checkUserManagement(findStudentForUser($pdo, $studentId) !== false, 'UM-01 links a student account to a student record');
try { registerUser($pdo, $student); checkUserManagement(false, 'UM-01 rejects duplicate email'); } catch (InvalidArgumentException $e) { checkUserManagement(true, 'UM-01 rejects duplicate email'); }
checkUserManagement(findUserById($pdo, $studentId)['email'] === 'jordan@example.edu', 'UM-02 retrieves the authenticated account');
updateOwnAccount($pdo, $studentId, array('full_name'=>'Jordan A Lee','email'=>'jordan.a@example.edu'));
checkUserManagement(findUserById($pdo, $studentId)['full_name'] === 'Jordan A Lee', 'UM-04 updates only own permitted fields');
changePassword($pdo, $studentId, array('current_password'=>'CorrectHorseBattery1','new_password'=>'NewCorrectPassword2','confirm_password'=>'NewCorrectPassword2'));
checkUserManagement(authenticateUser($pdo, 'jordan.a@example.edu', 'NewCorrectPassword2') !== false && authenticateUser($pdo, 'jordan.a@example.edu', 'CorrectHorseBattery1') === false, 'UM-05 replaces the password hash');
$adminId = insertUser($pdo, 'Admin User', 'admin@example.edu', password_hash('AdministratorPass1', PASSWORD_DEFAULT), 'Administrator');
$matches = listUsers($pdo, 'Jordan', 'Student', 'Active');
checkUserManagement(count($matches) === 1 && (int)$matches[0]['user_id'] === $studentId, 'UM-03 filters user accounts');
changeUserRole($pdo, $adminId, $studentId, 'Tutor'); checkUserManagement(findUserById($pdo, $studentId)['role'] === 'Tutor', 'UM-06 updates a user role');
changeUserStatus($pdo, $adminId, $studentId, 'Inactive'); checkUserManagement(authenticateUser($pdo, 'jordan.a@example.edu', 'NewCorrectPassword2') === false, 'UM-07 prevents inactive login');
changeUserStatus($pdo, $adminId, $studentId, 'Active'); checkUserManagement(authenticateUser($pdo, 'jordan.a@example.edu', 'NewCorrectPassword2') !== false, 'UM-09 authenticates active user credentials');
try { changeUserStatus($pdo, $adminId, $adminId, 'Inactive'); checkUserManagement(false, 'UM-07 protects the last active administrator'); } catch (InvalidArgumentException $e) { checkUserManagement(true, 'UM-07 protects the last active administrator'); }
removeUserAccount($pdo, $adminId, $studentId); checkUserManagement(findUserById($pdo, $studentId) === false, 'UM-08 deletes an account without tutoring records');
echo 'Results: ' . $passed . ' passed, ' . $failed . ' failed' . PHP_EOL; exit($failed > 0 ? 1 : 0);
