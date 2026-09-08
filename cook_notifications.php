<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }
$cook_name = $_SESSION['cook_name'];
$cook_id   = $_SESSION['cook_id'];

// Send notification
if(isset($_POST['send_notif'])){
   $msg = mysqli_real_escape_string($conn, trim($_POST['message']));
   $to  = $_POST['to_type'];
   if($msg != ''){
      mysqli_query($conn,"INSERT INTO `notifications`(from_type,from_id,from_name,to_type,message) VALUES('cook','$cook_id','$cook_name','$to','$msg')");
      $message[] = '✅ Notification sent!';
   }
}

// Mark all read
if(isset($_GET['mark_read'])){
   mysqli_query($conn,"UPDATE `notifications` SET is_read=1 WHERE to_type IN('cook','all') AND is_read=0");
   header('location:cook_notifications.php'); exit;
}

// Delete
if(isset($_POST['delete_notif'])){
   $nid = intval($_POST['notif_id']);
   mysqli_query($conn,"DELETE FROM `notifications` WHERE id='$nid'");
}

// Fetch inbox
$notifs = mysqli_query($conn,"SELECT * FROM `notifications` WHERE to_type IN('cook','all') ORDER BY is_read ASC, created_at DESC");
$unread = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `notifications` WHERE to_type IN('cook','all') AND is_read=0"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Notifications - Cook - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .notif-card { background:#fff;border:1.5px solid #e8eaf6;border-radius:1.2rem;padding:1.8rem 2rem;margin-bottom:1.2rem;display:flex;align-items:flex-start;gap:1.5rem;transition:all .3s ease;box-shadow:0 2px 8px rgba(99,102,241,0.06); }
      .notif-card.unread { border-color:#f59e0b;background:#fffbeb;box-shadow:0 4px 16px rgba(245,158,11,0.15); }
      .notif-icon { width:4.5rem;height:4.5rem;min-width:4.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.8rem; }
      .notif-icon.admin  { background:#eef2ff;color:#6366f1; }
      .notif-icon.waiter { background:#d1fae5;color:#10b981; }
      .notif-icon.cook   { background:#fef3c7;color:#f59e0b; }
      .notif-body .from { font-size:1.3rem;font-weight:700;color:#f59e0b;margin-bottom:.3rem; }
      .notif-body .msg  { font-size:1.5rem;color:#1a1a2e;font-weight:500;margin-bottom:.5rem; }
      .notif-body .time { font-size:1.2rem;color:#9ca3af; }
      .unread-dot { width:1rem;height:1rem;min-width:1rem;background:#f59e0b;border-radius:50%;margin-top:.6rem; }
   </style>
</head>
<body>
<?php include 'cook_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <h1 class="title"><i class="fas fa-bell"></i> Notifications
      <?php if($unread > 0): ?>
         <span style="background:#ef4444;color:#fff;font-size:1.4rem;padding:.3rem .9rem;border-radius:2rem;margin-left:.5rem;"><?php echo $unread; ?> New</span>
      <?php endif; ?>
   </h1>

   <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:2rem;">
      <?php if($unread > 0): ?>
         <a href="cook_notifications.php?mark_read=1" class="btn"><i class="fas fa-check-double"></i> Mark All Read</a>
      <?php endif; ?>
      <button class="btn" onclick="toggleForm('send-form')"><i class="fas fa-paper-plane"></i> Send Notification</button>
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
                     <option value="waiter">🧑‍🍽️ All Waiters</option>
                     <option value="admin">🛡️ Admin</option>
                     <option value="all">📢 Everyone</option>
                  </select>
               </div>
               <div class="form-group">
                  <label>Message</label>
                  <input type="text" name="message" required placeholder="Type your message..." maxlength="300"
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

   <!-- Quick Buttons — Cook → Waiter (food ready alerts) -->
   <div style="margin-bottom:2rem;display:flex;gap:.8rem;flex-wrap:wrap;">
      <span style="font-size:1.4rem;font-weight:600;color:#6b7280;align-self:center;">Quick Alert Waiters:</span>
      <?php
      $quick = [
         ['to'=>'waiter','msg'=>'🍽️ Food is ready! Please pick up from kitchen.',       'label'=>'Food Ready!'],
         ['to'=>'waiter','msg'=>'⚡ Parcel order is ready for pickup!',                  'label'=>'Parcel Ready!'],
         ['to'=>'waiter','msg'=>'📢 Please pick up the order from kitchen immediately!', 'label'=>'Urgent Pickup!'],
         ['to'=>'admin', 'msg'=>'⚠️ Kitchen needs attention, please check!',             'label'=>'Call Admin'],
      ];
      foreach($quick as $q){
      ?>
      <form method="post" style="display:inline;">
         <input type="hidden" name="to_type" value="<?php echo $q['to']; ?>">
         <input type="hidden" name="message" value="<?php echo htmlspecialchars($q['msg']); ?>">
         <button type="submit" name="send_notif" class="option-btn" style="margin-top:0;font-size:1.3rem;padding:.6rem 1.3rem;"><?php echo $q['label']; ?></button>
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
      $icon_map = ['admin'=>'fas fa-shield-alt','waiter'=>'fas fa-user','cook'=>'fas fa-fire'];
   ?>
   <div class="notif-card <?php echo $unread_class; ?>">
      <?php if(!$n['is_read']): ?><div class="unread-dot"></div><?php endif; ?>
      <div class="notif-icon <?php echo $n['from_type']; ?>"><i class="<?php echo $icon_map[$n['from_type']]; ?>"></i></div>
      <div class="notif-body" style="flex:1;">
         <div class="from"><?php echo htmlspecialchars($n['from_name']); ?> <span style="font-weight:400;color:#9ca3af;">(<?php echo ucfirst($n['from_type']); ?>)</span></div>
         <div class="msg"><?php echo htmlspecialchars($n['message']); ?></div>
         <div class="time"><i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($n['created_at'])); ?></div>
      </div>
      <form method="post">
         <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
         <button type="submit" name="delete_notif" style="background:none;cursor:pointer;color:#9ca3af;font-size:1.6rem;"><i class="fas fa-trash-alt"></i></button>
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
function toggleForm(id){ var f=document.getElementById(id); f.style.display=f.style.display==='none'?'block':'none'; }
setTimeout(function(){ location.reload(); }, 30000);
</script>
</body>
</html>
