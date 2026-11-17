

<?php
if (PHP_SAPI !== 'cli') { exit('Command line only.' . PHP_EOL); }
if ($argc !== 4) { exit('Usage: php database/create-admin.php "Full Name" email@example.edu "password"' . PHP_EOL); }
require_once dirname(__DIR__) . '/config/db.php'; require_once dirname(__DIR__) . '/repositories/userPDO.php';
$name=trim($argv[1]);$email=strtolower(trim($argv[2]));$password=$argv[3];if($name===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<12){exit('Use a name, a valid email, and a password of at least 12 characters.' . PHP_EOL);}
$pdo=openDatabase();if(findUserByEmail($pdo,$email)){exit('An account already uses this email address.' . PHP_EOL);}insertUser($pdo,$name,$email,password_hash($password,PASSWORD_DEFAULT),'Administrator');echo "Administrator created." . PHP_EOL;
