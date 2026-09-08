<?php
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }
$current = basename($_SERVER['PHP_SELF']);

$orderPages   = ['admin_table_orders.php','admin_parcel_orders.php'];
$chatPages    = ['table_chat.php','parcel_chat.php'];
$insightPages = ['admin_reports.php','admin_reviews.php','popular_menu.php'];
?>

<header class="header">
   <div class="flex">

      <a href="admin_dashboard.php" class="logo">
         <img src="images/logo.png" alt="DineFlow" class="brand-mark">
         <span class="brand-role">Admin</span>
      </a>

      <nav class="navbar" id="navbar">

         <a href="admin_dashboard.php" <?= $current=='admin_dashboard.php' ? 'class="active"' : '' ?>>
            <i class="fas fa-tachometer-alt"></i> Dashboard
         </a>

         <!-- Orders dropdown -->
         <div class="nav-drop <?= in_array($current,$orderPages)?'open':'' ?>" id="dropOrders">
            <button class="nav-drop-btn <?= in_array($current,$orderPages)?'active':'' ?>" onclick="toggleDrop('dropOrders')">
               <i class="fas fa-receipt"></i> Orders <i class="fas fa-chevron-down caret"></i>
            </button>
            <div class="nav-drop-menu">
               <a href="admin_table_orders.php" class="<?= $current=='admin_table_orders.php'?'active':'' ?>">
                  <i class="fas fa-utensils"></i> Table Orders
               </a>
               <a href="admin_parcel_orders.php" class="<?= $current=='admin_parcel_orders.php'?'active':'' ?>">
                  <i class="fas fa-box"></i> Parcel Orders
               </a>
            </div>
         </div>

         <!-- Plain links -->
         <a href="admin_waiters.php"      <?= $current=='admin_waiters.php'     ?'class="active"':'' ?>><i class="fas fa-user-tie"></i> Waiters</a>
         <a href="admin_menu.php"         <?= $current=='admin_menu.php'        ?'class="active"':'' ?>><i class="fas fa-book-open"></i> Menu</a>
         <a href="admin_tables.php"       <?= $current=='admin_tables.php'      ?'class="active"':'' ?>><i class="fas fa-chair"></i> Tables</a>
         <a href="admin_waiting_list.php" <?= $current=='admin_waiting_list.php'?'class="active"':'' ?>><i class="fas fa-clock"></i> Waiting</a>

         
         <a href="admin_inventory.php"    <?= $current=='admin_inventory.php'   ?'class="active"':'' ?>><i class="fas fa-boxes"></i> Inventory</a>
         

         <!--  Chat dropdown --> 
         <div class="nav-drop <?= in_array($current,$chatPages)?'open':'' ?>" id="dropChat">
            <button class="nav-drop-btn <?= in_array($current,$chatPages)?'active':'' ?>" onclick="toggleDrop('dropChat')">
               <i class="fas fa-comments"></i> Chat <i class="fas fa-chevron-down caret"></i>
            </button>
            <div class="nav-drop-menu">
               <a href="table_chat.php"  class="<?= $current=='table_chat.php' ?'active':'' ?>">
                  <i class="fas fa-chair"></i> Table Chat
               </a>
               <a href="parcel_chat.php" class="<?= $current=='parcel_chat.php'?'active':'' ?>">
                  <i class="fas fa-box"></i> Parcel Chat
               </a>
            </div>
         </div>
         
         <!-- Insights dropdown -->
         <div class="nav-drop <?= in_array($current,$insightPages)?'open':'' ?>" id="dropInsights">
            <button class="nav-drop-btn <?= in_array($current,$insightPages)?'active':'' ?>" onclick="toggleDrop('dropInsights')">
               <i class="fas fa-chart-line"></i> Insights <i class="fas fa-chevron-down caret"></i>
            </button>
            <div class="nav-drop-menu">
               <a href="admin_reports.php" class="<?= $current=='admin_reports.php'?'active':'' ?>"><i class="fas fa-flag"></i> Reports</a>
               <a href="admin_reviews.php" class="<?= $current=='admin_reviews.php'?'active':'' ?>"><i class="fas fa-star"></i> Reviews</a>
               <a href="popular_menu.php"  class="<?= $current=='popular_menu.php' ?'active':'' ?>"><i class="fas fa-chart-bar"></i> Popular Menu</a>
            </div>
         </div>
            
      </nav>

      <div class="user-info">
         <span class="uname"><i class="fas fa-shield-alt"></i> <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
         <?php include 'notif_bell.php'; ?>
         <a href="admin_logout.php" class="logout-btn" onclick="return dfConfirmLogout(event,this)"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>

      <div id="menu-btn" class="fas fa-bars" onclick="toggleMobileNav()"></div>
   </div>
</header>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function dfConfirmLogout(e, el) {
   e.preventDefault();
   Swal.fire({
      icon: 'question',
      title: 'Log out?',
      text: "You'll need to sign in again to access the Admin Panel.",
      showCancelButton: true,
      confirmButtonText: 'Yes, log out',
      cancelButtonText: 'Stay signed in',
      buttonsStyling: false,
      customClass: { popup: 'dineflow-swal', confirmButton: 'dineflow-swal-confirm', cancelButton: 'dineflow-swal-cancel' }
   }).then(function(result){
      if (result.isConfirmed) { window.location.href = el.href; }
   });
   return false;
}
function toggleDrop(id) {
   const el = document.getElementById(id);
   const isOpen = el.classList.contains('open');
   document.querySelectorAll('.nav-drop').forEach(d => d.classList.remove('open'));
   if (!isOpen) el.classList.add('open');
}
function toggleMobileNav() { document.querySelector('.navbar').classList.toggle('active'); }
document.addEventListener('click', function(e) {
   if (!e.target.closest('.nav-drop')) document.querySelectorAll('.nav-drop').forEach(d => d.classList.remove('open'));
});
</script>
