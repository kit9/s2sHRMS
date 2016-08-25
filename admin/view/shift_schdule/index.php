<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
error_reporting(0);
// Set timezone
date_default_timezone_set('UTC');

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}


//initializing variables
$department_id = '';
$department_title = '';
$designation_title = '';
$subsection_title = '';
$staffgrade_id = '';
$staffgrade_title = '';
$reporting_id = '';
$reporting_title = '';
$attendance_policy_id = '';
$policy_title = '';
$shift_id = array();
$shift_title = '';
$company_id = '';
$company_title = '';
$saturday_start_time = '';
$staffgrades = $con->SelectAll("staffgrad");
$companies = $con->SelectAll("company");
$reportings = $con->SelectAll("reporting_method");
$attendances = $con->SelectAll("attendance_policy"); // $shifts  
$shifts = $con->SelectAll("shift_policy");
$shiftquery = array();
$termnitated_employees = array();
//To check if there are different companies for employees in a target date range for a shift.
$check_flag = '';

if (isset($_GET['shift_id'])) {
    $shift_id = $_GET['shift_id'];
    $_SESSION["shift_id"] = $shift_id;
    $condition = "shift_id='" . $shift_id . "'order by shift_id DESC";
    $shiftquery = $con->SelectAllByCondition("shift_policy", $condition);
    if (count($shiftquery) >= 1) {
        foreach ($shiftquery as $n) {
            $shift_title = $n->shift_title;
            $shift_start_day = $n->shift_start_day;
            $shift_end_day = $n->shift_end_day;
            $saturday_start_time = $n->saturday_start_time;
            $saturday_end_time = $n->saturday_end_time;
        }
    }

    //Destroying complete status on shift change
    if (isset($_SESSION["shift_id"])) {
        if ($_SESSION["shift_id"] != $_GET["shift_id"]) {
            unset($_SESSION["delete_complete"]);
        }
    }


    $query_shifting = "SELECT
                sh.*, e.emp_code,
                e.emp_firstname
        FROM
                employee_shifing_user AS sh,
                tmp_employee AS e
        WHERE
                sh.emp_id = e.emp_id
        AND sh.shift_id = '$shift_id'
        GROUP BY
                e.emp_code";

    $result_shifting = mysqli_query($open, $query_shifting);
    while ($rows_shifting = mysqli_fetch_object($result_shifting)) {
        $employee_shifts_user[] = $rows_shifting;
    }
}

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Declaring local variables
$resul = '';
$err = "";
$msg = '';

//Submitting the form
if (isset($_POST["submit_add"])) {
    extract($_POST);

    $shiftStart = date_create($shift_start_day);
    $shiftStartDate = date_format($shiftStart, 'Y-m-d');
    $shiftEnd = date_create($shift_end_day);
    $shiftEndDate = date_format($shiftEnd, 'Y-m-d');
    $strFirstTime = date("H:i:s", strtotime($_POST["saturday_start_time"]));
    $strEndTime = date("H:i:s", strtotime($_POST["saturday_end_time"]));
    $update_shift_id = $_GET["shift_id"];
    $temp_employees = '';
    $temp_success_employee = '';
    $allEmployees = count($_POST['emp_code']);

    if ($main_company_id == 0) {
        $err = "Please select a company!";
    } elseif (empty($shift_start_day)) {
        $err = "Shift Start Day is not selected!";
    } elseif (empty($shift_end_day)) {
        $err = "Shift End Day is not selected!";
    } else {
        for ($i = 0; $i < $allEmployees; $i++) {
            $tmp_emp_code = end(explode("-", $_POST['emp_code'][$i]));
            $emps = $con->SelectAllByCondition("tmp_employee", " emp_code='$tmp_emp_code'");
            $tmp_emp_id = $emps{0}->emp_id;
            $tmp_emp = explode("-", $_POST['emp_code'][$i]);
            if ($checkExists == 0) {

                $temp_success_employee .= " " . $tmp_emp;
                $dates = $con->SelectAllByCondition("dates", " date BETWEEN '$shiftStartDate' AND '$shiftEndDate' AND company_id='$main_company_id'");

                foreach ($dates as $d) {

                    //Array to insert
                    $DatesarrayN = array(
                        "shift_id" => $shift_id,
                        "company_id" => $main_company_id,
                        "schedule_date" => $d->date,
                        "emp_id" => $tmp_emp_id
                    );

                    $schedule_date = $d->date;

                    //Check company :: modified to main company table
                    $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$tmp_emp_code' AND ec_effective_start_date <= '$schedule_date' AND ec_effective_end_date >= '$schedule_date' LIMIT 0,1");
                    if (count($existing_company) > 0) {
                        $emp_company_id = $existing_company{0}->ec_company_id;
                    } else {
                        $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$tmp_emp_code' AND ec_effective_start_date <= '$schedule_date' AND ec_effective_end_date = '0000-00-00'");
                        if (count($existing_company) > 0) {
                            $emp_company_id = $existing_company{0}->ec_company_id;
                        }
                    }

                    /*
                     * Check flag variable keeps the company ID in each iteration
                     * Conditon checks if found company ID is a match with it. 
                     * If not, in any iteration, instantly it creates terminate_flag with a value 1
                     */

                    //Check if terminate flag is on or not
                    if ($emp_company_id != $main_company_id) {
                        $terminated_employee = $tmp_emp_code;
                        $msg_terminated = "Assignment partially successfull. <font color=\"red\">"
                                . " For one or more employee Company information does not match with the"
                                . " selected company for assigning shift."
                                . " This happens when you select a different company"
                                . " than the one you selected to assign the shift, one or more employees"
                                . " have two different company assigned in selected date range."
                                . " Check following employees-</font>";
                        array_push($termnitated_employees, $terminated_employee);
                    } else {
                        //Check if shift company and emp company matches
                        if ($emp_company_id == $main_company_id) {
                            //if employees exits in the same shift same date
                            $existing_employees_diff_shift = "SELECT sh.emp_id, tmp.emp_code FROM employee_shifing_user sh 
                            INNER JOIN tmp_employee tmp ON tmp.emp_id=sh.emp_id
                            WHERE schedule_date = '$d->date' AND sh.shift_id != '$shift_id' AND sh.emp_id = '$tmp_emp_id' AND sh.company_id='$main_company_id'";
                            $output_diff_shift = $con->QueryResult($existing_employees_diff_shift);

                            //if employees exists in a differet shift, but in same date
                            $existing_employees = "SELECT sh.emp_id, tmp.emp_code FROM employee_shifing_user sh 
                            INNER JOIN tmp_employee tmp ON tmp.emp_id=sh.emp_id
                            WHERE schedule_date = '$d->date' AND sh.shift_id = '$shift_id' AND sh.emp_id = '$tmp_emp_id' AND sh.company_id='$main_company_id'";
                            $output = $con->QueryResult($existing_employees);

                            if (count($output_diff_shift) > 0) {
                                $msg = "Assignment partially successfull! <font style=\"color:red\">However, some employees were already in another shift definition for specified date. They were unchanged.</font>";

                                //Emloyees in other shifts in the date range
                                $output_diff_shift_value = $output_diff_shift{0}->emp_code;
                                array_push($exo_employee_diff_shift, $output_diff_shift_value);
                            } else if (count($output) > 0) {
                                $msg = "Assignment partially successfull. However, some employees were already in the shift definition for specified date. They were unchanged.";
                                //Employee is already has shift in this date
                                $output_value = $output{0}->emp_code;
                                array_push($exo_employee, $output_value);
                            } else {
                                //Finally with everything valid, insert the array
                                if ($con->insert("employee_shifing_user", $DatesarrayN) == 1) {
                                    $msg = 'Employees were successfully added to selected shift.';
                                }
                            }
                        } else {
                            $err = "Submission failed! One or more employees are not from the selected company for shift assignment.";
                        }
                    }
                }

                //Delete repeating element from all the arrays with error with failed emp_code. 
                $exo_employee_unique = array_unique($exo_employee);
                $exo_employee_diff_shift = array_unique($exo_employee_diff_shift);
                $termnitated_employees = array_unique($termnitated_employees);
            }
        }
    }
}

//Deleting existing employees in a shift
if (isset($_POST["delete_employee"])) {
    extract($_POST);
    if (empty($_POST['employee_new_shift_pattern'])) {
        $err = "No employee is selected!";
    } else {
        $employees = $_POST['employee_new_shift_pattern'];
        while (list ($key, $val) = @each($employees)) {
            $objectarray = array("emp_id" => $val);
            if ($con->delete("employee_shifing_user", $objectarray, $open) == 1) {
                $_SESSION["delete_complete"] = 'active';
                $msg = "Employee deleted successfully";
                $con->redirect("index.php?shift_id=$shift_id");
            } else {
                $err = "Something was wrong with deleting the employee!";
            }
        }
    }
}

//Fetch all employees in a shift
if (isset($_POST["view_employee"])) {
    extract($_POST);
    if ($view_shift_start_day == '') {
        $err = 'Please select start date of existing shift.';
    } else if ($view_shift_end_day == '') {
        $err = 'Please select end date of existing shift.';
    } else if ($shift_id == 0) {
        $err = 'Please select a shift to view assigned employees.';
    } else if ($company_id == 0) {
        $err = 'Please select a company to view assigned employees.';
    } else {
        $view_start_date = date("Y-m-d", strtotime($view_shift_start_day));
        $view_end_date = date("Y-m-d", strtotime($view_shift_end_day));
        $query_employees_in_shift = "SELECT
                    employee_shifing_user.*, tmp_employee.emp_firstname,
                    tmp_employee.emp_code
            FROM
                    employee_shifing_user
            INNER JOIN tmp_employee ON employee_shifing_user.emp_id = tmp_employee.emp_id
                    WHERE
                    employee_shifing_user.shift_id = '$shift_id'
                    AND employee_shifing_user.schedule_date >= '$view_start_date'
                    AND employee_shifing_user.schedule_date <= '$view_end_date'
                    AND employee_shifing_user.company_id='$company_id' GROUP BY employee_shifing_user.emp_id";
        $employees_in_shift = $con->QueryResult($query_employees_in_shift);
        $_SESSION["all_employee"] = $employees_in_shift;
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Employee Information</h6></div>
    <div class="widget-body">
        <div class="col-md-12">
            <?php if (count($exo_employee_unique) > 0): ?>
                <br />
                <div class="alert alert-success fade in">
                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $msg; ?>
                    <hr />
                    <?php
                    foreach ($exo_employee_unique as $key => $data) {
                        echo $data;
                        echo ", ";
                    }
                    echo '<br />';
                    ?>
                </div>
            <?php elseif (count($exo_employee_diff_shift) > 0): ?>
                <br />
                <div class="alert alert-success fade in">
                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $msg; ?>
                    <hr />
                    <font style="color: red;">
                    <?php
                    foreach ($exo_employee_diff_shift as $key => $data_diff_shift) {
                        echo $data_diff_shift;
                        echo ", ";
                    }
                    echo '<br />';
                    ?>
                    </font>
                </div>

                <!--List of terminated employees-->
            <?php elseif (count($termnitated_employees) > 0): ?>
                <br />
                <div class="alert alert-success fade in">
                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $msg_terminated; ?>
                    <hr />
                    <font style="color: red;">
                    <?php
                    foreach ($termnitated_employees as $key => $emp_terminated) {
                        echo $emp_terminated;
                        echo ", ";
                    }
                    echo '<br />';
                    ?>
                    </font>
                </div>               
            <?php else: ?>
                <br />
                <?php include("../../layout/msg.php"); ?>
            <?php endif; ?>

        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-6" style="margin-top: -20px; margin-left: -10px;">
            <?php
            if (isset($_GET["shift_id"])) {
                echo "<b>Selected Shift: </b>";
                echo $shift_title;
                echo ' (' . date("H:i", strtotime($saturday_start_time)) . ' - ' . date("H:i", strtotime($saturday_end_time)) . ')';
            }
            ?>
        </div>
        <div class="clearfix"></div>
        <br />

        <div>
            <div style="border: 1px solid #72AF46; min-height:200px;" class="col-md-2">
                <h5 style="text-align:center;"> Available Shifts</h5>
                <hr style="margin-top: -2px;" />
                <?php if (count($shifts) >= 1): ?>
                    <?php foreach ($shifts as $p): ?>
                        <label for="Full name"> <a href="index.php?shift_id=<?php echo $p->shift_id; ?>&permission_id=<?php echo $permission_id; ?>">
                                <?php
                                if ($p->shift_id == $shift_id) {
                                    echo "<span style=\"color:gray;\">" . $p->shift_title . "</span>";
                                } else {
                                    echo $p->shift_title;
                                }
                                ?>
                            </a></label> <br />
                    <?php endforeach; ?>
                <?php endif; ?> 
            </div>
        </div>  
        <div style="height: auto; width:82%; float: right;" id="example" class="k-content">

            <form method="post" enctype="multipart/form-data" name="frm1">
                <div id="tabstrip" style="border-style:none;">
                    <ul style="margin-top:-3px;">
                        <li>
                            Schedule
                        </li>
                        <li class="k-state-active">
                            Assign Employees
                        </li>
                    </ul>

                    <div style="min-height: 167px;">

                        <div class="weather">

                            <div style="border: 0px solid #72AF46; margin-left: 30px;" class="col-md-11">
                                <?php if (isset($_GET['shift_id'])) { ?>                                                                                                                                                                                                                                                                                                                                                                         <!--                                    <table style="width:800px; text-align: center; font-family: calibri; font-size: 16px;">
                                    <!--  sat start time-->
                                    <div class="col-md-4">
                                        Active for:
                                    </div>
                                    <div class="col-md-4">

                                        Start Date: <?php echo $shift_start_day; ?>
                                    </div>

                                    <div class="col-md-4">
                                        End Date: <?php echo $shift_end_day; ?>
                                    </div>

                                    <hr>
                                    <div style="margin-bottom: 4px; " class="clearfix"></div>

                                    <!--  sat start time-->
                                    <div class="col-md-4">
                                        Shift Schedule: 
                                    </div>
                                    <div class="col-md-4">

                                        Start Time: <?php echo $saturday_start_time; ?>
                                    </div>
                                    <div class="col-md-4">
                                        End Time: <?php echo $saturday_end_time; ?>
                                    </div>

                                    <div class="clearfix"></div>
                                    <?php
                                } else {
                                    echo "<h4>Please Select a Shifting Policy</h4>";
                                }
                                ?>

                            </div>


                            <div class="clearfix"></div>
                            <br />

                        </div>
                    </div>

                    <div style="min-height: 167px;">
                        <div class="weather" style="padding-top: 5px;">
                            <?php if (isset($_GET['shift_id'])) { ?>
                                <div class="col-md-4">
                                    <label for="Full name">Company:</label> <br />
                                    <select id="main_company_id" style="width: 100%" name="main_company_id">
                                        <option value="0">Select Company</option>
                                        <?php if (count($companies) >= 1): ?>
                                            <?php foreach ($companies as $com): ?>
                                                <option value="<?php echo $com->company_id; ?>" 
                                                <?php
                                                if ($com->company_id == $main_company_id) {
                                                    echo "selected='selected'";
                                                }
                                                ?>><?php echo $com->company_title; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                    </select>
                                </div>                              
                                <div class="col-md-4">
                                    <label for="Full name">Start Date:</label> <br />
                                    <input type="text" value="<?php echo $shift_start_day; ?>" id="shift_start_day" placeholder="" name="shift_start_day" type="text" style="width: 100%;"/>
                                </div>
                                <div class="col-md-4">
                                    <label for="Full name">End Date:</label> <br />
                                    <input type="text" value="<?php echo $shift_end_day; ?>" id="shift_end_day" placeholder="" name="shift_end_day" type="text" style="width: 99%;"/>
                                </div>
                                <div class="clearfix"></div>
                                <br/>


                                <div class="widget" style="background-color: white; width:98%;">
                                    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Employee Information</h6></div>
                                    <div class="widget-body">

                                        <div class="col-md-4">
                                            <label for="Full name">Company:</label><br/> 
                                            <input id="companies1" name="company_title" style="width: 80%;" value="<?php echo $company_title; ?>" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="Full name">Department:</label> <br />
                                            <input id="department1" name="department_title" style="width: 80%;" value="<?php echo $department_title; ?>" />
                                        </div>

                                        <div class="col-md-4">
                                            <label for="Full name">Sub Section:</label> <br />
                                            <input id="subsections1" name="emp_subsection" style="width: 80%;" value="<?php echo $emp_subsection; ?>" />
                                            <!-- auto complete start-->
                                        </div>

                                        <div class="clearfix"></div>
                                        <br/>

                                        <script>
                                            $(document).ready(function () {
                                                // create DatePicker from input HTML element
                                                $("#shift_start_day").kendoDatePicker();
                                                $("#shift_end_day").kendoDatePicker();
                                                $("#saturday_start_time").kendoTimePicker();
                                                $("#saturday_end_time").kendoTimePicker();
                                            });
                                        </script>

                                        <script type="text/javascript">
                                            $(document).ready(function () {
                                                var companies1 = $("#companies1").kendoComboBox({
                                                    placeholder: "Select Company...",
                                                    dataTextField: "company_title",
                                                    dataValueField: "company_id",
                                                    dataSource: {
                                                        transport: {
                                                            read: {
                                                                url: "../../controller/company.php",
                                                                type: "GET"
                                                            }
                                                        },
                                                        schema: {
                                                            data: "data"
                                                        }
                                                    }
                                                }).data("kendoComboBox");

                                                var department1 = $("#department1").kendoComboBox({
                                                    autoBind: true,
                                                    placeholder: "Select Department..",
                                                    dataTextField: "department_title",
                                                    dataValueField: "department_id",
                                                    dataSource: {
                                                        transport: {
                                                            read: {
                                                                url: "../../controller/department.php",
                                                                type: "GET"
                                                            }
                                                        },
                                                        schema: {
                                                            data: "data"
                                                        }
                                                    }
                                                }).data("kendoComboBox");

                                                var subsections1 = $("#subsections1").kendoComboBox({
                                                    autoBind: false,
                                                    placeholder: "Select Subsection..",
                                                    dataTextField: "subsection_title",
                                                    dataValueField: "subsection_title",
                                                    dataSource: {
                                                        transport: {
                                                            read: {
                                                                url: "../../controller/subsection.php",
                                                                type: "GET"
                                                            }
                                                        },
                                                        schema: {
                                                            data: "data"
                                                        }
                                                    }
                                                }).data("kendoComboBox");

                                            });
                                        </script>


                                        <script type="text/javascript">
                                            $(document).on('change', '#companies1', function () {
                                                //                            alert("asdas");
                                                var com_id = $("#companies1").val();
                                                $.ajax({
                                                    type: 'POST',
                                                    url: '../../controller/getCompanyEmployee.php',
                                                    data: {com_id: com_id},
                                                    success: function (response) {

                                                        var objects = eval(response.data);
                                                        //                                     alert(objects);
                                                        var checkBoxHtml = "";
                                                        $("#valueCheck").html('');
                                                        //                                    $("#givmail").html('');

                                                        $(objects).each(function (index, obj) {
                                                            checkBoxHtml += '<input class="case1" style="margin-bottom: 3%" type="checkbox" name="emp_code[]" id="emp_code"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';

                                                            //console.log(obj.Email_address);
                                                        });
                                                        // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                                        $("#valueCheck").html(checkBoxHtml);
                                                        //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                                        //                                    $("#givmail").html(datas);
                                                    }
                                                });
                                            });
                                        </script>




                                        <script type="text/javascript">
                                            $(document).on('change', '#department1', function () {
                                                var dept_id = $("#department1").val();
                                                $.ajax({
                                                    type: 'POST',
                                                    url: '../../controller/get_department_employee.php',
                                                    data: {dept_id: dept_id},
                                                    success: function (response) {

                                                        var objects = eval(response.data);
                                                        //                                     alert(objects);
                                                        var checkBoxHtml = "";
                                                        $("#valueCheck").html('');
                                                        //                                    $("#givmail").html('');

                                                        $(objects).each(function (index, obj) {
                                                            checkBoxHtml += '<input class="case1" style="margin-bottom: 3%" type="checkbox" name="emp_code[]" id="emp_code"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';

                                                            //console.log(obj.Email_address);
                                                        });
                                                        // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                                        $("#valueCheck").html(checkBoxHtml);
                                                        //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                                        //                                    $("#givmail").html(datas);
                                                    }
                                                });
                                            });
                                        </script>


                                        <script type="text/javascript">
                                            $(document).on('change', '#subsections1', function () {
                                                //                            alert("asdas");
                                                var subsection_title = $("#subsections1").val();
                                                //                            console.log(department_id);
                                                $.ajax({
                                                    type: 'POST',
                                                    url: '../../controller/get_subsection_employee.php',
                                                    data: {subsection_title: subsection_title},
                                                    success: function (response) {

                                                        var objects = eval(response.data);
                                                        console.log(objects);
                                                        var checkBoxHtml = "";
                                                        $("#valueCheck").html('');
                                                        //                                    $("#givmail").html('');

                                                        $(objects).each(function (index, obj) {
                                                            checkBoxHtml += '<input class="case1" type="checkbox" name="emp_code[]" id="emp_code"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';
                                                            //console.log(obj.Email_address);
                                                        });
                                                        // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                                        $("#valueCheck").html(checkBoxHtml);
                                                        //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                                        //                                    $("#givmail").html(datas);
                                                    }
                                                });
                                            });
                                        </script>

                                        <div class="clearfix"></div>
                                        <br/>
                                        <div class="col-md-6">
                                            <div id="valueCheck"> 

                                            </div>
                                        </div>


                                        <div class="clearfix"></div>
                                        <br/>
                                        <div class="col-md-6">
                                            <input type="checkbox" id="selectallemp"/> Select All
                                        </div>

                                        <script type="text/javascript">
                                            $(function () {
                                                $("#selectallemp").click(function () {
                                                    $('.case1').attr('checked', this.checked);
                                                });
                                                $(".case1").click(function () {
                                                    if ($(".case").length == $(".case:checked").length) {
                                                        $("#selectallemp").attr("checked", "checked");
                                                    } else {
                                                        $("#selectallemp").removeAttr("checked");
                                                    }

                                                });
                                            });
                                        </script>
                                        <div class="clearfix"></div>
                                        <br/>

                                        <?php if ($con->hasPermissionCreate($permission_id) == "yes"): ?>
                                            <div class="col-md-3">
                                                <input class="k-button" style="width:70px;" type="submit" value="Add" name="submit_add">
                                            </div>
                                        <?php endif; ?>

                                        <div class="clearfix"></div>
                                        <?php
                                    } else {
                                        echo "<br/><br/><br/><h4>Please Select a Shifting Policy.</h4>";
                                    }
                                    ?>

                                </div>
                            </div>
                            <div class="widget" style="background-color: white; width:98%;">
                                <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Existing Employee Information</h6></div>
                                <div class="widget-body">
                                    <div style="min-height: 167px;">
                                        <div class="weather">
                                            <?php if (isset($_GET["shift_id"])) : ?>
                                                <div class="col-md-6">
                                                    <label for="Start Day" id="company_id">Company</label><br/>
                                                    <select id="to_view_company" style="width: 86.5%" name="company_id">
                                                        <option value="0">Select Company</option>
                                                        <?php if (count($companies) >= 1): ?>
                                                            <?php foreach ($companies as $com): ?>
                                                                <option value="<?php echo $com->company_id; ?>" 
                                                                <?php
                                                                if ($com->company_id == $company_id) {
                                                                    echo "selected='selected'";
                                                                }
                                                                ?>><?php echo $com->company_title; ?></option>
                                                                    <?php endforeach; ?>
                                                                <?php endif; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="col-md-6" style="padding-left: 0px;">
                                                        <label for="Start Day" id="company_id">Selected Shift</label>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                    <select class="shift_id_abc" id="shift_id" name="shift_id" disabled="disabled" style="width:95%;">
                                                        <option value="0">Select a Shift...</option>
                                                        <?php if (count($shifts) > 0): ?>
                                                            <?php foreach ($shifts as $shift): ?>
                                                                <option value="<?php echo $shift->shift_id; ?>" 
                                                                <?php
                                                                if ($shift->shift_id == $shift_id) {
                                                                    echo "selected='selected'";
                                                                }
                                                                ?>
                                                            <option><?php echo $shift->shift_title; ?></option> 
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="clearfix"></div>
                                            <br />
                                            <div class="col-md-6">
                                                <label for="Start Day" id="lbl_shift_start_day">Start Day</label><br/>
                                                <div><?php echo $con->DateTimePicker("view_shift_start_day", "view_shift_start_day", $view_shift_start_day, "width:86.5%", ""); ?></div> 
                                            </div>
                                            <div class="col-md-6">
                                                <label for="Start Day" id="lbl_shift_end_day">End Day</label><br/>
                                                <div><?php echo $con->DateTimePicker("view_shift_end_day", "view_end_start_day", $view_shift_end_day, "width:95%", ""); ?></div>
                                            </div>
                                            <div class="clearfix"></div>
                                            <br />
                                            <?php if ($con->hasPermissionView($permission_id) == "yes"): ?>
                                                <div class="col-md-12">
                                                    <input type="submit" class="k-button" name="view_employee" value="View Employees">
                                                </div>
                                            <?php endif; ?>
                                            <div class="clearfix"></div>
                                            <hr />                            
                                            <h5>Employees in this shift</h5>
                                            <div class="col-md-6">
                                                <?php if (count($employees_in_shift) >= 1): ?>
                                                    <?php foreach ($employees_in_shift as $ems): ?>
                                                        <input style="margin-bottom: 3%" type="checkbox" class="case" name="employee_new_shift_pattern[]" value="<?php echo $ems->emp_id; ?>"> &nbsp;<?php echo $ems->emp_firstname . " - " . $ems->emp_code; ?><br/>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="clearfix"></div>
                                            <div class="col-md-6">
                                                <input type="checkbox" id="selectall"/> Select All
                                            </div>
                                            <div class="clearfix"></div>
                                            <!--Select/Deselect Function-->
                                            <script type="text/javascript">
                                                $(function () {
                                                    // add multiple select / deselect functionality
                                                    $("#selectall").click(function () {
                                                        $('.case').attr('checked', this.checked);
                                                    });
                                                    // Reverse and Viceversa
                                                    $(".case").click(function () {
                                                        if ($(".case").length == $(".case:checked").length) {
                                                            $("#selectall").attr("checked", "checked");
                                                        } else {
                                                            $("#selectall").removeAttr("checked");
                                                        }
                                                    });
                                                });
                                            </script>
                                            <br/>
                                            <?php if ($con->hasPermissionDelete($permission_id) == "yes"): ?>
                                                <div class="col-md-6">
                                                    <input class="k-button" type="submit" value="Delete" name="delete_employee">
                                                </div>
                                            <?php endif; ?>
                                            <div class="clearfix"></div>        
                                        <?php endif; ?>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <br/>
                </div>
                <div class="clearfix"></div>


        </form>
        <style scoped>
            #forecast {
                width: 100%;
                height: auto;
                margin: 30px auto;
                padding: 80px 15px 0 15px;
                background: url('../../content/web/tabstrip/forecast.png') transparent no-repeat 0 0;
            }

            .sunny, .cloudy, .rainy {
                display: inline-block;
                margin: 20px 0 20px 10px;
                width: 128px;
                height: auto;
                background: url('../../content/web/tabstrip/weather.png') transparent no-repeat 0 0;
            }

            .cloudy{
                background-position: -128px 0;
            }

            .rainy{
                background-position: -256px 0;
            }

            .weather {
                width: 100%;
                padding: 40px 0 0 0;

            }

            #forecast h2 {
                font-weight: lighter;
                font-size: 5em;
                padding: 0;
                margin: 0;
            }

            #forecast h2 span {
                background: none;
                padding-left: 5px;
                font-size: .5em;
                vertical-align: top;
            }

            #forecast p {
                margin: 0;
                padding: 0;
            }
        </style>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#tabstrip").kendoTabStrip({
                    animation: {
                        open: {
                            effects: "fadeIn"
                        }
                    }
                });
            });

            $(document).ready(function () {
                $("#size").kendoDropDownList();
                $("#size2").kendoDropDownList();
                $("#size3").kendoDropDownList();
                $("#size4").kendoDropDownList();
                $("#size5").kendoDropDownList();
                $("#size6").kendoDropDownList();
                $("#size7").kendoDropDownList();
                $("#size8").kendoDropDownList();
                $("#size9").kendoDropDownList();
                $("#size10").kendoDropDownList();
                $("#size11").kendoDropDownList();
                $("#size13").kendoDropDownList();
                $("#size14").kendoDropDownList();
                $("#size15").kendoDropDownList();
                $("#new").kendoDropDownList();
            });

            $(document).ready(function () {
                $("#files").kendoUpload();
            });
        </script>

        <script type="text/javascript">
            $(document).ready(function () {
                var departments = $("#departments").kendoComboBox({
                    placeholder: "Select department...",
                    dataTextField: "department_title",
                    dataValueField: "department_id",
                    dataSource: {
                        //                            type: "json",
                        //                            data: categoriesData

                        transport: {
                            read: {
                                url: "../../controller/department.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox");

                var products = $("#sections").kendoComboBox({
                    autoBind: false,
                    cascadeFrom: "departments",
                    placeholder: "Select Section..",
                    dataTextField: "subsection_title",
                    dataValueField: "subsection_id",
                    dataSource: {
                        //                        type: "json",
                        //                        data: productsData
                        transport: {
                            read: {
                                url: "../../controller/sub_section.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox");

                var products = $("#designations").kendoComboBox({
                    autoBind: false,
                    cascadeFrom: "departments",
                    placeholder: "Select Designation..",
                    dataTextField: "designation_title",
                    dataValueField: "designation_id",
                    dataSource: {
                        //                        type: "json",
                        //                        data: productsData
                        transport: {
                            read: {
                                url: "../../controller/designation.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox");

            });
        </script>
    </div>
</div>
<br />
<div class="clearfix"></div>

</div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#to_view_company").kendoDropDownList();
        $("#shift_id").kendoDropDownList();
        $("#main_company_id").kendoDropDownList();

    });
</script>

