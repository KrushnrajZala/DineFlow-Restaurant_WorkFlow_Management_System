<?php
// notif_bell.php — included in all 3 headers
// Detects role from session, fetches unread notifications for that role

$_bell_role    = '';
$_bell_to_type = [];
if(isset($_SESSION['admin_id'])){
   $_bell_role    = 'admin';
   $_bell_id      = $_SESSION['admin_id'];
   $_bell_name    = $_SESSION['admin_name'];
   $_bell_to_type = ['admin','all'];
   $_bell_notif_page = 'admin_notifications.php';
} elseif(isset($_SESSION['waiter_id'])){
   $_bell_role    = 'waiter';
   $_bell_id      = $_SESSION['waiter_id'];
   $_bell_name    = $_SESSION['waiter_name'];
   $_bell_to_type = ['waiter','all'];
   $_bell_notif_page = 'waiter_notifications.php';
} elseif(isset($_SESSION['cook_id'])){
   $_bell_role    = 'cook';
   $_bell_id      = $_SESSION['cook_id'];
   $_bell_name    = $_SESSION['cook_name'];
   $_bell_to_type = ['cook','all'];
   $_bell_notif_page = 'cook_notifications.php';
}

if($_bell_role != ''){
   $in_list  = "'" . implode("','", $_bell_to_type) . "'";
   $_bell_unread = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `notifications` WHERE to_type IN($in_list) AND is_read=0"))['c'];
   $_bell_recent = mysqli_query($conn,"SELECT * FROM `notifications` WHERE to_type IN($in_list) ORDER BY is_read ASC, created_at DESC LIMIT 8");
}
?>
<style>
.bell-wrap { position:relative;display:inline-flex;align-items:center; }
.bell-btn  {
   position:relative;background:none;cursor:pointer;
   font-size:2rem;color:var(--text-muted);padding:.5rem;
   border-radius:.8rem;display:flex;align-items:center;justify-content:center;
   transition:all .2s;
}
.bell-btn:hover { background:var(--primary-light);color:var(--primary); }
.bell-count {
   position:absolute;top:-.2rem;right:-.2rem;
   background:#ef4444;color:#fff;
   font-size:1rem;font-weight:800;
   min-width:1.8rem;height:1.8rem;
   border-radius:2rem;display:flex;align-items:center;justify-content:center;
   padding:0 .3rem;border:2px solid #fff;
   animation: pulse-bell 2s infinite;
}
@keyframes pulse-bell {
   0%,100%{ transform:scale(1); }
   50%{ transform:scale(1.15); }
}
.bell-dropdown {
   display:none;position:absolute;top:calc(100% + .8rem);right:0;
   background:#fff;border:1.5px solid var(--border);
   border-radius:1.4rem;box-shadow:0 12px 40px rgba(99,102,241,0.18);
   width:34rem;z-index:9999;overflow:hidden;
}
.bell-dropdown.open { display:block; }
.bell-dropdown .bd-header {
   padding:1.4rem 1.8rem;border-bottom:1px solid var(--border);
   display:flex;align-items:center;justify-content:space-between;
}
.bell-dropdown .bd-header span { font-size:1.5rem;font-weight:700;color:var(--text); }
.bell-dropdown .bd-header a   { font-size:1.2rem;color:var(--primary);font-weight:600; }
.bell-dropdown .bd-list { max-height:36rem;overflow-y:auto; }
.bell-dropdown .bd-item {
   padding:1.2rem 1.8rem;border-bottom:1px solid var(--border);
   display:flex;align-items:flex-start;gap:1rem;
   transition:background .15s;cursor:default;
}
.bell-dropdown .bd-item:hover  { background:var(--bg); }
.bell-dropdown .bd-item.unread { background:#eef2ff; }
.bell-dropdown .bd-icon {
   width:3.4rem;height:3.4rem;min-width:3.4rem;
   border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.4rem;
}
.bell-dropdown .bd-icon.admin  { background:#eef2ff;color:#6366f1; }
.bell-dropdown .bd-icon.waiter { background:#d1fae5;color:#10b981; }
.bell-dropdown .bd-icon.cook   { background:#fef3c7;color:#f59e0b; }
.bell-dropdown .bd-body { flex:1;min-width:0; }
.bell-dropdown .bd-from { font-size:1.2rem;font-weight:700;color:var(--primary);margin-bottom:.2rem; }
.bell-dropdown .bd-msg  { font-size:1.3rem;color:var(--text-2);line-height:1.5;word-break:break-word;margin-bottom:.3rem; }
.bell-dropdown .bd-time { font-size:1.1rem;color:var(--text-light); }
.bell-dropdown .bd-empty { padding:2.5rem;text-align:center;color:var(--text-light);font-size:1.4rem; }
.bell-dropdown .bd-footer {
   padding:1.1rem 1.8rem;border-top:1px solid var(--border);
   text-align:center;
}
.bell-dropdown .bd-footer a { font-size:1.3rem;font-weight:600;color:var(--primary); }
</style>

<?php if($_bell_role != ''): ?>
<div class="bell-wrap" id="bell-wrap">
   <button class="bell-btn" id="bell-btn" onclick="toggleBell()" title="Notifications">
      <i class="fas fa-bell"></i>
      <?php if($_bell_unread > 0): ?>
         <span class="bell-count"><?php echo $_bell_unread > 9 ? '9+' : $_bell_unread; ?></span>
      <?php endif; ?>
   </button>

   <div class="bell-dropdown" id="bell-dropdown">
      <div class="bd-header">
         <span><i class="fas fa-bell"></i> Notifications <?php if($_bell_unread > 0) echo '<span style="background:#ef4444;color:#fff;font-size:1rem;padding:.1rem .5rem;border-radius:2rem;margin-left:.4rem;">'.$_bell_unread.'</span>'; ?></span>
         <a href="<?php echo $_bell_notif_page; ?>?mark_read=1">Mark all read</a>
      </div>
      <div class="bd-list">
         <?php
         $icon_map  = ['admin'=>'fas fa-shield-alt','waiter'=>'fas fa-user','cook'=>'fas fa-fire'];
         $label_map = ['admin'=>'Admin','waiter'=>'Waiter','cook'=>'Cook'];
         $has_notif = false;
         if(isset($_bell_recent)){
            while($n = mysqli_fetch_assoc($_bell_recent)){
               $has_notif = true;
               $unread_class = !$n['is_read'] ? 'unread' : '';
               $icon_class   = $n['from_type'];

               // Time ago
               $diff = time() - strtotime($n['created_at']);
               if($diff < 60)       $time_ago = 'Just now';
               elseif($diff < 3600) $time_ago = floor($diff/60).' min ago';
               elseif($diff < 86400)$time_ago = floor($diff/3600).' hr ago';
               else                  $time_ago = date('d M', strtotime($n['created_at']));
         ?>
         <div class="bd-item <?php echo $unread_class; ?>">
            <div class="bd-icon <?php echo $icon_class; ?>">
               <i class="<?php echo $icon_map[$n['from_type']]; ?>"></i>
            </div>
            <div class="bd-body">
               <div class="bd-from">
                  <?php echo htmlspecialchars($n['from_name']); ?>
                  <span style="font-weight:400;color:#9ca3af;">(<?php echo $label_map[$n['from_type']]; ?>)</span>
               </div>
               <div class="bd-msg"><?php echo htmlspecialchars($n['message']); ?></div>
               <div class="bd-time"><i class="fas fa-clock"></i> <?php echo $time_ago; ?></div>
            </div>
         </div>
         <?php
            }
         }
         if(!$has_notif){
            echo '<div class="bd-empty"><i class="fas fa-bell-slash" style="font-size:2.5rem;display:block;margin-bottom:.8rem;color:#d1d5db;"></i>No notifications</div>';
         }
         ?>
      </div>
      <div class="bd-footer">
         <a href="<?php echo $_bell_notif_page; ?>"><i class="fas fa-list"></i> View All Notifications →</a>
      </div>
   </div>
</div>

<script>
function toggleBell(){
   var d = document.getElementById('bell-dropdown');
   d.classList.toggle('open');
}
// Close when clicking outside
document.addEventListener('click', function(e){
   var wrap = document.getElementById('bell-wrap');
   if(wrap && !wrap.contains(e.target)){
      var d = document.getElementById('bell-dropdown');
      if(d) d.classList.remove('open');
   }
});
</script>
<?php endif; ?>
