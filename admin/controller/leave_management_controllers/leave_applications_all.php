<?php
session_start();
include '../../config/class.config.php';
$con = new Config();
$open =$con->open();
date_default_timezone_set('UTC');
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

/* 
* Collect emp code from session
* This session code is supervisor code
* Fetch leave application master id, applicant emp_code,
* department from approval_status table
* Collect leave details based on leave_application_master_id
*/

if ($verb  == "GET"){
	$aws_sup_emp_code = $_SESSION["emp_code"];
   
   
    $applications_pending_query = "SELECT 
    department.department_title,
        lam.start_date, lam.end_date,
	lam.no_of_days,
	lam.application_date,
	aws.aws_id,
        aws.leave_application_master_id,
    aws.aws_status,
	aws.aws_emp_code, aws.aws_step,
	aws.aws_sup_emp_code,
    tmp.emp_firstname, tmp.emp_subsection
FROM
    approval_workflow_status as aws
INNER JOIN department on department.department_id = aws.aws_department_id
INNER JOIN leave_application_master as lam ON lam.leave_application_master_id = aws.leave_application_master_id
INNER JOIN tmp_employee as tmp ON tmp.emp_code = aws.aws_emp_code";
if($aws_sup_emp_code=="3016")
{
    $applications_pending_query .= " WHERE (aws.is_reviewed = 'yes' || aws.aws_step = 1)";
}
else
{
    $applications_pending_query .= " WHERE aws.aws_sup_emp_code = '$aws_sup_emp_code' AND (aws.is_reviewed = 'yes' || aws.aws_step = 1)";
}

//echo $applications_pending_query;

$applications_pending_results = $con->QueryResult($applications_pending_query);
     
	//Bind results to JSON
	if (count($applications_pending_results) > 0) {
	    echo "{\"data\":" . json_encode($applications_pending_results) . "}";
	} else {
	    echo "{\"data\":" . "[]" . "}";
	}
}



