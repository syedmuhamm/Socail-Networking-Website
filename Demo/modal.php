<?php
include("includes/header.php");
?>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap tutorial for begineers</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<br />
<div class="container">
    <div class="row">
        <div class="col-xs-12">

            <button id="btnShowModal" type="button"
                    class="btn btn-sm btn-default pull-right">
                Login
            </button>

            <div class="modal fade" tabindex="-1" id="loginModal"
                 data-keyboard="false" data-backdrop="static">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">
                                &times;
                            </button>
                            <h4 class="modal-title">Login</h4>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="form-group">
                                    <label for="inputUserName">Username</label>
                                    <input class="form-control" type="text"
                                           placeholder="Login Username" id="inputUserName" />
                                </div>
                                <div class="form-group">
                                    <label for="inputPassword">Password</label>
                                    <input class="form-control" placeholder="Login Password"
                                           type="password" id="inputPassword" />
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <button type="button" id="btnHideModal" class="btn btn-primary">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js">
</script>
<script src="bootstrap/js/bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
        $("#btnShowModal").click(function () {
            $("#loginModal").modal('show');
        });

        $("#btnHideModal").click(function () {
            $("#loginModal").modal('hide');
        });
    });
</script>

</body>
</html>