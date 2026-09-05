<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = [];
$expect = static function(bool $condition,string $message)use(&$failures):void{echo($condition?'PASS ':'FAIL ').$message."\n";if(!$condition)$failures[]=$message;};
$routes=file_get_contents($root.'/public_html/index.php')?:'';$schema=file_get_contents($root.'/database/schema.sql')?:'';$headers=file_get_contents($root.'/app/Core/SecurityHeaders.php')?:'';$orders=file_get_contents($root.'/app/Repositories/OrderRepository.php')?:'';$rx=file_get_contents($root.'/app/Repositories/PrescriptionRepository.php')?:'';
foreach(['/','/products','/cart','/checkout','/login','/register','/profile','/order/{orderNo}','/upload-prescription','/admin','/api/webhooks/meta','/sitemap.xml']as$route)$expect(str_contains($routes,"'{$route}'"),"route {$route}");
foreach(['users','sessions','products','orders','order_items','order_events','prescriptions','prescription_events','leads','webhook_jobs','rate_limit_buckets']as$table)$expect(str_contains($schema,"CREATE TABLE IF NOT EXISTS {$table}"),"table {$table}");
$expect(str_contains($headers,'Content-Security-Policy'),'CSP configured');$expect(str_contains($headers,'X-Content-Type-Options'),'nosniff configured');$expect(str_contains($orders,'FOR UPDATE'),'order rows locked');$expect(str_contains($orders,'beginTransaction'),'order transaction');$expect(str_contains($orders,'stock_restored_at'),'idempotent stock restoration');$expect(str_contains($rx,'aes-256-gcm'),'prescriptions encrypted');$expect(!is_file($root.'/public_html/.env'),'no public .env');$expect(count(glob($root.'/public_html/*.sql')?:[])===0,'no public SQL files');
exit($failures===[]?0:1);
