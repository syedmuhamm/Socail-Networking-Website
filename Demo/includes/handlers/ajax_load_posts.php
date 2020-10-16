<?php
include("../../config/config.php"); // config.php has mysql connection variable in it.
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; // Number of posts to be loaded per call

if(isset($_GET['limit']))
    $limit = $_GET['limit']; //Number of posts to be loaded per call
$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadPostsFriends($_REQUEST, $limit);


?>


