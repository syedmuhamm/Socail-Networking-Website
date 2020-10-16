<?php
//Declaring variables to prevent errors
$fname=""; //First Name
$lname=""; //Last Name
$em=""; //Email
$em2= ""; //Confirm Email
$password = ""; // Password
$password2 = ""; // Confirm Password
$date = ""; // Signup Date
$error_array = array(); // Holds error for wrong emails and passwords mismatch

if(isset($_POST['reg_button'])) {

    //Registration form values

    //First Name
    $fname = strip_tags($_POST['reg_fname']); // strip html tags from string
    $fname = str_replace(" ", "", $fname); // Replaces spaces with nospace
    $fname = ucfirst(strtolower($fname)); // Convert all string to lower case, then Captalize first letter of string
    $_SESSION['reg_fname'] = $fname; // stores firstname into session variable

    //Last Name
    $lname = strip_tags($_POST['reg_lname']); // strip html tags from string
    $lname = str_replace(" ", "", $lname); // Replaces spaces with non space
    $lname = ucfirst(strtolower($lname)); // Convert all string to lower case, then Captalize first letter of string
    $_SESSION['reg_lname'] = $lname; // stores lastname into session variable

    //Email
    $em = strip_tags($_POST['reg_email']); // strip html tags from string
    $em = str_replace(" ", "", $em); // Replaces spaces with non space
    $em = strtolower($em); // Convert all string to lower case, then Captalize first letter of string
    $_SESSION['reg_email'] = $em; // stores email into session variable

    //Email Confirmation
    $em2 = strip_tags($_POST['reg_email2']); // strip html tags from string
    $em2 = str_replace(" ", "", $em2); // Replaces spaces with non space
    $em2 = strtolower($em2); // Convert all string to lower case, then Captalize first letter of string
    $_SESSION['reg_email2'] = $em2; // stores confirm email into session variable

    //Password
    $password = strip_tags($_POST['reg_password']); // strip html tags from string

    //Password Confirmation
    $password2 = strip_tags($_POST['reg_password2']); // strip html tags from string

    //Current Date
    $date = date("Y-m-d");

    if ($em == $em2) {
        //check if email is in valid format
        if (filter_var($em, FILTER_VALIDATE_EMAIL)) {

            $em = filter_var($em, FILTER_VALIDATE_EMAIL);

            //check if email already exists
            $e_check = mysqli_query($con, "SELECT email FROM users WHERE email = '$em'");

            //counting number of rows
            $num_rows = mysqli_num_rows($e_check);

            if ($num_rows > 0) {
                array_push($error_array, "Email already in use<br>") ;
            }
        }
        else {
            array_push($error_array, "Invalid Format<br>") ;

        }
    }
    else {
        array_push($error_array, "Email does not match<br>") ;
    }

    //checking string length for each value
    if (strlen($fname) > 25 || strlen($fname) < 2) {
        array_push($error_array, "Your first name should be between 2 and 25 characters<br>") ;
    }
    //checking string length for each value
    if (strlen($lname) > 25 || strlen($lname) < 2) {
        array_push($error_array, "Your Last name should be between 2 and 25 characters<br>") ;
    }
    //comparing passwords
    if ($password != $password2) {
        array_push($error_array, "Your passwords do not match<br>") ;
    }
    //checking passwords for special characters
    else{
        if (preg_match('"/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i"', $password)) {
            array_push($error_array, "Passwords can only contain characters and numbers<br>") ;
        }
    }
    //checking passwords length
    if (strlen($password) > 30 || strlen($password) < 5) {
        array_push($error_array, "Your Password should be between 5 and 30 characters<br>") ;
    }

    //if $error_array is empty, we push values to database
    if (empty($error_array)){
        //encrypt password
        $password = md5($password);

        //generate automatic name for individual user by concatination
        $username = strtolower($fname. "_".$lname);
        //check if this $username exists in database
        $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username = '$username'");

        $increment_username =0;
        // If username exists, add 1 to username.
        while (mysqli_num_rows($check_username_query) != 0){
            $increment_username++;
            $username = $username."_".$increment_username;
            //check if this $username exists in database
            $check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username = '$username'");
        }

        //profile picture assignment
        $random_profile_pic = rand(1,2); // Creates random number 1 or 2

        if ($random_profile_pic == 1) {
            $profile_pic = "assets/images/profile_pics/defaults/head_alizarin.png";
        }
        elseif ($random_profile_pic ==2) {
            $profile_pic = "assets/images/profile_pics/defaults/head_amethyst.png";
        }

        //Storing values from FORM to database 0 = no of posts, 0 = no of likes, , = friend array
        $query_to_database = mysqli_query($con,
            "INSERT INTO users (id,first_name,last_name,username,email,password,signup_date,profile_pic,num_posts,num_likes,user_closed,friend_array)
                    VALUES ('', '$fname', '$lname','$username','$em','$password','$date','$profile_pic','0','0','no',', ')");


        array_push($error_array, "<span style='color: #14C800'> You are all set. Go ahead and login! </span><br>");

        // Clearing session variables for new empty form
        $_SESSION['reg_fname'] = "";
        $_SESSION['reg_lname'] = "";
        $_SESSION['reg_email'] = "";
        $_SESSION['reg_email2'] = "";

    }

}
?>
