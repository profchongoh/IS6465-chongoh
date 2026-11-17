<?php
require_once dirname(__DIR__) . '/includes/validation.php';

$results = [];
$passedCount = 0;
$failedCount = 0;

function checkValidationTest($condition, $label)
{
    global $results, $passedCount, $failedCount;

    $results[] = [
        'passed' => $condition,
        'label' => $label
    ];

    if ($condition) {
        $passedCount++;
    } else {
        $failedCount++;
    }
}

$timeTests = [
    ['name' => 'Valid time', 'input' => 'Morning', 'expected' => ''],
    ['name' => 'Missing time', 'input' => '', 'expected' => 'Choose a valid preferred time.'],
    ['name' => 'Altered value', 'input' => 'Midnight', 'expected' => 'Choose a valid preferred time.'],
];

foreach ($timeTests as $test) {
    $actual = validatePreferredTime($test['input']);

    checkValidationTest(
        $actual === $test['expected'],
        'Preferred time: ' . $test['name']
    );
}

$topicTests = [
    [
        'name' => 'Multiple valid topics',
        'input' => ['Exam review', 'Study plan'],
        'expected' => ''
    ],
    [
        'name' => 'Missing topics',
        'input' => [],
        'expected' => 'Select at least one valid topic.'
    ],
    [
        'name' => 'Altered topic',
        'input' => ['Free tutoring'],
        'expected' => 'Select at least one valid topic.'
    ]
];

foreach ($topicTests as $test) {
    $actual = validateTopics($test['input']);

    checkValidationTest(
        $actual === $test['expected'],
        'Topics: ' . $test['name']
    );
}

$reasonTests = [
    [
        'name' => 'Minimum length: 10 characters',
        'input' => '1234567890',
        'expected' => ''
    ],
    [
        'name' => 'Maximum length: 500 characters',
        'input' => str_repeat('a', 500),
        'expected' => ''
    ],
    [
        'name' => 'Too short: 9 characters',
        'input' => '123456789',
        'expected' => 'Describe your request using 10 to 500 characters.'
    ],
    [
        'name' => 'Too long: 501 characters',
        'input' => str_repeat('a', 501),
        'expected' => 'Describe your request using 10 to 500 characters.'
    ]
];

foreach ($reasonTests as $test) {
    $actual = validateReason($test['input']);

    checkValidationTest(
        $actual === $test['expected'],
        'Reason: ' . $test['name']
    );
}

$total = $passedCount + $failedCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Validation Test Results</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Validation Test Results</h1>

                <div class="alert <?= $failedCount === 0 ? 'alert-success' : 'alert-danger' ?>">
                    <?php if ($failedCount === 0): ?>
                        All <?= $total ?> validation tests passed.
                    <?php else: ?>
                        <?= $failedCount ?> of <?= $total ?> validation tests failed.
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <span class="badge text-bg-success me-2">
                        Passed: <?= $passedCount ?>
                    </span>
                    <span class="badge text-bg-danger me-2">
                        Failed: <?= $failedCount ?>
                    </span>
                    <span class="badge text-bg-secondary">
                        Total: <?= $total ?>
                    </span>
                </div>

                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Validation test</th>
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