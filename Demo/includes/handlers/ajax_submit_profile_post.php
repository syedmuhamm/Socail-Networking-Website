<?php
include('../../config/config.php');
include ("../classes/User.php");
include ("../classes/Post.php");
include ("../classes/Notification.php");

if(isset($_POST['post_body'])) {

    $post = new Post($con, $_POST['user_from']); // passed form profile.php <input type="hidden" name="user_from" value="<?php echo $userLoggedIn
    $post->submitPost($_POST['post_body'], $_POST['user_to'],'');

}

?>