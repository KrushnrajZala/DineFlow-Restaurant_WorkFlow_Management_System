<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }

if(isset($_POST['add_table'])){
   $last = mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(table_number) as mx FROM `tables`"));
   $tnum = ($last['mx'] ?? 0) + 1;
   $cap  = intval($_POST['capacity']);
   mysqli_query($conn,"INSERT INTO `tables`(table_number,capacity) VALUES('$tnum','$cap')");
   $message[] = "Table #$tnum added!";
}

if(isset($_POST['delete_table'])){
   $id = intval($_POST['table_id']);
   mysqli_query($conn,"DELETE FROM `tables` WHERE id='$id'");
   $message[] = 'Table removed!';
}

$total = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `tables`"));
$avail = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `tables` WHERE is_available=1"));
$occup = $total - $avail;
$tables= mysqli_query($conn,"SELECT * FROM `tables` ORDER BY table_number");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tables - Admin - DineFlow</title>
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
      <span class="page-kicker"><i class="fas fa-chair"></i> Admin · Floor</span>
      <h1>Manage Tables</h1>
      <p class="page-date">Add tables and track free vs occupied</p>
   </div>

   <div class="bento-grid cols-3">
      <div class="bento-tile">
         <div class="b-icon"><i class="fas fa-border-all"></i></div>
         <div class="b-value"><?php echo $total; ?></div>
         <div class="b-label">Total tables</div>
      </div>
      <div class="bento-tile green">
         <div class="b-icon"><i class="fas fa-door-open"></i></div>
         <div class="b-value"><?php echo $avail; ?></div>
         <div class="b-label">Available</div>
      </div>
      <div class="bento-tile orange">
         <div class="b-icon"><i class="fas fa-user-friends"></i></div>
         <div class="b-value"><?php echo $occup; ?></div>
         <div class="b-label">Occupied</div>
      </div>
   </div>

   <div class="box" style="max-width:40rem;margin-bottom:2rem;">
      <h3><i class="fas fa-plus" style="color:var(--primary);"></i> Add table</h3>
      <form method="post" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;margin-top:1rem;">
         <div class="form-group" style="margin:0;min-width:12rem;">
            <label>Capacity (seats)</label>
            <input type="number" name="capacity" value="4" min="1" max="20" required>
         </div>
         <button type="submit" name="add_table" class="btn"><i class="fas fa-plus"></i> Add</button>
      </form>
   </div>

   <div class="tables-grid">
   <?php while($tb = mysqli_fetch_assoc($tables)):
      $free = (int)$tb['is_available'];
   ?>
      <div class="table-card <?php echo $free?'available':'occupied'; ?>">
         <div class="table-card-head">
            <h3><i class="fas fa-chair"></i> Table <?php echo (int)$tb['table_number']; ?></h3>
            <span class="table-status <?php echo $free?'avail':'busy'; ?>">
               <?php echo $free ? 'Available' : 'Occupied'; ?>
            </span>
         </div>
         <p style="font-size:1.4rem;color:var(--text-muted);margin-bottom:1rem;">
            <i class="fas fa-users"></i> <?php echo (int)$tb['capacity']; ?> seats
         </p>
         <form method="post" class="swal-confirm" data-msg="Remove Table <?php echo (int)$tb['table_number']; ?>?">
            <input type="hidden" name="table_id" value="<?php echo $tb['id']; ?>">
            <button type="submit" name="delete_table" class="delete-btn" style="margin:0;"><i class="fas fa-trash"></i> Remove</button>
         </form>
      </div>
   <?php endwhile; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
