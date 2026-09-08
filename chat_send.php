<?php
include 'config.php';
session_start();

// Detect role
if(isset($_SESSION['admin_id'])){
   $role = 'admin'; $uid = $_SESSION['admin_id']; $uname = $_SESSION['admin_name'];
} elseif(isset($_SESSION['waiter_id'])){
   $role = 'waiter'; $uid = $_SESSION['waiter_id']; $uname = $_SESSION['waiter_name'];
} elseif(isset($_SESSION['cook_id'])){
   $role = 'cook'; $uid = $_SESSION['cook_id']; $uname = $_SESSION['cook_name'];
} else {
   echo json_encode(['success'=>false,'msg'=>'Not logged in']); exit;
}

$type    = $_POST['type']    ?? ''; // 'table' or 'parcel'
$ref_id  = intval($_POST['ref_id'] ?? 0); // table_id or parcel_id
$message = trim($_POST['message'] ?? '');

if(!$type || !$ref_id || $message === ''){
   echo json_encode(['success'=>false,'msg'=>'Missing data']); exit;
}

$message = mysqli_real_escape_string($conn, $message);
$uname   = mysqli_real_escape_string($conn, $uname);

mysqli_query($conn,"INSERT INTO `chats`(type,ref_id,role,user_id,user_name,message)
                    VALUES('$type','$ref_id','$role','$uid','$uname','$message')");

echo json_encode(['success'=>true]);
