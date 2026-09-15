<?php
require_once __DIR__.'/../../config/google.php'; require_once __DIR__.'/../../includes/functions.php';
if(!hash_equals($_SESSION['oauth_state']??'',$_GET['state']??'')) exit('Invalid OAuth state.');
if(!isset($_GET['code'])) exit('Google authorization failed.');
$c=google_client(); $tok=$c->fetchAccessTokenWithAuthCode($_GET['code']); if(isset($tok['error'])) exit('Google token exchange failed.');
$c->setAccessToken($tok['access_token']); $payload=$c->verifyIdToken($tok['id_token']??'');
if(!$payload) exit('Could not verify Google identity.');
$email=strtolower($payload['email']??''); $name=$payload['name']??'Google User'; $sub=$payload['sub']??''; $pic=$payload['picture']??null;
$st=db()->prepare('SELECT * FROM users WHERE google_id=? OR email=? LIMIT 1');$st->execute([$sub,$email]);$u=$st->fetch();
if($u){ if(!$u['google_id']) db()->prepare('UPDATE users SET google_id=?,profile_picture=?,auth_provider="google",updated_at=NOW() WHERE id=?')->execute([$sub,$pic,$u['id']]); }
else { $username='user_'.substr(hash('sha256',$email),0,10); $ins=db()->prepare('INSERT INTO users(full_name,username,email,google_id,profile_picture,auth_provider) VALUES(?,?,?,?,?,?)');$ins->execute([$name,$username,$email,$sub,$pic,'google']);$u=['id'=>db()->lastInsertId()]; ensure_user_root((int)$u['id']); }
login_user((int)$u['id']); log_activity((int)$u['id'],'Login',null,null,'Signed in with Google'); unset($_SESSION['oauth_state']); header('Location: ../dashboard.php'); exit;
