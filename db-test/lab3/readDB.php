<?php

$pdo = new PDO("sqlite:" . __DIR__ . "/request.db");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$requests = $pdo->query(
    "SELECT `requestid`, `course`, `topic`, `preferreddate`, `preferredtime`
     FROM `request`
     ORDER BY `requestid`"
)->fetchAll(PDO::FETCH_ASSOC);

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tutoring requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@latest/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <main class="container py-5">
        <h1>Tutoring requests</h1>
        <?php if (!$requests): ?>
            <p>No requests have been submitted.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Request ID</th>
                            <th scope="col">Course</th>
                            <th scope="col">Topic</th>
                            <th scope="col">Preferred date</th>
                            <th scope="col">Preferred time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?= escapeHtml((string) $request["requestid"]) ?></td>
                                <td><?= escapeHtml($request["course"]) ?></td>
                                <td><?= escapeHtml($request["topic"]) ?></td>
                                <td><?= escapeHtml($request["preferreddate"]) ?></td>
                                <td><?= escapeHtml($request["preferredtime"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
