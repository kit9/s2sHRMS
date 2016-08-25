<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();

//Checking if logged in
if ($con->authenticate() == 1){
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1){
        $con->redirect("../../login.php");
    }
}


$err = "";
$msg = '';
$admin_email = '';
$admin_user_name = '';
$admin_password = '';
$re_admin_password='';
if (isset($_POST['add_admin'])) {
    extract($_POST);
    
    //$msg=$con->insert("country", $object_array);
    if (empty($admin_email)) {
        $err = "Admin email is empty";
    } else if (empty($admin_user_name)) {
        $err = "Admin user name is empty";
    } else if (empty($admin_password)) {
        $err = "Admin password is empty";
    } else if($admin_password != $re_admin_password){
        $err="Admin Retype password is empty";   
    } else {
        if ($con->exists("admin", array("admin_email" => $admin_email)) == 1) {
            $err = "Admin email is already Exists";
        } else {
            $array = array("admin_email" => $admin_email, "admin_user_name" => $admin_user_name, "admin_password" => $admin_password);
            if ($con->insert("admin",$array ) == 1) {
                $msg = "Data saved successfully";
            } else {
                $err = "Invalid Query";
            }
        }
    }
}





?>
<?php include '../view_layout/header_view.php'; ?>

<div class="widget-body">
    <div class="widget-heading blue">
        <i class="icon-table pull-left"></i><h3 class="pull-left">Admin Information</h3>
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
         <div class="col-md-12">
            <a href="index.php" class="btn blue pull-right">Go To List</a>
        </div>
         <div style="height: 20px"></div>
        <?php include("../../layout/msg.php");?>

        <!--Add Admin form-->
        <form method="post">
        <div class="form-group">
        <label for="textelement">Admin Email</label>
        <input name="admin_email" type="text" class="form-control" id="text_element" placeholder=".....">
        </div>
                <div class="form-group">
                    <label for="textelement">Admin User Name</label>
                    <input name="admin_user_name" type="text" class="form-control" id="text_element" placeholder=".....">
                </div>
                <div class="form-group">
        <label for="textelement">Admin Password</label>
        <input name="admin_password" type="password" class="form-control" id="text_element" placeholder=".....">
        </div>
                <div class="form-group">
        <label for="textelement">Retype Password</label>
        <input name="re_admin_password" type="password" class="form-control" id="text_element" placeholder=".....">
        </div>

        <div class="col-md-6">
            <button name="add_admin" class="btn blue" type="submit">Save changes</button>
        </div>
        </form>
    </div>		

</div>	

<?php include '../view_layout/footer_view.php'; ?>


