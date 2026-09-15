<?php
require_once __DIR__.'/../../config/google.php';
require_once __DIR__.'/../../includes/functions.php';
if(!envv('GOOGLE_CLIENT_ID')||!envv('GOOGLE_CLIENT_SECRET')) exit('Google OAuth is not configured.');
$_SESSION['oauth_state']=bin2hex(random_bytes(24)); $c=google_client(); $c->setState($_SESSION['oauth_state']); header('Location: '.$c->createAuthUrl()); exit;
