<?php
// This class is used for posting a post.
class Notification
{
    private $user_obj;
    private $con;

    //called, as soon as user creates object of user class
    public function __construct($con, $user)
    {
        $this->con = $con;
        //Creating an instance (object) of User, which allows us to use username through this instance, i,e. $user_obj
        $this->user_obj = new User($con, $user);

    }

    public function getUnreadNumber() {
        $userLoggedIn = $this->user_obj->getUsername();
        $query = mysqli_query($this->con, " SELECT * FROM notifications WHERE viewed='no' AND user_to='$userLoggedIn'");
        return mysqli_num_rows($query);
    }

    public function insertNotification($post_id, $user_to, $type) {

        $userLoggedIn = $this->user_obj->getUsername();
        $userLoggedInName = $this->user_obj->getUsername();

        $date_time = date("Y-m-d H:i:s");

        switch ($type) {
            case 'comment':
                $message = $userLoggedInName. " commented on your post";
                break;
                case 'like':
                $message = $userLoggedInName. " liked your post";
                break;

            case 'profile_post':
                $message = $userLoggedInName. " posted on your profile";
                break;

            case 'comment_non-owner':
                $message = $userLoggedInName. " commented on a post you commented on";
                break;

            case 'profile_comment':
                $message = $userLoggedInName. " commented on your profile post";
                break;
        }

        $link = "post.php?id=". $post_id;

        $insert_query = mysqli_query($this->con, "INSERT INTO notifications (id, user_to, user_from, message, link, datetime, opened, viewed)
                                                        VALUES ('','$user_to','$userLoggedIn','$message','$link','$date_time','no','no')    ");
    }

    public function getNotification($data, $limit) {

        $page = $data['page'] ; // page is data passed from ajax, demo.js
        var_dump($data['page']);
        $userLoggedIn = $this->user_obj->getUsername();
        $return_string = "";

        if ($page == 1) {
            $start = 0;
        }
        else {
            $start = ($page -1) * $limit;
        }

        $set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed='yes' WHERE user_to='$userLoggedIn'");

        $query = mysqli_query($this->con, "SELECT * FROM notifications WHERE user_to='$userLoggedIn' ORDER BY id DESC");

        if (mysqli_num_rows($query) == 0) {
            echo "You have no notifications";
            return;
        }

        $num_iterations = 0; // Number of messages checked
        $count = 1; // Number of messages posted

        while ($row= mysqli_fetch_array($query)) {

            if ($num_iterations++ <= $start) {
                continue;
            }
            if ($count > $limit)
                break;
            else
                $count++;

            $user_from = $row['user_from'];

            $user_data_query = mysqli_query($this->con, "SELECT * FROM users WHERE username='$user_from'");
            $user_data =  mysqli_fetch_array($user_data_query);


            //Timeframe
            $date_time_now = date("Y-m-d H:i:s ");
            $start_date = new DateTime($row['datetime']); // time of post, using the date_added of post
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

            $opened = $row['opened'];
            $style = ($row['opened']== 'no') ? "background-color: #DDEDFF;" : "";

            $return_string .= "<a href='".$row['link'] . "'>
                                    <div class='resultDisplay resultDisplayNotification' style='".$style ."'>
                                        <div class='notificationsProfilePic'>
                                        <img src='".$user_data['profile_pic'] ."'>
                                        </div>
                                         <p class='timestamp_smaller' id='gray'>". $time_message ."</p>" .$row['message'] ."
                                    </div>
							</a>";
        }
        //If posts were loaded
        if($count > $limit)
            $return_string .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'><input type='hidden' class='noMoreDropdownData' value='false'>";
        else
            $return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'> <p style='text-align: center;'>No more Notifications to load!</p>";

        return $return_string;
    }
}
?>