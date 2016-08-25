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

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_SESSION["emp_code"])){
    $emp_code = $_SESSION["emp_code"];
}

$PSH_display_on = '';
$PSH_header_title = '';
$PSH_is_monthly = 'no';
$PSH_is_optional = 'no';
$count = 0;
$PSH_show_in_tmp_mod = 'no';

if (isset($_POST['btnSave'])) {
    extract($_POST);

    if ($PSH_header_title == "") {
        $err = "Header Title is required!";
    } elseif ($PSH_display_on == "") {
        $err = "Please select any one of Header Display On field";
    }else {

        $sqlCheck = "SELECT * FROM payroll_salary_header WHERE PSH_header_title='$PSH_header_title'";
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

            $insertHeader = '';
            $insertHeader .=' PSH_header_title = "' . mysqli_real_escape_string($open, $PSH_header_title) . '"';
            $insertHeader .=', PSH_display_on = "' . mysqli_real_escape_string($open, $PSH_display_on) . '"';
            $insertHeader .=', PSH_is_monthly = "' . mysqli_real_escape_string($open, $PSH_is_monthly) . '"';
            $insertHeader .=', PSH_is_optional = "' . mysqli_real_escape_string($open, $PSH_is_optional) . '"';
            $insertHeader .=', PSH_added_on = "' . mysqli_real_escape_string($open, $currentTime) . '"';
            $insertHeader .=', PSH_added_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';
            $insertHeader .=', PSH_updated_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';
            $insertHeader .=', PSH_show_in_tmp_mod = "' . mysqli_real_escape_string($open, $PSH_show_in_tmp_mod) . '"';
            
            $sqlSaveHeader = "INSERT INTO payroll_salary_header SET $insertHeader";

            $resultSaveHeader = mysqli_query($open, $sqlSaveHeader);

            if ($resultSaveHeader) {
                $msg = "Salary Header saved successfully.";
            } else {
                $err = "resultSaveHeader query failed." . mysqli_error($open);
            }
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
                <!--Flag: Decide if show in employee module-->
                <input value="yes" name="PSH_show_in_tmp_mod" type="checkbox" <?php
                       if ($PSH_show_in_tmp_mod == "yes") {
                           echo "checked='checked'";
                       }
                ?>/> Show in Employee Module?<br/>
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
            <div class="clearfix"></div>
            <div class="col-md-4">
                <br/><br/>
                <input type="submit" class="k-button" name="btnSave" value="Save Header">
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>