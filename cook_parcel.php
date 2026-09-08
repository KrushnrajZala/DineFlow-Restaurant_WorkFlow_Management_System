<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }
$today = date('Y-m-d');

if(isset($_POST['update_status'])){
   $oid    = intval($_POST['order_id']);
   $status = mysqli_real_escape_string($conn,$_POST['status']);
   mysqli_query($conn,"UPDATE `parcel_orders` SET status='$status' WHERE id='$oid'");
   $message[] = 'Status updated!';
}

$sort = $_GET['sort'] ?? 'time';
$order_by = ($sort == 'parcel') ? 'o.parcel_number, o.added_at' : 'o.added_at';

$waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$p1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$p2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'parcel'"));
$complete = $p1_complete + $p2_complete;

$active_orders   = mysqli_query($conn,"SELECT o.* FROM `parcel_orders` o WHERE o.status != 'complete' AND DATE(o.added_at)='$today' ORDER BY $order_by");
$complete_orders = mysqli_query($conn,"SELECT o.* FROM `parcel_orders` o WHERE o.status='complete' AND DATE(o.added_at)='$today' ORDER BY o.added_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="refresh" content="20">
   <title>Parcel Tickets - Kitchen</title>
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
      <span class="page-kicker"><i class="fas fa-box"></i> Kitchen · Parcels</span>
      <h1>Parcel tickets</h1>
      <p class="page-date">Takeaway / delivery tickets · auto-refresh every 20s</p>
   </div>

   <div class="bento-grid cols-3">
      <div class="bento-tile orange"><div class="b-icon"><i class="fas fa-clock"></i></div><div class="b-value"><?php echo $waiting; ?></div><div class="b-label">Waiting</div></div>
      <div class="bento-tile blue"><div class="b-icon"><i class="fas fa-fire"></i></div><div class="b-value"><?php echo $cooking; ?></div><div class="b-label">Cooking</div></div>
      <div class="bento-tile green"><div class="b-icon"><i class="fas fa-check"></i></div><div class="b-value"><?php echo $complete; ?></div><div class="b-label">Done today</div></div>
   </div>

   <div class="filter-bar" style="margin-bottom:1.5rem;">
      <a href="?sort=time" class="filter-chip <?php echo $sort=='time'?'active':''; ?>">By time</a>
      <a href="?sort=parcel" class="filter-chip <?php echo $sort=='parcel'?'active':''; ?>">By parcel #</a>
   </div>

   <h2 class="zone-title"><i class="fas fa-fire"></i> Active parcel tickets</h2>
   <?php if(mysqli_num_rows($active_orders)==0): ?>
   <div class="empty"><i class="fas fa-check-circle"></i><p>No active parcel tickets.</p></div>
   <?php else: ?>
   <div class="ticket-grid">
   <?php while($o=mysqli_fetch_assoc($active_orders)):
      $mins = floor((time()-strtotime($o['added_at']))/60);
      $overdue = $mins >= 30;
   ?>
      <div class="ticket-card parcel <?php echo $o['status']; ?> <?php echo $overdue?'overdue':''; ?>">
         <div class="ticket-top">
            <span class="ticket-table"><i class="fas fa-box"></i> Parcel #<?php echo (int)$o['parcel_number']; ?></span>
            <span class="ticket-time <?php echo $overdue?'hot':''; ?>"><?php echo $mins; ?> min</span>
         </div>
         <div class="ticket-dish"><?php echo htmlspecialchars($o['item_name']); ?></div>
         <div class="ticket-meta">
            Qty <strong>×<?php echo (int)$o['quantity']; ?></strong>
            <?php if(!empty($o['waiter_name'])): ?> · <?php echo htmlspecialchars($o['waiter_name']); ?><?php endif; ?>
            · <span class="badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span>
         </div>
         <form method="post" class="ticket-actions">
            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
            <select name="status" class="ticket-select">
               <option value="waiting" <?php echo $o['status']=='waiting'?'selected':''; ?>>Waiting</option>
               <option value="cooking" <?php echo $o['status']=='cooking'?'selected':''; ?>>Cooking</option>
               <option value="complete" <?php echo $o['status']=='complete'?'selected':''; ?>>Complete</option>
            </select>
            <button type="submit" name="update_status" class="btn" style="margin:0;"><i class="fas fa-check"></i> Update</button>
         </form>
      </div>
   <?php endwhile; ?>
   </div>
   <?php endif; ?>

   <h2 class="zone-title" style="margin-top:2.5rem;"><i class="fas fa-check-double"></i> Recently completed</h2>
   <div class="ticket-grid">
   <?php while($o=mysqli_fetch_assoc($complete_orders)): ?>
      <div class="ticket-card parcel complete">
         <div class="ticket-top">
            <span class="ticket-table"><i class="fas fa-box"></i> Parcel #<?php echo (int)$o['parcel_number']; ?></span>
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
