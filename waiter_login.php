<?php
include 'config.php';
session_start();
if(isset($_SESSION['waiter_id'])){ header('location:waiter_dashboard.php'); exit; }

if(isset($_POST['submit'])){
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass  = mysqli_real_escape_string($conn, $_POST['password']);
   $q = mysqli_query($conn,"SELECT * FROM `waiters` WHERE email='$email' AND password='$pass'");
   if(mysqli_num_rows($q) > 0){
      $row = mysqli_fetch_assoc($q);
      $_SESSION['waiter_id']   = $row['id'];
      $_SESSION['waiter_name'] = $row['name'];
      $_SESSION['waiter_email']= $row['email'];
      mysqli_query($conn,"UPDATE `waiters` SET is_online=1 WHERE id='{$row['id']}'");
      header('location:waiter_dashboard.php'); exit;
   } else {
      $message[] = 'Incorrect email or password!';
   }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Waiter Login - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Compact light login — fits one screen */

body.auth-body.auth-light {
  min-height: 100vh !important;
  margin: 0 !important;
  background: #F3F0EB !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 1.2rem 1.2rem !important;
  position: relative !important;
  overflow-x: hidden !important;
  box-sizing: border-box !important;
}
.auth-bg-shapes { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.auth-bg-shapes .shape { position: absolute; border-radius: 50%; }
.auth-bg-shapes .s1 { width: 40rem; height: 40rem; top: -12rem; left: -10rem; background: radial-gradient(circle, rgba(255,255,255,.75) 0%, transparent 70%); }
.auth-bg-shapes .s2 { width: 34rem; height: 34rem; bottom: -10rem; right: -8rem; background: radial-gradient(circle, rgba(230,220,210,.5) 0%, transparent 70%); }
.auth-bg-shapes .s3 { width: 18rem; height: 18rem; top: 42%; right: 10%; background: radial-gradient(circle, rgba(255,255,255,.45) 0%, transparent 70%); }

.auth-center { position: relative; z-index: 1; width: 100%; max-width: 40rem; }
.auth-card-light {
  background: #FFFCFA !important;
  border-radius: 18px !important;
  box-shadow: 0 4px 6px rgba(40,32,26,.03), 0 18px 40px rgba(40,32,26,.09) !important;
  padding: 2.4rem 2.6rem 2rem !important;
  border: 1px solid rgba(224,214,201,.55) !important;
  width: 100%;
  box-sizing: border-box !important;
}

/* ===== LOGO CENTERING ===== */
.auth-mark {
  text-align: center;
  margin-bottom: 1.4rem;
}
.logo-wrapper {
  text-align: center;
  margin: 0 0 0.5rem;
}
.brand-mark {
  display: block;
  max-width: 220px;          /* change this value to make logo bigger/smaller */
  width: 100%;
  height: auto;
  margin: 0 auto;
}
.auth-tagline {
  font-size: 1.05rem !important;
  font-weight: 600 !important;
  letter-spacing: .06em !important;
  text-transform: uppercase !important;
  color: #8A9A88 !important;
  margin: 0 !important;
  text-align: center;
}

.auth-heading {
  font-family: 'Source Serif 4', Georgia, serif !important;
  font-size: 2.2rem !important; font-weight: 700 !important;
  color: #2A2420 !important; text-align: center !important;
  margin: 0 0 .3rem !important; letter-spacing: -.02em !important;
}
.auth-sub {
  text-align: center !important; font-size: 1.28rem !important;
  color: #7A6F66 !important; margin: 0 0 1.4rem !important; line-height: 1.4 !important;
}

.auth-switch {
  display: flex !important; gap: .3rem !important;
  background: #F0EBE4 !important; border-radius: 999px !important;
  padding: .25rem !important; margin-bottom: 1.5rem !important;
  border: none !important;
}
.auth-switch a {
  flex: 1 !important; display: flex !important; align-items: center !important; justify-content: center !important;
  gap: .35rem !important; padding: .6rem .6rem !important; border-radius: 999px !important;
  font-size: 1.2rem !important; font-weight: 600 !important; color: #7A6F66 !important;
  background: transparent !important; box-shadow: none !important;
}
.auth-switch a:hover { color: #3A322C !important; background: rgba(255,255,255,.55) !important; }
.auth-switch a.is-active {
  background: #FFFCFA !important; color: #9A5B52 !important;
  box-shadow: 0 1px 4px rgba(40,32,26,.08) !important; font-weight: 700 !important;
}

.auth-form { display: flex; flex-direction: column; gap: .95rem; }
.auth-field label {
  display: block !important; font-size: 1.2rem !important; font-weight: 700 !important;
  color: #3A322C !important; margin-bottom: .3rem !important;
}
.auth-input-wrap { position: relative; display: flex; align-items: center; }
.auth-input-wrap i {
  position: absolute; left: 1.15rem; color: #A09080; font-size: 1.25rem; pointer-events: none;
}
.auth-input-wrap input {
  width: 100% !important; padding: .95rem 1.1rem .95rem 3.5rem !important;
  font-size: 1.35rem !important; border: 1.5px solid #E5DDD3 !important;
  border-radius: 11px !important; background: #FFFEFC !important; color: #2A2420 !important;
  box-shadow: none !important; box-sizing: border-box !important;
}
.auth-input-wrap input::placeholder { color: #A09080 !important; }
.auth-input-wrap input:focus {
  border-color: #9A5B52 !important;
  box-shadow: 0 0 0 3px rgba(154,91,82,.12) !important; outline: none !important;
}

.auth-submit {
  width: 100% !important; margin-top: .35rem !important;
  padding: 1rem 1.8rem !important; border-radius: 11px !important;
  font-size: 1.4rem !important; font-weight: 700 !important; cursor: pointer !important;
  background: #A86B62 !important; color: #fff !important; border: none !important;
  box-shadow: 0 4px 12px rgba(168,107,98,.25) !important;
}
.auth-submit:hover {
  background: #945A52 !important; transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(168,107,98,.3) !important;
}

.auth-divider {
  display: flex; align-items: center; gap: .9rem;
  margin: 1.5rem 0 .6rem; color: #8A9A88;
}
.auth-divider span { flex: 1; height: 1px; background: #E5DDD3; }
.auth-divider i { font-size: 1.1rem; opacity: .7; }
.auth-foot-note {
  text-align: center !important; font-size: 1.25rem !important;
  color: #8A9A88 !important; margin: 0 !important; font-weight: 500 !important;
}

.auth-bg, .auth-blob, .auth-wrap { display: none !important; }

@media (max-height: 720px) {
  .auth-card-light { padding: 1.8rem 2rem 1.5rem !important; }
  .brand-mark { max-width: 190px; }
  .auth-heading { font-size: 1.95rem !important; }
  .auth-sub { margin-bottom: 1rem !important; font-size: 1.2rem !important; }
  .auth-switch { margin-bottom: 1.1rem !important; }
  .auth-form { gap: .75rem; }
  .auth-divider { margin: 1.1rem 0 .45rem; }
}

@media (max-width: 480px) {
  .auth-card-light { padding: 2rem 1.5rem 1.6rem !important; border-radius: 16px !important; }
  .brand-mark { max-width: 180px; }
  .auth-heading { font-size: 2rem !important; }
  .auth-switch a { font-size: 1.1rem !important; padding: .55rem .4rem !important; }
  .auth-tagline { font-size: .98rem !important; letter-spacing: .04em !important; }
}
</style>

</head>
<body class="auth-body auth-light">

   <div class="auth-bg-shapes" aria-hidden="true">
      <span class="shape s1"></span>
      <span class="shape s2"></span>
      <span class="shape s3"></span>
   </div>

   <div class="auth-center">
      <div class="auth-card-light">

         <!-- LOGO (Centered + No food cover) -->
         <div class="auth-mark">
            <div class="logo-wrapper">
               <img src="images/logo.png" alt="DineFlow" class="brand-mark">
            </div>
            <p class="auth-tagline">Restaurant Workflow Management System</p>
         </div>

         <h2 class="auth-heading">Welcome back</h2>
         <p class="auth-sub">Sign in to access your restaurant workspace.</p>

         <div class="auth-switch">
            <a href="admin_login.php"><i class="fas fa-shield-alt"></i> Admin</a>
            <a href="waiter_login.php" class="is-active"><i class="fas fa-user"></i> Waiter</a>
            <a href="cook_login.php"><i class="fas fa-fire"></i> Cook</a>
         </div>

         <form action="" method="post" class="auth-form">
            <div class="auth-field">
               <label for="email">Email address</label>
               <div class="auth-input-wrap">
                  <i class="fas fa-envelope"></i>
                  <input type="email" id="email" name="email" placeholder="Enter your email" required autocomplete="email">
               </div>
            </div>
            <div class="auth-field">
               <label for="password">Password</label>
               <div class="auth-input-wrap">
                  <i class="fas fa-lock"></i>
                  <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
               </div>
            </div>
            <button type="submit" name="submit" class="auth-submit">Login</button>
         </form>

         <div class="auth-divider">
            <span></span>
            <i class="fas fa-utensils"></i>
            <span></span>
         </div>
         <p class="auth-foot-note">Enjoy your shift</p>
      </div>
   </div>

<?php if(isset($message)): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
   Swal.fire({
      icon: 'error',
      title: 'Login failed',
      html: <?php echo json_encode(implode('<br>', $message)); ?>,
      confirmButtonText: 'Try Again',
      buttonsStyling: false,
      customClass: { popup: 'dineflow-swal', confirmButton: 'dineflow-swal-confirm' }
   });
});
</script>
<?php endif; ?>
</body>
</html>