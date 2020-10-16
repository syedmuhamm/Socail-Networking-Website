<?php
include "includes/header.php";



$message_obj = new Message($con, $userLoggedIn);

if(isset($_GET['u'])) { // if we get data, i,e username passed in URL
    $user_to = $_GET['u'];
}
else {
    $user_to = $message_obj->getMostRecentUser();
    if ($user_to == false) {
        $user_to = 'new';
    }
}

if ($user_to != "new"){
    $user_to_obj = new User($con, $user_to);
}


?>

<div class="user_detail column">
    <!-- importing user from header.php  && link on user profile picture-->
    <a href="<?php echo $userLoggedIn;?>"> <img src=" <?php echo $user['profile_pic'] ?> "> </a>

    <div class="user_details_left_right">
        <!-- link for profile page -->
        <a href="<?php echo $userLoggedIn;?>">
            <?php
            echo $user['first_name']. " ". $user['last_name'];
            ?>
        </a>
        <br>
        <?php
        echo "Posts: " . $user['num_posts']. "<br>";
        echo "Likes: " . $user['num_likes'];
        ?>
    </div>
</div>

<div class="main_column column" id="main_column">
    <!-- The <div> from class Message.php is inside this div below. -->
    <?php
    if ($user_to != "new"){
        echo "<h4> You and <a href='$user_to'>" . $user_to_obj->getFirstAndLastName() . "</a></h4><hr><br>";
        echo "<div class='loaded_messages' id='scroll_messages'>"; // div for getting messages
            echo $message_obj->getMessages($user_to);
        echo "</div>";
    }
    else {
        echo "<h4>New Message</h4>";
    }

    //sending user_to a message
    if (isset($_POST['post_message'])) {

        if (isset($_POST['message_body'])) {
            $body = mysqli_escape_string($con, $_POST['message_body']);
            $date = date("Y-m-d H:i:s");
            $message_obj->sendMessage($user_to, $body, $date );

        }
    }

    ?>
    <div class="message_post">
        <form action="" method="POST">
        <?php
        if ($user_to == "new") {
            echo "Select the friend, you would like to message <br><br>"; ?>

            To: <input type='text' onkeyup='getUsers(this.value, "<?php echo $userLoggedIn;?>" )' name='q' placeholder='Name' autocomplete='off' id='search_text_input'>;

            <?php
            echo "<div class='results' ></div>";
        }
        else {
            echo "<textarea name='message_body' id='message_textarea' placeholder='Your Message..'></textarea>";
            echo "<input type='submit' name='post_message' class='info' id='message_submit' value='send'>";
        }
        ?>
        </form>

    </div>
    <!-- This script push the scroller to the bottom for messages -->
    <script>
        var div = document.getElementById("scroll_messages");
        div.scrollTop = div.scrollHeight;
    </script>

</div>



<div class="user_conversations column" id="conversations">
    <h4>Conversations</h4>
    <div class="loaded_conversations">
         <?php echo $message_obj->getConvos(); ?>
    </div>
    <br>
    <a href="messages.php?u=new">New Message</a>


</div>





