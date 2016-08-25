<?php

session_start();
/*
 * author: Rajan Hossain
 * Date: June 15, 2014
 * RPAC- Payroll
 * controller file :: Manage Employee records 
 */

//Connection parameters
include '../../config/class.config.php';
$con = new Config();
$open = $con->open();

$hr_emp_code = '';

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if (isset($_GET["emp_code"])) {
    $emp_code = $_GET["emp_code"];
    $_SESSION["hr_emp_code"] = $emp_code;
}

/*
 * Read data from employee table
 * Bind data to JSON array
 */

//$history_query = "SELECT
//	A.leave_application_master_id,
//	A.leave_type_id,
//	A.mindate,
//	B.maxdate,
//	B.replacement_date,
//	B.is_half,
//	B.day_part,
//	A.details_no_of_days,
//	A. `status`,
//	A.remarks,
//	A.leave_title,
//        lam.reasons
//FROM
//	(
//		SELECT
//			leave_application_master_id,
//			leave_type_id,
//			min(details_date) AS mindate,
//			details_no_of_days,
//			leave_application_details. STATUS,
//			leave_title,
//			remarks
//		FROM
//			leave_application_details
//		LEFT JOIN leave_policy ON leave_policy.leave_policy_id = leave_application_details.leave_type_id
//		WHERE
//			leave_application_master_id IN (
//				SELECT
//					leave_application_master_id
//				FROM
//					leave_application_master
//				WHERE
//					emp_code = '$emp_code'
//			)
//		GROUP BY
//			leave_application_master_id,
//			leave_type_id
//	) A,
//	(
//		SELECT
//			leave_application_master_id,
//			is_half,
//			day_part,
//			replacement_date,
//			leave_type_id,
//			max(details_date) AS maxdate
//		FROM
//			leave_application_details
//   
//		WHERE
//			leave_application_master_id IN (
//				SELECT
//					leave_application_master_id
//				FROM
//					leave_application_master
//				WHERE
//					emp_code = '$emp_code'
//			)
//		GROUP BY
//			leave_application_master_id,
//			leave_type_id
//	) B
//
//  LEFT JOIN leave_application_master lam ON lam.leave_application_master_id = B.leave_application_master_id
//WHERE
//	A.leave_application_master_id = B.leave_application_master_id
//AND A.leave_type_id = B.leave_type_id ORDER BY mindate DESC";
$history_query="SELECT
	A.leave_application_master_id,
	A.leave_type_id,
	A.mindate,
	A.maxdate,
	A.replacement_date,
	A.is_half,
	A.day_part,
	A.details_no_of_days,
	A. `status`,
	A.remarks,
	A.leave_title,
    lam.reasons
FROM
	(
		SELECT
			leave_application_master_id,
			leave_type_id,
			min(details_date) AS mindate,
			count(details_no_of_days) AS details_no_of_days,
			leave_application_details. STATUS,
			leave_title,
			remarks,
            emp_code,
        	is_half,
			day_part,
			replacement_date,
			max(details_date) AS maxdate
		FROM
			leave_application_details
		LEFT JOIN leave_policy ON leave_policy.leave_policy_id = leave_application_details.leave_type_id
		WHERE
			leave_application_master_id IN (
				SELECT
					leave_application_master_id
				FROM
					leave_application_master
				WHERE
					emp_code = '$emp_code'
			)
		GROUP BY
			leave_application_master_id,
			leave_type_id
	) A
	

  LEFT JOIN leave_application_master lam ON lam.leave_application_master_id =      A.leave_application_master_id
  WHERE A.emp_code='$emp_code'
  ORDER BY mindate DESC";
$histories = $con->QueryResult($history_query);


//Bind all employee data to array
if (count($histories) > 0) {
    echo "{\"data\":" . json_encode($histories) . "}";
} else {
    echo "{\"data\":" . [] . "}";
}

