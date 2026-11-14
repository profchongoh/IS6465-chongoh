<?php
declare(strict_types=1); require_once __DIR__.'/session.php';
function csrfToken(): string { startSecureSession(); if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function csrfField(): string { return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrfToken(),ENT_QUOTES,'UTF-8').'">'; }
function requireValidCsrf(): void { startSecureSession(); $t=$_POST['csrf_token']??''; if(!is_string($t)||!isset($_SESSION['csrf_token'])||!hash_equals($_SESSION['csrf_token'],$t)){http_response_code(403);exit('Invalid request token.');} }
