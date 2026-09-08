<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }
$today = date('Y-m-d');

$sort = $_GET['sort'] ?? 'time';
$order_sql = match($sort){
   'parcel' => 'o.parcel_number, o.added_at',
   'price'  => 'o.price DESC',
   'waiter' => 'o.waiter_name, o.added_at',
   default  => 'o.added_at DESC'
};

$p1_total    = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE DATE(added_at)='$today'"));
$p2_total = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'parcel'"));
$p_total = $p1_total+$p2_total;
$p_waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$p_cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$p1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$p2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'parcel'"));
$p_complete=$p1_complete+$p2_complete;
$p_deliver  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'parcel'"));
$orders = mysqli_query($conn,"SELECT o.* FROM `parcel_orders` o WHERE DATE(o.added_at)='$today' ORDER BY $order_sql");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Parcel Orders - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-layer-group"></i> Admin</span><h1>Parcel Orders — Today</h1></div>

   <div class="bento-grid cols-3">
      <div class="stat-box"><i class="fas fa-box"></i><h3><?php echo $p_total; ?></h3><p>Total</p></div>
      <div class="stat-box" style="border-color:#f59e0b;"><i class="fas fa-clock" style="color:#f59e0b;"></i><h3 style="color:#f59e0b;"><?php echo $p_waiting; ?></h3><p>Waiting</p></div>
      <div class="stat-box" style="border-color:#3498db;"><i class="fas fa-fire" style="color:#3498db;"></i><h3 style="color:#3498db;"><?php echo $p_cooking; ?></h3><p>Cooking</p></div>
      <div class="stat-box" style="border-color:#10b981;"><i class="fas fa-check-circle" style="color:#10b981;"></i><h3 style="color:#10b981;"><?php echo $p_complete; ?></h3><p>Complete</p></div>
      <div class="stat-box" style="border-color:#6366f1;"><i class="fas fa-truck"></i><h3><?php echo $p_deliver; ?></h3><p>Delivered</p></div>
   </div>

   <div style="margin-bottom:2rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
      <span style="font-size:1.6rem;color:#6366f1;">Sort by:</span>
      <a href="?sort=time"   class="<?php echo $sort=='time'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-clock"></i> Time</a>
      <a href="?sort=parcel" class="<?php echo $sort=='parcel'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-box"></i> Parcel</a>
      <a href="?sort=waiter" class="<?php echo $sort=='waiter'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-user"></i> Waiter</a>
      <a href="?sort=price"  class="<?php echo $sort=='price'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-rupee-sign"></i> Price</a>
   </div>

   <?php if(mysqli_num_rows($orders) > 0): ?>
   <div style="overflow-x:auto;">
   <table class="data-table">
      <thead>
         <tr><th>#</th><th>Parcel</th><th>Item</th><th>Qty</th><th>Price</th><th>Waiter</th><th>Status</th><th>Delivered</th><th>Payment</th><th>Wait Time</th><th>Time</th></tr>
      </thead>
      <tbody>
      <?php $sr=1; while($o=mysqli_fetch_assoc($orders)): $mins=floor((time()-strtotime($o['added_at']))/60); ?>
      <tr>
         <td><?php echo $sr++; ?></td>
         <td>Parcel #<?php echo $o['parcel_number']; ?></td>
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
      </tr>
      <?php endwhile; ?>
      </tbody>
   </table>
   </div>
   <?php else: ?>
   <div class="empty"><i class="fas fa-inbox"></i> No parcel orders today.</div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
