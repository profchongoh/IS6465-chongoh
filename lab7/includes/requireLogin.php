<?php
declare(strict_types=1); require_once __DIR__.'/session.php';
function requireLogin(): void { startSecureSession(); if(sessionIsExpired($_SESSION,time())){$expired=isset($_SESSION['user_id']);destroyAuthenticatedSession();header('Location: login.php'.($expired?'?expired=1':''),true,303);exit;} $_SESSION['last_activity']=time(); }
