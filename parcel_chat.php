<?php
include 'config.php';
session_start();

if(isset($_SESSION['admin_id'])){
   $role = 'admin'; $uid = $_SESSION['admin_id']; $uname = $_SESSION['admin_name'];
} elseif(isset($_SESSION['waiter_id'])){
   $role = 'waiter'; $uid = $_SESSION['waiter_id']; $uname = $_SESSION['waiter_name'];
} elseif(isset($_SESSION['cook_id'])){
   $role = 'cook'; $uid = $_SESSION['cook_id']; $uname = $_SESSION['cook_name'];
} else {
   header('location:index.php'); exit;
}

$parcels = mysqli_query($conn,"SELECT * FROM `parcels` WHERE is_active=1 ORDER BY parcel_number DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Parcel Chat - DineFlow</title>
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
if($role=='admin') include 'admin_header.php';
elseif($role=='waiter') include 'waiter_header.php';
else include 'cook_header.php';
?>

<section>
   <div class="page-head">
      <span class="page-kicker"><i class="fas fa-box"></i> Parcel Chat</span>
      <h1>Parcel conversations</h1>
      <p class="page-date">Click a parcel to see full order and chat. Collapsed shows total only.</p>
   </div>

   <?php if(mysqli_num_rows($parcels)==0): ?>
   <div class="empty"><i class="fas fa-box-open"></i><p>No active parcels right now.</p></div>
   <?php else: ?>
   <div class="chat-grid">
   <?php while($p = mysqli_fetch_assoc($parcels)):
      $pnum = (int)$p['parcel_number'];
      $orders = mysqli_query($conn,"SELECT * FROM `parcel_orders` WHERE parcel_number='$pnum' ORDER BY added_at");
      $cnt = mysqli_num_rows($orders);
      $tot_r = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(price*quantity) as t FROM `parcel_orders` WHERE parcel_number='$pnum'"));
      $total = $tot_r['t'] ?? 0;
   ?>
   <div class="order-chat-card" id="pchat-<?php echo $pnum; ?>">
      <div class="order-chat-head" onclick="toggleOrderChat('pchat-<?php echo $pnum; ?>')">
         <div>
            <h3><i class="fas fa-box"></i> Parcel #<?php echo $pnum; ?></h3>
            <p class="och-meta">
               <?php if(!empty($p['customer_name'])): ?><?php echo htmlspecialchars($p['customer_name']); ?> · <?php endif; ?>
               <?php echo $cnt; ?> items · <strong>₹<?php echo number_format($total,2); ?></strong>
            </p>
         </div>
         <i class="fas fa-chevron-down och-caret"></i>
      </div>

      <div class="order-chat-body">
         <div class="och-orders">
            <h4><i class="fas fa-utensils"></i> Order items</h4>
            <?php if($cnt > 0):
               mysqli_data_seek($orders, 0);
               while($o = mysqli_fetch_assoc($orders)): ?>
            <div class="item-row">
               <span class="item-name"><?php echo htmlspecialchars($o['item_name']); ?> ×<?php echo (int)$o['quantity']; ?></span>
               <span class="badge status-<?php echo $o['status'] ?? 'pending'; ?>"><?php echo ucfirst($o['status'] ?? 'pending'); ?></span>
               <span class="item-price">₹<?php echo number_format($o['price']*$o['quantity'],2); ?></span>
            </div>
            <?php endwhile; else: ?>
            <p class="och-empty">No items yet</p>
            <?php endif; ?>
         </div>

         <div class="och-chat">
            <h4><i class="fas fa-comments"></i> Messages</h4>
            <div class="chat-messages" id="pmsgs-<?php echo $pnum; ?>">
               <div class="no-msgs"><i class="fas fa-comment-slash"></i> Loading…</div>
            </div>
            <div class="chat-input-area">
               <input type="text" id="pinp-<?php echo $pnum; ?>" placeholder="Type a message…"
                      onkeypress="if(event.key==='Enter') sendPMsg(<?php echo $pnum; ?>)">
               <button type="button" onclick="sendPMsg(<?php echo $pnum; ?>)"><i class="fas fa-paper-plane"></i></button>
            </div>
         </div>
      </div>
   </div>
   <?php endwhile; ?>
   </div>
   <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script>
const MY_ROLE = '<?php echo $role; ?>';
const MY_ID = '<?php echo $uid; ?>';
const MY_NAME = <?php echo json_encode($uname); ?>;

function toggleOrderChat(id) {
  var el = document.getElementById(id);
  if (!el) return;
  el.classList.toggle('open');
  if (el.classList.contains('open')) {
    var pid = id.replace('pchat-','');
    loadPMsgs(pid);
  }
}

function escHtml(s) {
  var d = document.createElement('div');
  d.textContent = s;
  return d.innerHTML;
}

function loadPMsgs(pid) {
  fetch('chat_fetch.php?type=parcel&ref_id=' + pid)
    .then(function(r){ return r.json(); })
    .then(function(msgs){
      var box = document.getElementById('pmsgs-' + pid);
      if (!box) return;
      if (!msgs || msgs.length === 0) {
        box.innerHTML = '<div class="no-msgs"><i class="fas fa-comment-slash"></i> No messages yet</div>';
        return;
      }
      box.innerHTML = '';
      msgs.forEach(function(m){
        var isMe = (m.role === MY_ROLE && String(m.user_id) === String(MY_ID));
        var time = new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        var wrap = document.createElement('div');
        wrap.className = 'msg-wrap ' + (isMe ? 'me' : 'them');
        wrap.innerHTML = '<div class="msg-name">' + escHtml(m.user_name) + ' · ' + escHtml(m.role) + '</div>'
          + '<div class="msg-bubble">' + escHtml(m.message) + '</div>'
          + '<div class="msg-time">' + time + '</div>';
        box.appendChild(wrap);
      });
      box.scrollTop = box.scrollHeight;
    })
    .catch(function(){
      var box = document.getElementById('pmsgs-' + pid);
      if (box) box.innerHTML = '<div class="no-msgs">Could not load messages</div>';
    });
}

function sendPMsg(pid) {
  var inp = document.getElementById('pinp-' + pid);
  var msg = (inp.value || '').trim();
  if (!msg) return;
  var fd = new FormData();
  fd.append('type', 'parcel');
  fd.append('ref_id', pid);
  fd.append('message', msg);
  fetch('chat_send.php', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(){
      inp.value = '';
      loadPMsgs(pid);
    })
    .catch(function(){
      if (typeof Swal !== 'undefined') Swal.fire({icon:'error', title:'Send failed', text:'Try again'});
    });
}
</script>
</body>
</html>
