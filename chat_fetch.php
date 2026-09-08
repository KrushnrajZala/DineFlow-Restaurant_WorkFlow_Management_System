<?php
include 'config.php';
session_start();

if(isset($_SESSION['admin_id'])){
   $role = 'admin'; $uid = $_SESSION['admin_id'];
} elseif(isset($_SESSION['waiter_id'])){
   $role = 'waiter'; $uid = $_SESSION['waiter_id'];
} elseif(isset($_SESSION['cook_id'])){
   $role = 'cook'; $uid = $_SESSION['cook_id'];
} else {
   echo json_encode([]); exit;
}

$type   = $_GET['type']   ?? '';
$ref_id = intval($_GET['ref_id'] ?? 0);

if(!$type || !$ref_id){ echo json_encode([]); exit; }

$res = mysqli_query($conn,"SELECT * FROM `chats` WHERE type='$type' AND ref_id='$ref_id' ORDER BY created_at ASC");
$msgs = [];
while($r = mysqli_fetch_assoc($res)){
   $r['is_me'] = ($r['role'] == $role && $r['user_id'] == $uid);
   $msgs[] = $r;
}
echo json_encode($msgs);
