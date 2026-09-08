<?php
// ============================================================
//  Spice Garden — Cook AI API
//  Returns live kitchen data for AI bot
// ============================================================
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ echo json_encode(['error'=>'Unauthorized']); exit; }
header('Content-Type: application/json');

$today = date('Y-m-d');

// Pending table orders with time
$t_waiting_res = mysqli_query($conn,"SELECT item_name, quantity, added_at, table_id FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today' ORDER BY added_at ASC");
$t_waiting = [];
while($r = mysqli_fetch_assoc($t_waiting_res)){
    $mins = round((time() - strtotime($r['added_at'])) / 60);
    $t_waiting[] = $r['item_name'].' x'.$r['quantity'].' (Table '.$r['table_id'].', waiting '.$mins.' mins)';
}

// Cooking table orders
$t_cooking_res = mysqli_query($conn,"SELECT item_name, quantity, table_id, added_at FROM `table_orders` WHERE status='cooking' AND DATE(added_at)='$today'");
$t_cooking = [];
while($r = mysqli_fetch_assoc($t_cooking_res)){
    $mins = round((time() - strtotime($r['added_at'])) / 60);
    $t_cooking[] = $r['item_name'].' x'.$r['quantity'].' (Table '.$r['table_id'].', cooking '.$mins.' mins)';
}

// Pending parcel orders
$p_waiting_res = mysqli_query($conn,"SELECT item_name, quantity, added_at, parcel_number FROM `parcel_orders` WHERE status='waiting' AND DATE(added_at)='$today' ORDER BY added_at ASC");
$p_waiting = [];
while($r = mysqli_fetch_assoc($p_waiting_res)){
    $mins = round((time() - strtotime($r['added_at'])) / 60);
    $p_waiting[] = $r['item_name'].' x'.$r['quantity'].' (Parcel #'.$r['parcel_number'].', waiting '.$mins.' mins)';
}

// Cooking parcel orders
$p_cooking_res = mysqli_query($conn,"SELECT item_name, quantity, parcel_number, added_at FROM `parcel_orders` WHERE status='cooking' AND DATE(added_at)='$today'");
$p_cooking = [];
while($r = mysqli_fetch_assoc($p_cooking_res)){
    $mins = round((time() - strtotime($r['added_at'])) / 60);
    $p_cooking[] = $r['item_name'].' x'.$r['quantity'].' (Parcel #'.$r['parcel_number'].', cooking '.$mins.' mins)';
}

// Completed today
$t_complete = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `table_orders` WHERE status='complete' AND DATE(added_at)='$today'"))['t'] ?? 0;
$p_complete = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `parcel_orders` WHERE status='complete' AND DATE(added_at)='$today'"))['t'] ?? 0;
$t_billed = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `complete_orders` WHERE order_type='table' AND DATE(billed_at)='$today'"))['t'] ?? 0;
$p_billed = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM `complete_orders` WHERE order_type='parcel' AND DATE(billed_at)='$today'"))['t'] ?? 0;

// Most ordered items today
$top_res = mysqli_query($conn,"SELECT item_name, SUM(quantity) as total FROM `table_orders` WHERE DATE(added_at)='$today' GROUP BY item_name ORDER BY total DESC LIMIT 5");
$top_items = [];
while($r = mysqli_fetch_assoc($top_res)) $top_items[] = $r['item_name'].' ('.$r['total'].'x)';

// Menu cooking times
$menu_res = mysqli_query($conn,"SELECT name, cooking_time FROM `menu` WHERE is_available=1 ORDER BY cooking_time DESC");
$menu_times = [];
while($r = mysqli_fetch_assoc($menu_res)) $menu_times[] = $r['name'].' = '.$r['cooking_time'].' mins';

// Oldest waiting order
$oldest = mysqli_fetch_assoc(mysqli_query($conn,"SELECT item_name, added_at FROM `table_orders` WHERE status='waiting' AND DATE(added_at)='$today' ORDER BY added_at ASC LIMIT 1"));
$oldest_msg = $oldest ? $oldest['item_name'].' (waiting '.round((time()-strtotime($oldest['added_at']))/60).' mins)' : 'None';

$data = "
=== SPICE GARDEN KITCHEN — LIVE DATA ===
Time: ".date('h:i A')." | Date: ".date('d M Y')."

TABLE ORDERS WAITING (".count($t_waiting)." items):
".( $t_waiting ? implode("\n", $t_waiting) : 'None waiting')."

TABLE ORDERS COOKING (".count($t_cooking)." items):
".( $t_cooking ? implode("\n", $t_cooking) : 'None cooking')."

PARCEL ORDERS WAITING (".count($p_waiting)." items):
".( $p_waiting ? implode("\n", $p_waiting) : 'None waiting')."

PARCEL ORDERS COOKING (".count($p_cooking)." items):
".( $p_cooking ? implode("\n", $p_cooking) : 'None cooking')."

COMPLETED TODAY:
- Table orders completed: ".$t_complete."
- Parcel orders completed: ".$p_complete."
- Total completed: ".($t_complete+$p_complete)."
- Table items billed (permanent): ".$t_billed."
- Parcel items billed (permanent): ".$p_billed."
- Total billed today: ".($t_billed+$p_billed)."

OLDEST WAITING ORDER: ".$oldest_msg."

TOP ORDERED ITEMS TODAY:
".( $top_items ? implode("\n", $top_items) : 'No data yet')."

MENU COOKING TIMES (for reference):
".implode(", ", $menu_times)."
";

echo json_encode(['data' => $data]);
?>
