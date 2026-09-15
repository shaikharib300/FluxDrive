<?php
require_once __DIR__.'/../../includes/functions.php';$u=require_auth();$id=(int)($_GET['id']??0);$f=owned_file($id,$u['id']);$url=create_presigned_url($f['s3_key'],10);log_activity($u['id'],'Download',$id,$f['folder_id'],'Downloaded '.$f['file_name']);header('Location: '.$url);exit;
