<?php
session_start();
/*
 * Author: Rajan Hossain
 * Page: Search Employee
 */

//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Initialize variables
$emp_code = '';
$emp_name = '';

?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Search Employee</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post" action="payslip.php">
            <div class="col-md-6">
                <label for="Full name">Employee Code:</label><br/>
                <input type="text" value="<?php echo $emp_code;?>" name="emp_code" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <input type="submit" class="k-button" value="Search Employee" name="search_employee"><br /><br />
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>

</form>
</div>

</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
    



