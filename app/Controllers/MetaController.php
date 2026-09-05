<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;use App\Core\Request;use App\Core\Response;use PDO;

final class MetaController
{
    public function __construct(private readonly ?PDO $db){}
    public function verify(Request$request):Response{$mode=(string)($request->query['hub_mode']??$request->query['hub.mode']??'');$token=(string)($request->query['hub_verify_token']??$request->query['hub.verify_token']??'');$challenge=(string)($request->query['hub_challenge']??$request->query['hub.challenge']??'');if($mode==='subscribe'&&Env::get('META_VERIFY_TOKEN')!==''&&hash_equals(Env::get('META_VERIFY_TOKEN'),$token))return new Response($challenge,200,['Content-Type'=>'text/plain']);return Response::json(['message'=>'Verification failed.'],403);}
    public function receive(Request$request):Response
    {
        $raw=file_get_contents('php://input');$signature=$request->headers['x-hub-signature-256']??'';$secret=Env::get('META_APP_SECRET');
        if(!is_string($raw)||$secret===''||!preg_match('/^sha256=([a-f0-9]{64})$/i',$signature,$m)||!hash_equals(strtolower($m[1]),hash_hmac('sha256',$raw,$secret)))return Response::json(['message'=>'Invalid signature.'],401);
        if(!$this->db)return Response::json(['message'=>'Queue unavailable.'],503);$payload=json_decode($raw,true);if(!is_array($payload))return Response::json(['message'=>'Invalid payload.'],400);
        $ids=[];foreach($payload['entry']??[]as$entry)foreach($entry['changes']??[]as$change){$id=(string)($change['value']['leadgen_id']??'');if($id!=='')$ids[]=$id;}
        $insert=$this->db->prepare("INSERT IGNORE INTO webhook_jobs(provider,external_id,payload_json) VALUES('META',:id,:payload)");foreach(array_unique($ids)as$id)$insert->execute(['id'=>$id,'payload'=>$raw]);return Response::json(['received'=>true]);
    }
}
