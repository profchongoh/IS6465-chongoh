<?php
require_once dirname(__DIR__) . '/services/requestService.php';

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys=ON');
$pdo->exec(file_get_contents(dirname(__DIR__) . '/database/setup-database.sql'));

$failed = 0;
$results = [];

function check($ok, $label)
{
    global $failed, $results;

    $results[] = [
        'passed' => $ok,
        'label' => $label
    ];

    if (!$ok) {
        $failed++;
    }
}

$sample = [
    'student_name' => 'Jordan Lee',
    'student_email' => 'jordan@example.edu',
    'course' => 'IS 6465',
    'reason' => 'Please help with PHP form validation.',
    'topics' => ['Exam review', 'Study plan'],
    'preferreddate' => (new DateTimeImmutable('+10 days'))->format('Y-m-d'),
    'preferredtime' => 'Morning'
];

$id = submitRequest($pdo, $sample);
$r = findRequest($pdo, $id);

check($r !== false && $r['status'] === 'Open', 'UC01: Create request with Open status');
check(
    decodeTopic($r['topic'])['topics'] === ['Exam review', 'Study plan'],
    'Lab 4: Checkbox values preserved'
);

$id2 = submitRequest($pdo, $sample);

check(
    (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn() === 1,
    'Duplicate email reuses existing student'
);

check(
    count(listRequests($pdo, 'IS 6465', 'Open')) === 2,
    'UC02: Filter requests by course and Open status'
);

$bad = $sample;
$bad['reason'] = 'short';

try {
    submitRequest($pdo, $bad);
    check(false, 'Invalid reason is rejected');
} catch (InvalidArgumentException $e) {
    check(true, 'Invalid reason is rejected');
}

check(
    (int)$pdo->query('SELECT COUNT(*) FROM tutoring_requests')->fetchColumn() === 2,
    'Invalid submission creates no database record'
);

$edit = $sample;
$edit['reason'] = 'Updated request reason for the exam.';

check(reviseRequest($pdo, $id, $edit), 'Edit an Open request');

check(
    decodeTopic(findRequest($pdo, $id)['topic'])['reason'] === $edit['reason'],
    'Updated reason is saved'
);

check(cancelOpenRequest($pdo, $id), 'Cancel an Open request');
check(findRequest($pdo, $id)['status'] === 'Cancelled', 'Cancelled request remains in database');
check(!cancelOpenRequest($pdo, $id), 'Second cancellation is rejected');
check(findRequest($pdo, $id2)['status'] === 'Open', 'Unrelated request remains Open');

try {
    $pdo->exec(
        "INSERT INTO tutoring_requests
        (student_id, course, topic, preferred_date, preferred_time)
        VALUES (999, 'IS 6465', 'x', '2030-01-01', 'Morning')"
    );
    check(false, 'Foreign-key constraint is enforced');
} catch (PDOException $e) {
    check(true, 'Foreign-key constraint is enforced');
}

check(
    count(listRequests($pdo, 'IS 6570', '')) === 0,
    'UC02: Empty filter result is handled'
);

$total = count($results);
$passed = $total - $failed;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Integration Test Results</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Integration Test Results</h1>

                <div class="alert <?= $failed === 0 ? 'alert-success' : 'alert-danger' ?>">
                    <?php if ($failed === 0): ?>
                        All <?= $total ?> integration checks passed.
                    <?php else: ?>
                        <?= $failed ?> of <?= $total ?> integration checks failed.
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <span class="badge text-bg-success me-2">
                        Passed: <?= $passed ?>
                    </span>
                    <span class="badge text-bg-danger">
                        Failed: <?= $failed ?>
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
                                    <?php if ($result['passed']): ?>
                                        <span class="badge text-bg-success">PASS</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger">FAIL</span>
                                    <?php endif; ?>
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