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

$emp_code = '';
$emp_name = '';
$start_date = '';
$end_date = '';
$emp_fullname  = '';
$emp_department = '';
$start_date = '';
$end_date = '';
$emp_contact_number = '';
$emp_id = '';
$employees = array();

if (isset($_POST["search_employee"])) {
    extract($_POST);
    $employees = $con->SelectAllByCondition("employee", " emp_code='$emp_code'");
    if (count($employees) > 0) {
        foreach ($employees as $employee) {
            $_SESSION["emp_id"] = $employee->emp_id;
            $emp_fullname .= $employee->emp_firstname;
            $emp_fullname .= ' ' . $employee->emp_lastname;
            $emp_photo = $employee->emp_photo;
            $emp_email = $employee->emp_email;
            $emp_department = $employee->emp_department;
            $emp_contact_number = $employee->emp_contact_number;
        }
    }
}

/*
 * Globally usable employee ID
 * Not generated until above form posted
 */
$emp_id = $_SESSION["emp_id"];

//Create schedule
if (isset($_POST['save'])) {
    extract($_POST);
    if ($start_date == '') {
        $err = 'Please specify start date.';
    } else if ($end_date == '') {
        $err = 'Please specify end date.';
    } else {
        //Check if the emp_id exists.
        if ($con->exists("annual_leave", array("emp_id" => $emp_id))) {
            $err = 'A schedule already exists for this employee.';
        } else {

            // Format start date
            $frm_start_date = date_create($start_date);
            $formatted_start_date = date_format($frm_start_date, 'Y-m-d');

            //Format end date
            $frm_end_date = date_create($end_date);
            $formatted_end_date = date_format($frm_end_date, 'Y-m-d');

            //Row Information
            $array = array(
                "emp_id" => $emp_id,
                "start_date" => $formatted_start_date,
                "end_date" => $formatted_end_date,
                "status" => 1
            );

            //Array insert
            if ($con->insert("annual_leave", $array) == 1) {
                //Now applied annual leave table data will be inserted with same dates
                $app_array = array(
                    "emp_id" => $emp_id,
                    "app_start_date" => $formatted_start_date,
                    "app_end_date" => $formatted_end_date,
                    "status" => 1
                );
                if ($con->insert("applied_annual_leave", $app_array) == 1){
                     $msg = 'A leave shcedule is created!';
                }        
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Search Employee</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-6">
                <label for="Full name">Employee Code:</label><br/>
                <input type="text" value="<?php echo $emp_code; ?>" name="emp_code" placeholder="" class="k-textbox" style="width: 80%;"/>
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

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Leave Schedule</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Information-->
        <div class="col-md-12">
            <?php if (count($employees) <= 0): ?>

                <span class="gray">Employee information will load here.</span>
                <br /><hr />
            <?php endif; ?>
            <?php if (count($employees) > 0): ?>
                <div class="col-md-3">
                    <img src="<?php echo $con->baseUrl($emp_photo); ?>" alt="" style="width:100px; height: 100px; border-radius: 5px;"/>
                </div>
                <div class="col-md-6">
                    Full Name: <?php echo $emp_fullname; ?><br />
                    Email : <?php echo $emp_email;?> <br />
                    Phone : <?php echo $emp_contact_number;?> <br />
                    Department: <?php echo $emp_department; ?>
                </div>

                <div class="clearfix"></div>
                <br /><hr />
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>

        <!--Leave Scheduler-->
        <form method="post">
            <div class="col-md-6">
                <label for="Start Date">Start Date:</label><br/>
                <input type="text" class="emp_datepicker" value="<?php echo $start_date; ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="col-md-6">
                <label for="Start Date">End Date:</label><br/>
                <input type="text" class="emp_datepicker" value="<?php echo $end_date; ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <input type="submit" class="k-button" value="Save Schedule" name="save"><br/><br />
            </div>
            <div class="clearfix"></div>
        </form>

        <script type="text/javascript">
            $(document).ready(function() {
                // create DatePicker from input HTML element
                $(".emp_datepicker").kendoDatePicker();
            });

        </script>

    </div>



</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
    




