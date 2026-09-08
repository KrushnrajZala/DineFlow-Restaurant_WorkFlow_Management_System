<?php
include 'config.php';
session_start();

// Only admin or system can delete
$type   = $_POST['type']   ?? '';
$ref_id = intval($_POST['ref_id'] ?? 0);

if(!$type || !$ref_id){ echo json_encode(['success'=>false]); exit; }

mysqli_query($conn,"DELETE FROM `chats` WHERE type='$type' AND ref_id='$ref_id'");
echo json_encode(['success'=>true]);
