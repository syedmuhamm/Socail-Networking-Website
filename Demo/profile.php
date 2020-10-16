<?php
include("includes/header.php");

$message_obj = new Message($con, $userLoggedIn);

if (isset($_GET['profile_username'])) { // /demo/profile.php?profile_username=geeshah , whenever username is called in URL, this is what is actually called
    $username = $_GET['profile_username'];
    $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
    $user_array = mysqli_fetch_array($user_details_query);

    $num_friends = (substr_count($user_array['friend_array'],",")) - 1;// how many occurences of a string inside another string
}

// adding, removing and sending friend requests

if(isset($_POST['remove_friend'])) {
    $user = new User($con, $userLoggedIn); // Telling USER class, which user is logged in
    $user->removeFriend($username); // Removing username, a name GET from URL
}

if(isset($_POST['add_friend'])) {
    $user = new User($con, $userLoggedIn);
    $user->sendRequest($username);
}

if(isset($_POST['respond_request'])) {
    header("Location:requests.php");
}

// setting send message from profile.php
if(isset($_POST['post_message'])) {
    if (isset($_POST['message_body'])) {
        $body = mysqli_real_escape_string($con, $_POST['message_body']);
        $date = date("Y-m-d H:i:s");
        $message_obj->sendMessage($username, $body, $date);
    }
    // setting reroute after message is sent from profile.php to the same page, but only using #profileTabs, #messages_div

    $link = '#profileTabs a[href = "#messages_div"]';
    echo " <script>
                $(function () {
                    $('" .$link  . "').tab('show');
                })
           </script>";
}


?>
    <style type="text/css">
        .wrapper {
            margin-left :0 ;
            padding-left :0 ;
        }
    </style>

    <div class="profile_left">
        <img src="<?php echo $user_array['profile_pic'] ?>">

        <div class="profile_info">
            <p><?php echo "Posts: " . $user_array['num_posts'] ?></p>
            <p><?php echo "Likes: " . $user_array['num_likes'] ?></p>
            <p><?php echo "Friends: " . $num_friends ?></p>
        </div>

        <form action="<?php echo $username; ?>" method="POST">
            <?php
            $profile_user_obj = new User($con, $username);
            if ($profile_user_obj->isClosed()) {
                header("Location: user_closed.php");
            }

            $logged_in_user_obj = new User($con, $userLoggedIn); //UserloggedIn taken from header.php SESSION

            if ($username !=$userLoggedIn ) { //if loggedin person is not on his own profile.

                if ($logged_in_user_obj->isFriend($username)) { // if user is already friend
                    echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"> <br>';
                }

                elseif ($logged_in_user_obj->didReceieveRequest($username)) {
                    echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"> <br>';
                }

                elseif ($logged_in_user_obj->didSendRequest($username)) {
                    echo '<input type="submit" name="" class="default" value="Request Sent"> <br>';
                }

                else {
                    echo '<input type="submit" name="add_friend" class="success" value="Add Friend    "> <br>';

                }
            }
            ?>

        </form>

        <input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" role="button" value="Post Something!">

        <!-- For submitting bootstrap styled form -->

        <?php
        if ($userLoggedIn != $username) { // if user is not on his profile
            echo '<div class="profile_info_bottom">';
                echo $logged_in_user_obj->getMutualFriends($username). " Mutual Friends";
            echo '</div>';
        }

        ?>

    </div>

    <div class="profile_main_column column">

        <ul class="nav nav-tabs" role="tablist" id="profileTabs">
            <li role="presentation" class="active"><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a></li>
            <li role="presentation"><a href="#messages_div" aria-controls="messages_div" role="tab" data-toggle="tab">Messages</a></li>
        </ul>

        <div class="tab-content">

            <div role="tabpanel" class="tab-pane active" id="newsfeed_div">
                <div class="posts_area"></div>
                <img id="loading" src="assets/images/icons/loading.gif">
            </div>


            <div role="tabpanel" class="tab-pane" id="messages_div">
                <?php
                echo "<h4>You and <a href='" . $username ."'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";

                echo "<div class='loaded_messages' id='scroll_messages'>";
                echo $message_obj->getMessages($username);
                echo "</div>";
                ?>



                <div class="message_post">
                    <form action="" method="POST">
                        <textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>
                        <input type='submit' name='post_message' class='info' id='message_submit' value='Send'>
                    </form>

                </div>

                <script>
                    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                        var div = document.getElementById("scroll_messages");
                        div.scrollTop = div.scrollHeight;
                    });
                </script>
            </div>

        </div>

    </div>

        <!-- Modal -->
        <div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">x</span>
                            <h4 class="modal-title" id="myModalLabel"> Post something!</h4>
                        </button>
                    </div>

                    <div class="modal-body">
                        <p> This will appear on user's profile page and on their newsfeed, for their friends to see..!</p>

                        <form class="profile_post" action="" method="POST">
                            <div class="form-group">
                                <textarea class="form-control" name="post_body"></textarea>
                                <input type="hidden" name="user_from" value="<?php echo $userLoggedIn ?>">
                                <input type="hidden" name="user_to" value="<?php echo $username ?>">
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>

            var userLoggedIn = '<?php echo $userLoggedIn; ?>';
            var profileUsername = '<?php echo $username; ?>';

            //this one happens when no posts are loaded
            $(document).ready(function() {

                $('#loading').show();

                //Original ajax request for loading first posts
                $.ajax({
                    url: "includes/handlers/ajax_load_profile_posts.php",
                    type: "POST",
                    data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                    cache:false,

                    success: function(data) {
                        $('#loading').hide();
                        $('.posts_area').html(data);
                    }
                });
                // this happens when they are loading posts multiple times

                $(window).scroll(function() {
                    var height = $('.posts_area').height(); //Div containing posts
                    var scroll_top = $(this).scrollTop();
                    var page = $('.posts_area').find('.nextPage').val();
                    var noMorePosts = $('.posts_area').find('.noMorePosts').val();

                    if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {

                        var inProgress = true;
                        $('#loading').show();

                        var ajaxReq = $.ajax({
                            url: "includes/handlers/ajax_load_profile_posts.php",
                            type: "POST",
                            data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
                            cache:false,

                            success: function(response) {
                                $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
                                $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage

                                $('#loading').hide();
                                $('.posts_area').append(response);
                                inProgress = false;
                            }
                        });

                    } //End if

                    return false;

                }); //End (window).scroll(function())

            });

        </script>

    </div>
</body>
</html>