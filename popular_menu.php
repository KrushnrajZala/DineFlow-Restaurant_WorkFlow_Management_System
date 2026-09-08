<?php
include 'config.php';
session_start();

// Detect which role is logged in
$role = '';
$header_file = '';
$page_title  = '';
if(isset($_SESSION['admin_id'])){
   $role = 'admin'; $header_file = 'admin_header.php';
} elseif(isset($_SESSION['waiter_id'])){
   $role = 'waiter'; $header_file = 'waiter_header.php';
} elseif(isset($_SESSION['cook_id'])){
   $role = 'cook'; $header_file = 'cook_header.php';
} else {
   header('location:index.php'); exit;
}

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';   // all / table / parcel
$period = isset($_GET['period']) ? $_GET['period'] : 'all';   // all / today / week / month

// Build date condition
$date_cond = '';
if($period == 'today')  $date_cond = "AND DATE(added_at) = CURDATE()";
if($period == 'week')   $date_cond = "AND added_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
if($period == 'month')  $date_cond = "AND added_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

// Build source query based on filter
// NOTE: We UNION both active orders AND complete_orders (billed/history)
// so popular menu is always accurate even after bills are printed
if($filter == 'table'){
   $sql = "SELECT item_name, menu_id, SUM(total_qty) as total_qty, SUM(order_count) as order_count,
           SUM(revenue) as revenue FROM (
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(price*quantity) as revenue
              FROM `table_orders` WHERE 1 $date_cond GROUP BY menu_id, item_name
              UNION ALL
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(subtotal) as revenue
              FROM `complete_orders` WHERE order_type='table' $date_cond GROUP BY menu_id, item_name
           ) combined GROUP BY menu_id, item_name ORDER BY total_qty DESC";
} elseif($filter == 'parcel'){
   $sql = "SELECT item_name, menu_id, SUM(total_qty) as total_qty, SUM(order_count) as order_count,
           SUM(revenue) as revenue FROM (
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(price*quantity) as revenue
              FROM `parcel_orders` WHERE 1 $date_cond GROUP BY menu_id, item_name
              UNION ALL
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(subtotal) as revenue
              FROM `complete_orders` WHERE order_type='parcel' $date_cond GROUP BY menu_id, item_name
           ) combined GROUP BY menu_id, item_name ORDER BY total_qty DESC";
} else {
   // Combined — all types, active + billed history
   $sql = "SELECT item_name, menu_id, SUM(total_qty) as total_qty, SUM(order_count) as order_count,
           SUM(revenue) as revenue FROM (
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(price*quantity) as revenue
              FROM `table_orders` WHERE 1 $date_cond GROUP BY menu_id, item_name
              UNION ALL
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(price*quantity) as revenue
              FROM `parcel_orders` WHERE 1 $date_cond GROUP BY menu_id, item_name
              UNION ALL
              SELECT item_name, menu_id, SUM(quantity) as total_qty, COUNT(*) as order_count, SUM(subtotal) as revenue
              FROM `complete_orders` WHERE 1 $date_cond GROUP BY menu_id, item_name
           ) combined GROUP BY menu_id, item_name ORDER BY total_qty DESC";
}

$result = mysqli_query($conn, $sql);
$items  = [];
while($row = mysqli_fetch_assoc($result)) $items[] = $row;

$total_items = count($items);
$total_orders = array_sum(array_column($items, 'total_qty'));
$total_revenue = array_sum(array_column($items, 'revenue'));

// Split: top half = popular, bottom half = less popular
$mid   = max(1, ceil($total_items / 2));
$popular    = array_slice($items, 0, $mid);
$less_popular = array_slice($items, $mid);

// Category breakdown — includes billed history
$cat_sql = "SELECT category, SUM(t.total_qty) as qty FROM (
   SELECT o.item_name, m.category, SUM(o.quantity) as total_qty
   FROM table_orders o LEFT JOIN menu m ON o.menu_id=m.id WHERE 1 $date_cond GROUP BY o.menu_id
   UNION ALL
   SELECT o.item_name, m.category, SUM(o.quantity) as total_qty
   FROM parcel_orders o LEFT JOIN menu m ON o.menu_id=m.id WHERE 1 $date_cond GROUP BY o.menu_id
   UNION ALL
   SELECT o.item_name, m.category, SUM(o.quantity) as total_qty
   FROM complete_orders o LEFT JOIN menu m ON o.menu_id=m.id WHERE 1 $date_cond GROUP BY o.menu_id
) t GROUP BY category ORDER BY qty DESC";
$cat_result = mysqli_query($conn, $cat_sql);
$cats = [];
while($c = mysqli_fetch_assoc($cat_result)) $cats[] = $c;
?>
<?php
// Chart data prep (derived from the arrays already computed above — no new queries)
$chart_top = array_slice($items, 0, 8);
$chartLabels  = array_map(fn($i) => $i['item_name'], $chart_top);
$chartQty     = array_map(fn($i) => (int)$i['total_qty'], $chart_top);
$chartRevenue = array_map(fn($i) => (float)$i['revenue'], $chart_top);
$catLabels = array_map(fn($c) => $c['category'] ?? 'Unknown', $cats);
$catQty    = array_map(fn($c) => (int)$c['qty'], $cats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Popular Menu - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
   <style>
      .filter-row { display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:2.4rem;align-items:center; }
      .filter-row .flabel { font-size:1.3rem;font-weight:700;color:var(--text-muted); }
      .filter-row a { padding:.6rem 1.5rem;border-radius:2rem;font-size:1.3rem;font-weight:600;background:var(--bg-2);color:var(--text-muted);border:1.5px solid var(--border); }
      .filter-row a.active { background:var(--primary);color:#fff;border-color:var(--primary); }
      .bar-wrap { background:var(--bg-2);border-radius:2rem;height:.8rem;flex:1;overflow:hidden; }
      .bar-fill  { height:100%;border-radius:2rem; }
      .pop-bar   { background:linear-gradient(135deg,var(--primary),var(--primary-dark)); }
      .less-bar  { background:linear-gradient(135deg,var(--text-light),var(--text-muted)); }
   </style>
</head>
<body>
<?php include $header_file; ?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-chart-bar"></i> Analytics</span>
      <h1><i class="fas fa-chart-bar"></i> Popular Menu Items</h1>
      <p class="page-date">Sales performance across your menu</p>
   </div>

   <!-- Summary -->
   <div class="bento-grid cols-3">
      <div class="bento-tile">
         <div class="b-icon"><i class="fas fa-utensils"></i></div>
         <div class="b-value"><?php echo $total_items; ?></div>
         <div class="b-label">Items Ordered</div>
      </div>
      <div class="bento-tile green">
         <div class="b-icon"><i class="fas fa-boxes"></i></div>
         <div class="b-value"><?php echo number_format($total_orders); ?></div>
         <div class="b-label">Total Qty Ordered</div>
      </div>
      <div class="bento-tile orange">
         <div class="b-icon"><i class="fas fa-rupee-sign"></i></div>
         <div class="b-value">₹<?php echo number_format($total_revenue, 2); ?></div>
         <div class="b-label">Total Revenue</div>
      </div>
   </div>

   <!-- Filters -->
   <div class="filter-row">
      <span class="flabel">Period:</span>
      <a href="?filter=<?php echo $filter; ?>&period=all"   class="<?php echo $period=='all'  ?'active':''; ?>">All Time</a>
      <a href="?filter=<?php echo $filter; ?>&period=today" class="<?php echo $period=='today'?'active':''; ?>">Today</a>
      <a href="?filter=<?php echo $filter; ?>&period=week"  class="<?php echo $period=='week' ?'active':''; ?>">Last 7 Days</a>
      <a href="?filter=<?php echo $filter; ?>&period=month" class="<?php echo $period=='month'?'active':''; ?>">Last 30 Days</a>
      &nbsp;|&nbsp;
      <span class="flabel">Source:</span>
      <a href="?filter=all&period=<?php echo $period; ?>"    class="<?php echo $filter=='all'   ?'active':''; ?>">All</a>
      <a href="?filter=table&period=<?php echo $period; ?>"  class="<?php echo $filter=='table' ?'active':''; ?>">Table Only</a>
      <a href="?filter=parcel&period=<?php echo $period; ?>" class="<?php echo $filter=='parcel'?'active':''; ?>">Parcel Only</a>
   </div>

   <?php if($total_items == 0): ?>
      <div class="empty"><i class="fas fa-chart-bar"></i> No order data found for the selected filters.</div>
   <?php else: ?>

   <!-- Charts -->
   <div class="chart-grid">
      <div class="chart-card">
         <div class="chart-card-head"><h3><i class="fas fa-fire" style="color:var(--primary);"></i> Top Sellers — Qty vs Revenue</h3></div>
         <canvas id="topItemsChart"></canvas>
      </div>
      <div class="chart-card">
         <div class="chart-card-head"><h3><i class="fas fa-tags"></i> Category Share</h3></div>
         <canvas id="categoryChart"></canvas>
      </div>
   </div>

   <!-- Most Popular -->
   <div class="zone-head">
      <div class="zone-icon"><i class="fas fa-fire"></i></div>
      <h2>Most Popular Items</h2>
      <div class="zone-rule"></div>
   </div>
   <div class="table-container">
      <table class="table">
         <thead>
            <tr><th>Rank</th><th>Item Name</th><th>Total Ordered (Qty)</th><th>Times Ordered</th><th>Revenue</th><th>Popularity</th></tr>
         </thead>
         <tbody>
         <?php
         $max_qty = $items[0]['total_qty'] ?? 1;
         foreach($popular as $i => $item){
            $rank = $i + 1;
            $rank_class = $rank == 1 ? 'top1' : ($rank == 2 ? 'top2' : ($rank == 3 ? 'top3' : ''));
            $pct = $max_qty > 0 ? ($item['total_qty'] / $max_qty * 100) : 0;
         ?>
         <tr>
            <td><span class="rank-pill <?php echo $rank_class; ?>"><?php echo $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : ($rank == 3 ? '🥉' : $rank)); ?></span></td>
            <td><strong><?php echo htmlspecialchars($item['item_name']); ?></strong></td>
            <td><strong style="font-size:1.7rem;color:var(--primary);"><?php echo number_format($item['total_qty']); ?></strong></td>
            <td><?php echo number_format($item['order_count']); ?> orders</td>
            <td>₹<?php echo number_format($item['revenue'], 2); ?></td>
            <td style="min-width:12rem;">
               <div style="display:flex;align-items:center;gap:1rem;">
                  <div class="bar-wrap"><div class="bar-fill pop-bar" style="width:<?php echo $pct; ?>%"></div></div>
                  <span style="font-size:1.2rem;color:var(--text-muted);min-width:3rem;"><?php echo round($pct); ?>%</span>
               </div>
            </td>
         </tr>
         <?php } ?>
         </tbody>
      </table>
   </div>

   <!-- Less Popular -->
   <?php if(!empty($less_popular)): ?>
   <div class="zone-head">
      <div class="zone-icon"><i class="fas fa-arrow-down"></i></div>
      <h2>Less Popular Items</h2>
      <div class="zone-rule"></div>
   </div>
   <div class="table-container">
      <table class="table">
         <thead>
            <tr><th>Rank</th><th>Item Name</th><th>Total Ordered (Qty)</th><th>Times Ordered</th><th>Revenue</th><th>Popularity</th></tr>
         </thead>
         <tbody>
         <?php
         foreach($less_popular as $i => $item){
            $rank = $mid + $i + 1;
            $pct  = $max_qty > 0 ? ($item['total_qty'] / $max_qty * 100) : 0;
         ?>
         <tr style="opacity:.8;">
            <td><span class="rank-pill"><?php echo $rank; ?></span></td>
            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
            <td><strong style="font-size:1.7rem;color:var(--text-light);"><?php echo number_format($item['total_qty']); ?></strong></td>
            <td><?php echo number_format($item['order_count']); ?> orders</td>
            <td>₹<?php echo number_format($item['revenue'], 2); ?></td>
            <td style="min-width:12rem;">
               <div style="display:flex;align-items:center;gap:1rem;">
                  <div class="bar-wrap"><div class="bar-fill less-bar" style="width:<?php echo $pct; ?>%"></div></div>
                  <span style="font-size:1.2rem;color:var(--text-muted);min-width:3rem;"><?php echo round($pct); ?>%</span>
               </div>
            </td>
         </tr>
         <?php } ?>
         </tbody>
      </table>
   </div>
   <?php endif; ?>

   <!-- Category Breakdown tiles -->
   <?php if(!empty($cats)): ?>
   <div class="zone-head">
      <div class="zone-icon"><i class="fas fa-tags"></i></div>
      <h2>Category Breakdown</h2>
      <div class="zone-rule"></div>
   </div>
   <div class="bento-grid">
   <?php
   $max_cat = $cats[0]['qty'] ?? 1;
   $tileColors = ['orange','green','blue','red',''];
   foreach($cats as $ci => $cat){
      $cpct = $max_cat > 0 ? round($cat['qty'] / $max_cat * 100) : 0;
      $tc = $tileColors[$ci % count($tileColors)];
   ?>
   <div class="bento-tile <?php echo $tc; ?>">
      <div class="b-icon"><i class="fas fa-tag"></i></div>
      <div class="b-value"><?php echo number_format($cat['qty']); ?></div>
      <div class="b-label"><?php echo htmlspecialchars($cat['category'] ?? 'Unknown'); ?></div>
      <div class="bar-wrap" style="margin-top:1rem;"><div class="bar-fill pop-bar" style="width:<?php echo $cpct; ?>%;"></div></div>
   </div>
   <?php } ?>
   </div>
   <?php endif; ?>

   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<?php if($total_items > 0): ?>
<script>
new Chart(document.getElementById('topItemsChart'), {
   type: 'bar',
   data: {
      labels: <?php echo json_encode($chartLabels); ?>,
      datasets: [
         { label: 'Qty Sold', data: <?php echo json_encode($chartQty); ?>, backgroundColor: '#f2711c', borderRadius: 6, yAxisID: 'y' },
         { label: 'Revenue (₹)', data: <?php echo json_encode($chartRevenue); ?>, backgroundColor: '#5a9216', borderRadius: 6, yAxisID: 'y1' }
      ]
   },
   options: {
      responsive: true,
      plugins: { legend: { position: 'bottom' } },
      scales: {
         y:  { type:'linear', position:'left',  beginAtZero:true, ticks:{ precision:0 } },
         y1: { type:'linear', position:'right', beginAtZero:true, grid:{ drawOnChartArea:false } }
      }
   }
});
new Chart(document.getElementById('categoryChart'), {
   type: 'doughnut',
   data: {
      labels: <?php echo json_encode($catLabels); ?>,
      datasets: [{ data: <?php echo json_encode($catQty); ?>, backgroundColor: ['#f2711c','#5a9216','#3b82f6','#ef4444','#ffb27a','#8b5cf6','#06b6d4','#c2540f'], borderWidth: 0 }]
   },
   options: { responsive: true, cutout: '62%', plugins: { legend: { position: 'bottom' } } }
});
</script>
<?php endif; ?>
</body>
</html>
