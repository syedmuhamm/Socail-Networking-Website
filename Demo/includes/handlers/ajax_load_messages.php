<?php
include ("../../config/config.php");
include ("../classes/User.php");
include ("../classes/Post.php");
include ("../classes/Message.php");

$limit = 20;
// $_REQUEST['userLoggedIn'] is passed from AJAX call data: "page=1&user=" + user, in demo.js
$message = new Message($con, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropDown($_REQUEST['userLoggedIn'], $limit);

?>