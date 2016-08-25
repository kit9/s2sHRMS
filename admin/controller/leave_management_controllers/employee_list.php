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

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

$is_super = '';
$company_id = '';

//Fetch company and user type from
if (isset($_SESSION["company_id"])) {
    $company_id = $_SESSION["company_id"];
}
if (isset($_SESSION["is_super"])) {
    $is_super = $_SESSION["is_super"];
}

/*
 * Read data from employee table
 * Bind data to JSON array
 */
if ($verb == "GET") {
    $allemp_array = array();
    if ($is_super == 'yes') {
        $employees = mysqli_query($open, "SELECT CONCAT(tmp.emp_code, '-', tmp.emp_firstname) as emp_name, tmp.emp_id,tmp.emp_code, tmp.company_id, tmp.emp_firstname, com.company_title as company_title FROM tmp_employee as tmp, company as com WHERE tmp.company_id = com.company_id");
    } else {
        $employees = mysqli_query($open, "SELECT CONCAT(tmp.emp_code, '-', tmp.emp_firstname) as emp_name, tmp.emp_id,tmp.emp_code, tmp.company_id, tmp.emp_firstname, com.company_title as company_title FROM tmp_employee as tmp, company as com WHERE tmp.company_id = $company_id");
    }
    while ($obj = mysqli_fetch_object($employees)) {
        //$arr :: changed to $allemp_array
        $allemp_array[] = $obj;
    }
    //Bind all employee data to array
    echo "{\"data\":" . json_encode($allemp_array) . "}";
}
?>
