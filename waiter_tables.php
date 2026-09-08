<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }
$wid   = $_SESSION['waiter_id'];
$wname = $_SESSION['waiter_name'];

// Add item to table
if(isset($_POST['add_item'])){
   $tid        = intval($_POST['table_id']);
   $mid        = intval($_POST['menu_id']);
   $qty        = max(1, intval($_POST['quantity']));
   $delay_min  = max(0, intval($_POST['delay_minutes'] ?? 0));
   $mrow       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `menu` WHERE id='$mid'"));
   if($mrow){
      $iname = mysqli_real_escape_string($conn, $mrow['name']);
      $price = $mrow['price'];
      if($delay_min > 0){
         $show_after = date('Y-m-d H:i:s', strtotime("+$delay_min minutes"));
         mysqli_query($conn,"INSERT INTO `table_orders`(table_id,waiter_id,waiter_name,menu_id,item_name,price,quantity,delay_minutes,show_after) VALUES('$tid','$wid','$wname','$mid','$iname','$price','$qty','$delay_min','$show_after')");
         $message[] = "⏱️ '{$mrow['name']}' scheduled — will appear in cook's screen after $delay_min min!";
      } else {
         mysqli_query($conn,"INSERT INTO `table_orders`(table_id,waiter_id,waiter_name,menu_id,item_name,price,quantity) VALUES('$tid','$wid','$wname','$mid','$iname','$price','$qty')");
         $message[] = 'Item added successfully!';
      }
      mysqli_query($conn,"UPDATE `tables` SET is_available=0 WHERE id='$tid'");
   } else {
      $message[] = 'Menu item not found!';
   }
}

// Remove item
if(isset($_POST['remove_item'])){
   $oid = intval($_POST['order_id']);
   mysqli_query($conn,"DELETE FROM `table_orders` WHERE id='$oid'");
   $message[] = 'Item removed!';
}

// Remind cook for specific order item
if(isset($_POST['remind_cook'])){
   $oid = intval($_POST['order_id']);
   $o   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT o.*,tb.table_number FROM `table_orders` o JOIN `tables` tb ON o.table_id=tb.id WHERE o.id='$oid'"));
   if($o){
      $msg = mysqli_real_escape_string($conn,"⚡ Reminder from $wname (Waiter) — {$o['item_name']} x{$o['quantity']} — Table #{$o['table_number']} — Order #{$oid} — Please prepare fast!");
      mysqli_query($conn,"INSERT INTO `notifications`(from_type,from_id,from_name,to_type,message) VALUES('waiter','$wid','$wname','cook','$msg')");
      $message[] = "🔔 Cook reminded for {$o['item_name']}!";
   }
}

// Clear all items for table
if(isset($_POST['clear_table'])){
   $tid = intval($_POST['table_id']);
   mysqli_query($conn,"DELETE FROM `table_orders` WHERE table_id='$tid' and is_delivered = 0");
   mysqli_query($conn,"UPDATE `tables` SET is_available=1 WHERE id='$tid'");
   $message[] = 'Table cleared!';
}

// Update table status
if(isset($_POST['update_status'])){
   $tid  = intval($_POST['table_id']);
   $stat = intval($_POST['is_available']);
   mysqli_query($conn,"UPDATE `tables` SET is_available='$stat' WHERE id='$tid'");
   $message[] = 'Table status updated!';
}

// Mark item delivered
if(isset($_POST['mark_delivered'])){
   $oid = intval($_POST['order_id']);
   mysqli_query($conn,"UPDATE `table_orders` SET is_delivered=1 WHERE id='$oid'");
   $message[] = 'Item marked as delivered!';
}

// Update payment method
if(isset($_POST['update_payment'])){
   $tid    = intval($_POST['table_id']);
   $method = mysqli_real_escape_string($conn, $_POST['payment_method']);
   mysqli_query($conn,"UPDATE `table_orders` SET payment_method='$method', payment_done=1 WHERE table_id='$tid'");
   $message[] = 'Payment updated!';
}

// Print bill - clears table
if(isset($_POST['print_bill'])){
   $tid = intval($_POST['table_id']);
   $total_q = mysqli_query($conn,"SELECT SUM(price*quantity) as tot FROM `table_orders` WHERE table_id='$tid'");
   $total_r = mysqli_fetch_assoc($total_q);
   $total   = $total_r['tot'] ?? 0;
   $method  = mysqli_real_escape_string($conn, $_POST['bill_payment'] ?? 'cash');
   mysqli_query($conn,"INSERT INTO `bills`(type,ref_id,total_amount,payment_method) VALUES('table','$tid','$total','$method')");
   mysqli_query($conn,"UPDATE `table_orders` SET payment_done=1, payment_method='$method' WHERE table_id='$tid'");
   $items_q = mysqli_query($conn,"SELECT * FROM `table_orders` WHERE table_id='$tid'");
   $bill_items = [];
   while($r=mysqli_fetch_assoc($items_q)) $bill_items[] = $r;
   $_SESSION['bill_data'] = ['table_id'=>$tid,'items'=>$bill_items,'total'=>$total,'method'=>$method,'time'=>date('d M Y h:i A')];
   $save_items = mysqli_query($conn,"SELECT * FROM `table_orders` WHERE table_id='$tid'");
   while($si = mysqli_fetch_assoc($save_items)){
      $subtotal = $si['price'] * $si['quantity'];
      $safe_item = mysqli_real_escape_string($conn, $si['item_name']);
      $safe_wname = mysqli_real_escape_string($conn, $si['waiter_name']);
      $safe_method = mysqli_real_escape_string($conn, $method);
      mysqli_query($conn,"INSERT INTO `complete_orders`
         (order_type, ref_id, waiter_id, waiter_name, menu_id, item_name, price, quantity, subtotal, payment_method, bill_total, customer_name, customer_phone)
         VALUES('table','$tid','{$si['waiter_id']}','$safe_wname','{$si['menu_id']}','$safe_item','{$si['price']}','{$si['quantity']}','$subtotal','$safe_method','$total','','')");
   }
   mysqli_query($conn,"DELETE FROM `table_orders` WHERE table_id='$tid'");
   mysqli_query($conn,"UPDATE `tables` SET is_available=1 WHERE id='$tid'");
   mysqli_query($conn,"DELETE FROM `chats` WHERE ref_id='$tid'");
   header('location:waiter_tables.php?print=1'); exit;
}

$tables = mysqli_query($conn,"SELECT * FROM `tables` ORDER BY table_number");
$menu   = mysqli_query($conn,"SELECT * FROM `menu` WHERE is_available=1 ORDER BY category,name");
$bill_data = isset($_GET['print']) ? ($_SESSION['bill_data'] ?? null) : null;
if($bill_data) unset($_SESSION['bill_data']);

$avail_count = 0;
$occ_count = 0;
$tables_arr = [];
while($t = mysqli_fetch_assoc($tables)){
   $tables_arr[] = $t;
   if($t['is_available']) $avail_count++; else $occ_count++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tables - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'waiter_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<?php if($bill_data): ?>
<section class="no-print" style="max-width:60rem;margin:0 auto;">
   <div class="print-area" id="bill-print">
      <div class="bill-header">
         <h2><i class="fas fa-utensils"></i> Spice Garden</h2>
         <p>Table #<?php echo $bill_data['table_id']; ?> — <?php echo $bill_data['time']; ?></p>
      </div>
      <table class="bill-items">
         <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
         <tbody>
         <?php foreach($bill_data['items'] as $bi): ?>
            <tr><td><?php echo $bi['item_name']; ?></td><td><?php echo $bi['quantity']; ?></td><td>₹<?php echo $bi['price']; ?></td><td>₹<?php echo $bi['price']*$bi['quantity']; ?></td></tr>
         <?php endforeach; ?>
         </tbody>
      </table>
      <div class="bill-total">Total: ₹<?php echo number_format($bill_data['total'],2); ?></div>
      <p style="font-size:1.4rem;margin-top:1rem;">Payment: <?php echo ucfirst($bill_data['method']); ?></p>
      <div class="bill-footer">Thank you for visiting Spice Garden! 🌶️</div>
   </div>
   <div style="margin-top:2rem;display:flex;gap:1rem;">
      <button onclick="printBillNow()" class="btn"><i class="fas fa-print"></i> Print Bill</button>
      <a href="waiter_tables.php" class="white-btn"><i class="fas fa-arrow-left"></i> Back to Tables</a>
   </div>
</section>
<script>
function printBillNow(){
   const a=document.getElementById('bill-print');
   const w=window.open('','','width=800,height=600');
   w.document.write('<html><head><title>Bill</title><style>body{font-family:Arial;padding:30px;max-width:400px;margin:0 auto;}h2{text-align:center;}table{width:100%;border-collapse:collapse;margin:15px 0;}th,td{border:1px solid #ddd;padding:8px;font-size:13px;}.total{text-align:right;font-size:18px;font-weight:bold;margin-top:15px;}.footer{text-align:center;color:#666;margin-top:20px;font-size:12px;}</style></head><body>');
   w.document.write(a.innerHTML);
   w.document.write('</body></html>');
   w.document.close(); w.print();
}
</script>
<?php else: ?>

<section class="waiter-tables-page">
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-chair"></i> Floor</span>
      <h1>Table Orders</h1>
      <p class="page-date">Green = available · Terracotta = occupied · Tap a card to manage orders</p>
   </div>

   <div class="tables-legend">
      <div class="legend-item legend-available">
         <span class="legend-dot"></span>
         <span>Available <strong><?php echo $avail_count; ?></strong></span>
      </div>
      <div class="legend-item legend-occupied">
         <span class="legend-dot"></span>
         <span>Occupied <strong><?php echo $occ_count; ?></strong></span>
      </div>
      <div class="legend-hint">
         <i class="fas fa-info-circle"></i> Click a table card to expand orders &amp; actions
      </div>
   </div>

   <div class="tables-grid">
   <?php foreach($tables_arr as $table):
      $tid = $table['id'];
      $orders = mysqli_query($conn,"SELECT o.*, m.cooking_time FROM `table_orders` o LEFT JOIN `menu` m ON o.menu_id=m.id WHERE o.table_id='$tid' ORDER BY o.added_at");
      $total_q = mysqli_query($conn,"SELECT SUM(price*quantity) as tot FROM `table_orders` WHERE table_id='$tid'");
      $total_r = mysqli_fetch_assoc($total_q);
      $total   = $total_r['tot'] ?? 0;
      $item_count = mysqli_num_rows($orders);
      $is_avail = (int)$table['is_available'];
      $status_class = $is_avail ? 'available' : 'occupied';
   ?>
   <div class="table-card <?php echo $status_class; ?>" data-table="<?php echo $tid; ?>">
      <div class="table-card-head">
         <div class="table-card-title">
            <div class="table-number-badge">
               <i class="fas fa-chair"></i>
               <span><?php echo htmlspecialchars($table['table_number']); ?></span>
            </div>
            <div class="table-meta">
               <span class="table-seats"><i class="fas fa-users"></i> <?php echo (int)$table['capacity']; ?> seats</span>
            </div>
         </div>
         <span class="table-status <?php echo $is_avail ? 'avail' : 'busy'; ?>">
            <?php if($is_avail): ?>
               <i class="fas fa-check-circle"></i> Available
            <?php else: ?>
               <i class="fas fa-user-friends"></i> Occupied
            <?php endif; ?>
         </span>
      </div>

      <div class="order-summary-bar" onclick="toggleOrderDetails('odet-<?php echo $tid; ?>', this)" role="button" tabindex="0">
         <div class="summary-left">
            <?php if($item_count > 0): ?>
               <strong><?php echo $item_count; ?> item<?php echo $item_count!=1?'s':''; ?></strong>
               <span class="summary-dot">·</span>
               <span class="summary-total">₹<?php echo number_format($total, 2); ?></span>
            <?php else: ?>
               <span class="summary-empty">No orders yet</span>
            <?php endif; ?>
         </div>
         <span class="och-caret-wrap">
            <i class="fas fa-chevron-down"></i>
            <span class="caret-label"><?php echo $item_count > 0 ? 'View' : 'Open'; ?></span>
         </span>
      </div>

      <div class="order-details-panel" id="odet-<?php echo $tid; ?>">
         <?php if($item_count > 0): ?>
         <div class="order-items">
            <?php mysqli_data_seek($orders,0); while($o = mysqli_fetch_assoc($orders)): ?>
            <div class="item-row">
               <div class="item-main">
                  <span class="item-name"><?php echo htmlspecialchars($o['item_name']); ?></span>
                  <span class="item-qty">×<?php echo (int)$o['quantity']; ?></span>
               </div>
               <span class="item-price">₹<?php echo number_format($o['price'] * $o['quantity'], 0); ?></span>
               <span class="item-status status-<?php echo htmlspecialchars($o['status']); ?>"><?php echo ucfirst($o['status']); ?></span>
               <?php if(!empty($o['show_after']) && strtotime($o['show_after']) > time()): ?>
                  <span class="delay-chip"><i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($o['show_after'])); ?></span>
               <?php endif; ?>
               <div class="item-actions">
                  <?php if(!$o['is_delivered'] && $o['status']=='complete'): ?>
                  <form method="post" style="display:inline;">
                     <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                     <button name="mark_delivered" class="btn btn-sm" title="Mark delivered"><i class="fas fa-check"></i></button>
                  </form>
                  <?php elseif($o['is_delivered']): ?>
                     <span class="delivered-tag"><i class="fas fa-check-double"></i> Done</span>
                  <?php endif; ?>
                  <?php if($o['status'] != 'complete'): ?>
                  <form method="post" style="display:inline;">
                     <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                     <button name="remind_cook" class="option-btn btn-sm" title="Remind cook"><i class="fas fa-bell"></i></button>
                  </form>
                  <?php endif; ?>
                  <?php if(!$o['is_delivered']): ?>
                  <form method="post" style="display:inline;" class="swal-confirm" data-msg="Remove this item?">
                     <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                     <button name="remove_item" class="delete-btn btn-sm" title="Remove"><i class="fas fa-trash"></i></button>
                  </form>
                  <?php endif; ?>
               </div>
            </div>
            <?php endwhile; ?>
         </div>
         <div class="total-amount">Total: ₹<?php echo number_format($total, 2); ?></div>
         <?php else: ?>
            <div class="empty-orders"><i class="fas fa-utensils"></i> No items on this table yet</div>
         <?php endif; ?>

         <button type="button" class="option-btn add-item-toggle" onclick="toggleForm('add-<?php echo $tid; ?>')">
            <i class="fas fa-plus"></i> Add Item
         </button>
         <div class="add-item-form" id="add-<?php echo $tid; ?>">
            <form method="post">
               <input type="hidden" name="table_id" value="<?php echo $tid; ?>">
               <div class="add-item-row">
                  <div class="form-group" style="flex:2;margin:0;">
                     <label>Menu Item</label>
                     <input type="search" class="menu-filter-input" placeholder="Search dish..." id="ms-<?php echo $tid; ?>" oninput="filterSelectOptions('ms-<?php echo $tid; ?>', 'sel-<?php echo $tid; ?>')">
                     <select name="menu_id" id="sel-<?php echo $tid; ?>" required>
                        <?php
                        $menu_items = mysqli_query($conn,"SELECT * FROM `menu` WHERE is_available=1 ORDER BY category,name");
                        $cur_cat = '';
                        while($mi = mysqli_fetch_assoc($menu_items)){
                           if($cur_cat != $mi['category']){
                              if($cur_cat) echo '</optgroup>';
                              echo '<optgroup label="'.htmlspecialchars($mi['category']).'">';
                              $cur_cat = $mi['category'];
                           }
                           echo '<option value="'.$mi['id'].'">'.htmlspecialchars($mi['name']).' — ₹'.$mi['price'].' ('.$mi['cooking_time'].' min)</option>';
                        }
                        if($cur_cat) echo '</optgroup>';
                        ?>
                     </select>
                  </div>
                  <div class="form-group" style="flex:0 0 7rem;margin:0;">
                     <label>Qty</label>
                     <input type="number" name="quantity" value="1" min="1" max="20">
                  </div>
                  <button type="submit" name="add_item" class="btn" style="margin:0;align-self:flex-end;">
                     <i class="fas fa-plus"></i> Add
                  </button>
               </div>
            </form>
         </div>

         <div class="table-actions">
            <?php if($item_count > 0): ?>
            <button type="button" class="option-btn" onclick="toggleForm('pay-<?php echo $tid; ?>')">
               <i class="fas fa-money-bill-wave"></i> Payment
            </button>
            <div class="pay-form" id="pay-<?php echo $tid; ?>">
               <form method="post" class="pay-form-inner">
                  <input type="hidden" name="table_id" value="<?php echo $tid; ?>">
                  <select name="bill_payment">
                     <option value="cash">Cash</option>
                     <option value="upi">UPI</option>
                     <option value="credit_card">Credit Card</option>
                  </select>
                  <button name="print_bill" class="btn"><i class="fas fa-print"></i> Print Bill &amp; Clear</button>
               </form>
            </div>
            <form method="post" class="swal-confirm" data-msg="Clear all items from this table?">
               <input type="hidden" name="table_id" value="<?php echo $tid; ?>">
               <button name="clear_table" class="delete-btn"><i class="fas fa-broom"></i> Clear</button>
            </form>
            <?php endif; ?>

            <?php if($item_count == 0): ?>
            <form method="post">
               <input type="hidden" name="table_id" value="<?php echo $tid; ?>">
               <input type="hidden" name="is_available" value="<?php echo $is_avail ? 0 : 1; ?>">
               <button name="update_status" class="white-btn status-toggle-btn">
                  <?php if($is_avail): ?>
                     <i class="fas fa-lock"></i> Mark Occupied
                  <?php else: ?>
                     <i class="fas fa-lock-open"></i> Mark Available
                  <?php endif; ?>
               </button>
            </form>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <?php endforeach; ?>
   </div>
</section>
<?php endif; ?>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
if (typeof toggleForm !== 'function') {
  function toggleForm(id) {
    var el = document.getElementById(id);
    if (!el) return;
    var open = el.style.display === 'block' || el.classList.contains('open');
    document.querySelectorAll('.add-item-form, .pay-form').forEach(function(f){
      f.style.display = 'none';
      f.classList.remove('open');
    });
    if (!open) {
      el.style.display = 'block';
      el.classList.add('open');
    }
  }
}
</script>
</body>
</html>
