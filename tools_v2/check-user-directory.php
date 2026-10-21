<?php
declare(strict_types=1);
require __DIR__ . '/../kdrk_v2/bootstrap.php';
use App\Core\App;
if (!App::isLocal() || App::config('db.name') !== 'kdrk_web_v2') exit('Local V2 only');
$db=App::db(); $base='http://localhost:8001'; $jars=[]; $users=[]; $acts=[]; $apps=[]; $checks=0;
function check(bool $ok,string $label): void { global $checks; if (!$ok) throw new RuntimeException($label); $checks++; echo "OK: $label\n"; }
function http(string $role,string $path,?array $post=null): array {
 global $base,$jars;
 $jar=$jars[$role]??=tempnam(sys_get_temp_dir(),'kdrk-scope-');
 static $handles=[];
 $c=$handles[$role]??=curl_init();curl_setopt($c,CURLOPT_URL,$base.$path);curl_setopt($c,CURLOPT_HTTPGET,true);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$jar,CURLOPT_COOKIEFILE=>$jar,CURLOPT_TIMEOUT=>8,CURLOPT_FOLLOWLOCATION=>false]);
 if($post!==null)curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($post)]);
 $body=curl_exec($c); if($body===false)throw new RuntimeException(curl_error($c));
 $code=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_setopt($c,CURLOPT_COOKIELIST,'FLUSH');return ['code'=>$code,'body'=>$body];
}
function token(array $response): string { preg_match('/name="_csrf" value="([^"]+)"/',$response['body'],$m);return $m[1]??''; }
try {
 $suffix=bin2hex(random_bytes(6));$pw=bin2hex(random_bytes(16));
 foreach(['guide','admin','viewer'] as $role) $users[$role]=$db->insert('users',['name'=>'Scope Test '.$role,'email'=>"$role-$suffix@scope.invalid",'phone'=>'+905321234567','role'=>$role,'password_hash'=>password_hash($pw,PASSWORD_DEFAULT)]);
 $source=$db->one('SELECT * FROM activities ORDER BY id LIMIT 1');if(!$source)throw new RuntimeException('Activity fixture missing');
 unset($source['id'],$source['created_at'],$source['updated_at']);
 foreach(['own','other'] as $kind){$a=$source;$a['title']="Scope $kind $suffix";$a['slug']="scope-$kind-$suffix";$a['starts_on']=date('Y-m-d',strtotime('+10 days'));$a['ends_on']=null;$a['guide_primary_user_id']=$kind==='own'?$users['guide']:null;$a['guide_secondary_user_id']=null;$a['guide_primary_id']=null;$a['guide_secondary_id']=null;$a['is_published']=0;$a['in_slider']=0;$acts[$kind]=$db->insert('activities',$a);
 $apps[$kind]=$db->insert('applications',['activity_id'=>$acts[$kind],'name'=>'Synthetic participant','phone'=>'5321234567','email'=>'participant@scope.invalid','kvkk_consent_at'=>date('Y-m-d H:i:s')]);}
 foreach($users as $role=>$id){$t=token(http($role,'/yonetim/giris'));$login=http($role,'/yonetim/giris',['_csrf'=>$t,'email'=>"$role-$suffix@scope.invalid",'password'=>$pw]);check($login['code']===303,"$role login (HTTP ".$login['code'].")");}
 $list=http('guide','/yonetim/faaliyetler?durum=tumu');check($list['code']===200,'guide activity list');check(str_contains($list['body'],"Scope own $suffix")&&!str_contains($list['body'],"Scope other $suffix"),'list assignment scope');
 check(!str_contains($list['body'],'/yonetim/kullanicilar'),'guide menu hides users');
 foreach(['/yonetim/kullanicilar','/yonetim/ayarlar','/yonetim/galeri','/yonetim/slaytlar'] as $path)check(http('guide',$path)['code']===403,'guide denied '.$path);
 check(http('guide',"/yonetim/faaliyetler/{$acts['own']}")['code']===200,'assigned activity readable');
 check(http('guide',"/yonetim/faaliyetler/{$acts['other']}")['code']===403,'other activity denied');
 $own=http('guide',"/yonetim/faaliyetler/{$acts['own']}/basvurular");$t=token($own);check($own['code']===200,'assigned applicants readable');
 check(http('guide',"/yonetim/faaliyetler/{$acts['other']}/basvurular")['code']===403,'other applicants denied');
 check(http('guide',"/yonetim/faaliyetler/{$acts['other']}/liste/katilim")['code']===403,'other print denied');
 check(http('guide',"/yonetim/basvurular/{$apps['own']}",['_csrf'=>$t,'status'=>'approved'])['code']===303,'assigned approval accepted');
 check($db->value('SELECT status FROM applications WHERE id = ?',[$apps['own']])==='approved','approval persisted');
 check(http('guide',"/yonetim/basvurular/{$apps['other']}",['_csrf'=>$t,'status'=>'approved'])['code']===403,'cross activity POST denied');
 check($db->value('SELECT status FROM applications WHERE id = ?',[$apps['other']])==='pending','other applicant unchanged');
 check(http('guide',"/yonetim/basvurular/{$apps['own']}",['status'=>'rejected'])['code']===419,'CSRF enforced');
 check(http('guide',"/yonetim/faaliyetler/{$acts['own']}",['_csrf'=>$t,'title'=>'changed'])['code']===403,'guide cannot edit activity');
 $v=token(http('viewer',"/yonetim/faaliyetler/{$acts['own']}/basvurular"));check(http('viewer',"/yonetim/basvurular/{$apps['own']}",['_csrf'=>$v,'status'=>'rejected'])['code']===403,'viewer approval denied');
 $form=http('admin',"/yonetim/kullanicilar/{$users['guide']}");check($form['code']===200&&str_contains($form['body'],'name="phone"'),'phone form rendered');
 check(http('admin',"/yonetim/kullanicilar/{$users['guide']}",['_csrf'=>token($form),'name'=>'Scope Test guide','email'=>"guide-$suffix@scope.invalid",'phone'=>'0533 123 45 67','role'=>'guide','is_active'=>'1'])['code']===303,'user phone save');
 check($db->value('SELECT phone FROM users WHERE id = ?',[$users['guide']])==='+905331234567','user phone normalized and persisted');
 echo "$checks integration checks passed.\n";
} finally {
 foreach($apps as $id)$db->run('DELETE FROM applications WHERE id = ?',[$id]);
 foreach($acts as $id)$db->run('DELETE FROM activities WHERE id = ?',[$id]);
 foreach($users as $id){$db->run('DELETE FROM audit_log WHERE user_id = ?',[$id]);$db->run('DELETE FROM users WHERE id = ?',[$id]);}
 foreach($jars as $jar)@unlink($jar);
}
