<?php
session_start();
session_destroy();
header("Location: ../../register.php"); //We have to go back to folder from logout.php to register.php
?>
