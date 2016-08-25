<?php

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

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

/*
 * Read data from employee table
 * Bind data to JSON array
 */

$id = $_POST['com_id'];

$allemp_array = '';
if ($verb == "POST") {
    $allemp_array = array();

    /*
     * Fetch date from emp_company table.
     * The flag will be today's date from the system
     * Of the moment this page is requested.
     * Find everyhting for the id posted by front page
     * The further filter again against date
     * Find all employee code and join employee table
     * Find employee code and employee first name 
     */

    //Collect and format date from the system.
    $today = date("Y/m/d");
    $sys_date = date_create($today);
    $formatted_today = date_format($sys_date, 'Y-m-d');

    //Query against today for selected company
    //For all the request :: last assigned company will be counted
    
    $employees = mysqli_query($open, "SELECT
	tmp.*
    FROM
	tmp_employee AS tmp
    WHERE
	emp_code IN (
		SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$id'
		AND (
			(
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date >= '$formatted_today'
			)
			OR (
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date = '0000-00-00'
			)
		)
    ) ORDER BY emp_code");

    while ($obj = mysqli_fetch_object($employees)) {
        $allemp_array[] = $obj->emp_firstname . "-" . $obj->emp_code;
    }

    //Bind all employee data to array
    echo "{\"data\":" . json_encode($allemp_array) . "}";
}
?>