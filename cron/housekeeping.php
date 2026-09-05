<?php
declare(strict_types=1);
use App\Core\Database;
require dirname(__DIR__).'/app/bootstrap.php';
$db=Database::connection();$sessions=$db->exec('DELETE FROM sessions WHERE expires_at<UTC_TIMESTAMP()');$buckets=$db->exec('DELETE FROM rate_limit_buckets WHERE updated_at<DATE_SUB(UTC_TIMESTAMP(),INTERVAL 2 DAY)');echo "Removed {$sessions} sessions and {$buckets} rate-limit rows.\n";
