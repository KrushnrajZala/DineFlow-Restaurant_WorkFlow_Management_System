<?php
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }
$current = basename($_SERVER['PHP_SELF']);

$chatPages    = ['table_chat.php','parcel_chat.php'];
$insightPages = ['cook_report.php','cook_reviews.php','popular_menu.php'];
?>

<header class="header">
   <div class="flex">

      <a href="cook_dashboard.php" class="logo">
         <img src="images/logo.png" alt="DineFlow" class="brand-mark">
         <span class="brand-role">Cook</span>
      </a>

      <nav class="navbar" id="navbar">

         <a href="cook_dashboard.php" <?= $current=='cook_dashboard.php'?'class="active"':'' ?>>
            <i class="fas fa-tachometer-alt"></i> Dashboard
         </a>
         <a href="cook_tables.php" <?= $current=='cook_tables.php'?'class="active"':'' ?>><i class="fas fa-chair"></i> Table Orders</a>
         <a href="cook_parcel.php" <?= $current=='cook_parcel.php'?'class="active"':'' ?>><i class="fas fa-box"></i> Parcel Orders</a>
         <a href="cook_menu.php"   <?= $current=='cook_menu.php'  ?'class="active"':'' ?>><i class="fas fa-book-open"></i> Menu</a>

         <!-- Chat dropdown -->
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
               <a href="cook_report.php"  class="<?= $current=='cook_report.php' ?'active':'' ?>"><i class="fas fa-flag"></i> Report</a>
               <a href="cook_reviews.php" class="<?= $current=='cook_reviews.php'?'active':'' ?>"><i class="fas fa-star"></i> Reviews</a>
               <a href="popular_menu.php" class="<?= $current=='popular_menu.php'?'active':'' ?>"><i class="fas fa-chart-bar"></i> Popular Menu</a>
            </div>
         </div>
            
      </nav>

      <div class="user-info">
         <span class="uname"><i class="fas fa-fire"></i> <?php echo htmlspecialchars($_SESSION['cook_name']); ?></span>
         <?php include 'notif_bell.php'; ?>
         <a href="cook_logout.php" class="logout-btn" onclick="return dfConfirmLogout(event,this)"><i class="fas fa-sign-out-alt"></i> Logout</a>
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
      text: "You'll need to sign in again to access the Kitchen dashboard.",
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
