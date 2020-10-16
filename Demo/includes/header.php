<?php
require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");
include("includes/classes/Notification.php");


?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet"href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous"></script>

<?php

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

    <title>Swirl feed</title>

    <!-- CSS -->
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />

    <!-- Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/bootbox.min.js"></script>
    <script src="assets/js/demo.js"></script>
    <script src="assets/js/jquery.jcrop.js"></script>
    <script src="assets/js/jcrop_bits.js"></script>
    
</head>
<body>

    <div class="top_bar">

        <div class="logo">
            <a href="index.php">SwirlFeed!</a>
        </div>

        <div class="search">
            <form action="search.php" method="GET" name="search_form">
                <input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $userLoggedIn; ?>')" name="q" placeholder="Search.." autocomplete="no" id="search_text_input">

                <div class="button_holder">
                    <img src="assets/images/icons/magnifying-glass.png">
                </div>
            </form>

            <div class="search_results">

            </div>

            <div class="search_results_footer_empty">

            </div>

        </div>

       <nav>

           <?php
                //unread messages
                $messages = new Message($con, $userLoggedIn);
                $num_messages = $messages->getUnreadNumber();

                //unread notifications
                $notifications = new Notification($con, $userLoggedIn);
                $num_notifications = $notifications->getUnreadNumber();

                //unread friend requests
                $user_obj = new User($con, $userLoggedIn);
                $num_requests = $user_obj->getNumberOfFriendRequests();
           ?>
            <a href="<?php echo $userLoggedIn;?>">
                <?php
                    echo $user['first_name'];
                ?>
            </a>
            <a href="index.php">
                <i class="fa fa-home fa-lg"></i>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'message')"> <!-- 'message is the value passed to demo.js to the method getDropdownData defined also in demo.js ' -->
                <i class="fa fa-envelope fa-lg"></i>
                <?php
                if($num_messages > 0)
                    echo '<span class="notification_badge" id="unread_message">'. $num_messages .'</span>';
                ?>
            </a>
            <a href="javascript:void(0);" onclick="getDropdownData('<?php echo $userLoggedIn; ?>', 'notification')">
                <i class="fa fa-bell-o fa-lg"></i>
                <?php
                if($num_notifications > 0)
                    echo '<span class="notification_badge" id="unread_notification">'. $num_notifications .'</span>'; // id is used in demo.js in getDropdownData() method
                ?>
            </a>
            <a href="requests.php">
                <i class="fa fa-users fa-lg"></i>
                <?php
                if($num_requests > 0)
                    echo '<span class="notification_badge" id="unread_requests">'. $num_requests .'</span>'; // id is used in demo.js in getDropdownData() method
                ?>
            </a>
            <a href="settings.php">
                <i class="fa fa-cog fa-lg"></i>
            </a>
            <a href="includes/handlers/logout.php">
                <i class="fa fa-sign-out fa-lg"></i>
            </a>
        </nav>

        <!-- Creating div for messages drop down -->
        <div class="dropdown_data_window" style="height: 0; border: none" >
            <input type="hidden" id="dropdown_data_type" value="">
        </div>

    </div>

    <script>
        var userLoggedIn = '<?php echo $userLoggedIn; ?>';

        $(document).ready(function() {

            $('.dropdown_data_window').scroll(function() {
                var inner_height = $('.dropdown_data_window').innerHeight(); //Div containing data
                var scroll_top = $('.dropdown_data_window').scrollTop();
                var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();
                var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                if ((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false') {

                    var pageName; //Holds name of page to send ajax request to
                    var type = $('#dropdown_data_type').val();


                    if(type == 'notification')
                        pageName = "ajax_load_notifications.php";
                    else if(type == 'message')
                        pageName = "ajax_load_messages.php"


                    var ajaxReq = $.ajax({
                        url: "includes/handlers/" + pageName,
                        type: "POST",
                        data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
                        cache:false,

                        success: function(response) {
                            $('.dropdown_data_window').append(response);
                        }
                    });
                    $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage
                    $('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current .nextpage



                } //End if

                return false;

            }); //End (window).scroll(function())

        });

    </script>
    <div class="wrapper">

