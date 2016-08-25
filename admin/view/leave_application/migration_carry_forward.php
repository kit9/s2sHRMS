<?php
//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

$permission_id = '';

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

/*
 * Loop against employee
 * For each employee, run another loop
 * Find joining date of each employee
 * Only if the joining date is bigger than Januray 1st
 * Find total possible working days upto December 31st. 
 */

/*
 * Nested loop: inside employee loop
 * Loop through all leave types
 * Find total working days.
 * Formula to be applied - (available days of a leave type/365)*total working days
 * Pro rate basis is inserted into database
 */

/*
 * If the joining date is before this year, then all employees should be able to 
 * have all days avaialable in this year. Default, based on the available after settings,
 * validation will be checked in time of application.
 * Normally, employee will see all available days for him on pro rate basis. 
 */

/*
 * After all this has been done succesfully,
 * then carried forward values for employees from the previous year 
 * will be fecthed and updated for each leave type and for each employee 
 */

/*
 * I will also need to develop a machanism for updating leave status for all employee
 * for each year. This machanism should be consistent and should be working with exquisite accuracy. 
 */

/*
 * Fetch all employees
 */
$remaining_days_prev_year = '';
$carry_forward_flag = '';
$total_days_current = '';
$current_leave_meta_id = '';
$is_carried_forward = '';
$max_carry_forward = '';
$existing_status_of_leave_type = array();
$leave_types = array();
$employees = array();

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$today_array = explode("-", $formatted_today);
$today_year = $today_array[0];
$previous_year = $today_year - 1;

$employees = $con->SelectAll("tmp_employee");
if (count($employees) > 0) {
    foreach ($employees as $tmp) {
        $emp_code = $tmp->emp_code;
        $leave_types = $con->SelectAll("leave_policy");
        if (count($leave_types) > 0) {
            foreach ($leave_types as $leave_all) {
                $leave_type_id = $leave_all->leave_policy_id;
                $is_carried_forward = $leave_all->is_carried_forward;
                if ($is_carried_forward == "true") {
                    $max_carry_forward = $leave_all->max_carry_forward;
                    $existing_status_of_leave_type = $con->SelectAllByCondition("leave_status_meta", "emp_code='$emp_code' AND leave_type_id='$leave_type_id' AND year = '$previous_year'");

                    if (count($existing_status_of_leave_type) > 0) {
                        $remaining_days_prev_year = $existing_status_of_leave_type{0}->remaining_days;
                    }
                    
                    if ($remaining_days_prev_year > $max_carry_forward) {
                        $remaining_days = $max_carry_forward;
                    } else {
                        $remaining_days = $remaining_days_prev_year;
                    }

                    $current_leave_meta = $con->SelectAllByCondition("leave_status_meta", "emp_code='$emp_code' AND leave_type_id='$leave_type_id' AND year = '$today_year'");
                    if (count($current_leave_meta) > 0) {
                        $current_leave_meta_id = $current_leave_meta{0}->leave_status_meta_id;
                        $total_days_current = $current_leave_meta{0}->total_days;
                        $carry_forward_flag = $current_leave_meta{0}->carry_forward_flag;
                    }

                    $carried_total = $total_days_current + $remaining_days;
                    $update_array = array(
                        "leave_status_meta_id" => $current_leave_meta_id,
                        "total_days" => $carried_total,
                        "remaining_days" => $carried_total,
                        "carry_forward_flag" => 1
                    );
                    
                    if ($carry_forward_flag != 1) {
                        $con->update("leave_status_meta", $update_array);
                    }
                }
            }
        }
    }
}
$con->redirect("migration_job_details.php?permission_id=" . $permission_id);




