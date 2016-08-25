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

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Initialize variables
//Employee Info
$emp_code = '';
$emp_name = '';
$emp_fullname = '';
$emp_department = '';
$emp_contact_number = '';
$emp_id = '';
$employees = array();

//Application info
$application_date = '';
$employee_id = '';
$start_date = '';
$end_date = '';
$total_days = '';
$no_of_days = '';
$leave_type_id = '';
$is_approved = '';
$status = '';
$approved_date = '';
$approved_by_id = '';
$leaves = array();
$leave_type = '';

$applied_annual_leave_id = '';
//Fetch all leave types
$leaves = $con->SelectAll("leave_policy");

//Search employee
if (isset($_POST["search_employee"])) {
    extract($_POST);
    $employees = $con->SelectAllByCondition("employee", " emp_code='$emp_code'");
    if (count($employees) > 0) {
        foreach ($employees as $employee) {
            $emp_id = $employee->emp_id;
            $_SESSION["emp_id"] = $employee->emp_id;
            $emp_fullname .= $employee->emp_firstname;
            $emp_fullname .= ' ' . $employee->emp_lastname;
            $emp_photo = $employee->emp_photo;
            $emp_email = $employee->emp_email;
            $emp_department = $employee->emp_department;
            $emp_contact_number = $employee->emp_contact_number;
        }

        $applied_leaves = $con->SelectAllByCondition("applied_annual_leave", "emp_id='$emp_id'");
        if (count($applied_leaves) > 0) {
            foreach ($applied_leaves as $aleave) {
                $_SESSION['applied_annual_leave_id'] = $aleave->applied_annual_leave_id;
            }
        }
    }
}

/*
 * Globally usable employee ID
 * Not generated until above form posted
 */

if (isset($_SESSION["emp_id"])) {
    $emp_id = $_SESSION["emp_id"];
}

if (isset($_SESSION["applied_annual_leave_id"])) {
    $applied_annual_leave_id = $_SESSION["applied_annual_leave_id"];
}

//Create schedule
if (isset($_POST['save'])) {
    extract($_POST);
    if ($start_date == '') {
        $err = 'Please specify start date.';
    } else if ($end_date == '') {
        $err = 'Please specify end date.';
    } else if ($start_date < $end_date){
        $err = 'Invalid date range. Start date can not be smaller than end date.';
    } else if ($leave_type == '') {
        $err = 'Please select a leave type.';
    } else if ($emp_id == '') {
        $err = 'Please select an employee.';
    } else {
        //Formal today
        $today = date("Y/m/d");
        $sys_date = date_create($today);
        $formatted_today = date_format($sys_date, 'Y-m-d');

        //Format start date
        $frm_start_date = date_create($start_date);
        $formatted_start_date = date_format($frm_start_date, 'Y-m-d');

        //Format end date
        $frm_end_date = date_create($end_date);
        $formatted_end_date = date_format($frm_end_date, 'Y-m-d');

        //Now applied annual leave table data will be inserted with same dates
        $app_array = array(
            "application_date" => $formatted_today,
            "employee_id" => $emp_id,
            "start_date" => $formatted_start_date,
            "end_date" => $formatted_end_date,
            "no_of_days" => $no_of_days,
            "leave_type_id" => $leave_type,
            "is_approved" => $is_approved,
            "status" => $status,
            "approved_date" => $approved_date,
            "approved_by_id" => $approved_by_id
        );
        if ($con->insert("leave_application", $app_array) == 1) {
            unset($_SESSION["emp_id"]);
            if ($leave_type == 4) {
                $array = array(
                    "applied_annual_leave_id" => $applied_annual_leave_id,
                    "emp_id" => $emp_id,
                    "app_start_date" => $formatted_start_date,
                    "app_end_date" => $formatted_end_date,
                    "status" => 1
                );
                if ($con->update("applied_annual_leave", $array) == 1) {
                    
                }
            }
            $msg = 'A leave request is submitted. Once reviewed, you should recieve a confirmation email.';
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!--Link to All Leave Applications Page-->
<a href="../leave_applications/index.php" class="k-button pull-right" style="text-decoration: none;">All Leave Applications</a>
<div class="clearfix"></div>
<br />

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Employee Information</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-6">
                <label for="Full name">Enter your Employee Code:</label><br/>
                <input type="text" value="<?php echo $emp_code; ?>" name="emp_code" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <input type="submit" class="k-button" value="Show Details" name="search_employee"><br /><br />
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>

    <div class="col-md-12">
        <?php if (count($employees) > 0): ?>
            <span class="gray">Review Employee Information-</span>
            <br /><hr />
            <div class="col-md-3">
                <img src="<?php echo $con->baseUrl($emp_photo); ?>" alt="" style="width:100px; height: 100px; border-radius: 5px;"/>
            </div>
            <div class="col-md-6">
                Full Name: <?php echo $emp_fullname; ?><br />
                Email : <?php echo $emp_email; ?> <br/>
                Phone : <?php echo $emp_contact_number; ?><br/>
                Department: <?php echo $emp_department; ?>
            </div>

            <div class="clearfix"></div>
            <br /><hr />
        <?php endif; ?>
    </div>
    <div class="clearfix"></div>

</form>
</div>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Leave Request</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Information-->


        <!--Leave Scheduler-->
        <form method="post">

            <div class="col-md-4">
                <span>Please select a leave type:</span>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-12">
                <?php foreach ($leaves as $leave): ?>
                    <input type="radio" name="leave_type" value="<?php echo $leave->leave_policy_id; ?>">
                    <?php echo $leave->leave_title; ?> &nbsp;&nbsp;
                <?php endforeach; ?>
            </div>
            <div class="clearfix"></div>
            <hr />
            <div class="col-md-4">
                <label for="Start Date">Leave Starts From:</label><br/>
                <input type="text" class="emp_datepicker" value="<?php echo $start_date; ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="col-md-4">
                <label for="Start Date">Leave Ends At:</label><br/>
                <input type="text" class="emp_datepicker" value="<?php echo $end_date; ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="col-md-4">
                <label for="Start Date">Total Days:</label><br/>
                <input type="text" class="k-textbox" value="<?php echo $no_of_days; ?>" name="no_of_days" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <input type="submit" class="k-button" value="Submit" name="save"><br/><br />
            </div>
            <div class="clearfix"></div>
        </form>

        <script type="text/javascript">
            $(document).ready(function () {
                // create DatePicker from input HTML element
                $(".emp_datepicker").kendoDatePicker();
            });

        </script>

    </div>

</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
    




