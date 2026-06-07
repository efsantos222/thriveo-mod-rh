<?php
session_start();
// Redirect to main logout to clear all sessions properly
header("Location: ../../logout.php");
exit;
?>
