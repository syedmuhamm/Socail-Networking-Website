<?php

if (isset($_POST['login_button'])) {

    $email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); // sanitizes email from login form though $_POST

    $_SESSION['log_email'] = $email; // Assigns $email to session, so that values does not erase after wrong attempt
    $password = md5($_POST['log_password']); // stores data from $_POST of log_password value in $password
    // Check database for match of email and password pressed in login FORM.
    $check_database_query = mysqli_query($con, "SELECT * FROM users WHERE email = '$email' AND password = '$password'");
    $check_login_query = mysqli_num_rows($check_database_query); // Returns any match rows for $check_database_query as 0 or 1.

    if ($check_login_query == 1) { // if a match is found
        $row = mysqli_fetch_array($check_database_query); // fetches all data for specific matched row, i,e $check_login_query
        $username = $row['username']; // $username is assigned 'username', 'username' is from database with the help of $row variable.

        //if account is closed, i,e yes then change it to no
        $user_closed_query = mysqli_query($con, "SELECT * FROM users WHERE email = '$email' AND user_closed = 'yes'");
        if (mysqli_num_rows($user_closed_query)== 1) {
            //reopen account, since yes means that user is logged out
            $reopen_account = mysqli_query($con, "UPDATE users SET user_closed = 'no' WHERE email = '$email'");
        }



        $_SESSION['username'] = $username; //$username is assigned to $_SESSION
        header("Location: index.php"); // user is logged in and directed to index.php
        exit();
    }
    else {
        array_push($error_array, "Your Email or Password was incorrect<br>");
    }


}


?>
