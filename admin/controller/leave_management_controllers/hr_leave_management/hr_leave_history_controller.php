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


/*
 * Read data from employee table
 * Bind data to JSON array
 */ $histories = array();
$history_query = "select  A.leave_application_master_id, A.leave_type_id, A.mindate, B.replacement_date, B.is_half, B.day_part, B.maxdate, A.details_no_of_days,  A.status, A.remarks, A.leave_title, A.review_remark FROM
    (SELECT lam.review_remark, leave_application_details.leave_application_master_id, leave_type_id, min(details_date) as mindate , details_no_of_days,  leave_application_details.status, leave_title, remarks from leave_application_details 
    LEFT JOIN leave_policy  ON leave_policy.leave_policy_id =  leave_application_details.leave_type_id 
JOIN leave_application_master lam on lam.leave_application_master_id = leave_application_details.leave_application_master_id 
     where leave_application_details.leave_application_master_id 
    in(SELECT  leave_application_master_id from leave_application_master  WHERE emp_code='$emp_code')
    GROUP BY leave_application_master_id, leave_type_id)  A,
    (SELECT  leave_application_master_id, leave_type_id, replacement_date, is_half, day_part, max(details_date) as maxdate from leave_application_details where leave_application_master_id 
    in(SELECT  leave_application_master_id from leave_application_master  WHERE emp_code='$emp_code')
    GROUP BY leave_application_master_id, leave_type_id) B
    WHERE A.leave_application_master_id = B.leave_application_master_id and A.leave_type_id = B.leave_type_id";
$histories = $con->QueryResult($history_query);

//Bind all employee data to array
if (count($histories) > 0) {
    echo "{\"data\":" . json_encode($histories) . "}";
} else {
    echo "{\"data\":" . [] . "}";
}

