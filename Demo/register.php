<?php

require 'config/config.php';
require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome to swirlfood</title>
    <link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
    <script src="assets/js/register.js"></script>

</head>
<body>

    <?php if (isset($_POST['reg_button'])) {
        echo '
        <script>
            $(document).ready(function (){
               $("#first").hide();
               $("#second").show();
            });
        </script>
        ';
    }?>

    <div class="wrapper">
        <div class="login_box">

            <div class="login_header">
                <h1>Swirlfeed!</h1>
                Login or Signup below!
            </div>
            <div id="first">
                <form action="register.php" method="POST">
                    <input type="email" name="log_email" placeholder="Email Address" value=" <?php
                    if (isset($_SESSION['log_email'])) {
                        echo $_SESSION['log_email'];
                    }
                    ?>" required >
                    <br>
                    <input type="password" name="log_password" placeholder="Password">
                    <br>

                    <?php if (in_array("Your Email or Password was incorrect<br>",$error_array )) echo "Your Email or Password was incorrect<br>"; ?>
                    <br>

                    <input type="submit" name="login_button" value="Login">
                    <br>

                    <a href="#" id="signup" class="signup"> Need an account? Register here!</a>
                    <br>
                </form>
            </div>

            <div id="second">
                <form action="register.php" method="POST">
                    <input type="text" name="reg_fname" placeholder="First Name" value=" <?php
                        if (isset($_SESSION['reg_fname'])) {
                            echo $_SESSION['reg_fname'];
                        }
                    ?>" required>
                    <br>
                    <?php if (in_array("Your first name should be between 2 and 25 characters<br>",$error_array)) echo "Your first name should be between 2 and 25 characters<br>"; ?>


                    <input type="text" name="reg_lname" placeholder="Last Name" value=" <?php
                    if (isset($_SESSION['reg_lname'])) {
                        echo $_SESSION['reg_lname'];
                    }
                    ?>" required>
                    <br>
                    <?php if (in_array("Your Last name should be between 2 and 25 characters<br>",$error_array)) echo "Your Last name should be between 2 and 25 characters<br>"; ?>


                    <input type="email" name="reg_email" placeholder="Email"  value=" <?php
                    if (isset($_SESSION['reg_email'])) {
                        echo $_SESSION['reg_email'];
                    }
                    ?>"  required>
                    <br>

                    <input type="email" name="reg_email2" placeholder="Confirm Email"  value=" <?php
                    if (isset($_SESSION['reg_email2'])) {
                        echo $_SESSION['reg_email2'];
                    }
                    ?>" required>
                    <br>
                    <?php if (in_array("Email already in use<br>",$error_array)) echo "Email already in use<br>";
                    elseif (in_array("Invalid Format<br>",$error_array)) echo "Invalid Format<br>";
                    elseif (in_array("Email does not match<br>",$error_array)) echo "Email does not match<br>"; ?>

                    <input type="password" name="reg_password" placeholder="Password" required>
                    <br>
                    <input type="password" name="reg_password2" placeholder="Confirm Password" required>
                    <br>
                    <?php if (in_array("Your passwords do not match<br>",$error_array)) echo "Your passwords do not match<br>";
                    elseif (in_array("Passwords can only contain characters and numbers<br>",$error_array)) echo "Passwords can only contain characters and numbers<br>";
                    elseif (in_array("Your Password should be between 5 and 30 characters<br>",$error_array)) echo "Your Password should be between 5 and 30 characters<br>"; ?>

                    <input type="submit" name="reg_button" value="Register" >
                    <br>

                    <?php if (in_array("<span style='color: #14C800'> You are all set. Go ahead and login! </span><br>",$error_array)) echo "<span style='color: #14C800'> You are all set. Go ahead and login! </span><br>";?>
                    <a href="#" id="signin" class="signin"> Already have an account? Signin here!</a>

                </form>
            </div>
        </div>
    </div>
</body>
</html>
