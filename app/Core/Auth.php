<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Auth
{
    private const COOKIE = 'gz_auth';
    private static ?PDO $database = null;
    /** @var array<string, mixed>|null|false */
    private static array|null|false $cachedUser = false;

    public static function initialize(?PDO $database): void
    {
        self::$database = $database;
    }

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        if (self::$cachedUser !== false) {
            return self::$cachedUser;
        }
        self::$cachedUser = null;
        $token = $_COOKIE[self::COOKIE] ?? '';
        if (!self::$database || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }
        $statement = self::$database->prepare(
            'SELECT u.id, u.name, u.email, u.phone, u.address, u.role
             FROM sessions s JOIN users u ON u.id = s.user_id
             WHERE s.token_hash = :hash AND s.expires_at > UTC_TIMESTAMP() LIMIT 1'
        );
        $statement->bindValue(':hash', hash('sha256', $token, true), PDO::PARAM_LOB);
        $statement->execute();
        $user = $statement->fetch();
        if (is_array($user)) {
            self::$cachedUser = $user;
            self::$database->prepare('UPDATE sessions SET last_seen_at = UTC_TIMESTAMP() WHERE token_hash = :hash')
                ->execute(['hash' => hash('sha256', $token, true)]);
        }
        return self::$cachedUser ?: null;
    }

    public static function attempt(string $email, string $password, string $ip, string $agent): bool
    {
        if (!self::$database) {
            return false;
        }
        $statement = self::$database->prepare('SELECT id, password_hash, role FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => strtolower(trim($email))]);
        $record = $statement->fetch();
        $validHash = is_array($record) ? (string) $record['password_hash'] : '$2y$12$C6UzMDM.H6dfI/f/IKcEe.9GXn4HHEsQl94OBchQh.4dbMBh1M1iK';
        if (!password_verify($password, $validHash) || !is_array($record)) {
            return false;
        }
        self::issue((int) $record['id'], (string) $record['role'], $ip, $agent);
        return true;
    }

    public static function issue(int $userId, string $role, string $ip, string $agent): void
    {
        if (!self::$database) {
            return;
        }
        self::logout();
        $token = bin2hex(random_bytes(32));
        $hours = $role === 'ADMIN' ? 8 : 24 * 30;
        $statement = self::$database->prepare(
            'INSERT INTO sessions (token_hash, user_id, ip_hash, user_agent_hash, expires_at)
             VALUES (:token, :user, :ip, :agent, DATE_ADD(UTC_TIMESTAMP(), INTERVAL :hours HOUR))'
        );
        $statement->bindValue(':token', hash('sha256', $token, true), PDO::PARAM_LOB);
        $statement->bindValue(':user', $userId, PDO::PARAM_INT);
        $statement->bindValue(':ip', hash('sha256', $ip, true), PDO::PARAM_LOB);
        $statement->bindValue(':agent', hash('sha256', $agent, true), PDO::PARAM_LOB);
        $statement->bindValue(':hours', $hours, PDO::PARAM_INT);
        $statement->execute();
        setcookie(self::COOKIE, $token, [
            'expires' => time() + ($hours * 3600), 'path' => '/', 'secure' => self::isHttps(),
            'httponly' => true, 'samesite' => 'Lax',
        ]);
        session_regenerate_id(true);
        self::$cachedUser = false;
    }

    public static function logout(): void
    {
        $token = $_COOKIE[self::COOKIE] ?? '';
        if (self::$database && is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {
            $statement = self::$database->prepare('DELETE FROM sessions WHERE token_hash = :hash');
            $statement->bindValue(':hash', hash('sha256', $token, true), PDO::PARAM_LOB);
            $statement->execute();
        }
        setcookie(self::COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'secure' => self::isHttps(), 'httponly' => true, 'samesite' => 'Lax']);
        unset($_COOKIE[self::COOKIE]);
        self::$cachedUser = null;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? '') === 'ADMIN';
    }

    private static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    }
}
