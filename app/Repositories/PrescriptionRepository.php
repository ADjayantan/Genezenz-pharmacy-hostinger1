<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Env;
use PDO;
use RuntimeException;

final class PrescriptionRepository
{
    private const ALLOWED=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/heic'=>'heic','image/heif'=>'heic','application/pdf'=>'pdf'];
    public function __construct(private readonly ?PDO $database) {}

    public function availableForOrder(int $userId): array
    {
        if (!$this->database) return [];
        $statement = $this->database->prepare("SELECT id,original_name,status FROM prescriptions WHERE user_id=:user AND order_id IS NULL AND status IN ('PENDING','APPROVED') ORDER BY created_at DESC LIMIT 100");
        $statement->execute(['user' => $userId]);
        return $statement->fetchAll();
    }

    public function store(int $userId, array $file, array $fields): int
    {
        if(!$this->database) throw new RuntimeException('Database is not configured.');
        if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new RuntimeException('Choose a prescription file.');
        if (!is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) throw new RuntimeException('Invalid upload.');
        foreach (['patient_name' => 80, 'doctor_name' => 80, 'notes' => 1000] as $field => $maximum) {
            if (strlen((string) ($fields[$field] ?? '')) > $maximum) throw new RuntimeException('Prescription details are too long.');
        }
        $size=(int)($file['size']??0); if($size<1||$size>8*1024*1024) throw new RuntimeException('Prescription must be 8 MB or smaller.');
        if(!function_exists('finfo_open')) throw new RuntimeException('Host must enable the fileinfo PHP extension.');
        $finfo=finfo_open(FILEINFO_MIME_TYPE); $mime=(string)finfo_file($finfo,(string)$file['tmp_name']); finfo_close($finfo);
        if(!isset(self::ALLOWED[$mime])) throw new RuntimeException('Only JPG, PNG, WEBP, HEIC or PDF files are accepted.');
        $key=Env::get('FILE_ENCRYPTION_KEY');
        $binaryKey=self::decodeKey($key); if(strlen($binaryKey)!==32) throw new RuntimeException('Prescription encryption key is not configured.');
        $plain=file_get_contents((string)$file['tmp_name']); if(!is_string($plain)) throw new RuntimeException('Could not read the upload.');
        $iv=random_bytes(12); $tag=''; $cipher=openssl_encrypt($plain,'aes-256-gcm',$binaryKey,OPENSSL_RAW_DATA,$iv,$tag);
        if(!is_string($cipher)) throw new RuntimeException('Could not encrypt the upload.');
        $storage=bin2hex(random_bytes(24)).'.rx'; $dir=BASE_PATH.'/private_uploads'; if(!is_dir($dir)&&!mkdir($dir,0700,true)) throw new RuntimeException('Private upload directory is unavailable.');
        if(file_put_contents($dir.'/'.$storage,'GZRX1'.$iv.$tag.$cipher,LOCK_EX)===false) throw new RuntimeException('Could not store the upload.');
        try {
            $this->database->beginTransaction();
            $s=$this->database->prepare('INSERT INTO prescriptions(user_id,storage_key,mime_type,size_bytes,original_name,patient_name,doctor_name,notes) VALUES(:user,:key,:mime,:size,:original,:patient,:doctor,:notes)');
            $s->execute(['user'=>$userId,'key'=>$storage,'mime'=>$mime,'size'=>$size,'original'=>substr(basename((string)$file['name']),0,255),'patient'=>$fields['patient_name']?:null,'doctor'=>$fields['doctor_name']?:null,'notes'=>$fields['notes']?:null]);
            $id=(int)$this->database->lastInsertId(); $this->database->prepare("INSERT INTO prescription_events(prescription_id,status,note) VALUES(:id,'PENDING','Uploaded by customer')")->execute(['id'=>$id]); $this->database->commit(); return $id;
        } catch(\Throwable $e) { if($this->database->inTransaction())$this->database->rollBack(); @unlink($dir.'/'.$storage); throw $e; }
    }

    /** @return array<int,array<string,mixed>> */
    public function forUser(int $userId): array { if(!$this->database)return[]; $s=$this->database->prepare('SELECT id,original_name,patient_name,doctor_name,status,review_note,created_at FROM prescriptions WHERE user_id=:user ORDER BY created_at DESC');$s->execute(['user'=>$userId]);return$s->fetchAll(); }
    /** @return array<int,array<string,mixed>> */
    public function all(): array { if(!$this->database)return[];return$this->database->query('SELECT p.*,u.name user_name,u.email FROM prescriptions p JOIN users u ON u.id=p.user_id ORDER BY p.created_at DESC LIMIT 300')->fetchAll(); }

    /** @return array{bytes:string,mime:string,name:string}|null */
    public function file(int $id, ?int $ownerId, bool $admin): ?array
    {
        if(!$this->database)return null; $sql='SELECT * FROM prescriptions WHERE id=:id'.(!$admin?' AND user_id=:user':'').' LIMIT 1';$s=$this->database->prepare($sql);$params=['id'=>$id];if(!$admin)$params['user']=$ownerId;$s->execute($params);$row=$s->fetch();if(!$row)return null;
        $blob=@file_get_contents(BASE_PATH.'/private_uploads/'.$row['storage_key']);if(!is_string($blob)||!str_starts_with($blob,'GZRX1'))return null;
        $key=self::decodeKey(Env::get('FILE_ENCRYPTION_KEY'));$plain=openssl_decrypt(substr($blob,33),'aes-256-gcm',$key,OPENSSL_RAW_DATA,substr($blob,5,12),substr($blob,17,16));if(!is_string($plain))return null;
        return['bytes'=>$plain,'mime'=>(string)$row['mime_type'],'name'=>(string)($row['original_name']?:'prescription')];
    }

    public function review(int $id,string $status,string $note,string $actor):void
    { if(!$this->database)throw new RuntimeException('Database is not configured.');if(!in_array($status,['PENDING','APPROVED','REJECTED'],true))throw new RuntimeException('Invalid review status.');$this->database->beginTransaction();try{$this->database->prepare('UPDATE prescriptions SET status=:status,review_note=:note,reviewed_at=IF(:reviewed_status=\'PENDING\',NULL,UTC_TIMESTAMP()),reviewed_by=IF(:reviewer_status=\'PENDING\',NULL,:actor) WHERE id=:id')->execute(['status'=>$status,'reviewed_status'=>$status,'reviewer_status'=>$status,'note'=>$note?:null,'actor'=>$actor,'id'=>$id]);$this->database->prepare('INSERT INTO prescription_events(prescription_id,status,note,actor) VALUES(:id,:status,:note,:actor)')->execute(['id'=>$id,'status'=>$status,'note'=>$note?:null,'actor'=>$actor]);$this->database->commit();}catch(\Throwable$e){if($this->database->inTransaction())$this->database->rollBack();throw$e;} }

    private static function decodeKey(string $value): string { $decoded=base64_decode($value,true);return is_string($decoded)&&strlen($decoded)===32?$decoded:$value; }
}
