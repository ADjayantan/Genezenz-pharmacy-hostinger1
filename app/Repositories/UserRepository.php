<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UserRepository
{
    public function __construct(private readonly ?PDO $database) {}

    public function create(string $name, string $email, string $password, string $phone): int
    {
        if (!$this->database) throw new \RuntimeException('Database is not configured.');
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_BCRYPT;
        $statement = $this->database->prepare('INSERT INTO users(name,email,password_hash,phone) VALUES(:name,:email,:password,:phone)');
        $statement->execute([
            'name' => $name, 'email' => strtolower($email), 'password' => password_hash($password, $algorithm), 'phone' => $phone ?: null,
        ]);
        return (int) $this->database->lastInsertId();
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        if (!$this->database) return [];
        return $this->database->query("SELECT u.id,u.name,u.email,u.phone,u.created_at,COUNT(o.id) order_count,COALESCE(SUM(CASE WHEN o.status!='CANCELLED' THEN o.total ELSE 0 END),0) spend FROM users u LEFT JOIN orders o ON o.user_id=u.id WHERE u.role='CUSTOMER' GROUP BY u.id ORDER BY u.created_at DESC LIMIT 300")->fetchAll();
    }
}
