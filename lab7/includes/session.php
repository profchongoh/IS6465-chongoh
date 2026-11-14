<?php
declare(strict_types=1);
function startSecureSession(): void { if (session_status() === PHP_SESSION_ACTIVE) return; session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>getenv('APP_ENV') !== 'local','httponly'=>true,'samesite'=>'Lax']); session_start(); }
function destroyAuthenticatedSession(): void { startSecureSession(); $_SESSION=[]; $p=session_get_cookie_params(); if(ini_get('session.use_cookies')) setcookie(session_name(),'', ['expires'=>time()-42000,'path'=>$p['path']??'/','domain'=>$p['domain']??'','secure'=>(bool)($p['secure']??true),'httponly'=>(bool)($p['httponly']??true),'samesite'=>$p['samesite']??'Lax']); session_destroy(); }
function sessionIsExpired(array $session, int $now, int $timeout=1200): bool { return !isset($session['user_id'],$session['last_activity']) || $now-(int)$session['last_activity']>$timeout; }
