<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }

if(isset($_POST['reply_report'])){
   $id     = intval($_POST['report_id']);
   $reply  = mysqli_real_escape_string($conn,$_POST['admin_reply']);
   $status = mysqli_real_escape_string($conn,$_POST['status']);
   mysqli_query($conn,"UPDATE `reports` SET admin_reply='$reply', status='$status' WHERE id='$id'");
   $message[] = 'Reply sent!';
}

$filter = $_GET['filter'] ?? 'all';
$where  = '';
if($filter == 'waiter') $where = "WHERE sender_type='waiter'";
elseif($filter == 'cook') $where = "WHERE sender_type='cook'";

// Only show unreplied in the top count
$unreplied = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `reports` WHERE admin_reply IS NULL"));
$reports   = mysqli_query($conn,"SELECT * FROM `reports` $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Reports - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-layer-group"></i> Admin</span><h1>Staff Reports</h1></div>

   <div class="bento-grid cols-3">
      <div class="stat-box" style="border-color:#ef4444;">
         <i class="fas fa-flag" style="color:#ef4444;"></i>
         <h3 style="color:#ef4444;"><?php echo $unreplied; ?></h3>
         <p>Unreplied Reports</p>
      </div>
      <div class="stat-box">
         <i class="fas fa-list"></i>
         <h3><?php echo mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `reports`")); ?></h3>
         <p>Total Reports</p>
      </div>
   </div>

   <!-- Filter -->
   <div style="margin-bottom:2rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
      <span style="font-size:1.6rem;color:#6366f1;">Filter:</span>
      <a href="?filter=all"    class="<?php echo $filter=='all'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-list"></i> All</a>
      <a href="?filter=waiter" class="<?php echo $filter=='waiter'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-user"></i> Waiter</a>
      <a href="?filter=cook"   class="<?php echo $filter=='cook'?'btn':'option-btn'; ?>" style="margin:0;padding:.6rem 1.5rem;"><i class="fas fa-fire"></i> Cook</a>
   </div>

   <?php if(mysqli_num_rows($reports) > 0): while($r=mysqli_fetch_assoc($reports)): ?>
   <div style="background:#f8f9ff;border:1px solid <?php echo $r['admin_reply'] ? 'rgba(46,204,113,0.3)' : 'rgba(231,76,60,0.3)'; ?>;border-radius:1rem;padding:2rem;margin-bottom:1.5rem;">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
         <div>
            <h4 style="font-size:1.8rem;color:#6366f1;"><?php echo $r['subject']; ?></h4>
            <p style="font-size:1.3rem;color:#9ca3af;margin-top:.3rem;">
               <span style="color:<?php echo $r['sender_type']=='waiter'?'#6366f1':'#f59e0b'; ?>;">
                  <i class="fas fa-<?php echo $r['sender_type']=='waiter'?'user':'fire'; ?>"></i>
                  <?php echo ucfirst($r['sender_type']); ?>: <?php echo $r['sender_name']; ?>
               </span>
               &nbsp;|&nbsp; <?php echo date('d M Y h:i A',strtotime($r['created_at'])); ?>
            </p>
         </div>
         <span class="item-status status-<?php echo $r['status']=='pending'?'waiting':($r['status']=='working'?'cooking':'complete'); ?>"><?php echo ucfirst($r['status']); ?></span>
      </div>

      <p style="font-size:1.5rem;color:#1a1a2e;background:#f8f9ff;padding:1.2rem;border-radius:.5rem;margin-bottom:1.5rem;"><?php echo $r['message']; ?></p>

      <?php if($r['admin_reply']): ?>
      <div style="background:#d1fae5;border-left:3px solid #10b981;padding:1rem 1.5rem;border-radius:0 .5rem .5rem 0;margin-bottom:1rem;">
         <p style="font-size:1.3rem;color:#10b981;font-weight:600;"><i class="fas fa-reply"></i> Your Reply:</p>
         <p style="font-size:1.4rem;color:#1a1a2e;margin-top:.5rem;"><?php echo $r['admin_reply']; ?></p>
      </div>
      <?php endif; ?>

      <!-- Reply Form -->
      <button class="option-btn" style="margin-bottom:1rem;" onclick="toggleForm('reply-<?php echo $r['id']; ?>')">
         <i class="fas fa-reply"></i> <?php echo $r['admin_reply'] ? 'Edit Reply' : 'Reply'; ?>
      </button>
      <div id="reply-<?php echo $r['id']; ?>" style="display:none;">
         <form method="post">
            <input type="hidden" name="report_id" value="<?php echo $r['id']; ?>">
            <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;margin-top:1rem;">
               <textarea name="admin_reply" placeholder="Write your reply..." style="flex:3;min-width:20rem;padding:1rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.4rem;min-height:8rem;resize:vertical;font-family:'Plus Jakarta Sans',sans-serif;"><?php echo $r['admin_reply']; ?></textarea>
               <div style="flex:1;min-width:15rem;">
                  <label style="font-size:1.4rem;color:#6b7280;display:block;margin-bottom:.5rem;">Update Status</label>
                  <select name="status" style="width:100%;padding:.8rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #6366f1;border-radius:.8rem;font-size:1.4rem;margin-bottom:.8rem;cursor:pointer;">
                     <option value="pending"  <?php echo $r['status']=='pending'?'selected':''; ?>>Pending</option>
                     <option value="working"  <?php echo $r['status']=='working'?'selected':''; ?>>Working</option>
                     <option value="complete" <?php echo $r['status']=='complete'?'selected':''; ?>>Complete</option>
                  </select>
                  <button name="reply_report" class="btn" style="width:100%;margin:0;"><i class="fas fa-paper-plane"></i> Send Reply</button>
               </div>
            </div>
         </form>
      </div>
   </div>
   <?php endwhile; else: ?>
   <div class="empty"><i class="fas fa-inbox"></i> No reports found.</div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
