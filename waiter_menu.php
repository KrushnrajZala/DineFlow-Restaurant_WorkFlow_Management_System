<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }

$total_items = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `menu`"))['c'];
$avail_items = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `menu` WHERE is_available=1"))['c'];
$unavail     = $total_items - $avail_items;
$cat_rows    = mysqli_query($conn,"SELECT DISTINCT category FROM `menu` ORDER BY category");
$categories  = [];
while($c = mysqli_fetch_assoc($cat_rows)) $categories[] = $c['category'];
$items = mysqli_query($conn,"SELECT * FROM `menu` ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Menu - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'waiter_header.php'; ?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-book-open"></i> Kitchen Menu</span>
      <h1>Restaurant Menu</h1>
      <p class="page-date">Browse dishes · Filter by category · Check availability</p>
   </div>

   <div class="bento-grid cols-3">
      <div class="bento-tile">
         <div class="b-icon"><i class="fas fa-utensils"></i></div>
         <div class="b-value"><?php echo $total_items; ?></div>
         <div class="b-label">Total Dishes</div>
      </div>
      <div class="bento-tile green">
         <div class="b-icon"><i class="fas fa-check"></i></div>
         <div class="b-value"><?php echo $avail_items; ?></div>
         <div class="b-label">Available Now</div>
      </div>
      <div class="bento-tile red">
         <div class="b-icon"><i class="fas fa-ban"></i></div>
         <div class="b-value"><?php echo $unavail; ?></div>
         <div class="b-label">Unavailable</div>
      </div>
   </div>

   <div class="filter-bar">
      <button type="button" class="filter-chip active" data-filter="all" onclick="filterMenu('all')">All</button>
      <?php foreach($categories as $cat): ?>
      <button type="button" class="filter-chip" data-filter="<?php echo htmlspecialchars($cat); ?>" onclick="filterMenu('<?php echo htmlspecialchars($cat, ENT_QUOTES); ?>')"><?php echo htmlspecialchars($cat); ?></button>
      <?php endforeach; ?>
      <input type="search" class="menu-search" placeholder="Search dish..." oninput="searchMenu(this.value)">
   </div>

   <div class="box-container">
   <?php while($item = mysqli_fetch_assoc($items)): ?>
      <div class="box" data-category="<?php echo htmlspecialchars($item['category']); ?>" data-name="<?php echo htmlspecialchars($item['name']); ?>">
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
      </div>
   <?php endwhile; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
