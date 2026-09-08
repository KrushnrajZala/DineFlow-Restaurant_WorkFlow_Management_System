<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }
$today = date('Y-m-d');

$earn_today = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_amount) as t FROM `bills` WHERE DATE(billed_at)='$today'"))['t'] ?? 0;
$earn_total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_amount) as t FROM `bills`"))['t'] ?? 0;
$pending_pay= mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(price*quantity) as t FROM `table_orders` WHERE payment_done=0"))['t'] ?? 0;
$pending_pay+= mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(price*quantity) as t FROM `parcel_orders` WHERE payment_done=0"))['t'] ?? 0;

$w_online  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `waiters` WHERE is_online=1"));
$w_offline = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `waiters` WHERE is_online=0"));
$w_total   = $w_online + $w_offline;

$t_waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$t_cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$t1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$t2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today'AND order_type = 'table'"));
$t_complete = $t1_complete + $t2_complete ;

$p_waiting  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='waiting' AND DATE(added_at)='$today'"));
$p_cooking  = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='cooking' AND DATE(added_at)='$today'"));
$p1_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE status='complete' AND DATE(added_at)='$today'"));
$p2_complete = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE DATE(billed_date)='$today' AND order_type = 'parcel'"));
$p_complete=$p1_complete+$p2_complete;

// ── Complete Orders (permanent history) ──
$co_today_table  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c, SUM(subtotal) as rev FROM `complete_orders` WHERE order_type='table'  AND DATE(billed_at)='$today'"));
$co_today_parcel = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c, SUM(subtotal) as rev FROM `complete_orders` WHERE order_type='parcel' AND DATE(billed_at)='$today'"));
$co_total_table  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c, SUM(subtotal) as rev FROM `complete_orders` WHERE order_type='table'"));
$co_total_parcel = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c, SUM(subtotal) as rev FROM `complete_orders` WHERE order_type='parcel'"));
$co_today_items  = ($co_today_table['c'] ?? 0) + ($co_today_parcel['c'] ?? 0);
$co_today_earn   = ($co_today_table['rev'] ?? 0) + ($co_today_parcel['rev'] ?? 0);
$co_all_items    = ($co_total_table['c'] ?? 0) + ($co_total_parcel['c'] ?? 0);
$co_all_earn     = ($co_total_table['rev'] ?? 0) + ($co_total_parcel['rev'] ?? 0);

$waiting_customers = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `waiting_list`"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Dashboard - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>

<section class="dash-shell">
   <div class="dash-hero">
      <div class="dash-hero-text">
         <span class="page-kicker"><i class="fas fa-shield-alt"></i> Admin</span>
         <h1>Operations Board</h1>
         <p class="page-date"><?php echo date('l, d M Y'); ?> · Live snapshot of money, kitchen & floor</p>
      </div>
      <div class="dash-hero-actions">
         <a href="admin_menu.php" class="btn"><i class="fas fa-utensils"></i> Menu</a>
         <a href="admin_table_orders.php" class="white-btn"><i class="fas fa-chair"></i> Table Orders</a>
         <a href="admin_parcel_orders.php" class="white-btn"><i class="fas fa-box"></i> Parcels</a>
         <button type="button" onclick="printEarningReport()" class="option-btn" style="background:rgba(255,255,255,0.12);color:#fff;border-color:rgba(255,255,255,0.25);"><i class="fas fa-print"></i> Print earnings</button>
      </div>
   </div>

   <!-- Money row -->
   <div class="stat-strip">
      <div class="stat-chip gold">
         <div class="chip-icon"><i class="fas fa-calendar-day"></i></div>
         <div>
            <div class="chip-val">₹<?php echo number_format($earn_today,2); ?></div>
            <div class="chip-lbl">Today's bills</div>
         </div>
      </div>
      <div class="stat-chip">
         <div class="chip-icon"><i class="fas fa-wallet"></i></div>
         <div>
            <div class="chip-val">₹<?php echo number_format($earn_total,2); ?></div>
            <div class="chip-lbl">All-time bills</div>
         </div>
      </div>
      <div class="stat-chip red">
         <div class="chip-icon"><i class="fas fa-exclamation"></i></div>
         <div>
            <div class="chip-val">₹<?php echo number_format($pending_pay,2); ?></div>
            <div class="chip-lbl">Unpaid orders</div>
         </div>
      </div>
      <div class="stat-chip blue">
         <div class="chip-icon"><i class="fas fa-check-double"></i></div>
         <div>
            <div class="chip-val">₹<?php echo number_format($co_today_earn ?? 0,2); ?></div>
            <div class="chip-lbl">Today completed rev.</div>
         </div>
      </div>
   </div>

   <div class="admin-ops-grid">
      <!-- Kitchen today -->
      <div class="ops-panel">
         <div class="ops-panel-head"><i class="fas fa-fire"></i> Kitchen today</div>
         <div class="ops-rows">
            <div class="ops-row"><span>Table · Waiting</span><strong><?php echo $t_waiting; ?></strong></div>
            <div class="ops-row"><span>Table · Cooking</span><strong><?php echo $t_cooking; ?></strong></div>
            <div class="ops-row"><span>Table · Complete</span><strong><?php echo $t_complete; ?></strong></div>
            <div class="ops-row"><span>Parcel · Waiting</span><strong><?php echo $p_waiting; ?></strong></div>
            <div class="ops-row"><span>Parcel · Cooking</span><strong><?php echo $p_cooking; ?></strong></div>
            <div class="ops-row"><span>Parcel · Complete</span><strong><?php echo $p_complete; ?></strong></div>
         </div>
      </div>

      <!-- Floor -->
      <div class="ops-panel">
         <div class="ops-panel-head"><i class="fas fa-store"></i> Floor</div>
         <div class="ops-rows">
            <div class="ops-row"><span>Waiters online</span><strong><?php echo $w_online; ?> / <?php echo $w_total; ?></strong></div>
            <div class="ops-row"><span>Tables free</span><strong><?php echo mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `tables` WHERE is_available=1")); ?></strong></div>
            <div class="ops-row"><span>Waiting list</span><strong><?php echo $waiting_customers; ?></strong></div>
            <div class="ops-row"><span>Billed items today</span><strong><?php echo number_format($co_today_items ?? 0); ?></strong></div>
            <div class="ops-row"><span>All-time items</span><strong><?php echo number_format($co_all_items ?? 0); ?></strong></div>
            <div class="ops-row"><span>All-time revenue</span><strong>₹<?php echo number_format($co_all_earn ?? 0,2); ?></strong></div>
         </div>
      </div>

      <!-- Quick links -->
      <div class="ops-panel">
         <div class="ops-panel-head"><i class="fas fa-bolt"></i> Quick actions</div>
         <div class="ops-actions">
            <a href="admin_menu.php" class="ops-link"><i class="fas fa-book-open"></i> Manage menu</a>
            <a href="admin_waiters.php" class="ops-link"><i class="fas fa-user-tie"></i> Waiters</a>
            <a href="admin_tables.php" class="ops-link"><i class="fas fa-chair"></i> Tables setup</a>
            <a href="admin_inventory.php" class="ops-link"><i class="fas fa-boxes"></i> Inventory</a>
            <a href="admin_reviews.php" class="ops-link"><i class="fas fa-star"></i> Reviews</a>
            <a href="admin_reports.php" class="ops-link"><i class="fas fa-chart-line"></i> Reports</a>
            <a href="popular_menu.php" class="ops-link"><i class="fas fa-trophy"></i> Popular dishes</a>
            <a href="admin_waiting_list.php" class="ops-link"><i class="fas fa-clock"></i> Waiting list</a>
         </div>
      </div>
   </div>
</section>

<div id="earning-report" style="display:none;">
   <h2>DineFlow — Earning Report — <?php echo date('d M Y'); ?></h2>
   <p>Today's Earning: ₹<?php echo number_format($earn_today,2); ?></p>
   <p>Total Earning: ₹<?php echo number_format($earn_total,2); ?></p>
   <p>Pending Payments: ₹<?php echo number_format($pending_pay,2); ?></p>
</div>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
function printEarningReport(){
   var a = document.getElementById('earning-report');
   var w = window.open('','','width=700,height=500');
   w.document.write('<html><head><title>Earning Report</title></head><body style="font-family:Georgia,serif;padding:28px;">');
   w.document.write(a.innerHTML);
   w.document.write('</body></html>');
   w.document.close();
   w.print();
}
</script>
</body>
</html>
