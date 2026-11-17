<?php
require_once dirname(__DIR__) . '/services/userService.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON');
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/setup-database.sql'));

$passed = 0;
$failed = 0;
$results = [];

function checkUserManagement($condition, $label)
{
    global $passed, $failed, $results;

    $results[] = [
        'passed' => $condition,
        'label' => $label
    ];

    if ($condition) {
        $passed++;
    } else {
        $failed++;
    }
}

$student = [
    'full_name' => 'Jordan Lee',
    'email' => 'jordan@example.edu',
    'password' => 'CorrectHorseBattery1',
    'confirm_password' => 'CorrectHorseBattery1',
    'role' => 'Student'
];

$studentId = registerUser($pdo, $student);
$studentRow = findUserById($pdo, $studentId);

checkUserManagement(
    $studentRow['status'] === 'Active' && $studentRow['role'] === 'Student',
    'UM-01: Creates an active Student account'
);

checkUserManagement(
    findStudentForUser($pdo, $studentId) !== false,
    'UM-01: Links a Student account to a student record'
);

try {
    registerUser($pdo, $student);
    checkUserManagement(false, 'UM-01: Rejects a duplicate email address');
} catch (InvalidArgumentException $e) {
    checkUserManagement(true, 'UM-01: Rejects a duplicate email address');
}

checkUserManagement(
    findUserById($pdo, $studentId)['email'] === 'jordan@example.edu',
    'UM-02: Retrieves the authenticated account'
);

updateOwnAccount($pdo, $studentId, [
    'full_name' => 'Jordan A Lee',
    'email' => 'jordan.a@example.edu'
]);

checkUserManagement(
    findUserById($pdo, $studentId)['full_name'] === 'Jordan A Lee',
    'UM-04: Updates only permitted account fields'
);

changePassword($pdo, $studentId, [
    'current_password' => 'CorrectHorseBattery1',
    'new_password' => 'NewCorrectPassword2',
    'confirm_password' => 'NewCorrectPassword2'
]);

checkUserManagement(
    authenticateUser($pdo, 'jordan.a@example.edu', 'NewCorrectPassword2') !== false
    && authenticateUser($pdo, 'jordan.a@example.edu', 'CorrectHorseBattery1') === false,
    'UM-05: Replaces the password hash'
);

$adminId = insertUser(
    $pdo,
    'Admin User',
    'admin@example.edu',
    password_hash('AdministratorPass1', PASSWORD_DEFAULT),
    'Administrator'
);

$matches = listUsers($pdo, 'Jordan', 'Student', 'Active');

checkUserManagement(
    count($matches) === 1 && (int)$matches[0]['user_id'] === $studentId,
    'UM-03: Filters user accounts'
);

changeUserRole($pdo, $adminId, $studentId, 'Tutor');

checkUserManagement(
    findUserById($pdo, $studentId)['role'] === 'Tutor',
    'UM-06: Updates a user role'
);

changeUserStatus($pdo, $adminId, $studentId, 'Inactive');

checkUserManagement(
    authenticateUser($pdo, 'jordan.a@example.edu', 'NewCorrectPassword2') === false,
    'UM-07: Prevents inactive user login'
);

changeUserStatus($pdo, $adminId, $studentId, 'Active');

checkUserManagement(
    authenticateUser($pdo, 'jordan.a@example.edu', 'NewCorrectPassword2') !== false,
    'UM-09: Authenticates active user credentials'
);

try {
    changeUserStatus($pdo, $adminId, $adminId, 'Inactive');
    checkUserManagement(false, 'UM-07: Protects the last active administrator');
} catch (InvalidArgumentException $e) {
    checkUserManagement(true, 'UM-07: Protects the last active administrator');
}

removeUserAccount($pdo, $adminId, $studentId);

checkUserManagement(
    findUserById($pdo, $studentId) === false,
    'UM-08: Deletes an account without tutoring records'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management Test Results</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">User Management Test Results</h1>

                <div class="alert <?= $failed === 0 ? 'alert-success' : 'alert-danger' ?>">
                    <?php if ($failed === 0): ?>
                        All <?= $passed ?> user-management tests passed.
                    <?php else: ?>
                        <?= $failed ?> of <?= $passed + $failed ?> tests failed.
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <span class="badge text-bg-success me-2">
                        Passed: <?= $passed ?>
                    </span>
                    <span class="badge text-bg-danger">
                        Failed: <?= $failed ?>
                    </span>
                    <span class="badge text-bg-secondary">
                        Total: <?= $passed + $failed ?>
                    </span>
                </div>

                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Test</th>
                            <th scope="col">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $index => $result): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($result['label'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= $result['passed'] ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= $result['passed'] ? 'PASS' : 'FAIL' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>