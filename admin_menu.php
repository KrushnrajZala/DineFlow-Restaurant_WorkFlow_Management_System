<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }

if(isset($_POST['add_item'])){
   $name  = mysqli_real_escape_string($conn,$_POST['name']);
   $price = floatval($_POST['price']);
   $cat   = mysqli_real_escape_string($conn,$_POST['category']);
   $ctime = intval($_POST['cooking_time']);
   mysqli_query($conn,"INSERT INTO `menu`(name,price,category,cooking_time) VALUES('$name','$price','$cat','$ctime')");
   $message[] = 'Menu item added!';
}

if(isset($_POST['update_item'])){
   $id    = intval($_POST['menu_id']);
   $name  = mysqli_real_escape_string($conn,$_POST['name']);
   $price = floatval($_POST['price']);
   $cat   = mysqli_real_escape_string($conn,$_POST['category']);
   $ctime = intval($_POST['cooking_time']);
   $avail = intval($_POST['is_available']);
   mysqli_query($conn,"UPDATE `menu` SET name='$name',price='$price',category='$cat',cooking_time='$ctime',is_available='$avail' WHERE id='$id'");
   $message[] = 'Item updated!';
}

if(isset($_POST['delete_item'])){
   $id = intval($_POST['menu_id']);
   mysqli_query($conn,"DELETE FROM `menu` WHERE id='$id'");
   $message[] = 'Item deleted!';
}

$total  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `menu`"))['c'];
$avail  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `menu` WHERE is_available=1"))['c'];
$unavail= $total - $avail;
$items  = mysqli_query($conn,"SELECT * FROM `menu` ORDER BY category, name");
$categories = ['Starter','Main Course','Bread','Rice','Drinks','Dessert','Soup','Salad','Other'];
$cat_rows = mysqli_query($conn,"SELECT DISTINCT category FROM `menu` ORDER BY category");
$filter_cats = [];
while($c = mysqli_fetch_assoc($cat_rows)) $filter_cats[] = $c['category'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Menu - Admin - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-utensils"></i> Admin · Menu</span>
      <h1>Menu Management</h1>
      <p class="page-date">Add, edit, filter and control dish availability</p>
   </div>

   <div class="bento-grid cols-3">
      <div class="bento-tile">
         <div class="b-icon"><i class="fas fa-list"></i></div>
         <div class="b-value"><?php echo $total; ?></div>
         <div class="b-label">Total dishes</div>
      </div>
      <div class="bento-tile green">
         <div class="b-icon"><i class="fas fa-check"></i></div>
         <div class="b-value"><?php echo $avail; ?></div>
         <div class="b-label">Available</div>
      </div>
      <div class="bento-tile red">
         <div class="b-icon"><i class="fas fa-ban"></i></div>
         <div class="b-value"><?php echo $unavail; ?></div>
         <div class="b-label">Unavailable</div>
      </div>
   </div>

   <button type="button" class="btn" onclick="toggleForm('add-menu-form')" style="margin-bottom:1.5rem;"><i class="fas fa-plus"></i> Add Menu Item</button>
   <div id="add-menu-form" class="add-item-form" style="max-width:56rem;margin-bottom:2rem;">
      <form method="post">
         <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;">
            <div class="form-group">
               <label>Item name</label>
               <input type="text" name="name" required placeholder="e.g. Paneer Butter Masala">
            </div>
            <div class="form-group">
               <label>Price (₹)</label>
               <input type="number" name="price" step="0.01" min="0" required placeholder="0.00">
            </div>
            <div class="form-group">
               <label>Category</label>
               <select name="category">
                  <?php foreach($categories as $c) echo '<option value="'.htmlspecialchars($c).'">'.htmlspecialchars($c).'</option>'; ?>
               </select>
            </div>
            <div class="form-group">
               <label>Cook time (min)</label>
               <input type="number" name="cooking_time" value="15" min="1" required>
            </div>
         </div>
         <button type="submit" name="add_item" class="btn" style="margin-top:1rem;"><i class="fas fa-save"></i> Save dish</button>
      </form>
   </div>

   <div class="filter-bar">
      <button type="button" class="filter-chip active" data-filter="all" onclick="filterMenu('all')">All</button>
      <?php foreach($filter_cats as $cat): ?>
      <button type="button" class="filter-chip" data-filter="<?php echo htmlspecialchars($cat); ?>" onclick="filterMenu('<?php echo htmlspecialchars($cat, ENT_QUOTES); ?>')"><?php echo htmlspecialchars($cat); ?></button>
      <?php endforeach; ?>
      <input type="search" class="menu-search" placeholder="Search dish..." oninput="searchMenu(this.value)">
   </div>

   <div class="box-container">
   <?php
   mysqli_data_seek($items, 0);
   while($item = mysqli_fetch_assoc($items)):
   ?>
      <div class="box menu-admin-card" data-category="<?php echo htmlspecialchars($item['category']); ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>">
         <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;">
            <h3 class="name"><?php echo htmlspecialchars($item['name']); ?></h3>
            <?php if($item['is_available']): ?>
               <span class="badge success">Available</span>
            <?php else: ?>
               <span class="badge danger">Off</span>
            <?php endif; ?>
         </div>
         <p style="margin:.4rem 0 .8rem;font-size:1.25rem;color:var(--text-muted);">
            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['category']); ?>
            &nbsp;·&nbsp;
            <i class="fas fa-clock"></i> <?php echo (int)$item['cooking_time']; ?> min
         </p>
         <div class="price">₹<?php echo number_format($item['price'], 2); ?></div>

         <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1.2rem;">
            <button type="button" class="option-btn" onclick="toggleForm('edit-menu-<?php echo $item['id']; ?>')"><i class="fas fa-edit"></i> Edit</button>
            <form method="post" class="swal-confirm" data-msg="Delete this item?" style="display:inline;">
               <input type="hidden" name="menu_id" value="<?php echo $item['id']; ?>">
               <button type="submit" name="delete_item" class="delete-btn" style="margin:0;"><i class="fas fa-trash"></i></button>
            </form>
         </div>

         <div id="edit-menu-<?php echo $item['id']; ?>" class="add-item-form" style="margin-top:1rem;">
            <form method="post">
               <input type="hidden" name="menu_id" value="<?php echo $item['id']; ?>">
               <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                  <div class="form-group" style="margin:0;">
                     <label>Name</label>
                     <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                  </div>
                  <div class="form-group" style="margin:0;">
                     <label>Price</label>
                     <input type="number" name="price" step="0.01" value="<?php echo $item['price']; ?>" required>
                  </div>
                  <div class="form-group" style="margin:0;">
                     <label>Category</label>
                     <select name="category">
                        <?php foreach($categories as $c) echo '<option value="'.htmlspecialchars($c).'"'.($c==$item['category']?' selected':'').'>'.htmlspecialchars($c).'</option>'; ?>
                     </select>
                  </div>
                  <div class="form-group" style="margin:0;">
                     <label>Cook time</label>
                     <input type="number" name="cooking_time" value="<?php echo (int)$item['cooking_time']; ?>" min="1">
                  </div>
                  <div class="form-group" style="margin:0;">
                     <label>Status</label>
                     <select name="is_available">
                        <option value="1" <?php echo $item['is_available']?'selected':''; ?>>Available</option>
                        <option value="0" <?php echo !$item['is_available']?'selected':''; ?>>Not available</option>
                     </select>
                  </div>
               </div>
               <button type="submit" name="update_item" class="btn" style="margin-top:1rem;"><i class="fas fa-save"></i> Save</button>
            </form>
         </div>
      </div>
   <?php endwhile; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
