<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }
$wid   = $_SESSION['waiter_id'];
$wname = $_SESSION['waiter_name'];

// Submit new review
if(isset($_POST['add_review'])){
   $cname  = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
   $mobile = mysqli_real_escape_string($conn, trim($_POST['mobile']));
   $visits = intval($_POST['visit_count']);
   $rating = intval($_POST['rating']);
   $review = mysqli_real_escape_string($conn, trim($_POST['review']));
   if($cname != '' && $review != ''){
      mysqli_query($conn,"INSERT INTO `customer_reviews`(customer_name,mobile,visit_count,rating,review,waiter_id,waiter_name)
         VALUES('$cname','$mobile','$visits','$rating','$review','$wid','$wname')");
      $message[] = '✅ Review added successfully!';
   } else {
      $message[] = '⚠️ Please fill customer name and review.';
   }
}

// Delete review
if(isset($_POST['delete_review'])){
   $rid = intval($_POST['review_id']);
   mysqli_query($conn,"DELETE FROM `customer_reviews` WHERE id='$rid' AND waiter_id='$wid'");
   $message[] = '🗑️ Review deleted.';
}

// Stats
$total_reviews = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews`"))['c'];
$avg_rating    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT ROUND(AVG(rating),1) as a FROM `customer_reviews`"))['a'] ?? 0;
$my_reviews    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE waiter_id='$wid'"))['c'];
$five_star     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM `customer_reviews` WHERE rating=5"))['c'];

// All reviews
$reviews = mysqli_query($conn,"SELECT * FROM `customer_reviews` ORDER BY added_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Customer Reviews - Waiter - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <style>
      .review-card { background:#fff;border:1.5px solid #e8eaf6;border-radius:1.4rem;padding:2rem;margin-bottom:1.5rem;box-shadow:0 2px 8px rgba(99,102,241,0.06); }
      .review-card .stars { color:#f59e0b;font-size:1.6rem;margin-bottom:.5rem; }
      .review-card .review-text { font-size:1.5rem;color:#374151;line-height:1.7;margin-bottom:1rem;font-style:italic; }
      .review-card .meta { font-size:1.3rem;color:#9ca3af;display:flex;gap:1.5rem;flex-wrap:wrap;align-items:center; }
      .review-card .meta strong { color:#6366f1; }
      .star-input { display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:.3rem; }
      .star-input input { display:none; }
      .star-input label { font-size:2.8rem;color:#d1d5db;cursor:pointer;transition:color .2s; }
      .star-input input:checked ~ label,
      .star-input label:hover,
      .star-input label:hover ~ label { color:#f59e0b; }
      .visit-badge { background:#eef2ff;color:#6366f1;padding:.3rem .9rem;border-radius:2rem;font-size:1.2rem;font-weight:600; }
   </style>
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'waiter_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section>
   <h1 class="title"><i class="fas fa-star"></i> Customer Reviews</h1>

   <!-- Stats -->
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
         <i class="fas fa-award"></i>
         <h3><?php echo $five_star; ?></h3>
         <p>5 Star Reviews</p>
      </div>
      <div class="stat-box">
         <i class="fas fa-user"></i>
         <h3><?php echo $my_reviews; ?></h3>
         <p>My Collections</p>
      </div>
   </div>

   <!-- Add Review Button -->
   <div style="margin-bottom:2rem;">
      <button class="btn" onclick="toggleForm('add-review-form')"><i class="fas fa-plus"></i> Add Customer Review</button>
   </div>

   <!-- Add Review Form -->
   <div id="add-review-form" style="display:none;margin-bottom:2.5rem;">
      <div class="form-container">
         <h3><i class="fas fa-star"></i> Collect Customer Review</h3>
         <p style="font-size:1.4rem;color:#6b7280;margin-top:.5rem;">Ask the customer and fill in their details below.</p>
         <form method="post" style="margin-top:1.8rem;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr));gap:1.2rem;">
               <div class="form-group">
                  <label>Customer Name <span style="color:#ef4444;">*</span></label>
                  <input type="text" name="customer_name" required placeholder="e.g. Rahul Shah"
                     style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
               </div>
               <div class="form-group">
                  <label>Mobile Number</label>
                  <input type="text" name="mobile" placeholder="e.g. 9876543210" maxlength="15"
                     style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
               </div>
               <div class="form-group">
                  <label>How many times visited?</label>
                  <select name="visit_count" style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;">
                     <option value="1">1st Visit</option>
                     <option value="2">2nd Visit</option>
                     <option value="3">3rd Visit</option>
                     <option value="4">4th Visit</option>
                     <option value="5">5+ Visits</option>
                     <option value="10">10+ Visits (Regular)</option>
                  </select>
               </div>
            </div>

            <!-- Star Rating -->
            <div class="form-group" style="margin-top:1.2rem;">
               <label>Rating <span style="color:#ef4444;">*</span></label>
               <div class="star-input">
                  <input type="radio" name="rating" id="s5" value="5" checked>
                  <label for="s5" title="5 stars"><i class="fas fa-star"></i></label>
                  <input type="radio" name="rating" id="s4" value="4">
                  <label for="s4" title="4 stars"><i class="fas fa-star"></i></label>
                  <input type="radio" name="rating" id="s3" value="3">
                  <label for="s3" title="3 stars"><i class="fas fa-star"></i></label>
                  <input type="radio" name="rating" id="s2" value="2">
                  <label for="s2" title="2 stars"><i class="fas fa-star"></i></label>
                  <input type="radio" name="rating" id="s1" value="1">
                  <label for="s1" title="1 star"><i class="fas fa-star"></i></label>
               </div>
            </div>

            <!-- Review Text -->
            <div class="form-group" style="margin-top:1.2rem;">
               <label>Customer Review <span style="color:#ef4444;">*</span></label>
               <textarea name="review" required placeholder="Type what the customer said about the food and service..." rows="4"
                  style="width:100%;padding:1.1rem 1.5rem;background:#f8f9ff;color:#1a1a2e;border:1.5px solid #e8eaf6;border-radius:.9rem;font-size:1.5rem;font-family:'Plus Jakarta Sans',sans-serif;resize:vertical;"></textarea>
            </div>

            <div style="display:flex;gap:1rem;margin-top:1rem;">
               <button type="submit" name="add_review" class="btn"><i class="fas fa-save"></i> Save Review</button>
               <button type="button" class="option-btn" onclick="toggleForm('add-review-form')"><i class="fas fa-times"></i> Cancel</button>
            </div>
         </form>
      </div>
   </div>

   <!-- All Reviews -->
   <h2 class="sec-heading"><i class="fas fa-comments"></i> All Customer Reviews
      <span style="font-size:1.3rem;font-weight:500;color:#9ca3af;margin-left:.5rem;">(Show these to new customers!)</span>
   </h2>

   <?php if(mysqli_num_rows($reviews) == 0): ?>
      <div style="text-align:center;padding:4rem;color:#9ca3af;font-size:1.6rem;">
         <i class="fas fa-star" style="font-size:3.5rem;display:block;margin-bottom:1rem;color:#fde68a;"></i>
         No reviews yet. Be the first to collect one!
      </div>
   <?php else: ?>
   <?php while($r = mysqli_fetch_assoc($reviews)): ?>
   <div class="review-card">
      <div class="stars">
         <?php for($i=1;$i<=5;$i++) echo $i<=$r['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
         <span style="font-size:1.3rem;color:#6b7280;margin-left:.5rem;"><?php echo $r['rating']; ?>/5</span>
      </div>
      <div class="review-text">"<?php echo htmlspecialchars($r['review']); ?>"</div>
      <div class="meta">
         <span><i class="fas fa-user"></i> <strong><?php echo htmlspecialchars($r['customer_name']); ?></strong></span>
         <?php if($r['mobile']): ?>
         <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($r['mobile']); ?></span>
         <?php endif; ?>
         <span class="visit-badge"><i class="fas fa-redo"></i>
            <?php
            $v = $r['visit_count'];
            echo $v >= 10 ? '10+ Visits (Regular)' : ($v.''.($v==1?'st':($v==2?'nd':($v==3?'rd':'th'))).' Visit');
            ?>
         </span>
         <span><i class="fas fa-user-tie"></i> Collected by: <?php echo htmlspecialchars($r['waiter_name']); ?></span>
         <span><i class="fas fa-clock"></i> <?php echo date('d M Y, h:i A', strtotime($r['added_at'])); ?></span>
         <?php if($r['waiter_id'] == $wid): ?>
         <form method="post" style="display:inline;" class="swal-confirm" data-msg="Delete this review?">
            <input type="hidden" name="review_id" value="<?php echo $r['id']; ?>">
            <button type="submit" name="delete_review" style="background:none;color:#ef4444;cursor:pointer;font-size:1.3rem;"><i class="fas fa-trash-alt"></i></button>
         </form>
         <?php endif; ?>
      </div>
   </div>
   <?php endwhile; ?>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
function toggleForm(id){ var f=document.getElementById(id); f.style.display=f.style.display==='none'?'block':'none'; }
</script>
</body>
</html>
