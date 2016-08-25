<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();

if (isset($_GET["id"])){
    $id = $_GET["id"];
}
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
$admin_name = '';
$password = '';
$re_password = '';
$address = '';
$country = '';
$phone = '';

$users = $con->SelectAllByCondition("admin", "ad_id='$id'");
foreach ($users as $user) {
    $admin_name = $user->admin_name;
    $address = $user->address;
    $country = $user->country;
    $admin_image = $user->admin_image;
    $user_id = $user->user_id ;
    $edit = $user->admin_updated;
    $phone = $user->phone;
}

if (isset($_POST['edit_admin'])) {
    extract($_POST);
    if (empty($admin_email)) {
        $err = "Admin email is empty";
    } else if (empty($admin_username)) {
        $err = "Admin user name is empty";
    } else {
            $targetfolder = '../../uploads/admin/';
            $filename = basename($_FILES['admin_image']['name']);
            $targetfolder = $targetfolder . $filename;
            $uploadPath = substr($targetfolder, 6);
            if ($filename != '') {
                $array = array(
                    "ad_id" => $id,
                    "admin_email" => $admin_email,
                    "phone" => $phone,
                    "admin_fullname" => $admin_fullname,
                    "password" => $password,
                    "address" => $address,
                    "country" => $country,
                    "user_id" => $user_id,
                    "admin_image" => $uploadPath
                );
            }else {
                  $array = array(
                    "ad_id" => $id,
                    "admin_email" => $admin_email,
                    "phone" => $phone,
                    "admin_name" => $admin_name,
                    "password" => $password,
                    "address" => $address,
                    "country" => $country,
                    "user_id" => $user_id,
                );
            }
            if ($con->update("admin", $array) == 1) {

                if ($filename != "") {
                    move_uploaded_file($_FILES['admin_image']['tmp_name'], $targetfolder);
                }
                $msg = "New ad min account is created successfully.";
            } else {
                $err = "Sorry! Something went wrong!";
            }
        }
    }
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>
<style type="text/css">
    .edit_profile_img{
        width:70px;
        height: 60px;
        border-radius: 5px;         
    }
</style>
<div class="widget-body">
    <div class="widget-heading blue">
        <i class="icon-table pull-left"></i><h3 class="pull-left">Admin Account Information</h3>
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
        <!--Add Admin form-->
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="textelement">Email</label>
                <input name="admin_email" value="<?php echo $admin_email; ?>" type="text" class="form-control" id="text_element" placeholder=".....">
            </div>

            <div class="form-group">
                <label for="textelement">Full Name</label>
                <input name="admin_name" value="<?php echo $admin_name;?>" type="text" class="form-control" id="text_element" placeholder=".....">
            </div>

            <div class="form-group">
                <label for="textelement">Phone</label>
                <input name="phone" value="<?php echo $phone; ?>" type="text" class="form-control" id="text_element" placeholder=".....">
            </div>

            <div class="form-group">
                <label for="textelement">Profile Photo</label> &nbsp; &nbsp; &nbsp;
                <?php if (!empty($admin_image)):?>
                <img src="<?php echo $con->baseUrl($admin_image) ?>" class="edit_profile_img"> <br /><br />
                <?php else:?>
                <img src="empty.jpg" class="edit_profile_img"> <br/><br/>
                <?php endif;?>
                <input name="admin_image" type="file" class="form-control">
            </div>
            <div class ="form-group">
                <label for="textelement">Address</label>
                <input name="address" value="<?php echo $address; ?>" type="text" value="" class="form-control">
            </div>
            <div class="form-group">
                <label for="textelement">Country</label>
                <input name="country" value="<?php echo $country;?>" type="text" value="" class="form-control">
            </div>
            <br />
            <div class="col-md-6">
                <button name="edit_admin" class="btn blue" type="submit">Save changes</button>
            </div>
        </form>
    </div>		

</div>	

<?php include '../view_layout/footer_view.php'; ?>


