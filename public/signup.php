<?php
require_once __DIR__.'/../includes/functions.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$error=null; $ok=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
 verify_csrf($_POST['_csrf']??null);
 $name=trim($_POST['full_name']??''); $username=trim($_POST['username']??''); $email=strtolower(trim($_POST['email']??'')); $password=$_POST['password']??'';
 if (strlen($name)<2 || !preg_match('/^[A-Za-z0-9_]{3,30}$/',$username) || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<8) $error='Use a valid name, username, email and a password of at least 8 characters.';
 else {
   $st=db()->prepare('SELECT id FROM users WHERE email=? OR username=? LIMIT 1'); $st->execute([$email,$username]);
   if($st->fetch()) $error='Email or username is already in use.';
   else {
     $hash=password_hash($password,PASSWORD_DEFAULT); $ins=db()->prepare('INSERT INTO users(full_name,username,email,password,auth_provider) VALUES(?,?,?,?,?)'); $ins->execute([$name,$username,$email,$hash,'local']); $id=(int)db()->lastInsertId(); ensure_user_root($id); login_user($id); log_activity($id,'Login',null,null,'Account created and signed in'); header('Location: dashboard.php'); exit;
   }
 }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign Up — FluxDrive</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css"></head><body class="auth-page">
<div class="auth-card"><a class="brand mb-4" href="index.php"><span class="brand-mark"><i class="bi bi-cloud-arrow-up"></i></span>FluxDrive</a><h1>Create your account</h1><p class="muted">Your cloud workspace starts here.</p><?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?><a class="btn btn-outline-dark w-100 mb-3 google-btn" href="auth/google-login.php"><i class="bi bi-google"></i> Continue with Google</a><div class="or-divider"><span>OR</span></div><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><label>Full name</label><input class="form-control form-control-lg mb-3" name="full_name" required><label>Username</label><input class="form-control form-control-lg mb-3" name="username" required><label>Email</label><input class="form-control form-control-lg mb-3" type="email" name="email" required><label>Password</label><input class="form-control form-control-lg mb-3" type="password" name="password" minlength="8" required><button class="btn btn-primary btn-lg w-100">Create Account</button></form><p class="text-center mt-4 mb-0">Already have an account? <a href="login.php">Login</a></p></div></body></html>
