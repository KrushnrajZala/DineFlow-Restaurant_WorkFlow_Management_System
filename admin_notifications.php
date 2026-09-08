<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }
$admin_name = $_SESSION['admin_name'];
$admin_id   = $_SESSION['admin_id'];

// Send notification
if(isset($_POST['send_notif'])){
   $msg    = mysqli_real_escape_string($conn, trim($_POST['message']));
   $to     = $_POST['to_type']; // 'cook', 'waiter', 'all'
   if($msg != ''){
      mysqli_query($conn,"INSERT INTO `notifications`(from_type,from_id,from_name,to_type,message) VALUES('admin','$admin_id','$admin_name','$to','$msg')");
      $message[] = '✅ Notification sent!';
   }
}

// Mark all read for admin
if(isset($_GET['mark_read'])){
   mysqli_query($conn,"UPDATE `notifications` SET is_read=1 WHERE to_type IN('admin','all') AND is_read=0");
   header('location:admin_notifications.php'); exit;
}

// Delete notification
if(isset($_POST['delete_notif'])){
   $nid = intval($_POST['notif_id']);
   mysqli_query($conn,"DELETE FROM `notifications` WHERE id='$nid'");
   $message[] = '🗑️ Notification deleted.';
}

// Fetch notifications for admin (sent to admin or all)
$notifs = mysqli_query($conn,"SELECT * FROM `notifications` WHERE to_type IN('admin','all') ORDER BY is_read ASC, created_at DESC");
$unread = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `notifications` WHERE to_type IN('admin','all') AND is_read=0"))['c'];

// Fetch sent by admin
$sent = mysqli_query($conn,"SELECT * FROM `notifications` WHERE from_type='admin' AND from_id='$admin_id' ORDER BY created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Notifications - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .notif-card {
         background:#fff;border:1.5px solid #e8eaf6;border-radius:1.2rem;
         padding:1.8rem 2rem;margin-bottom:1.2rem;
         display:flex;align-items:flex-start;gap:1.5rem;
         transition:all .3s ease;box-shadow:0 2px 8px rgba(99,102,241,0.06);
      }
      .notif-card.unread {
         border-color:#6366f1;background:#eef2ff;
         box-shadow:0 4px 16px rgba(99,102,241,0.15);
         order:-1;
      }
      .notif-icon { width:4.5rem;height:4.5rem;min-width:4.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem; }
      .notif-icon.admin  { background:#eef2ff;color:#6366f1; }
      .notif-icon.waiter { background:#d1fae5;color:#10b981; }
      .notif-icon.cook   { background:#fef3c7;color:#f59e0b; }
      .notif-body { flex:1; }
      .notif-body .from { font-size:1.3rem;font-weight:700;color:#6366f1;margin-bottom:.3rem; }
      .notif-body .msg  { font-size:1.5rem;color:#1a1a2e;font-weight:500;margin-bottom:.5rem; }
      .notif-body .time { font-size:1.2rem;color:#9ca3af; }
      .unread-dot { width:1rem;height:1rem;min-width:1rem;background:#6366f1;border-radius:50%;margin-top:.6rem; }
   </style>
</head>
<body>
<?php include 'admin_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <h1 class="title"><i class="fas fa-bell"></i> Notifications
      <?php if($unread > 0): ?>
         <span style="background:#ef4444;color:#fff;font-size:1.4rem;padding:.3rem .9rem;border-radius:2rem;margin-left:.5rem;"><?php echo $unread; ?> New</span>
      <?php endif; ?>
   </h1>

   <div class="stats-container cols-2">
      <div class="stat-box">
         <i class="fas fa-bell"></i>
         <h3><?php echo $unread; ?></h3>
         <p>Unread Notifications</p>
      </div>
      <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
         <?php if($unread > 0): ?>
            <a href="admin_notifications.php?mark_read=1" class="btn" style="margin-top:0;"><i class="fas fa-check-double"></i> Mark All Read</a>
         <?php endif; ?>
         <button class="btn" onclick="toggleForm('send-form')" style="margin-top:0;"><i class="fas fa-paper-plane"></i> Send Notification</button>
      </div>
   </div>

   <!-- Send Form -->
   <div id="send-form" style="display:none;margin-bottom:2rem;">
      <div class="form-container">
         <h3><i class="fas fa-paper-plane"></i> Send Notification</h3>
         <form method="post" style="margin-top:1.5rem;">
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:1rem;align-items:end;">
               <div class="form-group">
                  <label>Send To</label>
                  <select name="to_type" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
                     <option value="cook">👨‍🍳 Cook</option>
                     <option value="waiter">🧑‍🍽️ All Waiters</option>
                     <option value="all">📢 Everyone</option>
                  </select>
               </div>
               <div class="form-group">
                  <label>Message</label>
                  <input type="text" name="message" required placeholder="Type your message here..." maxlength="300"
                     style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
               </div>
            </div>
            <div style="margin-top:1rem;display:flex;gap:1rem;">
               <button type="submit" name="send_notif" class="btn"><i class="fas fa-paper-plane"></i> Send</button>
               <button type="button" class="option-btn" onclick="toggleForm('send-form')"><i class="fas fa-times"></i> Cancel</button>
            </div>
         </form>
      </div>
   </div>

   <!-- Quick message buttons -->
   <div style="margin-bottom:2rem;display:flex;gap:.8rem;flex-wrap:wrap;">
      <span style="font-size:1.4rem;font-weight:600;color:#6b7280;align-self:center;">Quick Send:</span>
      <?php
      $quick = [
         ['to'=>'cook',   'msg'=>'⚠️ Please speed up the orders!',                        'label'=>'Speed Up Cook'],
         ['to'=>'cook',   'msg'=>'🍽️ Table order is waiting too long, please prioritise!', 'label'=>'Order Reminder'],
         ['to'=>'cook',   'msg'=>'📦 Parcel order is urgent, please prepare immediately!', 'label'=>'Urgent Parcel'],
         ['to'=>'cook',   'msg'=>'🔥 Please start cooking for Table — customer is waiting!','label'=>'Start Cooking'],
         ['to'=>'cook',   'msg'=>'🧂 Please check food quality before serving!',           'label'=>'Quality Check'],
         ['to'=>'waiter', 'msg'=>'📢 Please check your pending orders!',                   'label'=>'Alert Waiters'],
         ['to'=>'waiter', 'msg'=>'🍽️ Food is ready in kitchen, please pick up!',          'label'=>'Pick Up Food'],
         ['to'=>'all',    'msg'=>'✅ All orders cleared. Good job team!',                  'label'=>'All Clear'],
      ];
      foreach($quick as $q){
      ?>
      <form method="post" style="display:inline;">
         <input type="hidden" name="to_type" value="<?php echo $q['to']; ?>">
         <input type="hidden" name="message" value="<?php echo htmlspecialchars($q['msg']); ?>">
         <button type="submit" name="send_notif" class="option-btn" style="margin-top:0;font-size:1.3rem;padding:.6rem 1.3rem;">
            <?php echo $q['label']; ?>
         </button>
      </form>
      <?php } ?>
   </div>

   <!-- Inbox -->
   <h2 class="sec-heading"><i class="fas fa-inbox"></i> Inbox</h2>
   <div style="display:flex;flex-direction:column;">
   <?php
   $has = false;
   while($n = mysqli_fetch_assoc($notifs)){
      $has = true;
      $unread_class = !$n['is_read'] ? 'unread' : '';
      $icon_class   = $n['from_type'];
      $icon_map     = ['admin'=>'fas fa-shield-alt','waiter'=>'fas fa-user','cook'=>'fas fa-fire'];
      $label_map    = ['admin'=>'Admin','waiter'=>'Waiter','cook'=>'Cook'];
   ?>
   <div class="notif-card <?php echo $unread_class; ?>">
      <?php if(!$n['is_read']): ?><div class="unread-dot"></div><?php endif; ?>
      <div class="notif-icon <?php echo $icon_class; ?>"><i class="<?php echo $icon_map[$n['from_type']]; ?>"></i></div>
      <div class="notif-body">
         <div class="from"><?php echo htmlspecialchars($n['from_name']); ?> <span style="font-weight:400;color:#9ca3af;">(<?php echo ucfirst($n['from_type']); ?>)</span>
            <span style="font-weight:500;color:#6b7280;font-size:1.2rem;margin-left:.8rem;">→ To: <?php echo ucfirst($n['to_type']); ?></span>
         </div>
         <div class="msg"><?php echo htmlspecialchars($n['message']); ?></div>
         <div class="time"><i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></div>
      </div>
      <form method="post" style="display:inline;">
         <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
         <button type="submit" name="delete_notif" style="background:none;cursor:pointer;color:#9ca3af;font-size:1.6rem;" title="Delete">
            <i class="fas fa-trash-alt"></i>
         </button>
      </form>
   </div>
   <?php } ?>
   <?php if(!$has): ?>
      <div style="text-align:center;padding:4rem;color:#9ca3af;font-size:1.6rem;"><i class="fas fa-bell-slash" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>No notifications yet.</div>
   <?php endif; ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
function toggleForm(id){
   var f = document.getElementById(id);
   f.style.display = f.style.display === 'none' ? 'block' : 'none';
}
// Auto-refresh every 30 seconds
setTimeout(function(){ location.reload(); }, 30000);
</script>
</body>
</html>
