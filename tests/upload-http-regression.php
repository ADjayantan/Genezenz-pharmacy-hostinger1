<?php
declare(strict_types=1);
// Local-only integration test. It creates random synthetic fixtures and removes only those fixtures.
require dirname(__DIR__).'/app/bootstrap.php';
if ($path=getenv('TEST_ENV_FILE')) \App\Core\Env::load($path);
if (getenv('RUN_LOCAL_UPLOAD_TESTS') !== '1' || \App\Core\Env::get('APP_ENV') !== 'local') {
    fwrite(STDERR,"Set RUN_LOCAL_UPLOAD_TESTS=1 and APP_ENV=local to run synthetic upload tests.\n"); exit(1);
}
$base='http://127.0.0.1:8787';
$db=\App\Core\Database::connection();
$marker='qa-'.bin2hex(random_bytes(8));
$userIds=[]; $files=[]; $rxPaths=[]; $imageUrls=[]; $productIds=[]; $checks=0;
$jar=tempnam(sys_get_temp_dir(),'gz-cookie-'); $files[]=$jar;
function verify(bool $ok,string $message):void {
    global $checks;
    if(!$ok)throw new RuntimeException('FAIL '.$message);
    $checks++; echo 'PASS '.$message."\n";
}
function http(string $path,?array $data=null,string $token='',bool $json=false):array {
    global $base,$jar;
    $curl=curl_init($base.$path);
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_COOKIEFILE=>$jar,CURLOPT_COOKIEJAR=>$jar,CURLOPT_COOKIE=>'gz_auth='.$token,CURLOPT_FOLLOWLOCATION=>false]);
    if($json)curl_setopt($curl,CURLOPT_HTTPHEADER,['Accept: application/json']);
    if($data!==null){curl_setopt($curl,CURLOPT_POST,true);curl_setopt($curl,CURLOPT_POSTFIELDS,$data);}
    $result=curl_exec($curl);
    if($result===false)throw new RuntimeException(curl_error($curl));
    $length=curl_getinfo($curl,CURLINFO_HEADER_SIZE); $status=curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return ['status'=>$status,'headers'=>substr($result,0,$length),'body'=>substr($result,$length)];
}
function csrf(string $path,string $auth):string {
    $r=http($path,null,$auth);
    if($r['status']!==200||!preg_match('/name="_token" value="([^"]+)"/',$r['body'],$m))throw new RuntimeException('Unable to obtain test CSRF token');
    return html_entity_decode($m[1],ENT_QUOTES);
}
$failed=false;
try {
    $tokens=[];
    foreach(['CUSTOMER','ADMIN','CUSTOMER'] as $i=>$role){
        $db->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)')->execute(['Synthetic QA',$marker.'-'.$i.'@example.invalid',password_hash(bin2hex(random_bytes(20)),PASSWORD_DEFAULT),$role]);
        $id=(int)$db->lastInsertId();$userIds[]=$id;$token=bin2hex(random_bytes(32));$tokens[]=$token;
        $s=$db->prepare('INSERT INTO sessions(token_hash,user_id,expires_at) VALUES(?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 1 HOUR))');
        $s->execute([hash('sha256',$token,true),$id]);
    }
    $png=tempnam(sys_get_temp_dir(),'gz-image-');$files[]=$png;
    $canvas=imagecreatetruecolor(1800,900);imagefill($canvas,0,0,imagecolorallocate($canvas,40,80,60));imagepng($canvas,$png);imagedestroy($canvas);
    $invalid=tempnam(sys_get_temp_dir(),'gz-invalid-');$files[]=$invalid;file_put_contents($invalid,'This is synthetic test text, not an image.');
    $oversize=tempnam(sys_get_temp_dir(),'gz-oversize-');$files[]=$oversize;file_put_contents($oversize,str_repeat('x',5*1024*1024+1));
    $customerToken=csrf('/upload-prescription',$tokens[0]);
    $r=http('/api/prescriptions/upload',['_token'=>$customerToken,'prescription'=>new CURLFile($png,'image/png','synthetic.png')],'',true);
    verify($r['status']===401,'Anonymous prescription upload rejected');
    $r=http('/api/prescriptions/upload',['_token'=>'invalid','prescription'=>new CURLFile($png,'image/png','synthetic.png')],$tokens[0],true);
    verify($r['status']===419,'Prescription upload rejects invalid CSRF');
    $r=http('/api/prescriptions/upload',['_token'=>$customerToken,'prescription'=>new CURLFile($invalid,'image/png','spoof.png')],$tokens[0],true);
    verify($r['status']===422,'Prescription API rejects spoofed image MIME');
    $r=http('/api/prescriptions/upload',['_token'=>$customerToken,'prescription'=>new CURLFile($png,'image/png','synthetic.png')],$tokens[0],true);
    $rx=json_decode($r['body'],true); verify($r['status']===201&&($rx['status']??'')==='PENDING','Prescription API accepts multipart upload as pending');
    $s=$db->prepare('SELECT storage_key FROM prescriptions WHERE id=? AND user_id=?');$s->execute([$rx['id'],$userIds[0]]);
    $rxPath=BASE_PATH.'/private_uploads/'.$s->fetchColumn();$rxPaths[]=$rxPath;
    $encrypted=file_get_contents($rxPath);
    verify(str_starts_with($encrypted,'GZRX1')&&$encrypted!==file_get_contents($png),'Stored prescription is encrypted');
    $r=http('/api/prescriptions/file/'.$rx['id'],null,$tokens[0]);
    verify($r['status']===200&&hash('sha256',$r['body'])===hash_file('sha256',$png),'Owner download decrypts exact original upload');
    verify(http('/api/prescriptions/file/'.$rx['id'],null,$tokens[2])['status']===404,'Other account cannot read prescription');
    $r=http('/api/prescriptions/file/'.$rx['id']);
    verify($r['status']===404,'Anonymous prescription download rejected');
    $adminCsrf=csrf('/admin/products/new',$tokens[1]);
    foreach (['products','orders','prescriptions','leads','customers'] as $section) {
        verify(http('/admin/'.$section,null,$tokens[1])['status']===200, 'Admin '.$section.' section loads');
    }
    $data=['_token'=>$adminCsrf,'name'=>$marker,'slug'=>$marker,'category_id'=>'','description'=>'Synthetic QA product that is removed after the test.','price'=>'50','mrp'=>'60','stock'=>'1','image_url'=>'','product_image'=>new CURLFile($png,'image/png','test-product.png')];
    $r=http('/admin/products/new',$data,$tokens[1]);
    $s=$db->prepare('SELECT id,image_url FROM products WHERE slug=?');$s->execute([$marker]);$product=$s->fetch();
    if($product){$productIds[]=(int)$product['id'];if($product['image_url'])$imageUrls[]=$product['image_url'];}
    verify($r['status']===302&&is_array($product),'Admin creates product with direct file upload');
    $size=getimagesize(PUBLIC_PATH.$product['image_url']);
    verify($size[0]===1600&&$size[1]===800&&$size['mime']==='image/webp','Product upload resized to 1600px WebP');
    verify(http($product['image_url'])['status']===200,'Uploaded product image is served publicly');
    $data['slug']=$marker.'-blocked';$data['product_image']=new CURLFile($png,'image/png','test.php');
    http('/admin/products/new',$data,$tokens[1]);
    $s->execute([$marker.'-blocked']);verify(!$s->fetch(),'Executable image filename rejected without creating product');
    $data['slug']=$marker.'-spoof';$data['product_image']=new CURLFile($invalid,'image/png','test.png');
    http('/admin/products/new',$data,$tokens[1]);
    $s->execute([$marker.'-spoof']);verify(!$s->fetch(),'Spoofed product image rejected without creating product');
    $data['slug']=$marker.'-oversize';$data['product_image']=new CURLFile($oversize,'image/png','test.png');
    http('/admin/products/new',$data,$tokens[1]);
    $s->execute([$marker.'-oversize']);verify(!$s->fetch(),'Oversized product image rejected');
    $data['slug']=$marker;$data['product_image']=new CURLFile($png,'image/png','test.png');
    $imageCount=count(glob(PUBLIC_PATH.'/uploads/products/*.webp')?:[]);
    http('/admin/products/new',$data,$tokens[1]);
    verify(count(glob(PUBLIC_PATH.'/uploads/products/*.webp')?:[])===$imageCount,'Database save failure cleans newly uploaded image');
    $home=http('/');$login=http('/login');
    verify(str_contains(strtolower($home['headers']),'x-robots-tag: noindex'),'Staging is noindex by default');
    verify(!str_contains($login['body'],'rel="preload"'),'Non-home page does not preload hero banner');
    verify(str_contains($home['body'],'app.js?v='),'Asset URLs include cache version');
    verify(!str_contains(http('/robots.txt')['body'],'genezenz-pharmacy.in'),'Robots response does not use guessed sitemap host');
    echo "Completed {$checks} HTTP upload and page checks.\n";
} catch(Throwable $error) {
    $failed=true;fwrite(STDERR,$error->getMessage()."\n");
} finally {
    foreach($userIds as $id){
        $q=$db->prepare('SELECT storage_key FROM prescriptions WHERE user_id=?');$q->execute([$id]);
        foreach($q->fetchAll(PDO::FETCH_COLUMN) as $key)if(preg_match('/^[a-f0-9]{48}\.rx$/D',$key))$rxPaths[]=BASE_PATH.'/private_uploads/'.$key;
        $db->prepare('DELETE FROM users WHERE id=? AND email LIKE ?')->execute([$id,$marker.'-%@example.invalid']);
    }
    foreach($productIds as $id)$db->prepare('DELETE FROM products WHERE id=? AND slug=?')->execute([$id,$marker]);
    foreach(array_unique($imageUrls) as $url)\App\Support\ProductImageStorage::discard($url);
    foreach(array_unique(array_merge($files,$rxPaths)) as $path)if(is_file($path))unlink($path);
}
exit($failed?1:0);
