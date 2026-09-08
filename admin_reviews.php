<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }

// Delete
if(isset($_POST['delete_review'])){
   $rid = intval($_POST['review_id']);
   mysqli_query($conn,"DELETE FROM `customer_reviews` WHERE id='$rid'");
   $message[] = '🗑️ Review deleted.';
}

$total_reviews = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews`"))['c'];
$avg_rating    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT ROUND(AVG(rating),1) as a FROM `customer_reviews`"))['a'] ?? 0;
$five_star     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE rating=5"))['c'];
$low_rating    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE rating<=2"))['c'];
$today_reviews = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE DATE(added_at)=CURDATE()"))['c'];
$regulars      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE visit_count>=5"))['c'];

// Rating distribution
$dist = [];
for($i=1;$i<=5;$i++){
   $dist[$i] = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE rating=$i"))['c'];
}

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where  = '';
if($filter == 'good')    $where = "WHERE rating >= 4";
if($filter == 'bad')     $where = "WHERE rating <= 2";
if($filter == 'today')   $where = "WHERE DATE(added_at) = CURDATE()";
if($filter == 'regular') $where = "WHERE visit_count >= 5";

$reviews = mysqli_query($conn,"SELECT * FROM `customer_reviews` $where ORDER BY added_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Customer Reviews - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .review-card { background:#fff;border:1.5px solid #e8eaf6;border-radius:1.4rem;padding:2rem;margin-bottom:1.5rem;box-shadow:0 2px 8px rgba(99,102,241,0.06); }
      .review-card.good { border-left:4px solid #10b981; }
      .review-card.bad  { border-left:4px solid #ef4444; }
      .stars { color:#f59e0b;font-size:1.6rem;margin-bottom:.5rem; }
      .review-text { font-size:1.5rem;color:#374151;line-height:1.7;margin-bottom:1rem;font-style:italic; }
      .meta { font-size:1.3rem;color:#9ca3af;display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center; }
      .filter-row { display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:2rem; }
      .filter-row a { padding:.6rem 1.4rem;border-radius:2rem;font-size:1.3rem;font-weight:600;background:#f3f4f6;color:#6b7280;border:1.5px solid #e5e7eb; }
      .filter-row a.active { background:#6366f1;color:#fff;border-color:#6366f1; }
      .dist-bar { display:flex;align-items:center;gap:1rem;margin-bottom:.6rem; }
      .dist-bar .label { font-size:1.3rem;color:#6b7280;min-width:5rem; }
      .dist-bar .bar-wrap { flex:1;background:#f3f4f6;border-radius:2rem;height:1rem;overflow:hidden; }
      .dist-bar .bar-fill { height:100%;background:linear-gradient(135deg,#f59e0b,#d97706);border-radius:2rem; }
      .dist-bar .count { font-size:1.3rem;color:#374151;min-width:3rem;font-weight:600; }
   </style>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-star"></i> Admin · Feedback</span><h1><i class="fas fa-star"></i> Customer Reviews</h1></div>

   <!-- Stats -->
   <div class="bento-grid cols-3">
      <div class="bento-tile"><i class="fas fa-comments"></i><h3><?php echo $total_reviews; ?></h3><p>Total Reviews</p></div>
      <div class="bento-tile" style="border-color:#f59e0b;"><i class="fas fa-star" style="color:#f59e0b;"></i><h3 style="color:#f59e0b;"><?php echo $avg_rating ?: 'N/A'; ?> ★</h3><p>Average Rating</p></div>
      <div class="bento-tile green"><i class="fas fa-calendar-day"></i><h3><?php echo $today_reviews; ?></h3><p>Today's Reviews</p></div>
   </div>
   <div class="bento-grid cols-3">
      <div class="bento-tile green"><i class="fas fa-award"></i><h3><?php echo $five_star; ?></h3><p>5 Star Reviews</p></div>
      <div class="bento-tile red"><i class="fas fa-exclamation-circle"></i><h3><?php echo $low_rating; ?></h3><p>Low Ratings (1-2★)</p></div>
      <div class="bento-tile"><i class="fas fa-redo"></i><h3><?php echo $regulars; ?></h3><p>Regular Customers</p></div>
   </div>

   <!-- Rating Distribution -->
   <?php if($total_reviews > 0): ?>
   <div class="form-container" style="margin-bottom:2rem;padding:2rem;">
      <h3 style="font-size:1.6rem;margin-bottom:1.5rem;"><i class="fas fa-chart-bar"></i> Rating Distribution</h3>
      <?php for($i=5;$i>=1;$i--):
         $pct = $total_reviews > 0 ? ($dist[$i]/$total_reviews*100) : 0;
      ?>
      <div class="dist-bar">
         <span class="label"><?php echo $i; ?> ★</span>
         <div class="bar-wrap"><div class="bar-fill" style="width:<?php echo $pct; ?>%;"></div></div>
         <span class="count"><?php echo $dist[$i]; ?></span>
      </div>
      <?php endfor; ?>
   </div>
   <?php endif; ?>

   <!-- Filter -->
   <div class="filter-row">
      <span style="font-size:1.4rem;font-weight:600;color:#6b7280;align-self:center;">Filter:</span>
      <a href="?filter=all"     class="<?php echo $filter=='all'    ?'active':''; ?>">All</a>
      <a href="?filter=good"    class="<?php echo $filter=='good'   ?'active':''; ?>">⭐ Good (4-5★)</a>
      <a href="?filter=bad"     class="<?php echo $filter=='bad'    ?'active':''; ?>">⚠️ Low (1-2★)</a>
      <a href="?filter=today"   class="<?php echo $filter=='today'  ?'active':''; ?>">📅 Today</a>
      <a href="?filter=regular" class="<?php echo $filter=='regular'?'active':''; ?>">🔁 Regulars</a>
   </div>

   <!-- Reviews List -->
   <?php if(mysqli_num_rows($reviews) == 0): ?>
      <div style="text-align:center;padding:4rem;color:#9ca3af;font-size:1.6rem;">
         <i class="fas fa-star" style="font-size:3.5rem;display:block;margin-bottom:1rem;color:#fde68a;"></i>
         No reviews found.
      </div>
   <?php else: ?>
   <?php while($r = mysqli_fetch_assoc($reviews)):
      $card_class = $r['rating'] >= 4 ? 'good' : ($r['rating'] <= 2 ? 'bad' : '');
   ?>
   <div class="review-card <?php echo $card_class; ?>">
      <div class="stars">
         <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
         <span style="font-size:1.3rem;color:#6b7280;margin-left:.5rem;"><?php echo $r['rating']; ?>/5</span>
      </div>
      <div class="review-text">"<?php echo htmlspecialchars($r['review']); ?>"</div>
      <div class="meta">
         <span><i class="fas fa-user"></i> <strong style="color:#6366f1;"><?php echo htmlspecialchars($r['customer_name']); ?></strong></span>
         <?php if($r['mobile']): ?><span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($r['mobile']); ?></span><?php endif; ?>
         <span><i class="fas fa-redo"></i>
            <?php $v=$r['visit_count']; echo $v>=10?'Regular (10+)':$v.' visit(s)'; ?>
         </span>
         <span><i class="fas fa-user-tie"></i> By: <?php echo htmlspecialchars($r['waiter_name']); ?></span>
         <span><i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($r['added_at'])); ?></span>
         <form method="post" style="display:inline;" class="swal-confirm" data-msg="Delete this review?">
            <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
            <button type="submit" name="delete_review" style="background:none;color:#ef4444;cursor:pointer;font-size:1.3rem;"><i class="fas fa-trash-alt"></i> Delete</button>
         </form>
      </div>
   </div>
   <?php endwhile; ?>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
