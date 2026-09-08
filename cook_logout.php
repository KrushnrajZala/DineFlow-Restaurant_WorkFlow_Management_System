<?php
session_start();
session_destroy();
header('location:cook_login.php');
?>
