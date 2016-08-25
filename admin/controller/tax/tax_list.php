<?php
session_start();
include '../../config/class.config.php';
$con = new Config();

date_default_timezone_set('UTC');
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Read existing record for salary header
if ($verb == "GET") {
    $queryGetTaxList = "SELECT * FROM payroll_employee_tax "
                    . "LEFT JOIN tmp_employee ON emp_code=PET_employee_code";
    $resultGetTaxList = $con->QueryResult($queryGetTaxList);
    if (count($resultGetTaxList) > 0) {
        echo "{\"data\":" . json_encode($resultGetTaxList) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}

//Delete existing record
if ($verb == "DELETE") {
    $open = $con->open();
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $PSH_id = $request_vars["PSH_id"];
    $delete_array = array("PSH_id" => $PSH_id);
    $con->delete("payroll_salary_header", $delete_array);
}