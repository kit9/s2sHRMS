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
$leave_types = array();

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$today_array = explode("-", $formatted_today);
$today_year = $today_array[0];

$build_first_date = $today_year . "-01-01";
$build_last_date = $today_year . "-12-31";
$first_day = date("Y-m-d", strtotime($build_first_date));
$last_day = date("Y-m-d", strtotime($build_last_date));


$employees = $con->SelectAll("tmp_employee");
if (count($employees) > 0) {
    foreach ($employees as $tmp) {
        $emp_code = $tmp->emp_code;
        $availed_days = 0;
        //Update the status table for remaining days and availed days for this year
        $leave_applications = $con->SelectAllByCondition("leave_application_details", "emp_code='$emp_code' AND status = 'approved' ORDER BY details_date");
        if (count($leave_applications) > 0) {
            foreach ($leave_applications as $applications) {
                $raw_date = $applications->details_date;
                $application_date = date("Y-m-d", strtotime($raw_date));
                $date_array = explode("-", $application_date);
                $year = $date_array[0];
                $leave_type_id = $applications->leave_type_id;
                if ($today_year == $year) {
                    //loop through status table
                    $status_array = $con->SelectAllByCondition("leave_status_meta", "emp_code='$emp_code' AND year='$year' AND leave_type_id='$leave_type_id'");
                    if (count($status_array) > 0) {
                        
                        $leave_status_meta_id = $status_array{0}->leave_status_meta_id;
                        $status_array_availed = $status_array{0}->availed_days;
                        $status_array_remained = $status_array{0}->remaining_days;
                        $total_days = $status_array{0}->total_days;

                        $availed_days = $status_array_availed + 1;
                        $remaining_days = $total_days - $availed_days;
                        if ($remaining_days >= 0){
                            $remaining_days_insert = $remaining_days;
                        }else {
                            $remaining_days_insert = 0;
                        }
                        
                        $availed_days_array = array(
                            "leave_status_meta_id" => $leave_status_meta_id,
                            "availed_days" => $availed_days,
                            "remaining_days" => $remaining_days_insert
                        );
                        if ($con->update("leave_status_meta", $availed_days_array) == 1) {
                            
                        }
                    }
                }
            }
        }
    }
}
$con->redirect("migration_carry_forward.php?permission_id?=" . $permission_id);