<?php
declare(strict_types=1);
require __DIR__ . '/../kdrk_v2/bootstrap.php';
use App\Core\App;
use App\Core\Settings;
use App\Models\Activity;
use App\Models\Banner;
if (!App::isLocal() || App::config('db.name') !== 'kdrk_web_v2') exit('Local V2 only');
$db=App::db(); $base='http://localhost:8001'; $jars=[]; $users=[]; $acts=[]; $checks=0;$news=[];$banners=[];
$setting=Settings::get('slider_remove_ended_activities'); $orderSetting=Settings::get('slider_order'); $disabledSetting=Settings::get('slider_disabled');
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
 $suffix=bin2hex(random_bytes(5));$pw=bin2hex(random_bytes(12));
 foreach(['admin','viewer'] as $role) $users[$role]=$db->insert('users',['name'=>'Slider Test '.$role,'email'=>"$role-$suffix@slider.invalid",'role'=>$role,'password_hash'=>password_hash($pw,PASSWORD_DEFAULT)]);
 foreach($users as $role=>$id){$t=token(http($role,'/yonetim/giris'));check(http($role,'/yonetim/giris',['_csrf'=>$t,'email'=>"$role-$suffix@slider.invalid",'password'=>$pw])['code']===303,"$role login");}
 $src=$db->one('SELECT * FROM activities WHERE cover_image IS NOT NULL LIMIT 1');unset($src['id'],$src['created_at'],$src['updated_at']);
 foreach(['future','ended','ongoing','draft','cancelled'] as $kind){$a=$src;$a['title']="Slider $kind $suffix";$a['slug']="slider-$kind-$suffix";$a['starts_on']=date('Y-m-d',strtotime($kind==='ended'||$kind==='ongoing'?'-5 days':'+5 days'));$a['ends_on']=$kind==='ongoing'?date('Y-m-d',strtotime('+2 days')):null;$a['is_published']=$kind==='draft'?0:1;$a['manual_status']=$kind==='cancelled'?'cancelled':null;$a['in_slider']=0;$acts[$kind]=$db->insert('activities',$a);}
 $b=$db->one('SELECT * FROM banners LIMIT 1');unset($b['id'],$b['created_at'],$b['updated_at']);$b['title']="Slider banner $suffix";$b['is_active']=0;$banners[]=$bid=$db->insert('banners',$b);
 $n=$db->one('SELECT * FROM news LIMIT 1');unset($n['id'],$n['created_at'],$n['updated_at']);
 foreach(['published','draft'] as $status){$n['title']="Slider $status $suffix";$n['slug']="slider-news-$status-$suffix";$n['status']=$status;$n['published_on']=date('Y-m-d');$n['in_slider']=0;$n['slider_until']=null;$news[$status]=$db->insert('news',$n);}
 $page=http('admin','/yonetim/slaytlar');$t=token($page);check($page['code']===200,'workspace renders');
 check(str_contains($page['body'],'data-picker-open="activity"')&&str_contains($page['body'],'data-picker-open="news"'),'both picker entries');
 check(str_contains($page['body'],'data-picker-category')&&str_contains($page['body'],'data-picker-search'),'picker filters rendered');
 check(!str_contains(http('viewer','/yonetim/slaytlar')['body'],'data-picker-form'),'viewer cannot add');
 Settings::set('slider_remove_ended_activities','1');
 foreach(['draft','cancelled','ended'] as $kind){http('admin','/yonetim/slaytlar/faaliyet',['_csrf'=>$t,'do'=>'add','activity_id'=>$acts[$kind]]);check((int)$db->value('SELECT in_slider FROM activities WHERE id = ?',[$acts[$kind]])===0,"$kind activity rejected");}
 http('admin','/yonetim/slaytlar/faaliyet',['_csrf'=>$t,'do'=>'add','activity_id'=>$acts['future']]);check((int)$db->value('SELECT in_slider FROM activities WHERE id = ?',[$acts['future']])===1,'eligible activity added');
 foreach(['ended','ongoing'] as $kind)$db->run('UPDATE activities SET in_slider = 1 WHERE id = ?',[$acts[$kind]]);
 $ids=array_column(Activity::forSlider(10000),'id');check(!in_array($acts['ended'],$ids),'ended excluded with setting on');check(in_array($acts['ongoing'],$ids),'ongoing end date respected');
 check(!in_array($acts['ended'],array_column(Banner::forPanel()['activities'],'id')),'ended absent from panel');
 Settings::set('slider_remove_ended_activities','0');check(in_array($acts['ended'],array_column(Activity::forSlider(10000),'id')),'ended included with setting off');check(in_array($acts['ended'],array_column(Banner::forPanel()['activities'],'id')),'panel agrees with setting off');
 Settings::set('slider_remove_ended_activities','1');
 http('admin','/yonetim/slaytlar/haber',['_csrf'=>$t,'do'=>'add','news_id'=>$news['draft']]);check((int)$db->value('SELECT in_slider FROM news WHERE id = ?',[$news['draft']])===0,'draft news rejected');
 http('admin','/yonetim/slaytlar/haber',['_csrf'=>$t,'do'=>'add','news_id'=>$news['published']]);check((int)$db->value('SELECT in_slider FROM news WHERE id = ?',[$news['published']])===1,'published news added');
 foreach(['calendar'=>'Faaliyet Takvimi','membership'=>'Kulübe Üye Ol'] as $choice=>$label){
 $r=http('admin',"/yonetim/slaytlar/$bid",['_csrf'=>$t,'title'=>"Slider banner $suffix",'cta_choice'=>$choice,'cta_activity_id'=>$acts['future'],'cta2_choice'=>'']);$saved=$db->one('SELECT cta_label, cta_url FROM banners WHERE id = ?',[$bid]);
 check($r['code']===303 && $saved['cta_label']===$label && str_starts_with($saved['cta_url'],$base.'/'),'preset '.$choice.' saved with valid route');
 }
 foreach(['apply','details','keep'] as $bad){
 $before=$db->value('SELECT cta_label FROM banners WHERE id = ?',[$bid]);
 http('admin',"/yonetim/slaytlar/$bid",['_csrf'=>$t,'title'=>"Slider banner $suffix",'cta_choice'=>$bad,'cta_activity_id'=>$acts['future'],'cta2_choice'=>'']);
 check($db->value('SELECT cta_label FROM banners WHERE id = ?',[$bid])===$before,'promotion rejects '.$bad);
 }
 $page=http('admin','/yonetim/slaytlar');
 check(str_contains($page['body'],'data-type-choice="news"')&&!str_contains($page['body'],'data-type-choice="haber"'),'merged news filter');
 check(str_contains($page['body'],'data-view-choice="list"')&&str_contains($page['body'],'data-slide-remove')&&!str_contains($page['body'],'bw-card-menu'),'list toggle and inline remove');
 $keys=Banner::orderedKeys();
 $revision=hash('sha256',json_encode($keys));
 check(http('admin','/yonetim/slaytlar/sira',['_csrf'=>$t,'do'=>'set','revision'=>'stale','keys'=>$keys])['code']===409,'stale order rejected');
 $bad=$keys;$bad[]=end($keys);
 check(http('admin','/yonetim/slaytlar/sira',['_csrf'=>$t,'do'=>'set','revision'=>$revision,'keys'=>$bad])['code']===422,'duplicate order rejected');
 check(http('viewer','/yonetim/slaytlar/sira',['_csrf'=>token(http('viewer','/yonetim/slaytlar')),'do'=>'set','revision'=>$revision,'keys'=>$keys])['code']===403,'viewer cannot reorder');
 $r=http('admin','/yonetim/slaytlar/sira',['_csrf'=>$t,'do'=>'set','revision'=>$revision,'keys'=>$keys]);
 check($r['code']===200&&json_decode($r['body'],true)['ok'],'complete drag order saved');
 http('admin','/yonetim/slaytlar/durum',['_csrf'=>$t,'key'=>'news:'.$news['published'],'active'=>'0']);
 $page=http('admin','/yonetim/slaytlar');
 check(str_contains($page['body'],'data-state="inactive"'),'passive state visible');
 check((int)$db->value('SELECT in_slider FROM news WHERE id = ?',[$news['published']])===1,'passive news remains in slide collection');
 http('admin','/yonetim/slaytlar/durum',['_csrf'=>$t,'key'=>'news:'.$news['published'],'active'=>'1']);
 $slides=Banner::visible();
 check(count($slides)===count(Banner::orderedCandidates()),'all eligible slides visible without three slide cap');
 check(!str_contains($page['body'],'Otomatik sıraya dön')&&!str_contains($page['body'],'Sırada'),'no automatic ordering or waiting queue');
 $before=$db->value('SELECT cta_label FROM banners WHERE id = ?',[$bid]);http('admin',"/yonetim/slaytlar/$bid",['_csrf'=>$t,'title'=>"Slider banner $suffix",'cta_choice'=>'invalid','cta2_choice'=>'']);check($db->value('SELECT cta_label FROM banners WHERE id = ?',[$bid])===$before,'unknown preset rejected');
 $form=http('admin',"/yonetim/slaytlar/$bid");check($form['code']===200 && str_contains($form['body'],'name="cta_choice"')&&!str_contains($form['body'],'name="cta_url"'),'preset form replaces free text');
 check(str_contains(http('admin','/yonetim/ayarlar')['body'],'name="slider_remove_ended_activities"'),'settings option available');
 echo "$checks slider integration checks passed.\n";
} finally {
 if($disabledSetting===null)$db->run('DELETE FROM settings WHERE `key` = ?', ['slider_disabled']);else Settings::set('slider_disabled',$disabledSetting);
 if($orderSetting===null)$db->run('DELETE FROM settings WHERE `key` = ?', ['slider_order']);else Settings::set('slider_order',$orderSetting);
 if($setting===null){$db->run('DELETE FROM settings WHERE `key` = ?', ['slider_remove_ended_activities']);}else Settings::set('slider_remove_ended_activities',$setting);
 foreach($banners as $id)$db->run('DELETE FROM banners WHERE id = ?',[$id]);
 foreach($news as $id)$db->run('DELETE FROM news WHERE id = ?',[$id]);
 foreach($acts as $id)$db->run('DELETE FROM activities WHERE id = ?',[$id]);
 foreach($users as $id){$db->run('DELETE FROM audit_log WHERE user_id = ?',[$id]);$db->run('DELETE FROM users WHERE id = ?',[$id]);}
 foreach($jars as $jar)@unlink($jar);
}
