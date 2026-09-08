<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }

// Add customer
if(isset($_POST['add_customer'])){
   $name   = mysqli_real_escape_string($conn,$_POST['customer_name']);
   $phone  = mysqli_real_escape_string($conn,$_POST['phone']);
   $people = intval($_POST['total_people']);
   $note   = mysqli_real_escape_string($conn,$_POST['note'] ?? '');
   mysqli_query($conn,"INSERT INTO `waiting_list`(customer_name,phone,total_people,note) VALUES('$name','$phone','$people','$note')");
   $message[] = 'Customer added to waiting list!';
}

// Update customer
if(isset($_POST['update_customer'])){
   $id     = intval($_POST['wl_id']);
   $name   = mysqli_real_escape_string($conn,$_POST['customer_name']);
   $phone  = mysqli_real_escape_string($conn,$_POST['phone']);
   $people = intval($_POST['total_people']);
   $note   = mysqli_real_escape_string($conn,$_POST['note'] ?? '');
   mysqli_query($conn,"UPDATE `waiting_list` SET customer_name='$name',phone='$phone',total_people='$people',note='$note' WHERE id='$id'");
   $message[] = 'Customer updated!';
}

// Delete customer
if(isset($_POST['delete_customer'])){
   $id = intval($_POST['wl_id']);
   mysqli_query($conn,"DELETE FROM `waiting_list` WHERE id='$id'");
   $message[] = 'Customer removed from waiting list!';
}

// Assign table (removes from waiting)
if(isset($_POST['assign_table'])){
   $id  = intval($_POST['wl_id']);
   $tid = intval($_POST['assign_table_id']);
   mysqli_query($conn,"UPDATE `tables` SET is_available=0 WHERE id='$tid'");
   mysqli_query($conn,"DELETE FROM `waiting_list` WHERE id='$id'");
   $message[] = 'Table assigned and customer removed from waiting list!';
}

$waiting = mysqli_query($conn,"SELECT * FROM `waiting_list` ORDER BY added_at");
$total_waiting = mysqli_num_rows($waiting);
$avail_tables  = mysqli_query($conn,"SELECT * FROM `tables` WHERE is_available=1 ORDER BY table_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Waiting Area - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'waiter_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-clock"></i> Waiting</span><h1>Waiting Area</h1></div>

   <!-- Stats -->
   <div class="stats-container cols-3">
      <div class="stat-box">
         <i class="fas fa-users"></i>
         <h3><?php echo $total_waiting; ?></h3>
         <p>Customers Waiting</p>
      </div>
      <div class="stat-box">
         <i class="fas fa-chair"></i>
         <h3><?php echo mysqli_num_rows($avail_tables); ?></h3>
         <p>Tables Available</p>
      </div>
      <?php
      $total_people = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_people) as t FROM `waiting_list`"));
      ?>
      <div class="stat-box">
         <i class="fas fa-user-friends"></i>
         <h3><?php echo $total_people['t'] ?? 0; ?></h3>
         <p>Total People Waiting</p>
      </div>
   </div>

   <!-- Add Customer Form -->
   <div style="margin-bottom:2rem;">
      <button class="btn" onclick="toggleForm('add-customer-form')"><i class="fas fa-plus"></i> Add Customer to Waiting List</button>
      <div id="add-customer-form" style="display:none;margin-top:1.5rem;">
         <div class="form-container">
            <h3><i class="fas fa-user-plus"></i> Add Customer</h3>
            <form method="post">
               <div class="form-group">
                  <label>Customer Name</label>
                  <input type="text" name="customer_name" placeholder="Enter customer name" required class="box" style="background:#f8f9ff;border:1px solid #e8eaf6;color:#1a1a2e;padding:1rem;border-radius:.5rem;font-size:1.5rem;">
               </div>
               <div class="form-group">
                  <label>Phone Number</label>
                  <input type="text" name="phone" placeholder="Enter phone number" required class="box" style="background:#f8f9ff;border:1px solid #e8eaf6;color:#1a1a2e;padding:1rem;border-radius:.5rem;font-size:1.5rem;">
               </div>
               <div class="form-group">
                  <label>Total People</label>
                  <input type="number" name="total_people" placeholder="Number of people" min="1" required class="box" style="background:#f8f9ff;border:1px solid #e8eaf6;color:#1a1a2e;padding:1rem;border-radius:.5rem;font-size:1.5rem;">
               </div>
               <div class="form-group">
                  <label>Note (optional)</label>
                  <input type="text" name="note" placeholder="Any special note" class="box" style="background:#f8f9ff;border:1px solid #e8eaf6;color:#1a1a2e;padding:1rem;border-radius:.5rem;font-size:1.5rem;">
               </div>
               <button name="add_customer" class="btn"><i class="fas fa-plus"></i> Add to Waiting</button>
            </form>
         </div>
      </div>
   </div>

   <!-- Waiting List -->
   <?php if($total_waiting > 0): ?>
   <?php mysqli_data_seek($waiting,0); $sr=1; while($w=mysqli_fetch_assoc($waiting)): ?>
   <div class="waiting-card">
      <div class="customer-info">
         <h4><i class="fas fa-user"></i> <?php echo $w['customer_name']; ?> &nbsp; <small style="color:#9ca3af;font-size:1.3rem;">#<?php echo $sr++; ?></small></h4>
         <p><i class="fas fa-phone"></i> <?php echo $w['phone']; ?> &nbsp;|&nbsp; <i class="fas fa-users"></i> <?php echo $w['total_people']; ?> people</p>
         <?php if($w['note']): ?><p><i class="fas fa-sticky-note"></i> <?php echo $w['note']; ?></p><?php endif; ?>
      </div>
      <div class="wait-time">
         <span class="timer-badge" data-added-at="<?php echo $w['added_at']; ?>">...</span>
      </div>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
         <!-- Assign Table -->
         <form method="post" style="display:flex;gap:.5rem;align-items:center;">
            <input type="hidden" name="wl_id" value="<?php echo $w['id']; ?>">
            <select name="assign_table_id" style="padding:.6rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #6366f1;border-radius:.5rem;font-size:1.4rem;">
               <?php
               $at = mysqli_query($conn,"SELECT * FROM `tables` WHERE is_available=1 ORDER BY table_number");
               if(mysqli_num_rows($at)>0){ while($t=mysqli_fetch_assoc($at)) echo '<option value="'.$t['id'].'">Table '.$t['table_number'].' ('.$t['capacity'].' seats)</option>'; }
               else echo '<option value="">No tables available</option>';
               ?>
            </select>
            <button name="assign_table" class="btn" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-check"></i> Assign</button>
         </form>
         <!-- Edit -->
         <button class="option-btn" onclick="toggleForm('edit-<?php echo $w['id']; ?>')"><i class="fas fa-edit"></i> Edit</button>
         <!-- Delete -->
         <form method="post" class="swal-confirm" data-msg="Remove this customer?">
            <input type="hidden" name="wl_id" value="<?php echo $w['id']; ?>">
            <button name="delete_customer" class="delete-btn" style="margin:0;"><i class="fas fa-trash"></i></button>
         </form>
      </div>
      <!-- Edit Form -->
      <div id="edit-<?php echo $w['id']; ?>" style="display:none;width:100%;margin-top:1rem;padding-top:1rem;border-top:1px solid #e8eaf6;">
         <form method="post" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="wl_id" value="<?php echo $w['id']; ?>">
            <input type="text" name="customer_name" value="<?php echo $w['customer_name']; ?>" placeholder="Name" style="padding:.7rem;background:#f8f9ff;color:#1a1a2e;border:1px solid #e8eaf6;border-radius:.5rem;font-size:1.4rem;flex:1;">
            <input type="text" name="phone" value="<?php echo $w['phone']; ?>" placeholder="Phone" style="padding:.7rem;background:#f8f9ff;color:#1a1a2e;border:1px solid #e8eaf6;border-radius:.5rem;font-size:1.4rem;flex:1;">
            <input type="number" name="total_people" value="<?php echo $w['total_people']; ?>" min="1" style="padding:.7rem;background:#f8f9ff;color:#1a1a2e;border:1px solid #e8eaf6;border-radius:.5rem;font-size:1.4rem;width:10rem;">
            <input type="text" name="note" value="<?php echo $w['note']; ?>" placeholder="Note" style="padding:.7rem;background:#f8f9ff;color:#1a1a2e;border:1px solid #e8eaf6;border-radius:.5rem;font-size:1.4rem;flex:1;">
            <button name="update_customer" class="btn" style="margin:0;">Update</button>
         </form>
      </div>
   </div>
   <?php endwhile; ?>
   <?php else: ?>
      <div class="empty"><i class="fas fa-users"></i> No customers waiting right now.</div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
