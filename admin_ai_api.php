<?php
// ============================================================
//  Spice Garden — Admin AI API
//  Fetches real DB data + calls Groq browser-side via this PHP
//  Called by admin_ai_widget.php
// ============================================================
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ echo json_encode(['error'=>'Unauthorized']); exit; }

header('Content-Type: application/json');

$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));

// ── Fetch all real data from DB ───────────────────────────────

// Today earnings
$earn_today = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_amount) as t FROM `bills` WHERE DATE(billed_at)='$today'"))['t'] ?? 0;

// Total earnings
$earn_total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_amount) as t FROM `bills`"))['t'] ?? 0;

// This week earnings
$earn_week = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_amount) as t FROM `bills` WHERE DATE(billed_at)>='$week_start'"))['t'] ?? 0;

// Today orders count
$table_orders_today  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `table_orders` WHERE DATE(added_at)='$today'"))['t'] ?? 0;
$parcel_orders_today = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `parcel_orders` WHERE DATE(added_at)='$today'"))['t'] ?? 0;
$total_orders_today  = $table_orders_today + $parcel_orders_today;

// Pending orders
$pending_table  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `table_orders` WHERE status!='complete' AND DATE(added_at)='$today'"))['t'] ?? 0;
$pending_parcel = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `parcel_orders` WHERE status!='complete' AND DATE(added_at)='$today'"))['t'] ?? 0;

// Waiters
$w_online  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `waiters` WHERE is_online=1"))['t'] ?? 0;
$w_offline = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `waiters` WHERE is_online=0"))['t'] ?? 0;

// Most popular items (all time — includes billed history from complete_orders)
$popular_res = mysqli_query($conn,"SELECT item_name as name, SUM(qty) as total FROM (
   SELECT item_name, SUM(quantity) as qty FROM `table_orders` GROUP BY item_name
   UNION ALL SELECT item_name, SUM(quantity) as qty FROM `parcel_orders` GROUP BY item_name
   UNION ALL SELECT item_name, SUM(quantity) as qty FROM `complete_orders` GROUP BY item_name
) x GROUP BY item_name ORDER BY total DESC LIMIT 5");
$popular_items = [];
while($r = mysqli_fetch_assoc($popular_res)) $popular_items[] = $r['name'].' ('.$r['total'].' orders)';

// Most popular parcel items
$popular_parcel_res = mysqli_query($conn,"SELECT item_name as name, SUM(qty) as total FROM (
   SELECT item_name, SUM(quantity) as qty FROM `parcel_orders` GROUP BY item_name
   UNION ALL SELECT item_name, SUM(quantity) as qty FROM `complete_orders` WHERE order_type='parcel' GROUP BY item_name
) x GROUP BY item_name ORDER BY total DESC LIMIT 3");
$popular_parcel = [];
while($r = mysqli_fetch_assoc($popular_parcel_res)) $popular_parcel[] = $r['name'].' ('.$r['total'].' orders)';

// Least ordered items
$unpopular_res = mysqli_query($conn,"SELECT item_name as name, SUM(quantity) as total FROM `table_orders` GROUP BY item_name ORDER BY total ASC LIMIT 5");
$unpopular_items = [];
while($r = mysqli_fetch_assoc($unpopular_res)) $unpopular_items[] = $r['name'].' ('.$r['total'].' orders)';

// Weekly sales per day
$weekly_res = mysqli_query($conn,"SELECT DATE(billed_at) as day, SUM(total_amount) as revenue, COUNT(*) as bills FROM `bills` WHERE DATE(billed_at)>='$week_start' GROUP BY DATE(billed_at) ORDER BY day ASC");
$weekly_data = [];
while($r = mysqli_fetch_assoc($weekly_res)) $weekly_data[] = date('D', strtotime($r['day'])).': Rs.'.$r['revenue'].' ('.$r['bills'].' bills)';

// Top waiter by orders today
$top_waiter_res = mysqli_query($conn,"SELECT w.name, COUNT(t.id) as cnt FROM `table_orders` t JOIN `waiters` w ON t.waiter_id=w.id WHERE DATE(t.added_at)='$today' GROUP BY t.waiter_id ORDER BY cnt DESC LIMIT 1");
$top_waiter = mysqli_fetch_assoc($top_waiter_res);

// Menu categories count
$menu_cat_res = mysqli_query($conn,"SELECT category, COUNT(*) as cnt FROM `menu` WHERE is_available=1 GROUP BY category");
$menu_cats = [];
while($r = mysqli_fetch_assoc($menu_cat_res)) $menu_cats[] = $r['category'].': '.$r['cnt'].' items';

// Waiting customers
$waiting = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `waiting_list`"))['t'] ?? 0;

// Pending payments
$pending_pay = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(price*quantity) as t FROM `table_orders` WHERE payment_done=0"))['t'] ?? 0;
$pending_pay += mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(price*quantity) as t FROM `parcel_orders` WHERE payment_done=0"))['t'] ?? 0;

// Complete orders (permanent history)
$co_today = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT CONCAT(order_type,ref_id,DATE(billed_at))) as bills, SUM(subtotal) as rev FROM `complete_orders` WHERE DATE(billed_at)='$today'"));
$co_all   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(subtotal) as rev FROM `complete_orders`"));

// Build data summary for AI
$data_summary = "
=== SPICE GARDEN RESTAURANT — LIVE DATA ===
Date: ".date('d M Y')." | Time: ".date('h:i A')."

EARNINGS:
- Today's Revenue: Rs.".number_format($earn_today,2)."
- This Week Revenue: Rs.".number_format($earn_week,2)."
- Total All Time Revenue: Rs.".number_format($earn_total,2)."
- Pending Payments: Rs.".number_format($pending_pay,2)."

COMPLETE ORDERS HISTORY (Permanent — never deleted):
- Today's Bills Printed: ".($co_today['bills'] ?? 0)."
- Today's Billed Revenue: Rs.".number_format($co_today['rev'] ?? 0,2)."
- All-Time Billed Revenue: Rs.".number_format($co_all['rev'] ?? 0,2)."

TODAY'S ORDERS:
- Table Orders Today: ".$table_orders_today."
- Parcel Orders Today: ".$parcel_orders_today."
- Total Orders Today: ".$total_orders_today."
- Pending Table Orders: ".$pending_table."
- Pending Parcel Orders: ".$pending_parcel."

STAFF:
- Waiters Online: ".$w_online."
- Waiters Offline: ".$w_offline."
".($top_waiter ? "- Top Waiter Today: ".$top_waiter['name']." (".$top_waiter['cnt']." orders)" : "- No waiter data yet")."

MENU:
- ".implode(', ', $menu_cats)."

MOST POPULAR ITEMS (Table Orders):
- ".implode("\n- ", $popular_items ?: ['No data yet'])."

MOST POPULAR PARCEL ITEMS:
- ".implode("\n- ", $popular_parcel ?: ['No data yet'])."

LEAST ORDERED ITEMS:
- ".implode("\n- ", $unpopular_items ?: ['No data yet'])."

THIS WEEK DAILY SALES:
- ".implode("\n- ", $weekly_data ?: ['No sales data this week'])."

WAITING CUSTOMERS: ".$waiting."
";

echo json_encode(['data' => $data_summary]);
?>
