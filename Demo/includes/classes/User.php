<?php
// This class is used for User Information.
class User {
    private $user;
    private $con;

    //called, as soon as user creates object of user class
    public function __construct($con, $user) {

        if(!$user) {
            exit("user variable is null");
        }

        $this->con = $con;
        $user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username = '$user'");
        $this->user = mysqli_fetch_array($user_details_query);

//        if(!$this->user) {
//            exit("this->user variable is null. No results found for username: " . $user . "<br>");
//        }
        //var_dump($this->user);
    }

    //construct can be called by the following way
    //$user_obj = new User($con, $userLoggedIn);

    public function getFirstAndLastName(){
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username = '$username'");
        $row = mysqli_fetch_array($query);
        return $row['first_name']. " ". $row['last_name'];
        //used in index.php as include("includes/classes/User.php")
    }

    public function getProfilePic(){
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT profile_pic FROM users WHERE username = '$username'");
        $row = mysqli_fetch_array($query);
        return $row['profile_pic'];
    }

    public function getFriendArray(){
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username = '$username'");
        $row = mysqli_fetch_array($query);
        return $row['friend_array'];
    }

    public function getUsername() {
        return $this->user['username'];
    }

    public function getNumberOfFriendRequests() {
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to = '$username'");

        return mysqli_num_rows($query);
    }

    //Getting number of posts for user

    public function getNumPosts(){
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username = '$username'");

        $row = mysqli_fetch_array($query);
        return $row['num_posts'];
    }

    public function isClosed(){
        $username = $this->user['username'];
        $query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE username = '$username'");

        $row = mysqli_fetch_array($query);

        if ($row['user_closed'] == 'yes') {
            return true;
        }
        else
            return false;
    }

    // comparing username with comma to the database. or if username equals to the logged in person himself.

    public function isFriend($username_to_check) {
        $usernameComma = "," . $username_to_check. ",";

        if ((strstr($this->user['friend_array'] , $usernameComma) || $username_to_check == $this->user['username'] )) {
            return true;
        }
        else {
            return false;
        }
    }

    public function didReceieveRequest($user_from){
        $user_to = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from' ");
        if (mysqli_num_rows($check_request_query) > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function didSendRequest($user_to){
        $user_from = $this->user['username'];
        $check_request_query = mysqli_query($this->con, "SELECT * FROM friend_requests WHERE user_to='$user_to' AND user_from='$user_from' ");
        if (mysqli_num_rows($check_request_query) > 0) {
            return true;
        }
        else {
            return false;
        }
    }

    public function removeFriend($user_to_remove) {

        $logged_in_user = $this->user['username'];

        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$user_to_remove'");
        $row = mysqli_fetch_array($query);
        $friend_array_username = $row['friend_array'];

        $new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_array']);
        $remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array='$new_friend_array' WHERE username='$logged_in_user'");

        $new_friend_array = str_replace($this->user['username'] . ",", "", $friend_array_username);
        $remove_friend = mysqli_query($this->con, "UPDATE users SET friend_array='$new_friend_array' WHERE username='$user_to_remove'");

    }

    public function sendRequest($user_to) {
        $user_from = $this->user['username'];
        $query = mysqli_query($this->con, "INSERT INTO friend_requests (id, user_to, user_from) VALUES ('', '$user_to', '$user_from' )");

    }

    public function getMutualFriends($user_to_check) {
        $mutualFriends = 0;
        $user_array = $this->user['friend_array']; // All columns of table stored in user[], user logged in
        $user_array_explode = explode(",", $user_array); // splitting user_array, whereever it finds a comma

        $query = mysqli_query($this->con, "SELECT friend_array FROM users WHERE username='$user_to_check'");
        $row = mysqli_fetch_array($query);
        $user_to_check_array = $row['friend_array']; // friend array of user passed
        $user_to_check_array_explode = explode(",", $user_to_check_array);

        foreach ((array)$user_array_explode as $i) {

            foreach ((array)$user_to_check_array_explode as $j) {

                if(($i == $j) && ($i != "")){
                    $mutualFriends++;
                }
            }
            
        }
        return $mutualFriends;
    }

}
?>