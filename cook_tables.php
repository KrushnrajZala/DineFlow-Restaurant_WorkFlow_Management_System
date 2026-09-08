<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }
$cook_id   = $_SESSION['cook_id'];
$cook_name = $_SESSION['cook_name'];
$today = date('Y-m-d');

if(isset($_POST['update_status'])){
   $oid    = intval($_POST['order_id']);
   $status = mysqli_real_escape_string($conn,$_POST['status']);
   mysqli_query($conn,"UPDATE `table_orders` SET status='$status' WHERE id='$oid'");
   $message[] = 'Status updated!';
}

if(isset($_POST['food_ready'])){
   $oid = intval($_POST['order_id']);
   $o   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT o.*,tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.id='$oid'"));
   if($o){
      $msg = mysqli_real_escape_string($conn,"Food Ready! — {$o['item_name']} x{$o['quantity']} — Table #{$o['table_number']} — Order #{$oid} — Pick up & deliver now!");
      mysqli_query($conn,"INSERT INTO `notifications`(from_type,from_id,from_name,to_type,message) VALUES('cook','$cook_id','$cook_name','waiter','$msg')");
      mysqli_query($conn,"UPDATE `table_orders` SET status='complete' WHERE id='$oid'");
      $message[] = "All waiters notified — {$o['item_name']} is ready!";
   }
}

$sort = $_GET['sort'] ?? 'time';
$order_by = ($sort == 'table') ? 'tb.table_number, o.added_at' : 'o.added_at';

$waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$t1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$t2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'table'"));
$complete = $t1_complete + $t2_complete;

$active_orders = mysqli_query($conn,"SELECT o.*, tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.status != 'complete' AND DATE(o.added_at)='$today' AND (o.show_after IS NULL OR o.show_after <= NOW()) ORDER BY $order_by");
$scheduled_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today' AND show_after IS NOT NULL AND show_after > NOW()"))['c'] ?? 0;
$complete_orders = mysqli_query($conn,"SELECT o.*, tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.status='complete' AND DATE(o.added_at)='$today' ORDER BY o.added_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="refresh" content="20">
   <title>Table Tickets - Kitchen</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'cook_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-chair"></i> Kitchen · Tables</span>
      <h1>Table tickets</h1>
      <p class="page-date">Box tickets for active dishes · auto-refresh every 20s</p>
   </div>

   <div class="bento-grid cols-4">
      <div class="bento-tile orange"><div class="b-icon"><i class="fas fa-clock"></i></div><div class="b-value"><?php echo $waiting; ?></div><div class="b-label">Waiting</div></div>
      <div class="bento-tile blue"><div class="b-icon"><i class="fas fa-fire"></i></div><div class="b-value"><?php echo $cooking; ?></div><div class="b-label">Cooking</div></div>
      <div class="bento-tile green"><div class="b-icon"><i class="fas fa-check"></i></div><div class="b-value"><?php echo $complete; ?></div><div class="b-label">Done today</div></div>
      <div class="bento-tile"><div class="b-icon"><i class="fas fa-hourglass"></i></div><div class="b-value"><?php echo (int)$scheduled_count; ?></div><div class="b-label">Scheduled later</div></div>
   </div>

   <div class="filter-bar" style="margin-bottom:1.5rem;">
      <a href="?sort=time" class="filter-chip <?php echo $sort=='time'?'active':''; ?>">By time</a>
      <a href="?sort=table" class="filter-chip <?php echo $sort=='table'?'active':''; ?>">By table</a>
   </div>

   <h2 class="zone-title"><i class="fas fa-fire"></i> Active tickets</h2>
   <?php if(mysqli_num_rows($active_orders)==0): ?>
   <div class="empty"><i class="fas fa-check-circle"></i><p>No active table tickets. Kitchen is clear.</p></div>
   <?php else: ?>
   <div class="ticket-grid">
   <?php while($o=mysqli_fetch_assoc($active_orders)):
      $mins = floor((time()-strtotime($o['added_at']))/60);
      $overdue = $mins >= 30;
   ?>
      <div class="ticket-card <?php echo $o['status']; ?> <?php echo $overdue?'overdue':''; ?>">
         <div class="ticket-top">
            <span class="ticket-table"><i class="fas fa-chair"></i> Table <?php echo (int)$o['table_number']; ?></span>
            <span class="ticket-time <?php echo $overdue?'hot':''; ?>"><?php echo $mins; ?> min</span>
         </div>
         <div class="ticket-dish"><?php echo htmlspecialchars($o['item_name']); ?></div>
         <div class="ticket-meta">Qty <strong>×<?php echo (int)$o['quantity']; ?></strong> · <span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></div>
         <form method="post" class="ticket-actions">
            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
            <select name="status" class="ticket-select">
               <option value="waiting" <?php echo $o['status']=='waiting'?'selected':''; ?>>Waiting</option>
               <option value="cooking" <?php echo $o['status']=='cooking'?'selected':''; ?>>Cooking</option>
               <option value="complete" <?php echo $o['status']=='complete'?'selected':''; ?>>Complete</option>
            </select>
            <button type="submit" name="update_status" class="option-btn" style="margin:0;"><i class="fas fa-sync"></i></button>
            <button type="submit" name="food_ready" class="btn" style="margin:0;"><i class="fas fa-bell"></i> Ready</button>
         </form>
      </div>
   <?php endwhile; ?>
   </div>
   <?php endif; ?>

   <h2 class="zone-title" style="margin-top:2.5rem;"><i class="fas fa-check-double"></i> Recently completed</h2>
   <div class="ticket-grid">
   <?php while($o=mysqli_fetch_assoc($complete_orders)): ?>
      <div class="ticket-card complete">
         <div class="ticket-top">
            <span class="ticket-table"><i class="fas fa-chair"></i> Table <?php echo (int)$o['table_number']; ?></span>
            <span class="badge success">Done</span>
         </div>
         <div class="ticket-dish"><?php echo htmlspecialchars($o['item_name']); ?> ×<?php echo (int)$o['quantity']; ?></div>
      </div>
   <?php endwhile; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
