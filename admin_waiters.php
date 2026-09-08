<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }

if(isset($_POST['add_waiter'])){
   $name  = mysqli_real_escape_string($conn,$_POST['name']);
   $email = mysqli_real_escape_string($conn,$_POST['email']);
   $pass  = mysqli_real_escape_string($conn,$_POST['password']);
   $phone = mysqli_real_escape_string($conn,$_POST['phone']);
   $check = mysqli_query($conn,"SELECT id FROM `waiters` WHERE email='$email'");
   if(mysqli_num_rows($check)>0){ $message[]='Email already exists!'; }
   else { mysqli_query($conn,"INSERT INTO `waiters`(name,email,password,phone) VALUES('$name','$email','$pass','$phone')"); $message[]='Waiter added!'; }
}

if(isset($_POST['delete_waiter'])){
   $id = intval($_POST['waiter_id']);
   mysqli_query($conn,"DELETE FROM `waiters` WHERE id='$id'");
   $message[] = 'Waiter removed!';
}

$total   = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `waiters`"));
$online  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `waiters` WHERE is_online=1"));
$offline = $total - $online;
$waiters = mysqli_query($conn,"SELECT * FROM `waiters` ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Waiters - Admin - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-user-tie"></i> Admin · Staff</span>
      <h1>Manage Waiters</h1>
      <p class="page-date">Add staff accounts and see who is online</p>
   </div>

   <div class="bento-grid cols-3">
      <div class="bento-tile">
         <div class="b-icon"><i class="fas fa-users"></i></div>
         <div class="b-value"><?php echo $total; ?></div>
         <div class="b-label">Total waiters</div>
      </div>
      <div class="bento-tile green">
         <div class="b-icon"><i class="fas fa-circle"></i></div>
         <div class="b-value"><?php echo $online; ?></div>
         <div class="b-label">Online</div>
      </div>
      <div class="bento-tile red">
         <div class="b-icon"><i class="fas fa-circle"></i></div>
         <div class="b-value"><?php echo $offline; ?></div>
         <div class="b-label">Offline</div>
      </div>
   </div>

   <button type="button" class="btn" onclick="toggleForm('add-waiter-form')" style="margin-bottom:1.5rem;"><i class="fas fa-plus"></i> Add waiter</button>
   <div id="add-waiter-form" class="add-item-form" style="max-width:52rem;margin-bottom:2rem;">
      <form method="post">
         <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
            <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="text" name="password" required></div>
            <div class="form-group"><label>Phone</label><input type="text" name="phone"></div>
         </div>
         <button type="submit" name="add_waiter" class="btn" style="margin-top:1rem;"><i class="fas fa-save"></i> Save waiter</button>
      </form>
   </div>

   <div class="box-container">
   <?php while($w = mysqli_fetch_assoc($waiters)): ?>
      <div class="box">
         <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <h3 class="name"><?php echo htmlspecialchars($w['name']); ?></h3>
            <?php if(!empty($w['is_online'])): ?>
               <span class="badge success">Online</span>
            <?php else: ?>
               <span class="badge danger">Offline</span>
            <?php endif; ?>
         </div>
         <p style="font-size:1.3rem;color:var(--text-muted);margin:.5rem 0;">
            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($w['email']); ?><br>
            <?php if(!empty($w['phone'])): ?><i class="fas fa-phone"></i> <?php echo htmlspecialchars($w['phone']); ?><?php endif; ?>
         </p>
         <form method="post" class="swal-confirm" data-msg="Remove waiter <?php echo htmlspecialchars($w['name'], ENT_QUOTES); ?>?">
            <input type="hidden" name="waiter_id" value="<?php echo $w['id']; ?>">
            <button type="submit" name="delete_waiter" class="delete-btn" style="margin:0;"><i class="fas fa-trash"></i> Remove</button>
         </form>
      </div>
   <?php endwhile; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
