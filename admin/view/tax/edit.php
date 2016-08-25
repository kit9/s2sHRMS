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


$PET_employee_code = '';
$PET_employee_tax_status = false;
$PET_employee_tax_amount = 0;
$count = 0;
$taxID = 0;

if (isset($_GET['tid']) AND $_GET['tid'] > 0) {
    $taxID = $_GET['tid'];
}


if (isset($_POST['btnSave'])) {
    extract($_POST);

    if ($PET_employee_code == "") {
        $err = "Employee Code is required!";
    } elseif ($PET_employee_tax_status == true AND ( $PET_employee_tax_amount == "" OR $PET_employee_tax_amount <= 0)) {
        $err = "Tax Amount is required!";
    } else {

        $sqlCheck = "SELECT * FROM payroll_employee_tax WHERE PET_employee_code='$PET_employee_code' AND PET_id NOT IN ('$taxID')";
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

            $updateTax = '';
            $updateTax .=' PET_employee_code = "' . mysqli_real_escape_string($open, $PET_employee_code) . '"';
            $updateTax .=', PET_employee_tax_status = "' . mysqli_real_escape_string($open, $PET_employee_tax_status) . '"';
            $updateTax .=', PET_employee_tax_amount = "' . floatval($PET_employee_tax_amount) . '"';
            $updateTax .=', PET_updated_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';

            $sqlUpdateTax = "UPDATE payroll_employee_tax SET $updateTax WHERE PET_id=$taxID";
            $resultUpdateTax = mysqli_query($open, $sqlUpdateTax);

            if ($resultUpdateTax) {
                $msg = "Tax info updated successfully.";
            } else {
                $err = "resultUpdateTax query failed." . mysqli_error($open);
            }
        }
    }
}

if (isset($_GET['tid']) AND $_GET['tid'] > 0) {
    $sqlGetTax = "SELECT * FROM payroll_employee_tax WHERE PET_id=$taxID";
    $resultGetTax = mysqli_query($con->open(), $sqlGetTax);
    if ($resultGetTax) {
        $resultGetTaxObj = mysqli_fetch_object($resultGetTax);
        if (isset($resultGetTaxObj->PET_id)) {
            $PET_employee_code = $resultGetTaxObj->PET_employee_code;
            $PET_employee_tax_amount = $resultGetTaxObj->PET_employee_tax_amount;
            $PET_employee_tax_status = $resultGetTaxObj->PET_employee_tax_status;
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>


<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Update Tax to Employee</h6></div>
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
                <input type="submit" class="k-button" name="btnSave" value="Update Tax">
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