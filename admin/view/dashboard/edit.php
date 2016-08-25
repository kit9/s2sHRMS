<?php
session_start();
//Im porting class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
//Initialize variable
$emp_fullname = '';
$emp_contact_number = '';
//Employee code
if (isset($_GET["emp_id"])) {
    $emp_id = $_GET['emp_id'];
}
$_SESSION["emp_id"] = $emp_id;
$emp_id = $_SESSION["emp_id"];

$employees = $con->SelectAllByCondition("employee", " emp_id='$emp_id'");
if (count($employees) > 0) {
    foreach ($employees as $employee) {
        $_SESSION["emp_id"] = $employee->emp_id;
        $emp_fullname .= $employee->emp_firstname;
        $emp_fullname .= ' ' . $employee->emp_lastname;
        $emp_photo = $employee->emp_photo;
        $emp_email = $employee->emp_email;
        $emp_department = $employee->emp_department;
    }
}

//Getting planned leave data
$p_leaves = $con->SelectAllByCondition("annual_leave", "emp_id='$emp_id'");
foreach ($p_leaves as $pleave) {
    $annual_leave_id = $pleave->annual_leave_id;
    $start_date = $pleave->start_date;
    $end_date = $pleave->end_date;
}

//Getting applied leave data
$a_leaves = $con->SelectAllByCondition("applied_annual_leave", "emp_id='$emp_id'");
foreach ($a_leaves as $aleave) {
    $applied_annual_leave_id = $aleave->applied_annual_leave_id;
    $app_start_date = $aleave->app_start_date;
    $app_end_date = $aleave->app_end_date;
}

/*
 * Editing the planned leave
 * Saving edited schedule
 * Deleting the applied date
 */
if (isset($_POST['edit_leave'])) {
    extract($_POST);

    // Format start date
    $frm_start_date = date_create($start_date);
    $formatted_start_date = date_format($frm_start_date, 'Y-m-d');

    //Format end date
    $frm_end_date = date_create($end_date);
    $formatted_end_date = date_format($frm_end_date, 'Y-m-d');

    //Data Row to Edit    
    $array = array(
        "annual_leave_id" => $annual_leave_id,
        "start_date" => $formatted_start_date,
        "end_date" => $formatted_end_date
    );

    //Update annual leave planned
    if ($con->update("annual_leave", $array) == 1) {
        //Now edit the applied leave for this user
        $a_array = array(
            "applied_annual_leave_id" => $applied_annual_leave_id,
            "app_start_date" => $formatted_start_date,
            "app_end_date" => $formatted_end_date
        );

        //Update annual leave applied
        if ($con->update("applied_annual_leave", $a_array) == 1) {
            $msg = 'Leave schedule updated successfully!';
            
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Employee Information</h6></div>
    <div class="widget-body" background-color: white;>

         <?php include("../../layout/msg.php"); ?>
         <?php if (count($employees) > 0): ?>
             <div class="col-md-3">
                <img src="<?php echo $con->baseUrl($emp_photo); ?>" alt="" style="width:100px; height: 100px; border-radius: 5px;"/>
            </div>
            <div class="col-md-6">
                Full Name: <?php echo $emp_fullname; ?><br />
                Email : <?php echo $emp_email; ?> <br />
                Phone : <?php echo $emp_contact_number; ?> <br />
                Department: <?php echo $emp_department; ?>
            </div>

            <div class="clearfix"></div>
            <br /><hr />
        <?php endif; ?>


        <span>Planned Schedule: <?php echo $start_date . ' - ' . $end_date . ' '; ?> </span>
        &nbsp;&nbsp;  &nbsp;&nbsp;  &nbsp;&nbsp;
        <span>Applied Schedule: <?php echo $app_start_date . ' - ' . $app_end_date . ' '; ?> </span>
        <hr />
        
        <span class="gray">Finalize leave schedule using following form. This will merge planned schedule and applied schedule-
        <br /><br />
            <form method="post">
            <!--Start Date-->
            <div class="col-md-6">
                <label for="Full name">Leave Starts From:</label> <br />
                <input style="width: 60%" type="text" value="<?php echo $start_date; ?>" class="emp_datepicker" placeholder="" name="start_date" type="text"/>
            </div>
 
            <script type="text/javascript">
                $(document).ready(function() {
                    // create DatePicker from input HTML element
                    $(".emp_datepicker").kendoDatePicker();
                });
            </script>
            
            <!--End Date-->
            <div class="col-md-6">
                <label for="Full name">Leave Ends at:</label> <br />
                <input style="width:60%" type="text" value="<?php echo $end_date; ?>" class="emp_datepicker" placeholder="" name="end_date"/>
            </div>
            
            <script type="text/javascript">
                $(document).ready(function() {
                    $(".emp_datepicker").KendoDatePicker();
                });
            </script>
            <div class="clearfix"></div>
            <br />


            <div class="col-md-6">
                <input type="submit" class="k-button" value="Save Changes" name="edit_leave"><br /><br />
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
</div>

<?php include '../view_layout/footer_view.php'; ?>



