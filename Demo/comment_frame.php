<?php
require 'config/config.php';
include ("includes/classes/User.php");
include ("includes/classes/Post.php");
include ("includes/classes/Notification.php");

//if user is logged in, then make that user logged in.
// Since we include it in header, so every file that accesses header will know, which user is logged in.
if (isset($_SESSION['username'])){
    $userLoggedIn = $_SESSION['username'];
    //getting user information from database
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username = '$userLoggedIn'");
    //access all data as array and put it in user
    $user = mysqli_fetch_array($user_details_query);
}
// else send user to register page
else {
    header("Location: register.php");
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link rel="stylesheet" type="text/css" href="assets/css/style.css">

</head>
    <body>

    <style>
        * {
            font-size: 12px;
            font-family: Arial, Helvetica, sans-serif;
        }
    </style>

    <script>
        function toggle() {
            var element = document.getElementById("comment_section");

            if(element.style.display == "block")
                element.style.display = "none";
            else
                element.style.display = "block";
        }
    </script>
    <?php
        //get id of post
        if (isset($_GET['post_id'])) {
            $post_id =  $_GET['post_id'];
        }

        $user_query = mysqli_query($con, "SELECT added_by, user_to FROM posts WHERE id = '$post_id' ");
        $row = mysqli_fetch_array($user_query);

        $posted_to = $row['added_by'];
        $user_to = $row['user_to'];

        // if comment is submitted
        if (isset($_POST['postComment' . $post_id] )) {
            $post_body = $_POST['post_body'];
            $post_body = mysqli_escape_string($con, $post_body);
            $date_time_now = date("Y-m-d H:i:s");
            //insert into comments table
            $insert_into = mysqli_query($con, "INSERT INTO comments (id, post_body, posted_by, posted_to, date_added, removed, post_id)
                                                     VALUES ('', '$post_body', '$userLoggedIn', '$posted_to', '$date_time_now', 'no', '$post_id')");
            // Notification
            if ($posted_to != $userLoggedIn) {
                $notification = new Notification($con, $userLoggedIn);
                $notification->insertNotification($post_id, $posted_to, "comment");
            }
            if ($user_to != 'none' && $user_to != $userLoggedIn) {
                $notification = new Notification($con, $userLoggedIn);
                $notification->insertNotification($post_id, $user_to, "profile_comment");
            }

            $get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id'");
            $row = mysqli_fetch_array($get_commenters);

            $notified_users = array();

            if ($row['posted_by'] != $posted_to && $row['posted_by'] != $user_to
                && $row['posted_by'] != $userLoggedIn && in_array($row['posted_by'], $notified_users)  ){

               $notification = new Notification($con, $userLoggedIn);
               $notification->insertNotification($post_id, $row['posted_by'], "comment_non_owner");

               array_push($notified_users, $row['posted_by']);
            }

            echo "<p> Comment Posted! </p>";
        }
    ?>
    <!-- First we made this form, and then we get the postComment through POST with $post_id -->
    <form action="comment_frame.php?post_id=<?php echo $post_id; ?>" id="comment_form" name="postComment<?php echo $post_id; ?>" method="POST">
        <textarea name="post_body"></textarea>
        <input type="submit" name="postComment<?php echo $post_id; ?>" value="Post">
    </form>

    <!-- Load comments -->
    <?php
    $get_comments = mysqli_query($con, "SELECT * FROM comments WHERE post_id='$post_id'");
    $count = mysqli_num_rows($get_comments);

    //if we have data in count, i,e from database
    if ($count != 0) {

        while ($comment = mysqli_fetch_array($get_comments)) { // until we have comments from db

            $comment_body = $comment['post_body'];
            $posted_to = $comment['posted_to'];
            $posted_by = $comment['posted_by'];
            $date_added = $comment['date_added'];
            $removed = $comment['removed'];

            //Timeframe

            $date_time_now = date("Y-m-d H:i:s ");
            $start_date = new DateTime($date_added); // time of post, using the date_added of post
            $end_date = new DateTime($date_time_now);
            $interval = $start_date->diff($end_date);

            //checking for years
            if ($interval->y >= 1) { //interval->y means number of years
                if ($interval == 1)
                    $time_message = $interval->y . " year ago"; //produced a year ago.

                else if ($interval->y > 1)
                    $time_message = $interval->y . " years ago"; //produced years ago.
            }

            else if ($interval->m >= 1) {
                //checking how many days it is.
                if ($interval->d == 0) {
                    $days = " days ago";
                } elseif ($interval->d == 1) {
                    $days = $interval->d . " day ago";
                } else {
                    $days = $interval->d . " days ago";
                }

                //checking how many months it is.

                if ($interval->m == 1) {
                    $time_message = $interval->m . " month" . $days;
                } else {
                    $time_message = $interval->m . " months" . $days;
                }
            }
            else if ($interval->d >= 1) {
                if ($interval->d == 1) {
                    $time_message = "Yesterday";
                } else {
                    $time_message = $interval->d . " days ago";
                }
            } //calculating hours
            else if ($interval->h >= 1) {
                if ($interval->h == 1) {
                    $time_message = $interval->h . " hour ago";
                } else {
                    $time_message = $interval->h . " hours ago";
                }
            }
            //calculating seconds
            else if ($interval->i >= 1) {
                if ($interval->i == 1) {
                    $time_message = $interval->i . " minute ago";
                }
                else {
                    $time_message = $interval->i . " minutes ago";
                }
            } // within a minute or not
            else {
                if ($interval->s < 30) {
                    $time_message = " Just now";
                }
                else {
                    $time_message = $interval->s . " seconds ago";
                }
            } // Timeframe ends here

            $user_obj = new User($con, $posted_by);

            //load comment
            ?>

            <div class="comment_section">
                <a href="<?php echo $posted_by;?>" target="_parent"> <img src="<?php echo $user_obj->getProfilePic(); ?>" title="<?php echo $posted_by;?>" height="30" style="float: left"></a>
                <a href="<?php echo $posted_by;?>" target="_parent"> <b><?php echo $user_obj->getFirstAndLastName(); ?></b></a>
                &nbsp;&nbsp;&nbsp;&nbsp; <?php echo $time_message. "<br>". $comment_body; ?>
                <hr>
            </div>
    <?php
        } // while loop ends here
    }
    else {
        echo "<centre><br><br>No Comments to Show!</centre>";
    }

    ?>



    </body>
</html>