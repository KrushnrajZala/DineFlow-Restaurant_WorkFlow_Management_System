<?php
include 'config.php';
session_start();
if(!isset($_SESSION['waiter_id'])){ header('location:waiter_login.php'); exit; }

// Add customer
if(isset($_POST['add_customer'])){
   $name   = mysqli_real_escape_string($conn,$_POST['customer_name']);
   $phone  = mysqli_real_escape_string($conn,$_POST['phone']);
   $people = intval($_POST['total_people']);
   $note   = mysqli_real_escape_string($conn,$_POST['note'] ?? '');
   mysqli_query($conn,"INSERT INTO `waiting_list`(customer_name,phone,total_people,note) VALUES('$name','$phone','$people','$note')");
   $message[] = 'Customer added to waiting list!';
}

// Update customer
if(isset($_POST['update_customer'])){
   $id     = intval($_POST['wl_id']);
   $name   = mysqli_real_escape_string($conn,$_POST['customer_name']);
   $phone  = mysqli_real_escape_string($conn,$_POST['phone']);
   $people = intval($_POST['total_people']);
   $note   = mysqli_real_escape_string($conn,$_POST['note'] ?? '');
   mysqli_query($conn,"UPDATE `waiting_list` SET customer_name='$name',phone='$phone',total_people='$people',note='$note' WHERE id='$id'");
   $message[] = 'Customer updated!';
}

// Delete customer
if(isset($_POST['delete_customer'])){
   $id = intval($_POST['wl_id']);
   mysqli_query($conn,"DELETE FROM `waiting_list` WHERE id='$id'");
   $message[] = 'Customer removed from waiting list!';
}

// Assign table (removes from waiting)
if(isset($_POST['assign_table'])){
   $id  = intval($_POST['wl_id']);
   $tid = intval($_POST['assign_table_id']);
   mysqli_query($conn,"UPDATE `tables` SET is_available=0 WHERE id='$tid'");
   mysqli_query($conn,"DELETE FROM `waiting_list` WHERE id='$id'");
   $message[] = 'Table assigned and customer removed from waiting list!';
}

$waiting = mysqli_query($conn,"SELECT * FROM `waiting_list` ORDER BY added_at");
$total_waiting = mysqli_num_rows($waiting);
$avail_tables  = mysqli_query($conn,"SELECT * FROM `tables` WHERE is_available=1 ORDER BY table_number");
$avail_count   = mysqli_num_rows($avail_tables);
$total_people_row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total_people) as t FROM `waiting_list`"));
$total_people = $total_people_row['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Waiting Area - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <style>
      body { background: #F3F0EB !important; }

      .wl-shell {
         max-width: 1280px;
         margin: 0 auto;
         padding: 2rem 2.4rem 3.5rem;
      }

      /* Page head */
      .wl-head {
         margin-bottom: 1.6rem;
      }
      .wl-head .kicker {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
         font-size: 1.2rem;
         font-weight: 700;
         color: #A86B62;
         text-transform: uppercase;
         letter-spacing: .04em;
         margin-bottom: .35rem;
      }
      .wl-head h1 {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 2.6rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0;
         letter-spacing: -.02em;
      }
      .wl-head p {
         font-size: 1.3rem;
         color: #7A6F66;
         margin: .35rem 0 0;
      }

      /* Stat boxes */
      .wl-stats {
         display: grid;
         grid-template-columns: repeat(3, 1fr);
         gap: 1.2rem;
         margin-bottom: 1.8rem;
      }
      .wl-stat {
         background: #FFFCFA;
         border: 1px solid rgba(224,214,201,.6);
         border-radius: 14px;
         padding: 1.5rem 1.6rem;
         box-shadow: 0 2px 8px rgba(40,32,26,.04);
         display: flex;
         align-items: center;
         gap: 1.2rem;
      }
      .wl-stat-icon {
         width: 4.2rem;
         height: 4.2rem;
         border-radius: 12px;
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.7rem;
         flex-shrink: 0;
      }
      .wl-stat-icon.waiting { background: #F3EBE7; color: #A86B62; }
      .wl-stat-icon.tables { background: #E8F0E9; color: #4A6B4E; }
      .wl-stat-icon.people { background: #F3EDE2; color: #9A7B4F; }
      .wl-stat-val {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 2.4rem;
         font-weight: 700;
         color: #2A2420;
         line-height: 1.1;
      }
      .wl-stat-lbl {
         font-size: 1.25rem;
         color: #7A6F66;
         font-weight: 600;
         margin-top: .15rem;
      }

      /* Add form box */
      .wl-box {
         background: #FFFCFA;
         border: 1px solid rgba(224,214,201,.6);
         border-radius: 16px;
         box-shadow: 0 2px 10px rgba(40,32,26,.04);
         padding: 1.8rem 2rem;
         margin-bottom: 1.6rem;
      }
      .wl-box-head {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 1rem;
         margin-bottom: 1.4rem;
         flex-wrap: wrap;
      }
      .wl-box-head h2 {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 1.85rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0;
         display: flex;
         align-items: center;
         gap: .55rem;
      }
      .wl-box-head h2 i { color: #A86B62; font-size: 1.5rem; }

      .wl-btn {
         display: inline-flex;
         align-items: center;
         gap: .45rem;
         padding: .75rem 1.5rem;
         border-radius: 10px;
         font-size: 1.3rem;
         font-weight: 700;
         cursor: pointer;
         border: none;
         text-decoration: none;
         transition: .15s ease;
      }
      .wl-btn-primary {
         background: #A86B62;
         color: #fff;
         box-shadow: 0 4px 12px rgba(168,107,98,.22);
      }
      .wl-btn-primary:hover {
         background: #945A52;
         transform: translateY(-1px);
      }
      .wl-btn-outline {
         background: #FFFCFA;
         color: #A86B62;
         border: 1.5px solid #E5DDD3;
      }
      .wl-btn-outline:hover {
         background: #F8F0EC;
         border-color: #A86B62;
      }
      .wl-btn-danger {
         background: #F6E8E5;
         color: #8B342A;
         border: 1px solid rgba(139,52,42,.2);
      }
      .wl-btn-danger:hover {
         background: #8B342A;
         color: #fff;
      }
      .wl-btn-sm {
         padding: .55rem 1.1rem;
         font-size: 1.2rem;
      }

      .wl-form-grid {
         display: grid;
         grid-template-columns: 1.3fr 1.1fr .8fr 1.2fr auto;
         gap: 1rem;
         align-items: end;
      }
      .wl-field label {
         display: block;
         font-size: 1.2rem;
         font-weight: 700;
         color: #3A322C;
         margin-bottom: .35rem;
      }
      .wl-field input,
      .wl-field select {
         width: 100%;
         padding: .85rem 1rem;
         font-size: 1.35rem;
         border: 1.5px solid #E5DDD3;
         border-radius: 10px;
         background: #FFFEFC;
         color: #2A2420;
         box-sizing: border-box;
      }
      .wl-field input:focus,
      .wl-field select:focus {
         border-color: #A86B62;
         outline: none;
         box-shadow: 0 0 0 3px rgba(168,107,98,.12);
      }

      /* Section title */
      .wl-section-title {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 1.85rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0 0 1.2rem;
      }

      /* Customer cards grid */
      .wl-cards {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(32rem, 1fr));
         gap: 1.3rem;
      }
      .wl-card {
         background: #FFFCFA;
         border: 1px solid rgba(224,214,201,.6);
         border-radius: 14px;
         box-shadow: 0 2px 8px rgba(40,32,26,.04);
         padding: 1.5rem 1.6rem;
         display: flex;
         flex-direction: column;
         gap: 1rem;
         transition: .15s ease;
      }
      .wl-card:hover {
         box-shadow: 0 8px 22px rgba(40,32,26,.08);
         transform: translateY(-2px);
      }
      .wl-card-top {
         display: flex;
         justify-content: space-between;
         align-items: flex-start;
         gap: .8rem;
      }
      .wl-card-name {
         font-family: 'Source Serif 4', Georgia, serif;
         font-size: 1.75rem;
         font-weight: 700;
         color: #2A2420;
         margin: 0;
         display: flex;
         align-items: center;
         gap: .5rem;
      }
      .wl-card-num {
         font-size: 1.15rem;
         font-weight: 700;
         color: #A86B62;
         background: #F3EBE7;
         padding: .2rem .65rem;
         border-radius: 999px;
      }
      .wl-timer {
         font-size: 1.2rem;
         font-weight: 700;
         color: #9A7B4F;
         background: #F3EDE2;
         padding: .35rem .85rem;
         border-radius: 999px;
         white-space: nowrap;
      }
      .wl-card-meta {
         display: flex;
         flex-wrap: wrap;
         gap: 1rem;
         font-size: 1.3rem;
         color: #6B5F54;
      }
      .wl-card-meta span {
         display: inline-flex;
         align-items: center;
         gap: .4rem;
      }
      .wl-card-meta i { color: #A86B62; width: 1.3rem; text-align: center; }
      .wl-note {
         font-size: 1.25rem;
         color: #7A6F66;
         background: #F8F4EF;
         padding: .6rem .9rem;
         border-radius: 8px;
         border-left: 3px solid #E0D6C9;
      }
      .wl-card-actions {
         display: flex;
         flex-wrap: wrap;
         gap: .6rem;
         align-items: center;
         padding-top: .3rem;
         border-top: 1px dashed #E5DDD3;
         margin-top: .2rem;
      }
      .wl-card-actions select {
         padding: .55rem .8rem;
         font-size: 1.25rem;
         border: 1.5px solid #E5DDD3;
         border-radius: 8px;
         background: #FFFEFC;
         color: #2A2420;
         min-width: 14rem;
      }
      .wl-edit-panel {
         display: none;
         margin-top: .4rem;
         padding-top: 1rem;
         border-top: 1px solid #E5DDD3;
      }
      .wl-edit-panel.open { display: block; }
      .wl-edit-grid {
         display: grid;
         grid-template-columns: 1fr 1fr 1fr;
         gap: .8rem;
         margin-bottom: .8rem;
      }
      .wl-edit-grid input {
         width: 100%;
         padding: .7rem .9rem;
         font-size: 1.3rem;
         border: 1.5px solid #E5DDD3;
         border-radius: 8px;
         background: #FFFEFC;
         color: #2A2420;
         box-sizing: border-box;
      }

      /* Empty */
      .wl-empty {
         text-align: center;
         padding: 3.5rem 2rem;
         background: #FFFCFA;
         border: 1px dashed #E0D6C9;
         border-radius: 16px;
      }
      .wl-empty i {
         font-size: 3.4rem;
         color: #C9BDAE;
         margin-bottom: 1rem;
      }
      .wl-empty p {
         font-size: 1.5rem;
         color: #7A6F66;
         margin: 0 0 1.4rem;
      }

      @media (max-width: 900px) {
         .wl-shell { padding: 1.6rem 1.4rem 2.5rem; }
         .wl-stats { grid-template-columns: 1fr; }
         .wl-form-grid { grid-template-columns: 1fr 1fr; }
         .wl-form-grid .wl-field:last-of-type,
         .wl-form-grid .wl-btn { grid-column: 1 / -1; }
      }
      @media (max-width: 560px) {
         .wl-cards { grid-template-columns: 1fr; }
         .wl-form-grid { grid-template-columns: 1fr; }
         .wl-edit-grid { grid-template-columns: 1fr; }
         .wl-card-actions select { min-width: 100%; width: 100%; }
      }
   </style>
</head>
<body>
<?php include 'waiter_header.php'; ?>
<?php if(isset($message)) foreach($message as $msg) echo '<div class="message"><span>'.$msg.'</span><i class="fas fa-times" onclick="this.parentElement.remove()"></i></div>'; ?>

<section class="wl-shell">

   <div class="wl-head">
      <div class="kicker"><i class="fas fa-clock"></i> Waiting</div>
      <h1>Waiting Area</h1>
      <p>Manage guests waiting for a table — clear, fast, and simple.</p>
   </div>

   <!-- Stats boxes -->
   <div class="wl-stats">
      <div class="wl-stat">
         <div class="wl-stat-icon waiting"><i class="fas fa-users"></i></div>
         <div>
            <div class="wl-stat-val"><?php echo $total_waiting; ?></div>
            <div class="wl-stat-lbl">Customers Waiting</div>
         </div>
      </div>
      <div class="wl-stat">
         <div class="wl-stat-icon tables"><i class="fas fa-chair"></i></div>
         <div>
            <div class="wl-stat-val"><?php echo $avail_count; ?></div>
            <div class="wl-stat-lbl">Tables Available</div>
         </div>
      </div>
      <div class="wl-stat">
         <div class="wl-stat-icon people"><i class="fas fa-user-friends"></i></div>
         <div>
            <div class="wl-stat-val"><?php echo (int)$total_people; ?></div>
            <div class="wl-stat-lbl">Total People Waiting</div>
         </div>
      </div>
   </div>

   <!-- Add customer box -->
   <div class="wl-box">
      <div class="wl-box-head">
         <h2><i class="fas fa-user-plus"></i> Add Customer</h2>
      </div>
      <form method="post">
         <div class="wl-form-grid">
            <div class="wl-field">
               <label>Customer Name</label>
               <input type="text" name="customer_name" placeholder="Enter name" required>
            </div>
            <div class="wl-field">
               <label>Phone Number</label>
               <input type="text" name="phone" placeholder="Enter phone" required>
            </div>
            <div class="wl-field">
               <label>People</label>
               <input type="number" name="total_people" placeholder="Qty" min="1" required>
            </div>
            <div class="wl-field">
               <label>Note (optional)</label>
               <input type="text" name="note" placeholder="Any special note">
            </div>
            <button type="submit" name="add_customer" class="wl-btn wl-btn-primary">
               <i class="fas fa-plus"></i> Add
            </button>
         </div>
      </form>
   </div>

   <!-- Waiting list cards -->
   <h2 class="wl-section-title">Waiting List</h2>

   <?php if($total_waiting > 0): ?>
   <div class="wl-cards">
   <?php
   mysqli_data_seek($waiting, 0);
   $sr = 1;
   while($w = mysqli_fetch_assoc($waiting)):
   ?>
      <div class="wl-card">
         <div class="wl-card-top">
            <h3 class="wl-card-name">
               <?php echo htmlspecialchars($w['customer_name']); ?>
               <span class="wl-card-num">#<?php echo $sr++; ?></span>
            </h3>
            <span class="wl-timer timer-badge" data-added-at="<?php echo htmlspecialchars($w['added_at']); ?>">...</span>
         </div>

         <div class="wl-card-meta">
            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($w['phone']); ?></span>
            <span><i class="fas fa-users"></i> <?php echo (int)$w['total_people']; ?> people</span>
         </div>

         <?php if(!empty($w['note'])): ?>
         <div class="wl-note"><i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($w['note']); ?></div>
         <?php endif; ?>

         <div class="wl-card-actions">
            <form method="post" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;flex:1;">
               <input type="hidden" name="wl_id" value="<?php echo (int)$w['id']; ?>">
               <select name="assign_table_id" required>
                  <?php
                  $at = mysqli_query($conn,"SELECT * FROM `tables` WHERE is_available=1 ORDER BY table_number");
                  if(mysqli_num_rows($at) > 0){
                     echo '<option value="">Select table…</option>';
                     while($t = mysqli_fetch_assoc($at)){
                        echo '<option value="'.(int)$t['id'].'">Table '.$t['table_number'].' ('.$t['capacity'].' seats)</option>';
                     }
                  } else {
                     echo '<option value="">No tables available</option>';
                  }
                  ?>
               </select>
               <button type="submit" name="assign_table" class="wl-btn wl-btn-primary wl-btn-sm">
                  <i class="fas fa-check"></i> Assign
               </button>
            </form>
            <button type="button" class="wl-btn wl-btn-outline wl-btn-sm" onclick="toggleEdit(<?php echo (int)$w['id']; ?>)">
               <i class="fas fa-edit"></i> Edit
            </button>
            <form method="post" class="swal-confirm" data-msg="Remove this customer from the waiting list?">
               <input type="hidden" name="wl_id" value="<?php echo (int)$w['id']; ?>">
               <button type="submit" name="delete_customer" class="wl-btn wl-btn-danger wl-btn-sm">
                  <i class="fas fa-trash"></i>
               </button>
            </form>
         </div>

         <!-- Edit panel -->
         <div class="wl-edit-panel" id="edit-<?php echo (int)$w['id']; ?>">
            <form method="post">
               <input type="hidden" name="wl_id" value="<?php echo (int)$w['id']; ?>">
               <div class="wl-edit-grid">
                  <input type="text" name="customer_name" value="<?php echo htmlspecialchars($w['customer_name']); ?>" placeholder="Name" required>
                  <input type="text" name="phone" value="<?php echo htmlspecialchars($w['phone']); ?>" placeholder="Phone" required>
                  <input type="number" name="total_people" value="<?php echo (int)$w['total_people']; ?>" min="1" required>
               </div>
               <input type="text" name="note" value="<?php echo htmlspecialchars($w['note'] ?? ''); ?>" placeholder="Note" style="width:100%;padding:.7rem .9rem;font-size:1.3rem;border:1.5px solid #E5DDD3;border-radius:8px;background:#FFFEFC;color:#2A2420;box-sizing:border-box;margin-bottom:.8rem;">
               <button type="submit" name="update_customer" class="wl-btn wl-btn-primary wl-btn-sm">
                  <i class="fas fa-save"></i> Update
               </button>
            </form>
         </div>
      </div>
   <?php endwhile; ?>
   </div>
   <?php else: ?>
   <div class="wl-empty">
      <i class="fas fa-users"></i>
      <p>No customers waiting right now.</p>
      <p style="font-size:1.3rem;color:#94887C;">Add a guest using the form above.</p>
   </div>
   <?php endif; ?>

</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
function toggleEdit(id){
   const el = document.getElementById('edit-' + id);
   if(!el) return;
   el.classList.toggle('open');
}

// Live wait timers
function updateTimers(){
   document.querySelectorAll('.timer-badge').forEach(function(el){
      const added = el.getAttribute('data-added-at');
      if(!added) return;
      const start = new Date(added.replace(' ', 'T'));
      const now = new Date();
      let sec = Math.floor((now - start) / 1000);
      if(sec < 0) sec = 0;
      const m = Math.floor(sec / 60);
      const s = sec % 60;
      el.textContent = m + 'm ' + (s < 10 ? '0' : '') + s + 's';
   });
}
updateTimers();
setInterval(updateTimers, 1000);
</script>
</body>
</html>
