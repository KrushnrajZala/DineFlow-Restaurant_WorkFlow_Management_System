<?php
include 'config.php';
session_start();
if(isset($_SESSION['cook_id'])){ header('location:cook_dashboard.php'); exit; }

if(isset($_POST['submit'])){
   $email = mysqli_real_escape_string($conn,$_POST['email']);
   $pass  = mysqli_real_escape_string($conn,$_POST['password']);
   $q = mysqli_query($conn,"SELECT * FROM `cook` WHERE email='$email' AND password='$pass'");
   if(mysqli_num_rows($q)>0){
      $row = mysqli_fetch_assoc($q);
      $_SESSION['cook_id']   = $row['id'];
      $_SESSION['cook_name'] = $row['name'];
      header('location:cook_dashboard.php'); exit;
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
   <title>Cook Login - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-body">

   <div class="auth-shell">
      <!-- Left brand panel -->
      <aside class="auth-brand">
         <div class="auth-brand-inner">
            <div class="auth-brand-logo">
               <img src="images/logo.png" alt="DineFlow">
            </div>
            <h2 class="auth-brand-title">Kitchen flow,<br>under control.</h2>
            <p class="auth-brand-text">See the queue, mark dishes ready, and keep service moving without the noise.</p>
            <ul class="auth-brand-list">
               <li><i class="fas fa-check"></i> Live order queue</li>
               <li><i class="fas fa-check"></i> Overdue alerts</li>
               <li><i class="fas fa-check"></i> Status updates</li>
               <li><i class="fas fa-check"></i> Menu control</li>
            </ul>
         </div>
         <p class="auth-brand-foot">DineFlow · Restaurant OS</p>
      </aside>

      <!-- Right form panel -->
      <main class="auth-panel">
         <div class="auth-panel-inner">
            <div class="auth-switch">
               <a href="admin_login.php"><i class="fas fa-shield-alt"></i> Admin</a>
               <a href="waiter_login.php"><i class="fas fa-user"></i> Waiter</a>
               <a href="cook_login.php" class="is-active"><i class="fas fa-fire"></i> Cook</a>
            </div>

            <div class="auth-form-block">
               <span class="auth-role-tag"><i class="fas fa-fire"></i> Cook Portal</span>
               <h1>Hello, Chef</h1>
               <p class="auth-sub">Sign in to manage the kitchen queue and menu</p>

               <form action="" method="post" class="auth-form">
                  <div class="auth-field">
                     <label for="email">Email</label>
                     <div class="auth-input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="your@email.com" required autocomplete="email">
                     </div>
                  </div>
                  <div class="auth-field">
                     <label for="password">Password</label>
                     <div class="auth-input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                     </div>
                  </div>
                  <button type="submit" name="submit" class="auth-submit">
                     Login to Kitchen <i class="fas fa-arrow-right"></i>
                  </button>
               </form>

               <p class="auth-help">Trouble signing in? Contact <a href="mailto:info@dineflow.app">support</a></p>
            </div>
         </div>
      </main>
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
