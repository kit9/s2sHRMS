<?php
session_start();
//Importing class library
include ('../../config/class.config.php');

//Configuration classes
$con = new Config();

//Connection string
$open = $con->open();

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}


if (isset($_POST["frmUplaod"])) {
    extract($_POST);
    $filename = basename($_FILES['attendance_file']['name']);
    $targetfolder = 'Attendance/'. $filename .'';
   
    if ($filename != "") {
       move_uploaded_file($_FILES['attendance_file']['tmp_name'], $targetfolder);
       chmod($targetfolder, 0777);
       $msg = "Attendance file succesfully updated";
    }
}
?>


<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Attendance File</h6></div>
    <div class="widget-body" background-color: white;>
<?php include("../../layout/msg.php"); ?>
        <form method="post" enctype="multipart/form-data">
            <div class = "col-md-6">
                <div style = "width:80%">
                    <label for = "Full name">Upload a File</label> <br />
                    <input name = "attendance_file" id = "files" type = "file"/>
                </div>
            </div>
            <input type="submit" value="Upload File" name="frmUplaod">
        </form>
         <div class="clearfix"></div>
    </div>
</div>
<div class="clearfix"></div>
<?php include '../view_layout/footer_view.php'; ?>