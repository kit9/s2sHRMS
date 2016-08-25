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
$emp_code = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}


if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Restrict browsing through URL without having a permission
if ($con->hasPermissionView($permission_id) != "yes") {
    $con->redirect("../dashboard/index.php");
}


$PET_employee_code = '';
$PET_employee_tax_amount = 0;
$count = 0;

if (isset($_POST['btnSave'])) {
    extract($_POST);
    if ($PET_employee_code == "") {
        $err = "Employee Code is required!";
    } elseif ($PET_employee_tax_amount == "") {
        $err = "Tax Amount is required!";
    } else {

        //Find employee's company
        $company_id = '';
        $emp_company = array();
        $emp_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$PET_employee_code' ORDER BY emp_company_id DESC LIMIT 0,1");
        if (count($emp_company) > 0) {
            $company_id = $emp_company{0}->ec_company_id;
        }

        $sqlCheck = "SELECT * FROM payroll_employee_tax WHERE PET_employee_code='$PET_employee_code'";
        $resultCheck = mysqli_query($open, $sqlCheck);

        if ($resultCheck) {
            $count = mysqli_num_rows($resultCheck);
        } else {
            $err = "resultCheck query failed.";
        }

        if ($count > 0) {
            $err = "Tax record already exist for <strong>" . $PET_employee_code . "</strong>.";
        } else {

            $currentTime = date("Y-m-d H:i:s");

            $assignTax = '';
            $assignTax .=' PET_employee_code = "' . mysqli_real_escape_string($open, $PET_employee_code) . '"';
            $assignTax .=', PET_employee_tax_status = "' . mysqli_real_escape_string($open, "true") . '"';
            $assignTax .=', PET_employee_tax_amount = "' . floatval($PET_employee_tax_amount) . '"';
            $assignTax .=', PET_created_on = "' . mysqli_real_escape_string($open, $currentTime) . '"';
            $assignTax .=', PET_created_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';
            $assignTax .=', PET_updated_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';
            $assignTax .=', company_id = "' . mysqli_real_escape_string($open, $company_id) . '"';

            $sqlAssignTax = "INSERT INTO payroll_employee_tax SET $assignTax";
            $resultAssignTax = mysqli_query($open, $sqlAssignTax);

            if ($resultAssignTax) {
                $msg = "Tax assigned successfully.";
            } else {
                $err = "resultAssignTax query failed." . mysqli_error($open);
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>


<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Assign Tax to Employee</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-12">
                <label for="emp_code"> Employee Code : </label><br />
                <input type="text"  name="PET_employee_code" id="emp_code_hr" value="<?php echo $PET_employee_code; ?>" style="width: 80%;">
            </div>


            <div class="col-md-12">
                <br/><br/>
                <label for="Full name">Tax Amount:</label><br/>
                <input type="number" class="k-textbox"  name="PET_employee_tax_amount" value="<?php echo $PET_employee_tax_amount; ?>" placeholder=""  style="width: 80%;"/>
            </div>

            <div class="clearfix"></div>

            <div class="col-md-4">
                <br/><br/>
                <input type="submit" class="k-button" name="btnSave" value="Assign Tax">
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>



</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>

<script>

    $(document).ready(function() {
        jQuery(document).ready(function() {
            var departments = $("#emp_code_hr").kendoComboBox({
                placeholder: "Select Employee...",
                dataTextField: "emp_name",
                dataValueField: "emp_code",
                dataSource: {
                    transport: {
                        read: {
                            url: "../../controller/leave_management_controllers/employee_list.php",
                            type: "GET"
                        }
                    },
                    schema: {
                        data: "data"
                    }
                }
            }).data("kendoComboBox");
        });
    });
</script>