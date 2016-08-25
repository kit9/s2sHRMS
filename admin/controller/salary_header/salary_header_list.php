<?php
session_start();
include '../../config/class.config.php';
$con = new Config();

date_default_timezone_set('UTC');

header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Read existing record for salary header
if ($verb == "GET") {
    $queryGetHeader = "SELECT * FROM payroll_salary_header";

    $resultGetHeader = $con->QueryResult($queryGetHeader);
    if (count($resultGetHeader) > 0) {
        echo "{\"data\":" . json_encode($resultGetHeader) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}

//Delete existing record
//if ($verb == "DELETE") {
//    $open = $con->open();
//    $request_vars = Array();
//    parse_str(file_get_contents('php://input'), $request_vars);
//    $PSH_id = $request_vars["PSH_id"];
//    $delete_array = array("PSH_id" => $PSH_id);
//    $con->delete("payroll_salary_header", $delete_array);
//}

if ($verb == "POST") {

    extract($_POST);

    $PSH_id = mysqli_real_escape_string($con->open(),$PSH_id);

    $delete_sql = "DELETE FROM payroll_salary_header WHERE PSH_id = '" . $PSH_id. "'";

    $rs = mysqli_query($con->open(), $delete_sql);

    if ($rs) {
        echo json_encode($rs);
    } else {
        echo json_encode(0);
    }
}