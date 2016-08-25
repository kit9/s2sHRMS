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

if (isset($_GET["emp_code"])) {
    $emp_code = $_GET["emp_code"];
    $_SESSION["hr_emp_code"] = $emp_code;
}

//echo $_SESSION["hr_emp_code"];


/*
 * Read data from employee table
 * Bind data to JSON array
 */

if ($verb == "GET") {
    $employees_query = "select 
    tmp_employee.*,
    department.department_title,
    designation.designation_title,
    company.company_title
    from tmp_employee 
    left join department on department.department_id = tmp_employee.emp_department
    left join designation on designation.designation_id = tmp_employee.emp_designation
    left join company on company.company_id = tmp_employee.company_id
    where tmp_employee.emp_code='$emp_code'
    ";



    $employees = $con->QueryResult($employees_query);

    //Bind all employee data to array
    if (count($employees) > 0) {
        echo "{\"data\":" . json_encode($employees) . "}";
    } else {
        echo "{\"data\":" . [] . "}";
    }
}
