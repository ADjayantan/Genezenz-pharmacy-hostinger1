<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class LeadRepository
{
    public function __construct(private readonly ?PDO $database)
    {
    }

    /** @param array{name: string, phone: string, message: string} $lead */
    public function createWebsiteLead(array $lead): void
    {
        if (!$this->database) {
            throw new RuntimeException('Lead storage is not configured.');
        }

        $statement = $this->database->prepare(
            'INSERT INTO leads (source, status, name, phone, message, created_at, updated_at)
             VALUES (\'WEBSITE\', \'NEW\', :name, :phone, :message, UTC_TIMESTAMP(), UTC_TIMESTAMP())'
        );
        $statement->execute($lead);
    }

    /** @return array<int,array<string,mixed>> */
    public function all(): array
    {
        if(!$this->database)return[];
        return $this->database->query('SELECT * FROM leads ORDER BY created_at DESC LIMIT 500')->fetchAll();
    }

    public function update(int $id,string $status,string $note,string $assignedTo):void
    {
        if(!$this->database)throw new RuntimeException('Database is not configured.');
        if(!in_array($status,['NEW','CONTACTED','QUALIFIED','CONVERTED','LOST'],true))throw new RuntimeException('Invalid lead status.');
        $this->database->prepare('UPDATE leads SET status=:status,note=:note,assigned_to=:assigned WHERE id=:id')->execute(['status'=>$status,'note'=>$note?:null,'assigned'=>$assignedTo?:null,'id'=>$id]);
    }
}
