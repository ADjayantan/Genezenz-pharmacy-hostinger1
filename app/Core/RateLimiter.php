<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class RateLimiter
{
    public function __construct(private readonly ?PDO $database) {}

    public function allow(string $action, string $identity, int $limit, int $windowSeconds): bool
    {
        if (!$this->database) {
            $key = '_rate_' . hash('sha256', $action . '|' . $identity);
            $now = time();
            $state = $_SESSION[$key] ?? ['count' => 0, 'start' => $now];
            if ($now - (int) $state['start'] >= $windowSeconds) $state = ['count' => 0, 'start' => $now];
            $state['count']++;
            $_SESSION[$key] = $state;
            return $state['count'] <= $limit;
        }
        $hash = hash('sha256', $identity, true);
        $this->database->beginTransaction();
        try {
            $select = $this->database->prepare('SELECT attempts, window_started_at, blocked_until FROM rate_limit_buckets WHERE bucket_hash=:hash AND action_name=:action FOR UPDATE');
            $select->bindValue(':hash', $hash, PDO::PARAM_LOB);
            $select->bindValue(':action', $action);
            $select->execute();
            $row = $select->fetch();
            if (!$row) {
                $insert = $this->database->prepare('INSERT INTO rate_limit_buckets(bucket_hash,action_name,attempts,window_started_at) VALUES(:hash,:action,1,UTC_TIMESTAMP())');
                $insert->bindValue(':hash', $hash, PDO::PARAM_LOB); $insert->bindValue(':action', $action); $insert->execute();
                $allowed = true;
            } elseif ($row['blocked_until'] && strtotime((string) $row['blocked_until']) > time()) {
                $allowed = false;
            } elseif (time() - strtotime((string) $row['window_started_at']) >= $windowSeconds) {
                $reset = $this->database->prepare('UPDATE rate_limit_buckets SET attempts=1,window_started_at=UTC_TIMESTAMP(),blocked_until=NULL WHERE bucket_hash=:hash AND action_name=:action');
                $reset->bindValue(':hash', $hash, PDO::PARAM_LOB); $reset->bindValue(':action', $action); $reset->execute();
                $allowed = true;
            } else {
                $attempts = (int) $row['attempts'] + 1;
                $update = $this->database->prepare('UPDATE rate_limit_buckets SET attempts=:attempts,blocked_until=IF(:attempt_count>:limit,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE),NULL) WHERE bucket_hash=:hash AND action_name=:action');
                $update->bindValue(':attempts', $attempts, PDO::PARAM_INT); $update->bindValue(':attempt_count', $attempts, PDO::PARAM_INT); $update->bindValue(':limit', $limit, PDO::PARAM_INT);
                $update->bindValue(':hash', $hash, PDO::PARAM_LOB); $update->bindValue(':action', $action); $update->execute();
                $allowed = $attempts <= $limit;
            }
            $this->database->commit();
            return $allowed;
        } catch (\Throwable $error) {
            if ($this->database->inTransaction()) $this->database->rollBack();
            throw $error;
        }
    }
}
