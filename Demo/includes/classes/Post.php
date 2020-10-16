<?php
// This class is used for posting a post.
class Post
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


    public function submitPost($body, $user_to, $imageName)
    {
        $body = strip_tags($body); //Removes HTML tags
        $body = mysqli_real_escape_string($this->con, $body); // Escaping a single quote in query to avoid errors

        $body . str_replace('\r\n', '\n', $body); // Replaces \r\n ENTER as \n in $body
        $body . nl2br($body); // change nextline nl to breakline br

        $check_empty = preg_replace('/\s+/', '', $body);//Replacing regular expressions with strings

        if ($check_empty != "") { // if user has not put some data in post, other than nothing or empty spaces

            $body_array = preg_split("/\s+/", $body); // split body into array at spaces

            foreach ($body_array as $key => $value) {

                if (strpos($value, "www.youtube.com/watch?v=") !== false){

                    $link = preg_split("!&!", $value);
                    $value = preg_replace("!watch\?v=!", "embed/", $link[0]);
                    $value = "<br><iframe width=\'420\' height=\'315\' src=\'" .$value . "\' allowfullscreen></iframe><br>";
                    $body_array[$key] = $value;
                }
            }
            $body = implode(" ", $body_array );

            $date_added = date("Y-m-d H:i:s"); //Current Date
            $added_by = $this->user_obj->getUsername(); //get username

            //if user is on own profile, user_to is none
            if ($user_to == $added_by) {
                $user_to = "none";
            }

            //insert post
            $query = mysqli_query($this->con,
                "INSERT INTO posts (id,	body, added_by, user_to, date_added, user_closed, deleted,likes, image)
                VALUES ('', '$body', '$added_by','$user_to','$date_added','no','no','0', '$imageName')");
            //returns the "id" of the post, it just submitted
            $returnedId = mysqli_insert_id($this->con);

            //User Notification
            if ($user_to != 'none') {
                $notification = new Notification($this->con, $added_by);
                $notification->insertNotification($returnedId, $user_to, "profile_post");
            }



            //Update post count for user, using instance of User
            $num_posts = $this->user_obj->getNumPosts();
            $num_posts++;
            //inform database about increment of numPost, for the person who added the post
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts = '$num_posts' WHERE username = '$added_by'");

            $stopWords = "a about above across after again against all almost alone along already
			 also although always among am an and another any anybody anyone anything anywhere are 
			 area areas around as ask asked asking asks at away b back backed backing backs be became
			 because become becomes been before began behind being beings best better between big 
			 both but by c came can cannot case cases certain certainly clear clearly come could
			 d did differ different differently do does done down down downed downing downs during
			 e each early either end ended ending ends enough even evenly ever every everybody
			 everyone everything everywhere f face faces fact facts far felt few find finds first
			 for four from full fully further furthered furthering furthers g gave general generally
			 get gets give given gives go going good goods got great greater greatest group grouped
			 grouping groups h had has have having he her here herself high high high higher
		     highest him himself his how however i im if important in interest interested interesting
			 interests into is it its itself j just k keep keeps kind knew know known knows
			 large largely last later latest least less let lets like likely long longer
			 longest m made make making man many may me member members men might more most
			 mostly mr mrs much must my myself n necessary need needed needing needs never
			 new new newer newest next no nobody non noone not nothing now nowhere number
			 numbers o of off often old older oldest on once one only open opened opening
			 opens or order ordered ordering orders other others our out over p part parted
			 parting parts per perhaps place places point pointed pointing points possible
			 present presented presenting presents problem problems put puts q quite r
			 rather really right right room rooms s said same saw say says second seconds
			 see seem seemed seeming seems sees several shall she should show showed
			 showing shows side sides since small smaller smallest so some somebody
			 someone something somewhere state states still still such sure t take
			 taken than that the their them then there therefore these they thing
			 things think thinks this those though thought thoughts three through
	         thus to today together too took toward turn turned turning turns two
			 u under until up upon us use used uses v very w want wanted wanting
			 wants was way ways we well wells went were what when where whether
			 which while who whole whose why will with within without work
			 worked working works would x y year years yet you young younger
			 youngest your yours z lol haha omg hey ill iframe wonder else like 
             hate sleepy reason for some little yes bye choose";

            //Convert stop words into array - split at white space
            $stopWords = preg_split("/[\s,]+/", $stopWords);

            //Remove all punctionation
            $no_punctuation = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

            //Predict whether user is posting a url. If so, do not check for trending words
            if(strpos($no_punctuation, "height") === false && strpos($no_punctuation, "width") === false
                && strpos($no_punctuation, "http") === false && strpos($no_punctuation, "youtube") === false){
                //Convert users post (with punctuation removed) into array - split at white space
                $keywords = preg_split("/[\s,]+/", $no_punctuation);

                foreach($stopWords as $value) {
                    foreach($keywords as $key => $value2){
                        if(strtolower($value) == strtolower($value2))
                            $keywords[$key] = "";
                    }
                }

                foreach ($keywords as $value) {
                    $this->calculateTrend(ucfirst($value));
                }

            }
        }
    }

    public function calculateTrend($term) {

        if($term != '') {
            $query = mysqli_query($this->con, "SELECT * FROM trends WHERE title='$term'");

            if (mysqli_num_rows($query) == 0) {
                $insert_query = mysqli_query($this->con, "INSERT INTO trends(title,hits) VALUES ('$term','1')");
            }
            else {
                $insert_query = mysqli_query($this->con, "UPDATE trends SET hits=hits+1 WHERE title='$term'");
            }
        }
    }

    public function loadPostsFriends($data, $limit)
    {
        $page = $data['page'];
        $userLoggedIn = $this->user_obj->getUsername();

        // this if else check for the number of page, and corresponds accordingly
        if ($page == 1)
            $start =0;
        else
            $start = ($page - 1) * $limit;

        $str = ""; // string to return
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted = 'no' ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; // Number of results checked (not necessarily posted)
            $count_posts = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];
                $imagePath = $row['image'];

                //prepare user_to string, so it can be included even if not posted to a user
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href = '" . $row['user_to'] . "'>" . $user_to_name . "</a>"; // added to
                }

                //check if user who posted, has their account closed.
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    continue;
                }

                //working for isfriend() method in User.php for showing only people we are friends with.

                $user_logged_obj = new User($this->con, $userLoggedIn);
                if ($user_logged_obj->isFriend($added_by)) {

                    // iteration through the posts already loaded. $start counts how many posts are loaded
                    if ($num_iterations++ < $start)
                        continue;

                    //once 10 posts have been loaded, break
                    if ($count_posts > $limit){
                        break;
                    }
                    else{
                        $count_posts++;
                    }

                    //code for delete button

                    if ($userLoggedIn == $added_by){
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    }
                    else {
                        $delete_button = "";
                    }


                        $user_detials_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic From users WHERE
                                                                          username = '$added_by' ");

                    $user_row = mysqli_fetch_array($user_detials_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

                    ?>
                    <!--  this script is called from $str.= "div class = 'status_post' -->
                    <script>
                        function toggle<?php echo $id; ?>() { //id is defined as $id = $row['id']; in while loop

                            var target = $(event.target); // Place where user clicked
                            if (!target.is("a")) { // if target is not hyperlink <a>

                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }
                    </script>

                    <?php
                    //for showing number of comments
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments where post_id = '$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s ");
                    $start_date = new DateTime($date_time); // time of post, using the date_added of post
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

                    if($imagePath != "") {
                        $imageDiv = "<div class='postedImage'>
										<img src='$imagePath'>
									</div>";
                    }
                    else {
                        $imageDiv = "";
                    }

                        $str.= "<div class = 'status_post' onclick='javascript:toggle$id()'> <!-- // calling script toggle id -->
                                    <div class='post_profile_pic'>
                                        <img src='$profile_pic' width='50'>
                                    </div>
                                
                                <div class='posted_by' style='color: #ACACAC; '>
                                    <a href='$added_by'> $first_name $last_name </a> $user_to &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;$time_message
                                    $delete_button
                                </div>
                                <div id='post_body'>
                                    $body
                             
                                    <br>
                                    $imageDiv
                                    <br>
                                    <br>
                                </div>
                                <!-- Area for comments and like -->
                                <div class='newsfeedPostOptions'>
                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                </div>
                                
                                </div>
                                <!-- div for comments -->
                                <div class='post_comment' id='toggleComment$id' style='display: none'>
                                    <iframe src='comment_frame.php?post_id=$id' id='comment_frame' frameborder='0'></iframe>
                                </div>
                                <hr>";
                }
                //Making delete script
                ?>
                <script>
                    $.(document).ready(function() {

                       $('#post<?php echo $id; ?>').on('click', function () {
                           bootbox.confirm("Are you sure to want to delete this post?", function(result) {
                               $.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>" , {result:result}); // value of result is sent to this page

                               if (result)
                                   location.reload();
                           });
                       });

                    });

                </script>

            <?php
            } // end of while loop

            if ($count_posts > $limit) {
                $str .= "<input type = 'hidden' class = 'nextPage' value = '" . ($page + 1) .  "'>
                         <input type = 'hidden' class = 'noMorePosts' value = 'false' > ";
            }
            else {
                $str .= "<input type = 'hidden' class = 'noMorePosts' value = 'true' > 
                         <p style='text-align: center'> No More Posts to Show! </p> ";
            }
        }
        echo $str;

    }

    public function loadProfilePosts($data, $limit)
    {
        $page = $data['page'];
        $profileUser = $data['profileUsername'];
        $userLoggedIn = $this->user_obj->getUsername();

        // this if else check for the number of page, and corresponds accordingly
        if ($page == 1)
            $start =0;
        else
            $start = ($page - 1) * $limit;

        $str = ""; // string to return
        // if it wasn't to anybody, show it on the profile...
        // if they posted it, and wasn't for anybody, take it (added_by='$profileUser' AND user_to='none')
        // OR if they didn't post it, and it was to them, show it on profile ( OR user_to='$profileUser' )
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted = 'no' AND
         ((added_by='$profileUser' AND user_to='none') OR (user_to='$profileUser')) ORDER BY id DESC");

        if (mysqli_num_rows($data_query) > 0) {

            $num_iterations = 0; // Number of results checked (not necessarily posted)
            $count_posts = 1;

            while ($row = mysqli_fetch_array($data_query)) {
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                    // iteration through the posts already loaded. $start counts how many posts are loaded
                    if ($num_iterations++ < $start)
                        continue;

                    //once 10 posts have been loaded, break
                    if ($count_posts > $limit){
                        break;
                    }
                    else{
                        $count_posts++;
                    }

                    //code for delete button

                    if ($userLoggedIn == $added_by){
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    }
                    else {
                        $delete_button = "";
                    }

                    $user_detials_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic From users WHERE
                                                                          username = '$added_by' ");

                    $user_row = mysqli_fetch_array($user_detials_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

                    ?>
                    <!--  this script is called from $str.= "div class = 'status_post' -->
                    <script>
                        function toggle<?php echo $id; ?>() { //id is defined as $id = $row['id']; in while loop

                            var target = $(event.target); // Place where user clicked
                            if (!target.is("a")) { // if target is not hyperlink <a>

                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }
                    </script>

                    <?php
                    //for showing number of comments
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments where post_id = '$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s ");
                    $start_date = new DateTime($date_time); // time of post, using the date_added of post
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

                    $str.= "<div class = 'status_post' onclick='javascript:toggle$id()'> <!-- // calling script toggle id -->
                                    <div class='post_profile_pic'>
                                        <img src='$profile_pic' width='50'>
                                    </div>
                                
                                <div class='posted_by' style='color: #ACACAC; '>
                                    <a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;$time_message
                                    $delete_button
                                </div>
                                <div id='post_body'>
                                    $body
                                    <br>
                                    <br>
                                    <br>
                                </div>
                                <!-- Area for comments and like -->
                                <div class='newsfeedPostOptions'>
                                    Comments($comments_check_num)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                </div>
                                
                                </div>
                                <!-- div for comments -->
                                <div class='post_comment' id='toggleComment$id' style='display: none'>
                                    <iframe src='comment_frame.php?post_id=$id' id='comment_frame' frameborder='0'></iframe>
                                </div>
                                <hr>";
                //Making delete script
                ?>
                <script>
                    $.(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function () {
                            bootbox.confirm("Are you sure to want to delete this post?", function(result) {
                                $.ajax("includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>" , {result:result}); // value of result is sent to this page

                                if (result)
                                    location.reload();
                            });
                        });

                    });

                </script>

                <?php
            } // end of while loop

            if ($count_posts > $limit) {
                $str .= "<input type = 'hidden' class = 'nextPage' value = '" . ($page + 1) .  "'>
                         <input type = 'hidden' class = 'noMorePosts' value = 'false' > ";
            }
            else {
                $str .= "<input type = 'hidden' class = 'noMorePosts' value = 'true' > 
                         <p style='text-align: center'> No More Posts to Show! </p> ";
            }
        }
        echo $str;

    }

    public function getSinglePost($post_id) {

        $userLoggedIn = $this->user_obj->getUsername();

        //checks out the blue unread layout from notifications
        $opened_query = mysqli_query($this->con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link LIKE '%=$post_id' ");


        $str = "";
        $data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted = 'no' AND
                                                        id='$post_id' ");

        if (mysqli_num_rows($data_query) > 0) {

                $row = mysqli_fetch_array($data_query);
                $id = $row['id'];
                $body = $row['body'];
                $added_by = $row['added_by'];
                $date_time = $row['date_added'];

                //prepare user_to string, so it can be included even if not posted to a user
                if ($row['user_to'] == "none") {
                    $user_to = "";
                } else {
                    $user_to_obj = new User($this->con, $row['user_to']);
                    $user_to_name = $user_to_obj->getFirstAndLastName();
                    $user_to = "to <a href = '" . $row['user_to'] . "'>" . $user_to_name . "</a>"; // added to
                }

                //check if user who posted, has their account closed.
                $added_by_obj = new User($this->con, $added_by);
                if ($added_by_obj->isClosed()) {
                    return;
                }

                //working for isfriend() method in User.php for showing only people we are friends with.

                $user_logged_obj = new User($this->con, $userLoggedIn);
                if ($user_logged_obj->isFriend($added_by)) {


                    //code for delete button

                    if ($userLoggedIn == $added_by){
                        $delete_button = "<button class='delete_button btn-danger' id='post$id'>X</button>";
                    }
                    else {
                        $delete_button = "";
                    }


                    $user_detials_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic From users WHERE
                                                                              username = '$added_by' ");

                    $user_row = mysqli_fetch_array($user_detials_query);
                    $first_name = $user_row['first_name'];
                    $last_name = $user_row['last_name'];
                    $profile_pic = $user_row['profile_pic'];

                    ?>
                    <!--  this script is called from $str.= "div class = 'status_post' -->
                    <script>
                        function toggle<?php echo $id; ?>() { //id is defined as $id = $row['id']; in while loop

                            var target = $(event.target); // Place where user clicked
                            if (!target.is("a")) { // if target is not hyperlink <a>

                                var element = document.getElementById("toggleComment<?php echo $id; ?>");

                                if(element.style.display == "block")
                                    element.style.display = "none";
                                else
                                    element.style.display = "block";
                            }
                        }
                    </script>

                    <?php
                    //for showing number of comments
                    $comments_check = mysqli_query($this->con, "SELECT * FROM comments where post_id = '$id'");
                    $comments_check_num = mysqli_num_rows($comments_check);

                    //Timeframe
                    $date_time_now = date("Y-m-d H:i:s ");
                    $start_date = new DateTime($date_time); // time of post, using the date_added of post
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

                    $str.= "<div class = 'status_post' onclick='javascript:toggle$id()'> <!-- // calling script toggle id -->
                                        <div class='post_profile_pic'>
                                            <img src='$profile_pic' width='50'>
                                        </div>
                                    
                                    <div class='posted_by' style='color: #ACACAC; '>
                                        <a href='$added_by'> $first_name $last_name </a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;$time_message
                                        $delete_button
                                    </div>
                                    <div id='post_body'>
                                        $body
                                        <br>
                                        <br>
                                        <br>
                                    </div>
                                    <!-- Area for comments and like -->
                                    <div class='newsfeedPostOptions'>
                                        Comments($comments_check_num)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <iframe src='like.php?post_id=$id' scrolling='no'></iframe>
                                    </div>
                                    
                                    </div>
                                    <!-- div for comments -->
                                    <div class='post_comment' id='toggleComment$id' style='display: none'>
                                        <iframe src='comment_frame.php?post_id=$id' id='comment_frame' frameborder='0'></iframe>
                                    </div>
                                    <hr>";
                    //Making delete script


                ?>
                <script>
                    $.(document).ready(function() {

                        $('#post<?php echo $id; ?>').on('click', function () {
                            bootbox.confirm("Are you sure to want to delete this post?", function(result) {
                                $.post('includes/form_handlers/delete_post.php?post_id=<?php echo $id; ?>' , {result:result}); // value of result is sent to this page

                                if (result)
                                    location.reload();
                            });
                        });

                    });

                </script>

                <?php
                }
                else {
                    echo "<p>You cannot see this post, because you are not friends with this user.</p>";
                    return;
                }
        }
        else {
            echo "<p>No post found. If you clicked a link, it maybe broken.</p>";
            return;
        }
        echo $str;
    }

}
?>