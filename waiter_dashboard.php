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

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Morning' : ($hour < 17 ? 'Afternoon' : 'Evening');
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
      /* ===== Concept F — Soft cream dashboard (matches login) ===== */
      body { background: #F3F0EB !important; }

      .wd-shell {
         max-width: 1280px;
         margin: 0 auto;
         padding: 2rem 2.4rem 3.5rem;
      }

      /* Soft welcome bar */
      .wd-welcome {
         background: #FFFCFA;
         border: 1px solid rgba(224,214,201,.55);
         border-radius: 16px;
         box-shadow: 0 4px 6px rgba(40,32,26,.03), 0 12px 28px rgba(40,32,26,.06);
         padding: 1.8rem 2.2rem;
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1.6rem;
         flex-wrap: wrap;
         margin-bottom: 1.6rem;
      }
      .wd-welcome-text h1 {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 2.4rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0 0 .25rem;
         letter-spacing: -.02em;
      }
      .wd-welcome-text p {
         font-size: 1.3rem;
         color: #7A6F66;
         margin: 0;
      }
      .wd-welcome-meta {
         display: flex;
         align-items: center;
         gap: 1.4rem;
         flex-wrap: wrap;
      }
      .wd-date {
         display: flex;
         align-items: center;
         gap: .5rem;
         font-size: 1.25rem;
         color: #7A6F66;
         font-weight: 600;
      }
      .wd-date i { color: #A86B62; }
      .wd-actions {
         display: flex;
         gap: .7rem;
         flex-wrap: wrap;
      }
      .wd-btn {
         display: inline-flex;
         align-items: center;
         gap: .45rem;
         padding: .75rem 1.5rem;
         border-radius: 10px;
         font-size: 1.3rem;
         font-weight: 700;
         cursor: pointer;
         border: none;
         text-decoration: none;
         transition: .15s ease;
      }
      .wd-btn-primary {
         background: #A86B62;
         color: #fff;
         box-shadow: 0 4px 12px rgba(168,107,98,.25);
      }
      .wd-btn-primary:hover {
         background: #945A52;
         transform: translateY(-1px);
         box-shadow: 0 6px 16px rgba(168,107,98,.3);
      }
      .wd-btn-outline {
         background: #FFFCFA;
         color: #A86B62;
         border: 1.5px solid #E5DDD3;
      }
      .wd-btn-outline:hover {
         background: #F8F0EC;
         border-color: #A86B62;
      }

      /* Soft stats row */
      .wd-stats {
         display: grid;
         grid-template-columns: repeat(4, 1fr);
         gap: 1.2rem;
         margin-bottom: 2rem;
      }
      .wd-stat {
         background: #FFFCFA;
         border: 1px solid rgba(224,214,201,.55);
         border-radius: 14px;
         padding: 1.5rem 1.6rem;
         box-shadow: 0 2px 8px rgba(40,32,26,.04);
         display: flex;
         align-items: center;
         gap: 1.2rem;
         transition: .15s ease;
      }
      .wd-stat:hover {
         box-shadow: 0 6px 18px rgba(40,32,26,.08);
         transform: translateY(-2px);
      }
      .wd-stat-icon {
         width: 4.2rem;
         height: 4.2rem;
         border-radius: 12px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.7rem;
         flex-shrink: 0;
      }
      .wd-stat-icon.tables { background: #F3EBE7; color: #A86B62; }
      .wd-stat-icon.parcels { background: #E8F0E9; color: #4A6B4E; }
      .wd-stat-icon.pending { background: #F6E8E5; color: #8B342A; }
      .wd-stat-icon.pay { background: #F3EDE2; color: #9A7B4F; }
      .wd-stat-val {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 2.3rem;
         font-weight: 700;
         color: #2A2420;
         line-height: 1.1;
      }
      .wd-stat-lbl {
         font-size: 1.2rem;
         color: #7A6F66;
         font-weight: 600;
         margin-top: .15rem;
      }

      /* Section head */
      .wd-section-head {
         display: flex;
         align-items: center;
         justify-content: space-between;
         margin-bottom: 1.3rem;
         gap: 1rem;
         flex-wrap: wrap;
      }
      .wd-section-head h2 {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 1.9rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0;
      }
      .wd-section-head a {
         font-size: 1.25rem;
         font-weight: 700;
         color: #A86B62;
         text-decoration: none;
      }
      .wd-section-head a:hover { text-decoration: underline; }

      /* Order cards grid */
      .wd-orders {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(28rem, 1fr));
         gap: 1.3rem;
      }
      .wd-order {
         background: #FFFCFA;
         border: 1px solid rgba(224,214,201,.55);
         border-radius: 14px;
         padding: 1.5rem 1.6rem;
         box-shadow: 0 2px 8px rgba(40,32,26,.04);
         transition: .15s ease;
         display: flex;
         flex-direction: column;
         gap: .7rem;
      }
      .wd-order:hover {
         box-shadow: 0 8px 22px rgba(40,32,26,.09);
         transform: translateY(-2px);
      }
      .wd-order-top {
         display: flex;
         justify-content: space-between;
         align-items: center;
         gap: .8rem;
      }
      .wd-order-table {
         font-size: 1.25rem;
         font-weight: 700;
         color: #A86B62;
         display: flex;
         align-items: center;
         gap: .4rem;
      }
      .wd-order-time {
         font-size: 1.2rem;
         color: #94887C;
         font-weight: 600;
      }
      .wd-order-item {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 1.7rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0;
         line-height: 1.25;
      }
      .wd-order-meta {
         display: flex;
         flex-wrap: wrap;
         gap: 1rem;
         font-size: 1.25rem;
         color: #7A6F66;
      }
      .wd-order-meta span {
         display: inline-flex;
         align-items: center;
         gap: .35rem;
      }
      .wd-order-foot {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding-top: .7rem;
         border-top: 1px dashed #E5DDD3;
         margin-top: .2rem;
      }
      .wd-badge {
         display: inline-flex;
         align-items: center;
         gap: .3rem;
         padding: .3rem .85rem;
         border-radius: 999px;
         font-size: 1.15rem;
         font-weight: 700;
      }
      .wd-badge.ok { background: #E8F0E9; color: #4A6B4E; }
      .wd-badge.warn { background: #F3EDE2; color: #9A7B4F; }
      .wd-badge.danger { background: #F6E8E5; color: #8B342A; }
      .wd-badge.info { background: #F3EBE7; color: #A86B62; }
      .wd-pay {
         font-size: 1.2rem;
         color: #94887C;
         font-weight: 600;
      }

      /* Empty state */
      .wd-empty {
         text-align: center;
         padding: 4rem 2rem;
         background: #FFFCFA;
         border: 1px dashed #E0D6C9;
         border-radius: 16px;
      }
      .wd-empty i {
         font-size: 3.6rem;
         color: #C9BDAE;
         margin-bottom: 1rem;
      }
      .wd-empty p {
         font-size: 1.5rem;
         color: #7A6F66;
         margin-bottom: 1.5rem;
      }

      @media print {
         body * { visibility: hidden; }
         .report-print-area, .report-print-area * { visibility: visible; }
         .report-print-area { position: absolute; left: 0; top: 0; width: 100%; padding: 2rem; box-shadow: none; border: none; }
         .no-print { display: none !important; }
      }

      @media (max-width: 900px) {
         .wd-shell { padding: 1.6rem 1.4rem 2.5rem; }
         .wd-stats { grid-template-columns: repeat(2, 1fr); }
         .wd-welcome { padding: 1.4rem 1.5rem; }
         .wd-welcome-text h1 { font-size: 2rem; }
      }
      @media (max-width: 560px) {
         .wd-stats { grid-template-columns: 1fr; }
         .wd-orders { grid-template-columns: 1fr; }
         .wd-welcome-meta { width: 100%; }
         .wd-actions { width: 100%; }
         .wd-btn { flex: 1; justify-content: center; }
      }
   </style>
</head>
<body>
<?php include 'waiter_header.php'; ?>

<section class="wd-shell">

   <!-- Soft welcome bar -->
   <div class="wd-welcome no-print">
      <div class="wd-welcome-text">
         <h1>Good <?php echo $greeting; ?>, <?php echo htmlspecialchars($wname); ?></h1>
         <p>Hope you're having a great service!</p>
      </div>
      <div class="wd-welcome-meta">
         <div class="wd-date">
            <i class="fas fa-calendar-alt"></i>
            <?php echo date('l, d M Y'); ?>
            <span style="opacity:.5;">·</span>
            <i class="fas fa-clock"></i>
            <?php echo date('h:i A'); ?>
         </div>
         <div class="wd-actions">
            <a href="waiter_tables.php" class="wd-btn wd-btn-primary"><i class="fas fa-chair"></i> Open Tables</a>
            <a href="waiter_parcel.php" class="wd-btn wd-btn-outline"><i class="fas fa-box"></i> Parcels</a>
            <button type="button" onclick="printReport()" class="wd-btn wd-btn-outline"><i class="fas fa-print"></i> Print</button>
         </div>
      </div>
   </div>

   <!-- Soft stats -->
   <div class="wd-stats">
      <div class="wd-stat">
         <div class="wd-stat-icon tables"><i class="fas fa-receipt"></i></div>
         <div>
            <div class="wd-stat-val"><?php echo $total_orders; ?></div>
            <div class="wd-stat-lbl">Table Orders</div>
         </div>
      </div>
      <div class="wd-stat">
         <div class="wd-stat-icon parcels"><i class="fas fa-box"></i></div>
         <div>
            <div class="wd-stat-val"><?php echo $total_parcels; ?></div>
            <div class="wd-stat-lbl">Parcel Orders</div>
         </div>
      </div>
      <div class="wd-stat">
         <div class="wd-stat-icon pending"><i class="fas fa-exclamation-circle"></i></div>
         <div>
            <div class="wd-stat-val"><?php echo $total_delivered; ?></div>
            <div class="wd-stat-lbl">Not Delivered</div>
         </div>
      </div>
      <div class="wd-stat">
         <div class="wd-stat-icon pay"><i class="fas fa-rupee-sign"></i></div>
         <div>
            <div class="wd-stat-val">₹<?php echo number_format((float)$total_earning, 0); ?></div>
            <div class="wd-stat-lbl">Pending Pay</div>
         </div>
      </div>
   </div>

   <!-- Orders section -->
   <div class="wd-section-head">
      <h2>Today's Orders</h2>
      <a href="waiter_tables.php" class="no-print">View All <i class="fas fa-arrow-right" style="font-size:.9em;"></i></a>
   </div>

   <div class="report-print-area" id="report-area">
   <?php if(mysqli_num_rows($recent) > 0): ?>
      <div class="wd-orders">
      <?php while($r = mysqli_fetch_assoc($recent)):
         $status = strtolower($r['status'] ?? '');
         $badgeClass = 'info';
         if($status === 'complete' || $status === 'served') $badgeClass = 'ok';
         elseif($status === 'cooking' || $status === 'preparing') $badgeClass = 'warn';
         elseif($status === 'cancelled') $badgeClass = 'danger';
      ?>
         <div class="wd-order">
            <div class="wd-order-top">
               <span class="wd-order-table"><i class="fas fa-chair"></i> Table <?php echo htmlspecialchars($r['table_number']); ?></span>
               <span class="wd-order-time"><?php echo date('h:i A', strtotime($r['added_at'])); ?></span>
            </div>
            <h3 class="wd-order-item"><?php echo htmlspecialchars($r['item_name']); ?></h3>
            <div class="wd-order-meta">
               <span><i class="fas fa-hashtag"></i> ×<?php echo (int)$r['quantity']; ?></span>
               <span><i class="fas fa-rupee-sign"></i> <?php echo number_format($r['price'] * $r['quantity'], 2); ?></span>
            </div>
            <div class="wd-order-foot">
               <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;">
                  <span class="wd-badge <?php echo $badgeClass; ?>"><?php echo ucfirst($r['status']); ?></span>
                  <?php if($r['is_delivered']): ?>
                     <span class="wd-badge ok"><i class="fas fa-check"></i> Delivered</span>
                  <?php else: ?>
                     <span class="wd-badge danger"><i class="fas fa-times"></i> Not Delivered</span>
                  <?php endif; ?>
               </div>
               <span class="wd-pay"><?php echo ucfirst($r['payment_method'] ?? '—'); ?></span>
            </div>
         </div>
      <?php endwhile; ?>
      </div>
   <?php else: ?>
      <div class="wd-empty">
         <i class="fas fa-inbox"></i>
         <p>No orders today yet.</p>
         <a href="waiter_tables.php" class="wd-btn wd-btn-primary no-print"><i class="fas fa-plus"></i> Take First Order</a>
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
   win.document.write('<style>body{font-family:Arial,sans-serif;padding:20px;background:#fff;color:#222;} .wd-orders{display:grid;grid-template-columns:1fr 1fr;gap:12px;} .wd-order{border:1px solid #ddd;border-radius:10px;padding:14px;margin-bottom:8px;} .wd-order-item{font-size:16px;font-weight:bold;margin:6px 0;} .wd-order-top{display:flex;justify-content:space-between;font-size:13px;color:#555;} .wd-badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;background:#eee;}</style>');
   win.document.write('</head><body>');
   win.document.write('<h2>Today\'s Orders — <?php echo htmlspecialchars($wname); ?> · <?php echo date("d M Y"); ?></h2>');
   win.document.write(area.innerHTML);
   win.document.write('</body></html>');
   win.document.close();
   win.print();
}
</script>
</body>
</html>
