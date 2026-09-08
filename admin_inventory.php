<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }
$admin_name = $_SESSION['admin_name'];

// --- ADD NEW ITEM ---
if(isset($_POST['add_item'])){
   $name  = mysqli_real_escape_string($conn, $_POST['item_name']);
   $cat   = mysqli_real_escape_string($conn, $_POST['category']);
   $unit  = mysqli_real_escape_string($conn, $_POST['unit']);
   $qty   = floatval($_POST['quantity']);
   $minq  = floatval($_POST['min_quantity']);
   mysqli_query($conn,"INSERT INTO `inventory`(item_name,category,unit,quantity,min_quantity,updated_by) VALUES('$name','$cat','$unit','$qty','$minq','$admin_name')");
   // log
   $iid = mysqli_insert_id($conn);
   mysqli_query($conn,"INSERT INTO `inventory_log`(inv_id,item_name,action,quantity,note,done_by) VALUES('$iid','$name','add','$qty','Initial stock added','$admin_name')");
   $message[] = "✅ '$name' added to inventory!";
}

// --- UPDATE STOCK (add/remove) ---
if(isset($_POST['update_stock'])){
   $id     = intval($_POST['inv_id']);
   $action = $_POST['action'] == 'add' ? 'add' : 'remove';
   $qty    = floatval($_POST['qty']);
   $note   = mysqli_real_escape_string($conn, $_POST['note']);
   $item   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM `inventory` WHERE id='$id'"));
   if($item){
      if($action == 'add'){
         $new_qty = $item['quantity'] + $qty;
      } else {
         $new_qty = max(0, $item['quantity'] - $qty);
      }
      mysqli_query($conn,"UPDATE `inventory` SET quantity='$new_qty', updated_by='$admin_name' WHERE id='$id'");
      mysqli_query($conn,"INSERT INTO `inventory_log`(inv_id,item_name,action,quantity,note,done_by) VALUES('$id','{$item['item_name']}','$action','$qty','$note','$admin_name')");
      $message[] = "✅ Stock updated for '{$item['item_name']}'!";
   }
}

// --- DELETE ITEM ---
if(isset($_POST['delete_item'])){
   $id = intval($_POST['inv_id']);
   $item = mysqli_fetch_assoc(mysqli_query($conn,"SELECT item_name FROM `inventory` WHERE id='$id'"));
   mysqli_query($conn,"DELETE FROM `inventory` WHERE id='$id'");
   mysqli_query($conn,"DELETE FROM `inventory_log` WHERE inv_id='$id'");
   $message[] = "🗑️ '{$item['item_name']}' removed from inventory!";
}

// --- FETCH DATA ---
$total_items  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `inventory`"))['c'];
$low_stock    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `inventory` WHERE quantity <= min_quantity"))['c'];
$total_cats   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT category) as c FROM `inventory`"))['c'];
$items        = mysqli_query($conn,"SELECT * FROM `inventory` ORDER BY category, item_name");
$logs         = mysqli_query($conn,"SELECT * FROM `inventory_log` ORDER BY done_at DESC LIMIT 30");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Inventory - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-layer-group"></i> Admin</span><h1><i class="fas fa-boxes"></i> Inventory Management</h1></div>

   <!-- Stats -->
   <div class="bento-grid cols-3">
      <div class="stat-box">
         <i class="fas fa-boxes"></i>
         <h3><?php echo $total_items; ?></h3>
         <p>Total Items</p>
      </div>
      <div class="stat-box red">
         <i class="fas fa-exclamation-triangle"></i>
         <h3><?php echo $low_stock; ?></h3>
         <p>Low Stock Alert</p>
      </div>
      <div class="stat-box">
         <i class="fas fa-tags"></i>
         <h3><?php echo $total_cats; ?></h3>
         <p>Categories</p>
      </div>
   </div>

   <!-- Add Item Button -->
   <div style="margin-bottom:2rem;">
      <button class="btn" onclick="toggleForm('add-inv-form')"><i class="fas fa-plus"></i> Add New Item</button>
   </div>

   <!-- Add Item Form -->
   <div id="add-inv-form" style="display:none;margin-bottom:2rem;">
      <div class="form-container">
         <h3><i class="fas fa-plus"></i> Add New Inventory Item</h3>
         <form method="post">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr));gap:1rem;margin-top:1.5rem;">
               <div class="form-group">
                  <label>Item Name</label>
                  <input type="text" name="item_name" required placeholder="e.g. Tomato" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
               </div>
               <div class="form-group">
                  <label>Category</label>
                  <select name="category" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
                     <option>Vegetables</option><option>Dairy</option><option>Meat</option>
                     <option>Grains</option><option>Spices</option><option>Oils</option>
                     <option>Drinks</option><option>Utilities</option><option>Other</option>
                  </select>
               </div>
               <div class="form-group">
                  <label>Unit</label>
                  <select name="unit" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
                     <option>kg</option><option>litre</option><option>pcs</option>
                     <option>packet</option><option>dozen</option><option>gram</option>
                  </select>
               </div>
               <div class="form-group">
                  <label>Opening Quantity</label>
                  <input type="number" name="quantity" step="0.01" required placeholder="0" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
               </div>
               <div class="form-group">
                  <label>Min Alert Quantity</label>
                  <input type="number" name="min_quantity" step="0.01" required placeholder="5" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
               </div>
            </div>
            <button type="submit" name="add_item" class="btn" style="margin-top:1.5rem;"><i class="fas fa-save"></i> Save Item</button>
            <button type="button" class="option-btn" onclick="toggleForm('add-inv-form')" style="margin-top:1.5rem;margin-left:.5rem;"><i class="fas fa-times"></i> Cancel</button>
         </form>
      </div>
   </div>

   <!-- Inventory Table -->
   <div class="table-container">
      <table class="table">
         <thead>
            <tr>
               <th>#</th>
               <th>Item Name</th>
               <th>Category</th>
               <th>Stock</th>
               <th>Unit</th>
               <th>Min Alert</th>
               <th>Status</th>
               <th>Last Updated By</th>
               <th>Actions</th>
            </tr>
         </thead>
         <tbody>
         <?php
         $sr = 1;
         while($row = mysqli_fetch_assoc($items)){
            $low = ($row['quantity'] <= $row['min_quantity']);
            $row_style = $low ? 'background:#fff5f5;' : '';
         ?>
         <tr style="<?php echo $row_style; ?>">
            <td><?php echo $sr++; ?></td>
            <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td>
               <strong style="font-size:1.7rem;color:<?php echo $low ? '#ef4444' : '#10b981'; ?>">
                  <?php echo number_format($row['quantity'],2); ?>
               </strong>
            </td>
            <td><?php echo htmlspecialchars($row['unit']); ?></td>
            <td><?php echo number_format($row['min_quantity'],2); ?></td>
            <td>
               <?php if($low): ?>
                  <span class="badge red"><i class="fas fa-exclamation-triangle"></i> Low Stock</span>
               <?php else: ?>
                  <span class="badge green"><i class="fas fa-check-circle"></i> OK</span>
               <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['updated_by']); ?></td>
            <td style="display:flex;gap:.5rem;flex-wrap:wrap;">
               <!-- Add Stock -->
               <button class="btn" style="padding:.5rem 1rem;font-size:1.3rem;margin-top:0;"
                  onclick="openStockModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['item_name']); ?>', 'add')">
                  <i class="fas fa-plus"></i> Add
               </button>
               <!-- Remove Stock -->
               <button class="delete-btn" style="padding:.5rem 1rem;font-size:1.3rem;margin-top:0;"
                  onclick="openStockModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['item_name']); ?>', 'remove')">
                  <i class="fas fa-minus"></i> Remove
               </button>
               <!-- Delete -->
               <form method="post" style="display:inline;" class="swal-confirm" data-msg="Delete this item?">
                  <input type="hidden" name="inv_id" value="<?php echo $row['id']; ?>">
                  <button type="submit" name="delete_item" class="option-btn" style="padding:.5rem 1rem;font-size:1.3rem;margin-top:0;color:#ef4444;">
                     <i class="fas fa-trash"></i>
                  </button>
               </form>
            </td>
         </tr>
         <?php } ?>
         </tbody>
      </table>
   </div>

   <!-- Stock Update Modal -->
   <div id="stock-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;display:none;align-items:center;justify-content:center;">
      <div style="background:#fff;border-radius:1.6rem;padding:3rem;width:90%;max-width:42rem;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
         <h3 id="modal-title" style="font-size:2rem;font-weight:700;margin-bottom:2rem;color:#1a1a2e;"></h3>
         <form method="post">
            <input type="hidden" name="inv_id" id="modal-inv-id">
            <input type="hidden" name="action" id="modal-action">
            <div class="form-group" style="margin-bottom:1.5rem;">
               <label style="font-size:1.4rem;font-weight:600;color:#374151;display:block;margin-bottom:.6rem;">Quantity</label>
               <input type="number" name="qty" id="modal-qty" step="0.01" required placeholder="Enter quantity"
                  style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <div class="form-group" style="margin-bottom:1.5rem;">
               <label style="font-size:1.4rem;font-weight:600;color:#374151;display:block;margin-bottom:.6rem;">Note (optional)</label>
               <input type="text" name="note" placeholder="e.g. Purchased from market"
                  style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
            </div>
            <div style="display:flex;gap:1rem;">
               <button type="submit" name="update_stock" id="modal-btn" class="btn" style="flex:1;justify-content:center;"></button>
               <button type="button" class="option-btn" style="flex:1;justify-content:center;" onclick="closeStockModal()"><i class="fas fa-times"></i> Cancel</button>
            </div>
         </form>
      </div>
   </div>

   <!-- Inventory Log -->
   <h2 class="sec-heading" style="margin-top:3rem;"><i class="fas fa-history"></i> Recent Activity Log</h2>
   <div class="table-container">
      <table class="table">
         <thead>
            <tr>
               <th>#</th>
               <th>Item</th>
               <th>Action</th>
               <th>Quantity</th>
               <th>Note</th>
               <th>Done By</th>
               <th>Date & Time</th>
            </tr>
         </thead>
         <tbody>
         <?php
         $sr = 1;
         while($log = mysqli_fetch_assoc($logs)){
         ?>
         <tr>
            <td><?php echo $sr++; ?></td>
            <td><strong><?php echo htmlspecialchars($log['item_name']); ?></strong></td>
            <td>
               <?php if($log['action']=='add'): ?>
                  <span class="badge green"><i class="fas fa-plus"></i> Added</span>
               <?php else: ?>
                  <span class="badge red"><i class="fas fa-minus"></i> Removed</span>
               <?php endif; ?>
            </td>
            <td><?php echo number_format($log['quantity'],2); ?></td>
            <td><?php echo htmlspecialchars($log['note'] ?: '—'); ?></td>
            <td><?php echo htmlspecialchars($log['done_by']); ?></td>
            <td><?php echo date('d M Y, h:i A', strtotime($log['done_at'])); ?></td>
         </tr>
         <?php } ?>
         </tbody>
      </table>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
function toggleForm(id){
   var f = document.getElementById(id);
   f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
function openStockModal(id, name, action){
   document.getElementById('modal-inv-id').value  = id;
   document.getElementById('modal-action').value  = action;
   document.getElementById('modal-qty').value     = '';
   if(action === 'add'){
      document.getElementById('modal-title').innerHTML = '<i class="fas fa-plus-circle" style="color:#10b981;"></i> Add Stock — ' + name;
      document.getElementById('modal-btn').innerHTML   = '<i class="fas fa-plus"></i> Add Stock';
      document.getElementById('modal-btn').style.background = 'linear-gradient(135deg,#10b981,#059669)';
   } else {
      document.getElementById('modal-title').innerHTML = '<i class="fas fa-minus-circle" style="color:#ef4444;"></i> Remove Stock — ' + name;
      document.getElementById('modal-btn').innerHTML   = '<i class="fas fa-minus"></i> Remove Stock';
      document.getElementById('modal-btn').style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
   }
   document.getElementById('stock-modal').style.display = 'flex';
}
function closeStockModal(){
   document.getElementById('stock-modal').style.display = 'none';
}
// close on backdrop click
document.getElementById('stock-modal').addEventListener('click', function(e){
   if(e.target === this) closeStockModal();
});
</script>
</body>
</html>
