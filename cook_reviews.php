<?php
include 'config.php';
session_start();
if(!isset($_SESSION['cook_id'])){ header('location:cook_login.php'); exit; }

$total_reviews = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews`"))['c'];
$avg_rating    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT ROUND(AVG(rating),1) as a FROM `customer_reviews`"))['a'] ?? 0;
$five_star     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE rating=5"))['c'];
$low_rating    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE rating<=2"))['c'];

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where  = '';
if($filter == 'good')  $where = "WHERE rating >= 4";
if($filter == 'bad')   $where = "WHERE rating <= 2";
if($filter == 'today') $where = "WHERE DATE(added_at) = CURDATE()";

$reviews = mysqli_query($conn,"SELECT * FROM `customer_reviews` $where ORDER BY added_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Customer Reviews - Cook - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .review-card { background:#fff;border:1.5px solid #e8eaf6;border-radius:1.4rem;padding:2rem;margin-bottom:1.5rem;box-shadow:0 2px 8px rgba(99,102,241,0.06); }
      .review-card.good { border-left:4px solid #10b981; }
      .review-card.bad  { border-left:4px solid #ef4444; }
      .review-card .stars { color:#f59e0b;font-size:1.6rem;margin-bottom:.5rem; }
      .review-card .review-text { font-size:1.5rem;color:#374151;line-height:1.7;margin-bottom:1rem;font-style:italic; }
      .review-card .meta { font-size:1.3rem;color:#9ca3af;display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center; }
      .filter-row { display:flex;gap:.8rem;flex-wrap:wrap;margin-bottom:2rem; }
      .filter-row a { padding:.6rem 1.4rem;border-radius:2rem;font-size:1.3rem;font-weight:600;background:#f3f4f6;color:#6b7280;border:1.5px solid #e5e7eb; }
      .filter-row a.active { background:#6366f1;color:#fff;border-color:#6366f1; }
   </style>
</head>
<body>
<?php include 'cook_header.php'; ?>

<section>
   <h1 class="title"><i class="fas fa-star"></i> Customer Reviews
      <span style="font-size:1.4rem;font-weight:500;color:#9ca3af;margin-left:.5rem;">— See what customers say about your food!</span>
   </h1>

   <div class="stats-container cols-4">
      <div class="stat-box">
         <i class="fas fa-comments"></i>
         <h3><?php echo $total_reviews; ?></h3>
         <p>Total Reviews</p>
      </div>
      <div class="stat-box" style="border-color:#f59e0b;">
         <i class="fas fa-star" style="color:#f59e0b;"></i>
         <h3 style="color:#f59e0b;"><?php echo $avg_rating ?: 'N/A'; ?></h3>
         <p>Average Rating</p>
      </div>
      <div class="stat-box green">
         <i class="fas fa-thumbs-up"></i>
         <h3><?php echo $five_star; ?></h3>
         <p>5 Star Reviews</p>
      </div>
      <div class="stat-box red">
         <i class="fas fa-thumbs-down"></i>
         <h3><?php echo $low_rating; ?></h3>
         <p>Low Ratings (1-2★)</p>
      </div>
   </div>

   <!-- Filter -->
   <div class="filter-row">
      <span style="font-size:1.4rem;font-weight:600;color:#6b7280;align-self:center;">Filter:</span>
      <a href="?filter=all"   class="<?php echo $filter=='all'  ?'active':''; ?>">All Reviews</a>
      <a href="?filter=good"  class="<?php echo $filter=='good' ?'active':''; ?>">⭐ Good (4-5★)</a>
      <a href="?filter=bad"   class="<?php echo $filter=='bad'  ?'active':''; ?>">⚠️ Low (1-2★)</a>
      <a href="?filter=today" class="<?php echo $filter=='today'?'active':''; ?>">📅 Today</a>
   </div>

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
         <?php if($r['rating']==5): ?><span style="margin-left:1rem;font-size:1.3rem;background:#d1fae5;color:#065f46;padding:.2rem .8rem;border-radius:2rem;font-weight:600;">🎉 Excellent!</span><?php endif; ?>
         <?php if($r['rating']<=2): ?><span style="margin-left:1rem;font-size:1.3rem;background:#fee2e2;color:#991b1b;padding:.2rem .8rem;border-radius:2rem;font-weight:600;">⚠️ Needs Improvement</span><?php endif; ?>
      </div>
      <div class="review-text">"<?php echo htmlspecialchars($r['review']); ?>"</div>
      <div class="meta">
         <span><i class="fas fa-user"></i> <strong style="color:#6366f1;"><?php echo htmlspecialchars($r['customer_name']); ?></strong></span>
         <?php if($r['mobile']): ?><span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($r['mobile']); ?></span><?php endif; ?>
         <span><i class="fas fa-redo"></i>
            <?php $v=$r['visit_count']; echo $v>=10?'Regular Customer':$v.' visit(s)'; ?>
         </span>
         <span><i class="fas fa-user-tie"></i> Waiter: <?php echo htmlspecialchars($r['waiter_name']); ?></span>
         <span><i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($r['added_at'])); ?></span>
      </div>
   </div>
   <?php endwhile; ?>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
