<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }
$wid   = $_SESSION['waiter_id'];
$wname = $_SESSION['waiter_name'];
$today = date('Y-m-d');

// ── STEP 1: Create new parcel box ──
if(isset($_POST['add_parcel'])){
   $cname  = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
   $cphone = mysqli_real_escape_string($conn, $_POST['customer_phone'] ?? '');
   $last   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT MAX(parcel_number) as mx FROM `parcels`"));
   $pnum   = ($last['mx'] ?? 0) + 1;
   mysqli_query($conn,"INSERT INTO `parcels`(parcel_number,customer_name,customer_phone) VALUES('$pnum','$cname','$cphone')");
   $message[] = "Parcel #$pnum created! Now add items to it below.";
   $highlight = $pnum;
}

// ── STEP 2: Add item to an existing parcel ──
if(isset($_POST['add_parcel_item'])){
   $pnum = intval($_POST['parcel_number']);
   $mid  = intval($_POST['menu_id']);
   $qty  = intval($_POST['quantity']);
   $mrow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `menu` WHERE id='$mid'"));
   if($mrow){
      mysqli_query($conn,"INSERT INTO `parcel_orders`(parcel_number,waiter_id,waiter_name,menu_id,item_name,price,quantity) VALUES('$pnum','$wid','$wname','$mid','{$mrow['name']}','{$mrow['price']}','$qty')");
      $message[] = "'{$mrow['name']}' added to Parcel #$pnum!";
      $highlight = $pnum;
   }
}

// ── Remove item ──
if(isset($_POST['remove_parcel_item'])){
   $oid  = intval($_POST['order_id']);
   $pnum = intval($_POST['parcel_number']);
   mysqli_query($conn,"DELETE FROM `parcel_orders` WHERE id='$oid'");
   $message[] = 'Item removed!';
   $highlight = $pnum;
}

// ── Mark delivered ──
if(isset($_POST['mark_delivered'])){
   $oid  = intval($_POST['order_id']);
   $pnum = intval($_POST['parcel_number']);
   mysqli_query($conn,"UPDATE `parcel_orders` SET is_delivered=1 WHERE id='$oid'");
   $message[] = 'Item marked as delivered!';
   $highlight = $pnum;
}

// ── Print bill & close parcel ──
if(isset($_POST['print_parcel_bill'])){
   $pnum    = intval($_POST['parcel_number']);
   $total_q = mysqli_query($conn,"SELECT SUM(price*quantity) as tot FROM `parcel_orders` WHERE parcel_number='$pnum'");
   $tot_r   = mysqli_fetch_assoc($total_q);
   $total   = $tot_r['tot'] ?? 0;
   $method  = mysqli_real_escape_string($conn, $_POST['bill_payment'] ?? 'cash');
   mysqli_query($conn,"INSERT INTO `bills`(type,ref_id,total_amount,payment_method) VALUES('parcel','$pnum','$total','$method')");
   mysqli_query($conn,"UPDATE `parcel_orders` SET payment_method='$method', payment_done=1 WHERE parcel_number='$pnum'");
   $items_q = mysqli_query($conn,"SELECT * FROM `parcel_orders` WHERE parcel_number='$pnum'");
   $bill_items = [];
   while($r = mysqli_fetch_assoc($items_q)) $bill_items[] = $r;
   $prow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `parcels` WHERE parcel_number='$pnum' LIMIT 1"));
   $_SESSION['parcel_bill'] = [
      'pnum'   => $pnum,
      'items'  => $bill_items,
      'total'  => $total,
      'method' => $method,
      'time'   => date('d M Y h:i A'),
      'cname'  => $prow['customer_name'] ?? '',
      'cphone' => $prow['customer_phone'] ?? '',
   ];
   $save_items = mysqli_query($conn,"SELECT * FROM `parcel_orders` WHERE parcel_number='$pnum'");
   $cname  = mysqli_real_escape_string($conn, $prow['customer_name']  ?? '');
   $cphone = mysqli_real_escape_string($conn, $prow['customer_phone'] ?? '');
   while($si = mysqli_fetch_assoc($save_items)){
      $subtotal = $si['price'] * $si['quantity'];
      $safe_item  = mysqli_real_escape_string($conn, $si['item_name']);
      $safe_wname = mysqli_real_escape_string($conn, $si['waiter_name']);
      $safe_method2 = mysqli_real_escape_string($conn, $method);
      mysqli_query($conn,"INSERT INTO `complete_orders`
         (order_type, ref_id, waiter_id, waiter_name, menu_id, item_name, price, quantity, subtotal, payment_method, bill_total, customer_name, customer_phone)
         VALUES('parcel','$pnum','{$si['waiter_id']}','$safe_wname','{$si['menu_id']}','$safe_item','{$si['price']}','{$si['quantity']}','$subtotal','$safe_method2','$total','$cname','$cphone')");
   }
   mysqli_query($conn,"DELETE FROM `parcel_orders` WHERE parcel_number='$pnum'");
   mysqli_query($conn,"UPDATE `parcels` SET is_active=0 WHERE parcel_number='$pnum'");
   header('location:waiter_parcel.php?print=1'); exit;
}

// ── Reset / cancel parcel ──
if(isset($_POST['reset_parcel'])){
   $pnum = intval($_POST['parcel_number']);
   mysqli_query($conn,"DELETE FROM `parcel_orders` WHERE parcel_number='$pnum'");
   mysqli_query($conn,"UPDATE `parcels` SET is_active=0 WHERE parcel_number='$pnum'");
   $message[] = "Parcel #$pnum cancelled!";
}

// ── Data ──
$parcels       = mysqli_query($conn,"SELECT * FROM `parcels` WHERE is_active=1 ORDER BY parcel_number");
$total_active  = mysqli_num_rows($parcels);
$total_deliver = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `complete_orders` WHERE order_type='parcel' AND DATE(billed_date)='$today' AND waiter_id = $wid"));
$total_pending = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `parcel_orders` WHERE is_delivered=0 AND DATE(added_at)='$today'"));
$bill_data     = isset($_GET['print']) ? ($_SESSION['parcel_bill'] ?? null) : null;
if($bill_data) unset($_SESSION['parcel_bill']);
$highlight     = $highlight ?? 0;

$parcels_arr = [];
while($p = mysqli_fetch_assoc($parcels)) $parcels_arr[] = $p;
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Parcels - Spice Garden</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'waiter_header.php'; ?>

<?php if(isset($message)){ foreach($message as $msg){ echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>'; } } ?>

<?php if($bill_data): ?>
<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-receipt"></i> Parcel Bill</span>
      <h1>Parcel #<?php echo (int)$bill_data['pnum']; ?></h1>
   </div>
   <div class="box" id="parcel-bill-print" style="max-width:48rem;margin:0 auto;">
      <h3 style="text-align:center;margin-bottom:.5rem;">Spice Garden · Parcel Bill</h3>
      <p style="text-align:center;color:var(--text-muted);font-size:1.3rem;">
         <?php echo htmlspecialchars($bill_data['time']); ?><br>
         <?php if(!empty($bill_data['cname'])): ?>Customer: <?php echo htmlspecialchars($bill_data['cname']); ?><?php endif; ?>
         <?php if(!empty($bill_data['cphone'])): ?> · <?php echo htmlspecialchars($bill_data['cphone']); ?><?php endif; ?>
      </p>
      <table style="margin-top:1.5rem;">
         <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
         <tbody>
         <?php foreach($bill_data['items'] as $bi): ?>
            <tr>
               <td><?php echo htmlspecialchars($bi['item_name']); ?></td>
               <td><?php echo (int)$bi['quantity']; ?></td>
               <td>₹<?php echo number_format($bi['price'],2); ?></td>
               <td>₹<?php echo number_format($bi['price']*$bi['quantity'],2); ?></td>
            </tr>
         <?php endforeach; ?>
         </tbody>
      </table>
      <div class="total-amount" style="text-align:right;">Total: ₹<?php echo number_format($bill_data['total'],2); ?></div>
      <p style="text-align:center;margin-top:1rem;font-size:1.35rem;">Payment: <?php echo ucfirst($bill_data['method']); ?></p>
   </div>
   <div style="margin-top:2rem;display:flex;gap:1rem;justify-content:center;" class="no-print">
      <button onclick="window.print()" class="btn"><i class="fas fa-print"></i> Print Bill</button>
      <a href="waiter_parcel.php" class="white-btn"><i class="fas fa-arrow-left"></i> Back</a>
   </div>
</section>
<?php else: ?>

<section class="waiter-tables-page waiter-parcel-page">
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-box"></i> Takeaway</span>
      <h1>Parcel Orders</h1>
      <p class="page-date">Active parcels use the same card look as tables · Empty = green · With items = terracotta</p>
   </div>

   <div class="bento-grid cols-3">
      <div class="bento-tile">
         <div class="b-icon"><i class="fas fa-box-open"></i></div>
         <div class="b-value"><?php echo $total_active; ?></div>
         <div class="b-label">Active Parcels</div>
      </div>
      <div class="bento-tile green">
         <div class="b-icon"><i class="fas fa-truck"></i></div>
         <div class="b-value"><?php echo $total_deliver; ?></div>
         <div class="b-label">Delivered Today</div>
      </div>
      <div class="bento-tile orange">
         <div class="b-icon"><i class="fas fa-hourglass-half"></i></div>
         <div class="b-value"><?php echo $total_pending; ?></div>
         <div class="b-label">Pending Items</div>
      </div>
   </div>

   <!-- Create new parcel -->
   <div class="box new-parcel-box">
      <h3><i class="fas fa-plus-circle" style="color:var(--primary);"></i> New Parcel</h3>
      <form method="post" class="new-parcel-form">
         <div class="form-group" style="flex:1;min-width:14rem;margin:0;">
            <label>Customer name</label>
            <input type="text" name="customer_name" placeholder="Optional">
         </div>
         <div class="form-group" style="flex:1;min-width:12rem;margin:0;">
            <label>Phone</label>
            <input type="text" name="customer_phone" placeholder="Optional">
         </div>
         <button type="submit" name="add_parcel" class="btn"><i class="fas fa-box"></i> Create Parcel</button>
      </form>
   </div>

   <?php if($total_active > 0): ?>
   <div class="tables-legend" style="margin-top:0;">
      <div class="legend-item legend-available">
         <span class="legend-dot"></span>
         <span>Empty parcel (no items yet)</span>
      </div>
      <div class="legend-item legend-occupied">
         <span class="legend-dot"></span>
         <span>Has items / in progress</span>
      </div>
      <div class="legend-hint">
         <i class="fas fa-info-circle"></i> Click a card to expand orders &amp; actions
      </div>
   </div>

   <div class="tables-grid">
   <?php foreach($parcels_arr as $p):
      $pnum = (int)$p['parcel_number'];
      $orders = mysqli_query($conn,"SELECT * FROM `parcel_orders` WHERE parcel_number='$pnum' ORDER BY added_at");
      $total_q = mysqli_query($conn,"SELECT SUM(price*quantity) as tot FROM `parcel_orders` WHERE parcel_number='$pnum'");
      $total = mysqli_fetch_assoc($total_q)['tot'] ?? 0;
      $item_count = mysqli_num_rows($orders);
      $open = ($highlight == $pnum);
      $status_class = ($item_count > 0) ? 'occupied' : 'available';
   ?>
   <div class="table-card <?php echo $status_class; ?>" data-parcel="<?php echo $pnum; ?>">
      <div class="table-card-head">
         <div class="table-card-title">
            <div class="table-number-badge">
               <i class="fas fa-box"></i>
               <span>#<?php echo $pnum; ?></span>
            </div>
            <div class="table-meta">
               <?php if(!empty($p['customer_name']) || !empty($p['customer_phone'])): ?>
               <span class="table-seats parcel-customer">
                  <?php if($p['customer_name']): ?><i class="fas fa-user"></i> <?php echo htmlspecialchars($p['customer_name']); ?><?php endif; ?>
                  <?php if($p['customer_phone']): ?> · <i class="fas fa-phone"></i> <?php echo htmlspecialchars($p['customer_phone']); ?><?php endif; ?>
               </span>
               <?php else: ?>
               <span class="table-seats">Takeaway order</span>
               <?php endif; ?>
            </div>
         </div>
         <span class="table-status <?php echo $item_count > 0 ? 'busy' : 'avail'; ?>">
            <?php if($item_count > 0): ?>
               <i class="fas fa-box-open"></i> <?php echo $item_count; ?> item<?php echo $item_count!=1?'s':''; ?>
            <?php else: ?>
               <i class="fas fa-check-circle"></i> Empty
            <?php endif; ?>
         </span>
      </div>

      <div class="order-summary-bar <?php echo $open ? 'open' : ''; ?>" onclick="toggleOrderDetails('podet-<?php echo $pnum; ?>', this)" role="button" tabindex="0">
         <div class="summary-left">
            <?php if($item_count > 0): ?>
               <strong><?php echo $item_count; ?> item<?php echo $item_count!=1?'s':''; ?></strong>
               <span class="summary-dot">·</span>
               <span class="summary-total">₹<?php echo number_format($total, 2); ?></span>
            <?php else: ?>
               <span class="summary-empty">No orders yet — add items</span>
            <?php endif; ?>
         </div>
         <span class="och-caret-wrap">
            <i class="fas fa-chevron-down"></i>
            <span class="caret-label"><?php echo $item_count > 0 ? 'View' : 'Open'; ?></span>
         </span>
      </div>

      <div class="order-details-panel <?php echo $open ? 'open' : ''; ?>" id="podet-<?php echo $pnum; ?>">
         <?php if($item_count > 0): mysqli_data_seek($orders,0); ?>
         <div class="order-items">
            <?php while($o = mysqli_fetch_assoc($orders)): ?>
            <div class="item-row">
               <div class="item-main">
                  <span class="item-name"><?php echo htmlspecialchars($o['item_name']); ?></span>
                  <span class="item-qty">×<?php echo (int)$o['quantity']; ?></span>
               </div>
               <span class="item-price">₹<?php echo number_format($o['price']*$o['quantity'], 0); ?></span>
               <div class="item-actions">
                  <?php if(!$o['is_delivered']): ?>
                  <form method="post" style="display:inline;">
                     <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                     <input type="hidden" name="parcel_number" value="<?php echo $pnum; ?>">
                     <button type="submit" name="mark_delivered" class="btn btn-sm" title="Mark delivered"><i class="fas fa-check"></i></button>
                  </form>
                  <form method="post" style="display:inline;" class="swal-confirm" data-msg="Remove this item?">
                     <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                     <input type="hidden" name="parcel_number" value="<?php echo $pnum; ?>">
                     <button type="submit" name="remove_parcel_item" class="delete-btn btn-sm" title="Remove"><i class="fas fa-trash"></i></button>
                  </form>
                  <?php else: ?>
                  <span class="delivered-tag"><i class="fas fa-check-double"></i> Done</span>
                  <?php endif; ?>
               </div>
            </div>
            <?php endwhile; ?>
         </div>
         <div class="total-amount">Total: ₹<?php echo number_format($total, 2); ?></div>
         <?php else: ?>
         <div class="empty-orders"><i class="fas fa-box-open"></i> No items on this parcel yet</div>
         <?php endif; ?>

         <button type="button" class="option-btn add-item-toggle" onclick="toggleForm('padd-<?php echo $pnum; ?>')">
            <i class="fas fa-plus"></i> Add Item
         </button>
         <div class="add-item-form <?php echo $open?'open':''; ?>" id="padd-<?php echo $pnum; ?>" style="<?php echo $open?'display:block':''; ?>">
            <form method="post">
               <input type="hidden" name="parcel_number" value="<?php echo $pnum; ?>">
               <div class="add-item-row">
                  <div class="form-group" style="flex:2;margin:0;">
                     <label>Menu item</label>
                     <input type="search" class="menu-filter-input" placeholder="Search dish..." id="pms-<?php echo $pnum; ?>" oninput="filterSelectOptions('pms-<?php echo $pnum; ?>', 'psel-<?php echo $pnum; ?>')">
                     <select name="menu_id" id="psel-<?php echo $pnum; ?>" required>
                        <?php
                        $menu_items = mysqli_query($conn,"SELECT * FROM `menu` WHERE is_available=1 ORDER BY category,name");
                        $cur='';
                        while($mi=mysqli_fetch_assoc($menu_items)){
                           if($cur!=$mi['category']){ if($cur) echo '</optgroup>'; echo '<optgroup label="'.htmlspecialchars($mi['category']).'">'; $cur=$mi['category']; }
                           echo '<option value="'.$mi['id'].'">'.htmlspecialchars($mi['name']).' — ₹'.$mi['price'].'</option>';
                        }
                        if($cur) echo '</optgroup>';
                        ?>
                     </select>
                  </div>
                  <div class="form-group" style="flex:0 0 7rem;margin:0;">
                     <label>Qty</label>
                     <input type="number" name="quantity" value="1" min="1" max="30">
                  </div>
                  <button type="submit" name="add_parcel_item" class="btn" style="margin:0;align-self:flex-end;">
                     <i class="fas fa-plus"></i> Add
                  </button>
               </div>
            </form>
         </div>

         <div class="table-actions">
            <?php if($item_count > 0): ?>
            <button type="button" class="option-btn" onclick="toggleForm('ppay-<?php echo $pnum; ?>')">
               <i class="fas fa-money-bill-wave"></i> Payment
            </button>
            <div class="pay-form" id="ppay-<?php echo $pnum; ?>">
               <form method="post" class="pay-form-inner">
                  <input type="hidden" name="parcel_number" value="<?php echo $pnum; ?>">
                  <select name="bill_payment">
                     <option value="cash">Cash</option>
                     <option value="upi">UPI</option>
                     <option value="credit_card">Card</option>
                  </select>
                  <button type="submit" name="print_parcel_bill" class="btn"><i class="fas fa-print"></i> Bill &amp; Close</button>
               </form>
            </div>
            <?php endif; ?>
            <form method="post" class="swal-confirm" data-msg="Cancel Parcel #<?php echo $pnum; ?> and remove all items?">
               <input type="hidden" name="parcel_number" value="<?php echo $pnum; ?>">
               <button type="submit" name="reset_parcel" class="delete-btn"><i class="fas fa-times"></i> Cancel</button>
            </form>
         </div>
      </div>
   </div>
   <?php endforeach; ?>
   </div>
   <?php else: ?>
   <div class="empty">
      <i class="fas fa-box-open"></i>
      <p>No active parcels. Create one above.</p>
   </div>
   <?php endif; ?>
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
document.querySelectorAll('form.swal-confirm').forEach(function(form){
  form.addEventListener('submit', function(e){
    e.preventDefault();
    var msg = form.getAttribute('data-msg') || 'Are you sure?';
    var f = form;
    if (typeof Swal === 'undefined') {
      if (confirm(msg)) f.submit();
      return;
    }
    Swal.fire({
      title: 'Please confirm',
      text: msg,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes',
      cancelButtonText: 'No',
      buttonsStyling: false,
      customClass: { popup:'dineflow-swal', confirmButton:'dineflow-swal-confirm', cancelButton:'dineflow-swal-cancel' }
    }).then(function(r){ if(r.isConfirmed){ f.submit(); } });
  });
});
</script>
</body>
</html>
