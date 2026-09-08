<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }
$wid   = $_SESSION['waiter_id'];
$wname = $_SESSION['waiter_name'];
$today = date('Y-m-d');

$total_orders    = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE waiter_id='$wid' AND DATE(added_at)='$today'"));
$total_parcels   = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE waiter_id='$wid' AND DATE(added_at)='$today'"));
$total_delivered = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `table_orders` WHERE waiter_id='$wid' AND DATE(added_at)='$today' AND is_delivered=0"));
$earn_q          = mysqli_query($conn,"SELECT SUM(price*quantity) as total FROM `table_orders` WHERE waiter_id='$wid' AND DATE(added_at)='$today' AND payment_done=0");
$total_earning   = mysqli_fetch_assoc($earn_q)['total'] ?? 0;

$recent = mysqli_query($conn,"SELECT t.*, tb.table_number FROM `table_orders` t JOIN `tables` tb ON t.table_id=tb.id WHERE t.waiter_id='$wid' AND DATE(t.added_at)='$today' ORDER BY t.added_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Waiter Dashboard - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      section { padding: 3rem; width: 100%; box-sizing: border-box; }
      @media print {
         body * { visibility: hidden; }
         .report-print-area, .report-print-area * { visibility: visible; }
         .report-print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 2rem; box-shadow: none; border: none; }
         .no-print { display: none !important; }
      }
      @media(max-width: 768px) { section { padding: 2rem 1.5rem; } }
   </style>
</head>
<body>
<?php include 'waiter_header.php'; ?>

<section class="dash-shell">
   <!-- Hero banner -->
   <div class="dash-hero">
      <div class="dash-hero-text">
         <span class="page-kicker"><i class="fas fa-user"></i> Waiter Overview</span>
         <h1>Good <?php echo (int)date('H') < 12 ? 'Morning' : ((int)date('H') < 17 ? 'Afternoon' : 'Evening'); ?>, <?php echo htmlspecialchars($wname); ?></h1>
         <p class="page-date"><?php echo date('l, d M Y'); ?> · Manage tables, parcels &amp; deliveries</p>
      </div>
      <div class="dash-hero-actions no-print">
         <a href="waiter_tables.php" class="btn"><i class="fas fa-chair"></i> Open Tables</a>
         <a href="waiter_parcel.php" class="white-btn"><i class="fas fa-box"></i> Parcels</a>
         <button onclick="printReport()" class="option-btn"><i class="fas fa-print"></i> Print</button>
      </div>
   </div>

   <!-- Stat strip -->
   <div class="stat-strip">
      <div class="stat-chip">
         <div class="chip-icon"><i class="fas fa-receipt"></i></div>
         <div>
            <div class="chip-val"><?php echo $total_orders; ?></div>
            <div class="chip-lbl">Table Orders</div>
         </div>
      </div>
      <div class="stat-chip blue">
         <div class="chip-icon"><i class="fas fa-box"></i></div>
         <div>
            <div class="chip-val"><?php echo $total_parcels; ?></div>
            <div class="chip-lbl">Parcel Orders</div>
         </div>
      </div>
      <div class="stat-chip red">
         <div class="chip-icon"><i class="fas fa-times"></i></div>
         <div>
            <div class="chip-val"><?php echo $total_delivered; ?></div>
            <div class="chip-lbl">Not Delivered</div>
         </div>
      </div>
      <div class="stat-chip gold">
         <div class="chip-icon"><i class="fas fa-rupee-sign"></i></div>
         <div>
            <div class="chip-val">₹<?php echo number_format($total_earning,2); ?></div>
            <div class="chip-lbl">Pending Pay</div>
         </div>
      </div>
   </div>

   <div class="zone-head">
      <div class="zone-icon"><i class="fas fa-list-alt"></i></div>
      <h2>Today's Orders</h2>
      <div class="zone-rule"></div>
   </div>

   <div class="report-print-area" id="report-area">
   <?php if(mysqli_num_rows($recent) > 0): ?>
      <div class="order-grid">
      <?php while($r=mysqli_fetch_assoc($recent)):
         $tickClass = $r['status']=='complete' ? 'green' : ($r['status']=='cooking' ? '' : 'muted');
      ?>
      <div class="order-card <?php echo $tickClass; ?>">
         <div class="order-card-top">
            <span class="order-table"><i class="fas fa-chair"></i> Table <?php echo $r['table_number']; ?></span>
            <span class="item-status status-<?php echo $r['status']; ?>"><?php echo ucfirst($r['status']); ?></span>
         </div>
         <h3 class="order-item"><?php echo htmlspecialchars($r['item_name']); ?></h3>
         <div class="order-meta">
            <span><i class="fas fa-hashtag"></i> <?php echo $r['quantity']; ?></span>
            <span><i class="fas fa-rupee-sign"></i> <?php echo number_format($r['price']*$r['quantity'],2); ?></span>
            <span><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($r['added_at'])); ?></span>
         </div>
         <div class="order-foot">
            <?php if($r['is_delivered']): ?>
               <span class="badge success"><i class="fas fa-check"></i> Delivered</span>
            <?php else: ?>
               <span class="badge danger"><i class="fas fa-times"></i> Not Delivered</span>
            <?php endif; ?>
            <span class="pay-method"><?php echo ucfirst($r['payment_method'] ?? '—'); ?></span>
         </div>
      </div>
      <?php endwhile; ?>
      </div>
   <?php else: ?>
      <div class="empty">
         <i class="fas fa-inbox"></i>
         <p>No orders today yet.</p>
         <a href="waiter_tables.php" class="btn" style="margin-top:1.5rem;"><i class="fas fa-plus"></i> Take First Order</a>
      </div>
   <?php endif; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
function printReport(){
   const area = document.getElementById('report-area');
   const win  = window.open('','','width=1000,height=700');
   win.document.write('<html><head><title>Waiter Report</title>');
   win.document.write('<style>body{font-family:Arial;padding:20px;}.ticket-card{border:1px solid #ccc;border-radius:8px;padding:14px;margin-bottom:10px;}.ticket-head{display:flex;justify-content:space-between;font-weight:bold;}.ticket-meta{font-size:12px;color:#555;margin-top:6px;}</style>');
   win.document.write('</head><body>');
   win.document.write(area.innerHTML);
   win.document.write('</body></html>');
   win.document.close();
   win.print();
}
</script>
</body>
</html>
