<?php
require_once __DIR__.'/../includes/functions.php';
$u=current_user(); if($u) log_activity((int)$u['id'],'Logout',null,null,'Signed out'); logout_user(); header('Location: login.php'); exit;
