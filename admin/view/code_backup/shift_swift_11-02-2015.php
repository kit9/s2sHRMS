<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

date_default_timezone_set('UTC');
error_reporting(0);

$from_shift_start_day = '';
$from_shift_end_day = '';
$termnitated_employees = array();

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
$shifts = $con->SelectAll("shift_policy");

//Clear all employee from session
if (isset($_POST["clear_employee"])) {
    unset($_SESSION["all_employee"]);
    $con->redirect("shift_swift.php");
}
/*
 * Swaping shift
 * Displaying all existing employees in a shift
 * Collect all the employyes between two dates
 */
if (isset($_POST["viewAll"])) {
    extract($_POST);

    if ($from_shift_start_day == '') {
        $err = 'Please select start date of existing shift.';
    } else if ($from_shift_end_day == '') {
        $err = 'Please select end date of existing shift.';
    } else if ($shift_id == 0) {
        $err = 'Please select a shift to view assigned employees.';
    } else if ($company_id == 0) {
        $err = 'Please select a company to view assigned employees.';
    } else {

        $in_start_date = date("Y-m-d", strtotime($from_shift_start_day));
        $in_end_date = date("Y-m-d", strtotime($from_shift_end_day));


        //Inner Join query, company check removed due to company change
        $query_employees_in_shift = "SELECT
                    employee_shifing_user.*, tmp_employee.emp_firstname,
                    tmp_employee.emp_code
            FROM
                    employee_shifing_user
            INNER JOIN tmp_employee ON employee_shifing_user.emp_id = tmp_employee.emp_id 
                    WHERE
                    employee_shifing_user.shift_id = '$shift_id'
                    AND employee_shifing_user.schedule_date >= '$in_start_date'
                    AND employee_shifing_user.schedule_date <= '$in_end_date'
                    AND employee_shifing_user.company_id='$company_id' GROUP BY employee_shifing_user.emp_id";
        $employees_in_shift = $con->QueryResult($query_employees_in_shift);
        $_SESSION["all_employee"] = $employees_in_shift;
    }
}

//Storing all the employees in a session
if (isset($_SESSION["all_employee"])) {
    $employees_in_shift = $_SESSION["all_employee"];
}
/*
 * Existing employee arrays
 * $exo_employee : same date, same shift
 * $exo_employee_diff_shift : same date
 */
$exo_employee = array();
$exo_employee_diff_shift = array();

//Swap employees
if (isset($_POST["swap_employee"])) {
    extract($_POST);
    if ($company_id == 0) {
        $err = 'Please select a company before swapping.';
    } else if ($to_shift_start_day == '') {
        $err = 'Please select destination start date.';
    } else if ($to_shift_end_day == '') {
        $err = 'Please select destination end date.';
    } else if ($to_shift_id == '') {
        $err = 'Please select destination shift.';
    } else {
        //Format date
        $to_start_date = date("Y-m-d", strtotime($to_shift_start_day));
        $to_end_date = date("Y-m-d", strtotime($to_shift_end_day));
        //Collecting dates between date range
        $dates_query = "SELECT * from dates where company_id='$company_id' and date BETWEEN '$to_start_date' AND '$to_end_date'";
        $dates = $con->QueryResult($dates_query);
        //Collect all employee id
        $employees = $_POST['shift_employee'];
        if (count($employees) > 0) {
            //Build array for all employee
            while (list ($key, $val) = each($employees)) {
                foreach ($dates as $date) {
                    $s_date = $date->date;
                    
                    $array = array(
                        "emp_id" => $val,
                        "company_id" => $company_id,
                        "schedule_date" => $s_date,
                        "shift_id" => $to_shift_id
                    );

                    //Check company :: modified to main company table
                    $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$tmp_emp_code' AND ec_effective_start_date <= '$s_date' AND ec_effective_end_date >= '$s_date' LIMIT 0,1");
                    if (count($existing_company) > 0) {
                        $emp_company_id = $existing_company{0}->ec_company_id;
                    } else {
                        $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$tmp_emp_code' AND ec_effective_start_date <= '$s_date' AND ec_effective_end_date = '0000-00-00'");
                        if (count($existing_company) > 0) {
                            $emp_company_id = $existing_company{0}->ec_company_id;
                        }
                    }

                    if ($check_flag != '') {
                        if ($emp_company_id != $check_flag) {
                            $terminate_flag = 1;
                            $terminated_employee = $tmp_emp_code;
                        }
                    }

                    /*
                     * It is declared below the main condition
                     * Cause: it shouldnt be updated befor I check it in each iteration
                     */
                    $check_flag = $emp_company_id;

                    //Check if terminate flag is on or not
                    if ($terminate_flag == 1) {
                        $msg = "Submission succesfull! However, submission failed for one or more employees. Different companies were assigned to them within the selected date range.";
                        array_push($termnitated_employees, $terminated_employee);
                    } else {

                        //Check if shift company and employees are from the same company.
                        if ($emp_company_id == $company_id) {
                            //check existing defintion for diff shift ID
                            $existing_employees_diff_shift = "SELECT sh.emp_id, tmp.emp_code FROM employee_shifing_user sh 
                           INNER JOIN tmp_employee tmp ON tmp.emp_id=sh.emp_id
                           WHERE schedule_date = '$s_date' AND sh.shift_id != '$to_shift_id' AND sh.emp_id = '$val' AND sh.company_id='$company_id'";
                            $output_diff_shift = $con->QueryResult($existing_employees_diff_shift);

                            //Check existing definition for same shift ID
                            $exist_result = $con->existsByCondition("employee_shifing_user", " emp_id='$val' AND schedule_date ='$s_date' AND shift_id='$to_shift_id'");
                            if (count($output_diff_shift) > 0) {

                                $output_diff_shift_value = $output_diff_shift{0}->emp_code;
                                array_push($exo_employee_diff_shift, $output_diff_shift_value);
                                $msg = "Swap successfull! <font style=\"color:red\">However, following employees were already in another shift definition for specified date. They were unchanged.</font>";
                            } else if ($exist_result == 0) {

                                //Insert info shift table 
                                if ($con->insert("employee_shifing_user", $array, $open) == 1) {
                                    $msg = "Selected employees are shifted successfully shifted to target shift.";
                                    unset($_SESSION["all_employee"]);
                                } else {
                                    $err = "Something went wrong!";
                                }
                            } else {

                                $existing_employees = "SELECT sh.emp_id, tmp.emp_code FROM employee_shifing_user sh 
                        INNER JOIN tmp_employee tmp ON tmp.emp_id=sh.emp_id
                        WHERE schedule_date = '$s_date' AND sh.shift_id = '$to_shift_id' AND sh.emp_id = '$val' AND sh.company_id='$company_id'";
                                $output = $con->QueryResult($existing_employees);
                                if (count($output) > 0) {
                                    $output_value = $output{0}->emp_code;
                                    array_push($exo_employee, $output_value);
                                }

                                //if this date, emp_id, shift_id already exist.
                                $msg = "Swap successfull. However, following employees were already in the shift definition for specified date. They were unchanged.";
                                unset($_SESSION["all_employee"]);
                            }
                        } else {
                            $err = "Submission failed. One or more employees are not from the same company selected for shift assignment.";
                        }
                    }
                }
            }
            
            //Delete repeating element from array
            $exo_employee_unique = array_unique($exo_employee);
            $exo_employee_diff_shift = array_unique($exo_employee_diff_shift);
            $termnitated_employees = array_unique($termnitated_employees);
            
        } else {
            $err = 'Please select at least one employee to swap shift.';
        }
    }
}
if (isset($_POST["viewAll_sec"])) {
    extract($_POST);
    if ($to_shift_start_day == '') {
        $err = 'Please select destination start date.';
    } else if ($to_shift_end_day == '') {
        $err = 'Please select destination end date.';
    } else if ($to_shift_id == '') {
        $err = 'Please select destination shift.';
    } else {
        //Format date
        $to_start_date = date("Y-m-d", strtotime($to_shift_start_day));
        $to_end_date = date("Y-m-d", strtotime($to_shift_end_day));
        $query_employees_in_shift_sec = "SELECT
                    employee_shifing_user.*, tmp_employee.emp_firstname,
                    tmp_employee.emp_code
            FROM
                    employee_shifing_user
            INNER JOIN tmp_employee ON employee_shifing_user.emp_id = tmp_employee.emp_id
                    WHERE
                    employee_shifing_user.shift_id = '$to_shift_id'
                    AND employee_shifing_user.schedule_date >= '$to_start_date' 
                    AND employee_shifing_user.schedule_date <= '$to_end_date' AND employee_shifing_user.company_id='$company_id' GROUP BY employee_shifing_user.emp_id";
        $employees_in_shift_sec = $con->QueryResult($query_employees_in_shift_sec);
    }
}
//Collect all companies
$companies = $con->SelectAll("company");
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Body Content-->
<form method="post">
    <div class="widget" style="background-color: white;">
        <div class="widget-head">
            <h6 class="heading" style="color:whitesmoke;">Swap Employee in Shift</h6>
        </div>
        <div class="clearfix"></div>
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
                <?php else: ?>
                <br />
                <?php include("../../layout/msg.php"); ?>
            <?php endif; ?>

        </div>
        <div class="clearfix"></div>
        <br />

        <div class="col-md-12" style="padding-left:0px;">
            <!--Select Company Form-->
            <div class="col-md-6">
                <label for="Start Day" id="company_id">Company</label><br/>
                <select id="company" style="width: 86.5%" name="company_id">
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
        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-5" style="border: 1px solid silver; height: auto; margin-left: 8px; padding: 0 0 0 0px;">
            <div class="col-md-12" style="text-align: center; font-size: 12px; border-bottom: 1px solid silver">Select Properties to Swap</div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-12">
                <div class="col-md-6">
                    <label for="Start Day" id="lbl_shift_start_day">Start Day</label><br/>
                    <div><?php echo $con->DateTimePicker("from_shift_start_day", "from_shift_start_day", $from_shift_start_day, "", ""); ?></div> 
                </div>
                <div class="col-md-6">
                    <label for="Start Day" id="lbl_shift_end_day">End Day</label><br/>
                    <div><?php echo $con->DateTimePicker("from_shift_end_day", "from_shift_end_day", $from_shift_end_day, "", ""); ?></div>
                </div>
                <div class="clearfix"></div>
                <br />
                <div class="col-md-12">
                    <select id="office_start_day" name="shift_id" style="width:95%;">
                        <option>Select a Shift...</option>
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
            <div style="width:93%; padding-left: 7px;">
                <input type="submit" value="Clear Data" name="clear_employee" class="k-button" style="width: 120px;">
                <input type="submit" value="View Employees" name="viewAll" class="k-button pull-right" style="width: 120px;">
            </div>
            <div class="clearfix"></div>
            <br />
<?php if (count($employees_in_shift) > 0): ?>
                <div class="col-md-12">
                    <span style="font-size:12px;">Employees in this shift-</span>
                </div>
                <hr />
                <div class="col-md-12">
                    <script type="text/javascript">
                        $(function () {
                            // add multiple select / deselect functionality
                            $("#selectallemp").click(function () {
                                $('.case1').attr('checked', this.checked);
                            });

                            // if all checkbox are selected, check the selectall checkbox
                            $(".case1").click(function () {
                                if ($(".case").length == $(".case:checked").length) {
                                    $("#selectallemp").attr("checked", "checked");
                                } else {
                                    $("#selectallemp").removeAttr("checked");
                                }

                            });
                        });
                    </script>
                    <input type="checkbox" id="selectallemp"> Select All/Deselect All
                    <table style="width: 100%;">
                        <thead>
                        <th>Name</th>
                        <th >Code</th>
                        </thead>
    <?php foreach ($employees_in_shift as $emp): ?>
                            <tr>
                                <td>
                                    <span style="font-size: 11px;">
                                        <input type="checkbox" class="case1" name="shift_employee[]" checked="checked" value="<?php echo $emp->emp_id; ?>"> &nbsp;&nbsp;
                                        Name: <?php echo $emp->emp_firstname; ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="font-size: 11px;">
                                        Code:<?php echo $emp->emp_code; ?>
                                    </span>
                                </td>
                            </tr>
    <?php endforeach; ?>

                    </table> 

                    <br />
                    <div class="clearfix"></div>
                </div>
<?php endif; ?>
            <div class="clearfix"></div>
        </div>

    </div>

    <div class="col-md-1" style="text-align: center">
        <div style="height: 80px; width:20px;"></div>
        <img src="right_arrow.png" style="width:50px; height: 50px;"/>
    </div>

    <div class="col-md-5" style="border: 1px solid silver; height: auto; margin-left: 8px; padding: 0 0 0 0px;">
        <div class="col-md-12" style="text-align: center; font-size: 12px; border-bottom: 1px solid silver">Select Destination Properties</div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-12">
            <div class="col-md-6">
                <label for="Start Day" id="lbl_shift_start_day">Start Day</label><br/>
                <div><?php echo $con->DateTimePicker("to_shift_start_day", "to_shift_start_day", $to_shift_start_day, "", ""); ?></div> 
            </div>
            <div class="col-md-6">
                <label for="Start Day" id="lbl_shift_end_day">End Day</label><br/>
                <div><?php echo $con->DateTimePicker("to_shift_end_day", "to_shift_end_day", $to_shift_end_day, "", ""); ?></div> 
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-12">
                <select id="office_start_day_to" name="to_shift_id" style="width:95%;">
                    <option>Select a Shift...</option>
<?php if (count($shifts) > 0): ?>
    <?php foreach ($shifts as $shift): ?>
                            <option value="<?php echo $shift->shift_id; ?>" 
                            <?php
                            if ($shift->shift_id == $to_shift_id) {
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
        <div style="width:93%; padding-left: 7px;">
            <input type="submit" value="Swap Employees" name="swap_employee" class="k-button" style="width: 120px;"> 
            <input type="submit" value="View Employees" name="viewAll_sec" class="k-button pull-right" style="width: 120px;">

        </div>
        <div class="clearfix"></div>
        <br />
<?php if (count($employees_in_shift_sec) > 0): ?>
            <div class="col-md-12">
                <span style="font-size:12px;">Employees in this selected date and shift-</span>

            </div>
            <hr />
            <div class="col-md-12">
                <table style="width: 100%;">
                    <thead>
                    <th>Name</th>
                    <th >Code</th>
                    </thead>

    <?php foreach ($employees_in_shift_sec as $emp_sec): ?>
                        <tr>
                            <td>
                                <span style="font-size: 11px;">
                                    Name: <?php echo $emp_sec->emp_firstname; ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 11px;">
                                    Code:<?php echo $emp_sec->emp_code; ?>
                                </span>
                            </td>
                        </tr>
    <?php endforeach; ?>

                </table>
                <br />
                <div class="clearfix"></div>
            </div>
<?php endif; ?>
        <div class="clearfix"></div>

    </div>
</div>

<div class="clearfix"></div>
<br />
</div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#shift_start_day").kendoDatePicker({format: "d/m/Y"});
        $("#shift_end_day").kendoDatePicker();
        $("#shift_start_day_to").kendoDatePicker();
        $("#shift_end_day_to").kendoDatePicker();
        $("#office_start_day").kendoDropDownList();
        $("#office_start_day_to").kendoDropDownList();
        $("#company").kendoDropDownList();
    });
</script>
<?php include '../view_layout/footer_view.php'; ?>