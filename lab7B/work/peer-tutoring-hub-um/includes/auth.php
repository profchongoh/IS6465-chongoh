<?php

require_once dirname(__DIR__) . '/repositories/userPDO.php';

function startSecureSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        session_set_cookie_params(array('lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Lax'));
        session_start();
    }
}
function csrfToken()
{
    startSecureSession();
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}
function verifyCsrfToken($input)
{
    startSecureSession();
    $submitted = isset($input['csrf_token']) ? (string) $input['csrf_token'] : '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) { http_response_code(403); exit('Invalid request token.'); }
}
function requireLogin($pdo)
{
    startSecureSession();
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
    if (time() - (isset($_SESSION['last_activity']) ? $_SESSION['last_activity'] : 0) > 1200) { logoutSession(); header('Location: login.php?expired=1'); exit; }
    $user = findUserById($pdo, (int) $_SESSION['user_id']);
    if (!$user || $user['status'] !== 'Active') { logoutSession(); header('Location: login.php?inactive=1'); exit; }
    $_SESSION['last_activity'] = time(); $_SESSION['display_name'] = $user['full_name']; $_SESSION['role'] = $user['role'];
    return $user;
}
function requireRole($user, $allowedRoles)
{
    if (!in_array($user['role'], $allowedRoles, true)) { http_response_code(403); exit('You are not authorized to perform this action.'); }
}
function loginSession($user)
{
    startSecureSession(); session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['user_id']; $_SESSION['display_name'] = $user['full_name']; $_SESSION['role'] = $user['role']; $_SESSION['last_activity'] = time(); csrfToken();
}
function logoutSession()
{
    startSecureSession(); $_SESSION = array();
    if (ini_get('session.use_cookies')) { $params = session_get_cookie_params(); setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']); }
    session_destroy();
}
