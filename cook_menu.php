<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }

if(isset($_POST['toggle_available'])){
   $mid = intval($_POST['menu_id']);
   $cur = intval($_POST['current']);
   $new = $cur ? 0 : 1;
   mysqli_query($conn,"UPDATE `menu` SET is_available='$new' WHERE id='$mid'");
   $message[] = 'Menu item updated!';
}

$total  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `menu`"))['c'];
$avail  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `menu` WHERE is_available=1"))['c'];
$unavail= $total - $avail;
$items  = mysqli_query($conn,"SELECT * FROM `menu` ORDER BY category, name");
$cat_rows = mysqli_query($conn,"SELECT DISTINCT category FROM `menu` ORDER BY category");
$filter_cats = [];
while($c = mysqli_fetch_assoc($cat_rows)) $filter_cats[] = $c['category'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Menu - Cook - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'cook_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-fire"></i> Cook · Menu</span>
      <h1>Kitchen Menu</h1>
      <p class="page-date">Enable or disable dishes for the floor</p>
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

   <div class="filter-bar">
      <button type="button" class="filter-chip active" data-filter="all" onclick="filterMenu('all')">All</button>
      <?php foreach($filter_cats as $cat): ?>
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
         <form method="post" style="margin-top:1.2rem;">
            <input type="hidden" name="menu_id" value="<?php echo $item['id']; ?>">
            <input type="hidden" name="current" value="<?php echo $item['is_available']; ?>">
            <button type="submit" name="toggle_available" class="<?php echo $item['is_available']?'delete-btn':'btn'; ?>" style="margin:0;">
               <?php echo $item['is_available'] ? '<i class="fas fa-ban"></i> Disable' : '<i class="fas fa-check"></i> Enable'; ?>
            </button>
         </form>
      </div>
   <?php endwhile; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
