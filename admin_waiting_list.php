<?php
include 'config.php';
session_start();
if(!isset($_SESSION['admin_id'])){ header('location:admin_login.php'); exit; }

$total   = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM `waiting_list`"));
$total_p = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_people) as t FROM `waiting_list`"))['t'] ?? 0;
$waiting = mysqli_query($conn,"SELECT * FROM `waiting_list` ORDER BY added_at");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Waiting List - Admin - Spice Garden</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'admin_header.php'; ?>

<section>
   <div class="page-head"><span class="page-kicker"><i class="fas fa-layer-group"></i> Admin</span><h1>Waiting List</h1></div>

   <div class="bento-grid cols-3">
      <div class="stat-box" style="border-color:#f59e0b;"><i class="fas fa-users" style="color:#f59e0b;"></i><h3 style="color:#f59e0b;"><?php echo $total; ?></h3><p>Groups Waiting</p></div>
      <div class="stat-box"><i class="fas fa-user-friends"></i><h3><?php echo $total_p; ?></h3><p>Total People</p></div>
   </div>

   <?php if($total > 0): ?>
   <table class="data-table">
      <thead>
         <tr><th>#</th><th>Name</th><th>Phone</th><th>People</th><th>Note</th><th>Waiting Since</th><th>Wait Time</th></tr>
      </thead>
      <tbody>
      <?php $sr=1; while($w=mysqli_fetch_assoc($waiting)): ?>
      <tr>
         <td><?php echo $sr++; ?></td>
         <td><?php echo $w['customer_name']; ?></td>
         <td><?php echo $w['phone']; ?></td>
         <td><?php echo $w['total_people']; ?></td>
         <td><?php echo $w['note'] ?: '—'; ?></td>
         <td><?php echo date('h:i A',strtotime($w['added_at'])); ?></td>
         <td><span class="timer-badge" data-waiting-since="<?php echo $w['added_at']; ?>">...</span></td>
      </tr>
      <?php endwhile; ?>
      </tbody>
   </table>
   <?php else: ?>
   <div class="empty"><i class="fas fa-users"></i> No customers waiting right now.</div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>
