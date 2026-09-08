<?php
include 'config.php';
session_start();
if(isset($_SESSION['waiter_id'])){
   mysqli_query($conn,"UPDATE `waiters` SET is_online=0 WHERE id='{$_SESSION['waiter_id']}'");
}
session_destroy();
header('location:waiter_login.php');
?>
