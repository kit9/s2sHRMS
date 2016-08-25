<?php

session_start();
/*
 * author: Rajan Hossain
 * Date: June 15, 2014
 * RPAC- Payroll
 * controller file :: Manage Employee records 
 */

//Connection parameters
include '../../../config/class.config.php';
$con = new Config();
$open = $con->open();

$hr_emp_code = '';

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Fetch emp code from hr's selected emp code
if (isset($_SESSION["hr_emp_code"])) {
    $emp_code = $_SESSION["hr_emp_code"];
}

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$today_array = explode("-", $formatted_today);
$today_year = $today_array[0];

/*
 * Read data from employee table
 * Bind data to JSON array
 */

$query = "SELECT lms.*, lp.short_code, lp.leave_title FROM leave_status_meta lms
LEFT JOIN leave_policy lp ON lms.leave_type_id = lp.leave_policy_id
WHERE lms.emp_code = '$emp_code' AND year = '$today_year'";

$meta_result = $con->QueryResult($query);

if (count($meta_result) > 0) {
    for ($i = 0; $i < count($meta_result); $i++) {
        if ($meta_result[$i]->short_code == "LL") {
            unset($meta_result[$i]);
        }
    }
    /*
     * Since, an element is deleted.
     * Associated key is also deleted.
     * Sort function reorganizes the key from scratch
     * So, any missing key is added. 
     */
    sort($meta_result);
} else {
    $meta_result = $con->SelectAll("leave_policy");
    for ($i = 0; $i < count($meta_result); $i++) {
        if ($meta_result[$i]->short_code == "LL") {
            unset($meta_result[$i]);
        }
    }
    /*
     * Since, an element is deleted.
     * Associated key is also deleted.
     * Sort function reorganizes the key from scratch
     * So, any missing key is added. 
     */
    sort($meta_result);
}
//Bind all employee data to array
if (count($meta_result) > 0) {
    echo "{\"data\":" . json_encode($meta_result) . "}";
} else {
    echo "{\"data\":" . [] . "}";
}


