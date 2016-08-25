<?php
session_start();
/*
 * author: Rajan Hossain
 * Date: June 15, 2014
 * RPAC- Payroll
 * controller file :: Manage Employee records 
 */

//Connection parameters
include '../config/class.config.php';
$con = new Config();
$open = $con->open();

//Check company_id
$company_id = $_SESSION["company_id"];

//Check super user
$is_super = $_SESSION["is_super"];

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

/*
 * Read data from employee table
 * Bind data to JSON array
 */

$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');
$zero = "0000-00-00";

if ($verb == "GET") {
    $allemp_array = array();
    if ($is_super == "yes") {
        $employees = mysqli_query($open, "SELECT
	CONCAT(
		tmp.emp_code,
		'-',
		tmp.emp_firstname
	) AS emp_name,
	tmp.emp_id,
	tmp.emp_code,
	tmp.emp_firstname
FROM
	tmp_employee AS tmp");
    } else {
        $employees = mysqli_query($open, "SELECT
	CONCAT(
		tmp.emp_code,
		'-',
		tmp.emp_firstname
	) AS emp_name,
	tmp.emp_id,
	tmp.emp_code
FROM
	tmp_employee AS tmp
WHERE
    tmp.emp_code IN (SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$company_id'
		AND (
			(
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date >= '$formatted_today'
			)
			OR (
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date = '$zero'
			)
    ))");
    }
    while ($obj = mysqli_fetch_object($employees)) {
        $allemp_array[] = $obj;
    }

    //Bind all employee data to array
    echo "{\"data\":" . json_encode($allemp_array) . "}";
}
