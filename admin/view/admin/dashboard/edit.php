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
$admin_id="";
$admin_user_name="";
$admin_email="";
if (isset($_GET['id'])) {
    
    $id = base64_decode($_GET['id']);
    $object_array=array("admin_id"=>$id);
    $admins = $con->SelectAllByID("admin", $object_array);
   
    foreach ($admins as $ad) {
        $admin_id = $ad->admin_id;
        $admin_email = $ad->admin_email;
        $admin_user_name = $ad->admin_user_name ;
    }
}
if (isset($_POST['add_admin'])) {
    extract($_POST);
    if (empty($admin_email)) {
        $msg = "Admin email is empty";
    } else if (empty($admin_user_name)) {
        $msg = "Admin user name is empty";
    } else {
        
            $update_array = array("admin_id"=>$admin_id,"admin_email" => $admin_email, "admin_user_name" => $admin_user_name);
            if ($con->update("admin", $update_array) == 1) {
                $msg = "Admin Update Successfully";
               
            } else {
                $msg = "Something went wrong!";
            }
        
    }
}

if (isset($_POST['btnLogout'])) {
    
    if ($con->logout() == 1){
        echo "working";
        $con->redirect("../../login.php");
    }else {
        
    }
}

?>
<?php include '../view_layout/header_view.php'; ?>
<div class="widget-body">
    <div class="widget-heading blue">
        <i class="icon-table pull-left"></i><h3 class="pull-left">Edit Admin Information</h3>
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
            <div style="height: 20px"></div>
        </div>
    <?php include("../../layout/msg.php");?>
    
<!--        <form method="post">-->
<form method="post"  id="add_admin">
            <div class="form-group">
                <input type="hidden" value="<?php echo $admin_id; ?>" name="admin_id"/>
    <label for="textelement">Admin Email</label>
    <input name="admin_email" type="text" value="<?php echo $admin_email; ?>"  class="form-control" id="text_element" placeholder=".....">
    </div>
            <div class="form-group">
                <label for="textelement">Admin User Name</label>
                <input name="admin_user_name" value="<?php echo $admin_user_name; ?>" type="text" class="form-control" id="text_element" placeholder=".....">
            </div>
            
            <div class="col-md-6">
                <button name="add_admin" class="btn blue" type="submit">Edit changes</button>
            </div>
        </form>
    </div>		

</div>	

<?php include '../view_layout/footer_view.php'; ?>
