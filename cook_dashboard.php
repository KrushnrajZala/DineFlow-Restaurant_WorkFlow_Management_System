<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }
$today = date('Y-m-d');

$t_waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$t_cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$t1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$t2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'table'"));
$t_complete = $t1_complete + $t2_complete;

$p_waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$p_cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$p1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$p2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'parcel'"));
$p_complete = $p1_complete + $p2_complete;

$active_total = $t_waiting + $t_cooking + $p_waiting + $p_cooking;
$overdue = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status IN ('waiting','cooking') AND DATE(added_at)='$today' AND TIMESTAMPDIFF(MINUTE,added_at,NOW()) >= 30"));
$overdue += mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status IN ('waiting','cooking') AND DATE(added_at)='$today' AND TIMESTAMPDIFF(MINUTE,added_at,NOW()) >= 30"));

$next_table = mysqli_query($conn,"SELECT o.item_name, o.quantity, tb.table_number, o.added_at FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.status='waiting' AND DATE(o.added_at)='$today' AND (o.show_after IS NULL OR o.show_after <= NOW()) ORDER BY o.added_at LIMIT 5");
$next_parcel = mysqli_query($conn,"SELECT item_name, quantity, parcel_number, added_at FROM `parcel_orders` WHERE status='waiting' AND DATE(added_at)='$today' ORDER BY added_at LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Kitchen Board - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'cook_header.php'; ?>

<section class="dash-shell">
   <div class="dash-hero">
      <div class="dash-hero-text">
         <span class="page-kicker"><i class="fas fa-fire"></i> Kitchen</span>
         <h1>Cook Board</h1>
         <p class="page-date"><?php echo date('l, d M Y · h:i A'); ?> · Live kitchen snapshot</p>
      </div>
      <div class="dash-hero-actions">
         <a href="cook_tables.php" class="btn"><i class="fas fa-chair"></i> Table tickets</a>
         <a href="cook_parcel.php" class="white-btn"><i class="fas fa-box"></i> Parcel tickets</a>
         <a href="cook_menu.php" class="white-btn"><i class="fas fa-utensils"></i> Menu</a>
      </div>
   </div>

   <div class="stat-strip">
      <div class="stat-chip red">
         <div class="chip-icon"><i class="fas fa-bolt"></i></div>
         <div>
            <div class="chip-val"><?php echo $active_total; ?></div>
            <div class="chip-lbl">Active tickets</div>
         </div>
      </div>
      <div class="stat-chip gold">
         <div class="chip-icon"><i class="fas fa-hourglass-half"></i></div>
         <div>
            <div class="chip-val"><?php echo $t_waiting + $p_waiting; ?></div>
            <div class="chip-lbl">Waiting to start</div>
         </div>
      </div>
      <div class="stat-chip blue">
         <div class="chip-icon"><i class="fas fa-fire"></i></div>
         <div>
            <div class="chip-val"><?php echo $t_cooking + $p_cooking; ?></div>
            <div class="chip-lbl">On the stove</div>
         </div>
      </div>
      <div class="stat-chip <?php echo $overdue?'red':''; ?>">
         <div class="chip-icon"><i class="fas fa-exclamation-triangle"></i></div>
         <div>
            <div class="chip-val"><?php echo $overdue; ?></div>
            <div class="chip-lbl">Over 30 min</div>
         </div>
      </div>
   </div>

   <div class="admin-ops-grid">
      <div class="ops-panel">
         <div class="ops-panel-head"><i class="fas fa-chair"></i> Table kitchen</div>
         <div class="ops-rows">
            <div class="ops-row"><span>Waiting</span><strong><?php echo $t_waiting; ?></strong></div>
            <div class="ops-row"><span>Cooking</span><strong><?php echo $t_cooking; ?></strong></div>
            <div class="ops-row"><span>Completed today</span><strong><?php echo $t_complete; ?></strong></div>
         </div>
         <div class="ops-actions">
            <a href="cook_tables.php" class="ops-link"><i class="fas fa-arrow-right"></i> Open table tickets</a>
         </div>
      </div>
      <div class="ops-panel">
         <div class="ops-panel-head"><i class="fas fa-box"></i> Parcel kitchen</div>
         <div class="ops-rows">
            <div class="ops-row"><span>Waiting</span><strong><?php echo $p_waiting; ?></strong></div>
            <div class="ops-row"><span>Cooking</span><strong><?php echo $p_cooking; ?></strong></div>
            <div class="ops-row"><span>Completed today</span><strong><?php echo $p_complete; ?></strong></div>
         </div>
         <div class="ops-actions">
            <a href="cook_parcel.php" class="ops-link"><i class="fas fa-arrow-right"></i> Open parcel tickets</a>
         </div>
      </div>
      <div class="ops-panel">
         <div class="ops-panel-head"><i class="fas fa-list-ol"></i> Next up</div>
         <div class="ops-rows">
            <?php $n=0; while($r=mysqli_fetch_assoc($next_table)): $n++; ?>
            <div class="ops-row"><span>T<?php echo (int)$r['table_number']; ?> · <?php echo htmlspecialchars($r['item_name']); ?> ×<?php echo (int)$r['quantity']; ?></span><strong>wait</strong></div>
            <?php endwhile; while($r=mysqli_fetch_assoc($next_parcel)): $n++; ?>
            <div class="ops-row"><span>P#<?php echo (int)$r['parcel_number']; ?> · <?php echo htmlspecialchars($r['item_name']); ?> ×<?php echo (int)$r['quantity']; ?></span><strong>wait</strong></div>
            <?php endwhile; if(!$n): ?>
            <div class="ops-row"><span>Queue is clear</span><strong>—</strong></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</section>

<?php include 'footer.php'; ?>
</body>
</html>
