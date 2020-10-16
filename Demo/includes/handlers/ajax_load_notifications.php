<?php
include ("../../config/config.php");
include ("../classes/User.php");
include ("../classes/Post.php");
include ("../classes/Notification.php");

$limit = 20;
// $_REQUEST['userLoggedIn'] is passed from AJAX call data: "page=1&user=" + user, in demo.js
$notification = new Notification($con, $_REQUEST['userLoggedIn']);
echo $notification->getNotification($_REQUEST['userLoggedIn'], $limit);

?>