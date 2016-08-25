<?php
session_start();
include 'config/class.config.php';
$con = new Config();
$err = "";
$msg = '';
?>
<?php include 'view_controller/login.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>S2S HRMS</title>
        <link rel="stylesheet" href="<?php echo $con->baseUrl("assets2/login/css/style.default.css"); ?>" type="text/css" />
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#login').submit(function () {
                    var u = jQuery('#username').val();
                    var p = jQuery('#password').val();
                    if (u == '' && p == '') {
                        return false;
                    }
                });
                jQuery('.login-alert').fadeIn();
            });
        </script>
    </head>

    <body class="loginpage">
        <div class="loginpanel">
            <div class="loginpanelinner">
                <div class="logo animate0 bounceIn"><img  src="images/rpac-logo.png" alt="" style="width:60%;height:70%;"/></div>
                <form id="login" method="POST" action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" >

                    <?php if (isset($err) && !empty($err)): ?>
                        <div id="inputwrapper login-alert">
                            <div class="alert alert-error">
                                <?php echo $err; ?>
                            </div></div>
                    <?php endif; ?>

                    <div class="inputwrapper animate1 bounceIn">
                        <input type="text" name="uName" id="username" placeholder="Enter Employee Code" />
                    </div>
                    <div class="inputwrapper animate2 bounceIn">
                        <input type="password" name="password" id="password" placeholder="Enter Password" />
                    </div>
                    <div class="inputwrapper animate3 bounceIn">
                        <button name="btnLogin">Sign In</button>
                    </div>  
                </form>
            </div>
        </div>
        <div class="loginfooter">
            <p>&copy; 2015 Systech Unimax Bangladesh. All Rights Reserved.</p>
        </div>
        <script language="javascript" type="text/javascript">
            document.getElementById("username").focus();
        </script>

    </body>
</html>

