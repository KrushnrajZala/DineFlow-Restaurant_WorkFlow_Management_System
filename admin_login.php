<?php
include 'config.php';
session_start();
if(isset($_SESSION['admin_id'])){ header('location:admin_dashboard.php'); exit; }

if(isset($_POST['submit'])){
   $email = mysqli_real_escape_string($conn,$_POST['email']);
   $pass  = mysqli_real_escape_string($conn,$_POST['password']);
   $q = mysqli_query($conn,"SELECT * FROM `admins` WHERE email='$email' AND password='$pass'");
   if(mysqli_num_rows($q)>0){
      $row = mysqli_fetch_assoc($q);
      $_SESSION['admin_id']   = $row['id'];
      $_SESSION['admin_name'] = $row['name'];
      header('location:admin_dashboard.php'); exit;
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
   <title>Admin Login - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-body">
   <div class="auth-bg">
      <div class="auth-blob b1"></div>
      <div class="auth-blob b2"></div>
      <div class="auth-blob b3"></div>
   </div>

   <div class="auth-wrap">
      <div class="auth-switch">
         <a href="admin_login.php" class="is-active"><i class="fas fa-shield-alt"></i> Admin</a>
         <a href="waiter_login.php"><i class="fas fa-user"></i> Waiter</a>
         <a href="cook_login.php"><i class="fas fa-fire"></i> Cook</a>
      </div>

      <div class="auth-card">
         <div class="auth-logo"><img src="images/logo.png" alt="DineFlow"></div>
         <div class="auth-role-pill"><i class="fas fa-shield-alt"></i> Admin Control Panel</div>
         <h1>Welcome back</h1>
         <p class="auth-sub">Sign in to manage your restaurant, staff and reports</p>

         <form action="" method="post">
            <div class="auth-field">
               <i class="fas fa-envelope field-icon"></i>
               <input type="email" name="email" placeholder="admin@email.com" required>
            </div>
            <div class="auth-field">
               <i class="fas fa-lock field-icon"></i>
               <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="submit" class="auth-submit">
               Login to Admin Panel <i class="fas fa-arrow-right"></i>
            </button>
         </form>

         <div class="auth-chips">
            <div class="auth-chip"><i class="fas fa-chart-line"></i> Earnings</div>
            <div class="auth-chip"><i class="fas fa-users"></i> Staff</div>
            <div class="auth-chip"><i class="fas fa-book-open"></i> Menu</div>
            <div class="auth-chip"><i class="fas fa-boxes"></i> Inventory</div>
         </div>
      </div>

      <div class="auth-footer">
         <p>Trouble signing in? Contact <a href="mailto:info@dineflow.app">support</a></p>
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
