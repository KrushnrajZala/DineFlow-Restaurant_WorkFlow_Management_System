<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }
$admin_id   = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'];
$today = date('Y-m-d');

// Admin remind cook
if(isset($_POST['remind_cook'])){
   $oid = intval($_POST['order_id']);
   $o   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT o.*,tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.id='$oid'"));
   if($o){
      $msg = mysqli_real_escape_string($conn,"⚡ Reminder from $admin_name (Admin) — {$o['item_name']} x{$o['quantity']} — Table #{$o['table_number']} — Order #{$oid} — Please prepare fast!");
      mysqli_query($conn,"INSERT INTO `notifications`(from_type,from_id,from_name,to_type,message) VALUES('admin','$admin_id','$admin_name','cook','$msg')");
      $message[] = "🔔 Cook reminded for {$o['item_name']}!";
   }
}

// Admin notify all waiters food ready
if(isset($_POST['food_ready'])){
   $oid = intval($_POST['order_id']);
   $o   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT o.*,tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.id='$oid'"));
   if($o){
      $msg = mysqli_real_escape_string($conn,"🍽️ Food Ready! (Admin) — {$o['item_name']} x{$o['quantity']} — Table #{$o['table_number']} — Order #{$oid} — Pick up & deliver now!");
      mysqli_query($conn,"INSERT INTO `notifications`(from_type,from_id,from_name,to_type,message) VALUES('admin','$admin_id','$admin_name','waiter','$msg')");
      $message[] = "✅ All waiters notified — {$o['item_name']} ready!";
   }
}

$sort = $_GET['sort'] ?? 'time';
$order_sql = match($sort){
   'table'  => 'tb.table_number, o.added_at',
   'price'  => 'o.price DESC',
   'waiter' => 'o.waiter_name, o.added_at',
   default  => 'o.added_at DESC'
};

$t1_total    = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE DATE(added_at)='$today'"));
$t2_total = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today'AND order_type = 'table'"));
$t_total=$t1_total+$t2_total;
$t_waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$t_cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$t1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$t2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today'AND order_type = 'table'"));
$t_complete = $t1_complete + $t2_complete ;
$orders = mysqli_query($conn,"SELECT o.*, tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE DATE(o.added_at)='$today' ORDER BY $order_sql");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Table Orders - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-layer-group"></i> Admin</span><h1>Table Orders — Today</h1></div>

   <div class="bento-grid cols-3">
      <div class="stat-box"><i class="fas fa-receipt"></i><h3><?php echo $t_total; ?></h3><p>Total</p></div>
      <div class="stat-box" style="border-color:#f59e0b;"><i class="fas fa-clock" style="color:#f59e0b;"></i><h3 style="color:#f59e0b;"><?php echo $t_waiting; ?></h3><p>Waiting</p></div>
      <div class="stat-box" style="border-color:#3498db;"><i class="fas fa-fire" style="color:#3498db;"></i><h3 style="color:#3498db;"><?php echo $t_cooking; ?></h3><p>Cooking</p></div>
      <div class="stat-box" style="border-color:#10b981;"><i class="fas fa-check-circle" style="color:#10b981;"></i><h3 style="color:#10b981;"><?php echo $t_complete; ?></h3><p>Complete</p></div>
   </div>

   <div style="margin-bottom:2rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
      <span style="font-size:1.6rem;color:#6366f1;">Sort by:</span>
      <a href="?sort=time"   class="<?php echo $sort=='time'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-clock"></i> Time</a>
      <a href="?sort=table"  class="<?php echo $sort=='table'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-chair"></i> Table</a>
      <a href="?sort=waiter" class="<?php echo $sort=='waiter'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-user"></i> Waiter</a>
      <a href="?sort=price"  class="<?php echo $sort=='price'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-rupee-sign"></i> Price</a>
   </div>

   <?php if(mysqli_num_rows($orders) > 0): ?>
   <div style="overflow-x:auto;">
   <table class="data-table">
      <thead>
         <tr><th>#</th><th>Table</th><th>Item</th><th>Qty</th><th>Price</th><th>Waiter</th><th>Status</th><th>Delivered</th><th>Payment</th><th>Wait Time</th><th>Time</th><th>Actions</th></tr>
      </thead>
      <tbody>
      <?php $sr=1; while($o=mysqli_fetch_assoc($orders)): $mins=floor((time()-strtotime($o['added_at']))/60); ?>
      <tr>
         <td><?php echo $sr++; ?></td>
         <td>Table <?php echo $o['table_number']; ?></td>
         <td><?php echo $o['item_name']; ?></td>
         <td><?php echo $o['quantity']; ?></td>
         <td>₹<?php echo number_format($o['price']*$o['quantity'],2); ?></td>
         <td><?php echo $o['waiter_name']; ?></td>
         <td><span class="item-status status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
         <td><?php echo $o['is_delivered'] ? '<span style="color:#10b981">Yes</span>' : '<span style="color:#e74c3c">No</span>'; ?></td>
         <td><?php echo $o['payment_done'] ? '<span style="color:#10b981">'.ucfirst($o['payment_method']).'</span>' : '<span style="color:#f59e0b">Pending</span>'; ?></td>
         <td>
         <?php
                  if(!$o['is_delivered'])
                  {      
            ?>
            <span class="timer-badge <?php echo $mins>=30?'overdue':''; ?>" data-added-at="<?php echo $o['added_at']; ?>"><?php echo $mins; ?> min</span>
                  <?php } ?>
         </td>
         <td><?php echo date('h:i A',strtotime($o['added_at'])); ?></td>
         <td>
            <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
            <?php if($o['status'] != 'complete'): ?>
            <form method="post" style="display:inline;">
               <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
               <button name="remind_cook" class="option-btn" style="padding:.4rem .9rem;font-size:1.2rem;margin:0;color:#f59e0b;border-color:#f59e0b;" title="Remind Cook">
                  <i class="fas fa-bell"></i> Remind Cook
               </button>
            </form>
            <form method="post" style="display:inline;">
               <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
               <button name="food_ready" class="btn" style="padding:.4rem .9rem;font-size:1.2rem;margin:0;background:linear-gradient(135deg,#10b981,#059669);" title="Notify waiters food ready">
                  <i class="fas fa-bell"></i> Food Ready
               </button>
            </form>
            <?php else: ?>
               <span style="color:#10b981;font-size:1.3rem;"><i class="fas fa-check-circle"></i> Done</span>
            <?php endif; ?>
            </div>
         </td>
      <?php endwhile; ?>
      </tbody>
   </table>
   </div>
   <?php else: ?>
   <div class="empty"><i class="fas fa-inbox"></i> No table orders today.</div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
