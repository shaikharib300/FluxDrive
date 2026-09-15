<?php
require_once __DIR__.'/../includes/functions.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$error=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
  verify_csrf($_POST['_csrf'] ?? null);
  $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
  $st=db()->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $st->execute([$email]); $u=$st->fetch();
  if ($u && $u['password'] && password_verify($pass,$u['password'])) {
    login_user((int)$u['id']); log_activity((int)$u['id'],'Login',null,null,'Signed in'); header('Location: dashboard.php'); exit;
  }
  $error='Invalid email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login — FluxDrive</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css"></head><body class="auth-page">
<div class="auth-card"><a class="brand mb-4" href="index.php"><span class="brand-mark"><i class="bi bi-cloud-arrow-up"></i></span>FluxDrive</a><h1>Welcome back</h1><p class="muted">Access your files from anywhere.</p><?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?><a class="btn btn-outline-dark w-100 mb-3 google-btn" href="auth/google-login.php"><i class="bi bi-google"></i> Continue with Google</a><div class="or-divider"><span>OR</span></div><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf_token())?>"><label>Email</label><input class="form-control form-control-lg mb-3" type="email" name="email" required><label>Password</label><input class="form-control form-control-lg mb-3" type="password" name="password" required><div class="text-end mb-3"><a href="forgot-password.php">Forgot password?</a></div><button class="btn btn-primary btn-lg w-100">Login</button></form><p class="text-center mt-4 mb-0">Don't have an account? <a href="signup.php">Sign up</a></p></div></body></html>
