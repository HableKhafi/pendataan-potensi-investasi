<?php
session_start();
session_unset();
session_destroy();

// Redirect ke login.php di folder yang sama
header("Location: login.php");
exit;
?>