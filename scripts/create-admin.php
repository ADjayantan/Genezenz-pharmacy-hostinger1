<?php

declare(strict_types=1);

use App\Core\Database;

if (PHP_SAPI !== 'cli') exit("CLI only.\n");
require dirname(__DIR__) . '/app/bootstrap.php';
$email = strtolower(trim($argv[1] ?? ''));
$name = trim($argv[2] ?? 'Genezenz Administrator');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) exit("Usage: php scripts/create-admin.php admin@example.com [name]\n");
fwrite(STDOUT, 'New admin password: ');
$password = trim((string) fgets(STDIN));
if (strlen($password) < 12 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) exit("Password must be 12+ characters with letters and numbers.\n");
$algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
$db = Database::connection();
$statement = $db->prepare("INSERT INTO users(name,email,password_hash,role) VALUES(:name,:email,:password,'ADMIN') ON DUPLICATE KEY UPDATE name=VALUES(name),password_hash=VALUES(password_hash),role='ADMIN'");
$statement->execute(['name'=>$name,'email'=>$email,'password'=>password_hash($password,$algorithm)]);
fwrite(STDOUT, "Admin account created or rotated for {$email}.\n");
