<?php
session_start();
/** Author: Rajan Hossain
 * Page: Search Employee
 */
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';
$headerID = 0;

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
}

if (isset($_GET['hid']) AND $_GET['hid']) {
    $headerID = $_GET['hid'];
}

$PSH_display_on = '';
$PSH_header_title = '';
$PSH_is_monthly = 'no';
$PSH_is_optional = 'no';
$count = 0;

if (isset($_POST['btnSave'])) {
    extract($_POST);
    if ($PSH_header_title == "") {
        $err = "Header Title is required!";
    } elseif ($PSH_display_on == "") {
        $err = "Header Display On is required!";
    } else {

        $sqlCheck = "SELECT * FROM payroll_salary_header WHERE PSH_header_title='$PSH_header_title' AND PSH_id NOT IN ('$headerID')";
        $resultCheck = mysqli_query($open, $sqlCheck);

        if ($resultCheck) {
            $count = mysqli_num_rows($resultCheck);
        } else {
            $err = "resultCheck query failed.";
        }

        if ($count > 0) {
            $err = "<strong>" . $PSH_header_title . "</strong> already exist in database.";
        } else {
            $currentTime = date("Y-m-d H:i:s");
            $updateHeader = '';
            $updateHeader .=' PSH_header_title = "' . mysqli_real_escape_string($open, $PSH_header_title) . '"';
            $updateHeader .=', PSH_display_on = "' . mysqli_real_escape_string($open, $PSH_display_on) . '"';
            $updateHeader .=', PSH_is_monthly = "' . mysqli_real_escape_string($open, $PSH_is_monthly) . '"';
            $updateHeader .=', PSH_is_optional = "' . mysqli_real_escape_string($open, $PSH_is_optional) . '"';
            $updateHeader .=', PSH_updated_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';
//            $updateHeader .=', PSH_is_required = "' . mysqli_real_escape_string($open, $PSH_is_required) . '"';

            $sqlUpdateHeader = "UPDATE payroll_salary_header SET $updateHeader WHERE PSH_id=$headerID";
            $resultUpdateHeader = mysqli_query($open, $sqlUpdateHeader);

            if ($resultUpdateHeader) {
                $msg = "Salary Header updated successfully.";
            } else {
                $err = "resultUpdateHeader query failed." . mysqli_error($open);
            }
        }
    }
}

if ($headerID > 0) {
    $sqlGetHeader = "SELECT * FROM payroll_salary_header WHERE PSH_id=$headerID";
    $resultGetHeader = mysqli_query($open, $sqlGetHeader);
    if ($resultGetHeader) {
        $resultGetHeaderObj = mysqli_fetch_object($resultGetHeader);
        if (isset($resultGetHeaderObj->PSH_id)) {
            $PSH_display_on = $resultGetHeaderObj->PSH_display_on;
            $PSH_header_title = $resultGetHeaderObj->PSH_header_title;
            $PSH_is_monthly = $resultGetHeaderObj->PSH_is_monthly;
            $PSH_is_optional = $resultGetHeaderObj->PSH_is_optional;
//            $PSH_is_required = $resultGetHeaderObj->PSH_is_required;
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Salary Component</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-6">
                <label for="Full name">Header Title:</label><br/>
                <input type="text" class="k-textbox"  name="PSH_header_title" value="<?php echo $PSH_header_title; ?>" placeholder=""  style="width: 80%;"/>
            </div>

            <div class="col-md-6">
                <br/>
                <label for="Full name">Header Display On:</label><br/>
                <input value="sheet" name="PSH_display_on" type="radio" <?php
                if ($PSH_display_on == "sheet") {
                    echo "checked='checked'";
                }
                ?>/> Show in Salary Sheet?<br/>
                <input value="add" name="PSH_display_on" type="radio" <?php
                if ($PSH_display_on == "add") {
                    echo "checked='checked'";
                }
                ?>/> Show in Addition?<br/>
                <input value="deduct" name="PSH_display_on" type="radio" <?php
                if ($PSH_display_on == "deduct") {
                    echo "checked='checked'";
                }
                ?>/> Show in Deduction?<br/>
            </div>

            <div class="col-md-6">
                <br/>
                <label for="Full name">Is Monthly? :</label>
                <input value="yes" name="PSH_is_monthly" type="checkbox" <?php
                if ($PSH_is_monthly == "yes") {
                    echo "checked='checked'";
                }
                ?>/> (Yes)<br/>
            </div>

            <div class="col-md-6">
                <br/>
                <label for="Full name">Is Optional? :</label>
                <input value="yes" name="PSH_is_optional" type="checkbox" <?php
                if ($PSH_is_optional == "yes") {
                    echo "checked='checked'";
                }
                ?>/> (Yes)<br/>
            </div>
<!--            <div class="clearfix"></div>
             <div class="col-md-6">
                <br/>
                <label for="Is Required">Is Required? :</label>
                <input value="yes" name="PSH_is_required" type="checkbox" <?php
//                       if ($PSH_is_required == "yes") {
//                           echo "checked='checked'";
//                       }
                ?>/> (Yes)<br/>
            </div>-->
            <div class="clearfix"></div>

            <div class="col-md-4">
                <br/><br/>
                <input type="submit" class="k-button" name="btnSave" value="Update Header">
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>

</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>