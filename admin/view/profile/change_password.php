<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$err = "";
$msg = '';
$admin_email = '';
$admin_username = '';
$password = '';
$re_password = '';
$address = '';
$country = '';
$phone = '';
$user_id ='';
if (isset($_GET["id"])){
    $id = $_GET["id"];
    $users = $con->SelectAllByCondition("admin", "ad_id='$id'");
    foreach ($users as $user) {
        $password = $user->password;
        $admin_email = $user->admin_email;
    }
}
if (isset($_POST['edit_password'])) {
    extract($_POST);
    if (empty($old)) {
        $err = "Admin email is empty.";
    } else if (empty($new)) {
        $err = "Admin full name is empty.";
    } else if ($new != $re_password){
        $err = "Please make sure two new passwords match with each other.";
    } else {
        if ($password == $old){
            $array = array(
                "ad_id" => $id,
                "password" => $new
            );
            if ($con->update("admin", $array) == 1) {
                $msg = "Your password has been reset succefully!";
            } else {
                $err = "Password reset operation failed. Given old password does not match with your account credentials.";
            }
        }else {
            $err = "Password reset failed. Your old password did not match the one stores in the database. ";
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<style type="text/css">
    .edit_profile_img{
        width:70px;
        height: 60px;
        border-radius: 5px;         
    }
</style>
<div class="widget-body">
    <div class="widget-heading blue">
        <i class="icon-table pull-left"></i><h3 class="pull-left">Admin Account Information- Reset Password</h3>
        <ul>
            <li class="dropdown panel-function">
                <a href="#" data-toggle="dropdown" role="button" id="drop2"> <b class="caret"></b></a>
                <ul aria-labelledby="drop2" role="menu" class="dropdown-menu" id="menu2">
                    <li role="presentation"><a class="hide-btn" title="">-</a></li>
                    <li role="presentation"><a class="close-sec" title="">x</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="widget-sec">
        <div class="col-md-12"><a href="index.php" class="btn blue pull-right">Back to Profile</a></div>
        <div style="height: 40px"></div>
        <?php include("../../layout/msg.php"); ?>
        <!--Add Admin form-->
        <form method="post">
            <div class="form-group">
                <label for="textelement">Old Password</label>
                <input name="old" type="password" class="form-control" id="text_element" placeholder=".....">
            </div>

            <div class="form-group">
                <label for="textelement">New Password</label>
                <input name="new" type="password" class="form-control" id="text_element" placeholder=".....">
            </div>

            <div class="form-group">
                <label for="textelement">Confirm Password</label>
                <input name="re_password" type="password" class="form-control" id="text_element" placeholder=".....">
            </div>
            <div class="col-md-6">
                <button name="edit_password" class="btn blue" type="submit">Change Password</button>
            </div>
        </form>
    </div>		

</div>	

<?php include '../view_layout/footer_view.php'; ?>


