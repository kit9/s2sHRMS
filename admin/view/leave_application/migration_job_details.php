<?php

session_start();
error_reporting(1);
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

$company_id = '';
$all_dates = array();
$all_employee = array();
$query_execute = array();
$emp_code = '';

$permission_id = '';

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

/*
 * Format today
 * Find year
 * Find deploy
 */
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$today_array = explode("-", $formatted_today);
$today_year = $today_array[0];
$build_first_date = $today_year . "-01-01";
$build_last_date = $today_year . "-12-31";
$first_day = date("Y-m-d", strtotime($build_first_date));
$last_day = date("Y-m-d", strtotime($build_last_date));

//Fetch all dates for a year
$all_dates = $con->SelectAllByCondition("dates", "(date BETWEEN '$first_day' AND '$last_day') AND company_id='1'");
$all_employee = $con->SelectAll("tmp_employee");
if (count($all_employee) > 0) {
    foreach ($all_employee as $emp) {
        $emp_code = $emp->emp_code;
        //Now fetch company info for this date
        $custom_query_for_com = "SELECT
                        *
                FROM
                        emp_company
                WHERE
                        ec_emp_code = '$emp_code'
                AND (
                        (
                                ec_effective_start_date <= '$formatted_today'
                                AND ec_effective_end_date >= '$formatted_today'
                        )
                        OR (
                                ec_effective_start_date <= '$formatted_today'
                                AND ec_effective_end_date = '0000-00-00'
                        )
        )";

        $query_execute = $con->QueryResult($custom_query_for_com);
        if (count($query_execute) > 0) {
            if (isset($query_execute{0}->ec_company_id)) {
                $company_id = $query_execute{0}->ec_company_id;
            }
        }

        //Find existing data
        $leave_status_array = $con->SelectAllByCondition("leave_status_meta", "emp_code = '$emp_code' AND ISNULL(company_id) AND year= '$today_year'");
        if (count($leave_status_array) > 0) {
            foreach ($leave_status_array as $la) {
                $leave_status_meta_id = $la->leave_status_meta_id;
                $update_array = array(
                    "leave_status_meta_id" => $leave_status_meta_id,
                    "company_id" => $company_id
                );
                $con->update("leave_status_meta", $update_array);
            }
        }
    }
}
$con->redirect("yearly_leave_register.php?permission_id=" . $permission_id . "&process_flag=1");











