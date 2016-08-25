<?php
session_start();
/*
 * Author: Shahnaz
 * Page: Employee Shifting
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

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Initialize variables
$emp_code = '';
$emp_name = '';

$employes = $con->SelectAll("employee");
if (isset($_GET['shift_id'])) {
    $shift_id = $_GET['shift_id'];
    $condition = "shift_id='" . $shift_id . "'order by shift_id DESC";
}

//$con->debug($shift_id);


if (isset($_POST["add"])) {
    extract($_POST);

    if (empty($_POST['employee_shift'])) {
        $err = "No employee is selected!";
    } else {
        $employees = $_POST['employee_shift'];
        while (list ($key, $val) = @each($employees)) {
            $array1 = array(
                "shift_id" => $shift_id,
                "emp_id" => $val
            );
//            $con->debug($val);

            if ($con->exists("employee_shifing_user", array("emp_id" => $val)) == 1) {
                $err = "Employee is already Added";
            } elseif ($con->insert("employee_shifing_user", $array1, $open) == 1) {
                $msg = "Employee added successfully";

                $con->redirect("index.php");
            } else {
                $err = "Something was wrong with storing the package employee!";
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Employee</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Code-->
        <form method="POST">

               <div class="col-md-6">
                                <label for="Full name">Holiday Title</label> <br />
                                <input type="text" value="<?php echo $holiday_title; ?>" name="holiday_title" placeholder="" class="k-textbox" type="text" id="emp_fullname" style="width: 80%;"/>
                            </div>
            
            <div class="col-md-6">
                                <label for="Full name">Holiday Type</label> <br />
                                <input type="text" value="<?php echo $holiday_title; ?>" name="holiday_title" placeholder="" class="k-textbox" type="text" id="emp_fullname" style="width: 80%;"/>
                            </div>
            
            
            



<!--            <input style="margin-left: 200px;" class="k-button" type="submit" name="add" value="Add">-->
        </form>
        <!--Code-->



    </div>
    <div class="clearfix"></div>

</form>
</div>

</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
    



