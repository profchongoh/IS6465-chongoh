<?php
declare(strict_types=1);
function hasRole(string ...$allowed): bool { return array_intersect($allowed,$_SESSION['roles']??[])!==[]; }
function requireRole(string ...$allowed): void { if(!hasRole(...$allowed)){http_response_code(403);require __DIR__.'/../php/unauthorized.php';exit;} }
