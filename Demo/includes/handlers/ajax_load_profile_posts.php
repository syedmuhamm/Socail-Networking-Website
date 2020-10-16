<?php
include("../../config/config.php"); // config.php has mysql connection variable in it.
include("../classes/User.php");
include("../classes/Post.php");

$limit = 10; // Number of posts to be loaded per call

$posts = new Post($con, $_REQUEST['userLoggedIn']);
$posts->loadProfilePosts($_REQUEST, $limit);
// This AJAX script is added in index.php as <script>


?>