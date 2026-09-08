<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }
$wid = $_SESSION['waiter_id'];

if(isset($_POST['update_profile'])){
   $name  = mysqli_real_escape_string($conn,$_POST['name']);
   $phone = mysqli_real_escape_string($conn,$_POST['phone']);
   mysqli_query($conn,"UPDATE `waiters` SET name='$name',phone='$phone' WHERE id='$wid'");
   $_SESSION['waiter_name'] = $name;
   $message[] = 'Profile updated!';
}

if(isset($_POST['change_password'])){
   $old  = mysqli_real_escape_string($conn,$_POST['old_password']);
   $new  = mysqli_real_escape_string($conn,$_POST['new_password']);
   $cnew = mysqli_real_escape_string($conn,$_POST['confirm_password']);
   $check= mysqli_query($conn,"SELECT * FROM `waiters` WHERE id='$wid' AND password='$old'");
   if(mysqli_num_rows($check)==0){ $message[]='Old password incorrect!'; }
   elseif($new!=$cnew){ $message[]='New passwords do not match!'; }
   else { mysqli_query($conn,"UPDATE `waiters` SET password='$new' WHERE id='$wid'"); $message[]='Password changed!'; }
}

$waiter = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `waiters` WHERE id='$wid'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'waiter_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <h1 class="title">My Profile</h1>
   <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(35rem,1fr));gap:2rem;">
      <!-- Update Profile -->
      <div class="form-container">
         <h3><i class="fas fa-user-edit"></i> Update Profile</h3>
         <form method="post">
            <div class="form-group">
               <label>Full Name</label>
               <input type="text" name="name" value="<?php echo $waiter['name']; ?>" required style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <div class="form-group">
               <label>Email (read only)</label>
               <input type="email" value="<?php echo $waiter['email']; ?>" readonly style="width:100%;padding:1rem;background:#f8f9ff;color:#9ca3af;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;">
            </div>
            <div class="form-group">
               <label>Phone</label>
               <input type="text" name="phone" value="<?php echo $waiter['phone']; ?>" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <button name="update_profile" class="btn"><i class="fas fa-save"></i> Update Profile</button>
         </form>
      </div>

      <!-- Change Password -->
      <div class="form-container">
         <h3><i class="fas fa-lock"></i> Change Password</h3>
         <form method="post">
            <div class="form-group">
               <label>Old Password</label>
               <input type="password" name="old_password" required style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <div class="form-group">
               <label>New Password</label>
               <input type="password" name="new_password" required minlength="6" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <div class="form-group">
               <label>Confirm New Password</label>
               <input type="password" name="confirm_password" required minlength="6" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <button name="change_password" class="btn"><i class="fas fa-key"></i> Change Password</button>
         </form>
      </div>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
