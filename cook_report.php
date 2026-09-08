<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }
$cid   = $_SESSION['cook_id'];
$cname = $_SESSION['cook_name'];

if(isset($_POST['submit_report'])){
   $subject = mysqli_real_escape_string($conn,$_POST['subject']);
   $msg     = mysqli_real_escape_string($conn,$_POST['message']);
   mysqli_query($conn,"INSERT INTO `reports`(sender_type,sender_id,sender_name,subject,message) VALUES('cook','$cid','$cname','$subject','$msg')");
   $message[] = 'Report submitted to admin!';
}

$my_reports = mysqli_query($conn,"SELECT * FROM `reports` WHERE sender_type='cook' AND sender_id='$cid' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Report - Cook - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'cook_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <h1 class="title">Report Issue</h1>

   <div class="form-container" style="margin-bottom:3rem;">
      <h3><i class="fas fa-flag"></i> Submit New Report</h3>
      <form method="post">
         <div class="form-group">
            <label>Your Name (auto)</label>
            <input type="text" value="<?php echo $cname; ?>" readonly style="width:100%;padding:1rem;background:#f8f9ff;color:#9ca3af;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;">
         </div>
         <div class="form-group">
            <label>Subject</label>
            <input type="text" name="subject" placeholder="Brief subject of issue" required style="width:100%;padding:1rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;padding:1.1rem 1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
         </div>
         <div class="form-group">
            <label>Message</label>
            <textarea name="message" placeholder="Describe the issue..." required style="width:100%;padding:1rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;padding:1.1rem 1.5rem;font-family:'Plus Jakarta Sans',sans-serif;min-height:12rem;resize:vertical;"></textarea>
         </div>
         <button name="submit_report" class="btn"><i class="fas fa-paper-plane"></i> Submit Report</button>
      </form>
   </div>

   <h2 style="font-size:2.2rem;color:#6366f1;margin-bottom:1.5rem;">My Reports</h2>
   <?php if(mysqli_num_rows($my_reports)>0): while($r=mysqli_fetch_assoc($my_reports)): ?>
   <div style="background:#fff;border:1px solid #e8eaf6;border-radius:1rem;padding:2rem;margin-bottom:1.5rem;<?php if($r['admin_reply']) echo 'border-color:#6366f1;'; ?>">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
         <div>
            <h4 style="font-size:1.8rem;color:#6366f1;"><?php echo $r['subject']; ?></h4>
            <p style="font-size:1.3rem;color:#9ca3af;margin-top:.3rem;"><?php echo date('d M Y h:i A',strtotime($r['created_at'])); ?></p>
         </div>
         <span class="item-status status-<?php echo $r['status']=='pending'?'waiting':($r['status']=='working'?'cooking':'complete'); ?>"><?php echo ucfirst($r['status']); ?></span>
      </div>
      <p style="font-size:1.5rem;color:#374151;margin-bottom:1rem;"><?php echo $r['message']; ?></p>
      <?php if($r['admin_reply']): ?>
      <div style="background:#eef2ff;border-left:3px solid #6366f1;padding:1rem 1.5rem;border-radius:0 .5rem .5rem 0;">
         <p style="font-size:1.3rem;color:#6366f1;font-weight:600;"><i class="fas fa-reply"></i> Admin Reply:</p>
         <p style="font-size:1.4rem;color:#1a1a2e;margin-top:.5rem;"><?php echo $r['admin_reply']; ?></p>
      </div>
      <?php else: ?>
      <p style="font-size:1.3rem;color:#9ca3af;"><i class="fas fa-clock"></i> Waiting for admin reply...</p>
      <?php endif; ?>
   </div>
   <?php endwhile; else: ?>
   <div class="empty"><i class="fas fa-inbox"></i> No reports submitted yet.</div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
