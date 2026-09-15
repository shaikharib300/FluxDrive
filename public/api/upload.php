<?php
require_once __DIR__.'/../../includes/functions.php';$u=require_auth();verify_csrf($_POST['_csrf']??'');
$folder=(int)($_POST['folder_id']??root_folder_id($u['id']));$st=db()->prepare('SELECT id FROM folders WHERE id=? AND user_id=?');$st->execute([$folder,$u['id']]);if(!$st->fetch())json_response(['error'=>'Folder not found.'],404);
if(empty($_FILES['files']))json_response(['error'=>'No files.'],422);
$quota=storage_stats($u['id']);$max=(int)envv('MAX_UPLOAD_BYTES','524288000');$finfo=finfo_open(FILEINFO_MIME_TYPE);$ok=[];$failed=[];
foreach($_FILES['files']['tmp_name'] as $i=>$tmp){if($_FILES['files']['error'][$i]!==UPLOAD_ERR_OK){$failed[]=$_FILES['files']['name'][$i];continue;}
$name=basename($_FILES['files']['name'][$i]);$size=(int)$_FILES['files']['size'][$i];$mime=finfo_file($finfo,$tmp)?:'application/octet-stream'; if($size>$max||$quota['used']+$size>$quota['total']){$failed[]=$name;continue;}
$key='users/'.$u['id'].'/folders/'.$folder.'/'.bin2hex(random_bytes(12)).'-'.preg_replace('/[^A-Za-z0-9._-]/','_', $name);
try{s3()->putObject(['Bucket'=>bucket(),'Key'=>$key,'SourceFile'=>$tmp,'ContentType'=>$mime,'ServerSideEncryption'=>'AES256']);$ins=db()->prepare('INSERT INTO files(user_id,folder_id,file_name,s3_key,mime_type,file_size,file_extension) VALUES(?,?,?,?,?,?,?)');$ins->execute([$u['id'],$folder,$name,$key,$mime,$size,ext_of($name)]);$id=(int)db()->lastInsertId();log_activity($u['id'],'Upload',$id,$folder,'Uploaded '.$name);$ok[]=['id'=>$id,'name'=>$name];$quota['used']+=$size;}catch(Throwable $e){error_log((string)$e);$failed[]=$name;}}
json_response(['uploaded'=>$ok,'failed'=>$failed]);
