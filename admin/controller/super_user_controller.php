<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Fetch data for is_super active users
if ($verb == "GET") {
    $arr = array();
    $arr = $con->QueryResult("SELECT emp_code, emp_firstname, password FROM tmp_employee WHERE is_super='yes'");
    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "put") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $emp_code = $request_vars["emp_code"];
    $password = $request_vars["password"];
     $open = $con->open();
   
    $employees = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    $emp_id = $employees{0}->emp_id;

    $update_array = array(
        "emp_id" => $emp_id,
        "password" => $password
    );
    $con->update("tmp_employee", $update_array);
}

if ($verb == "post") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $emp_code = $request_vars["emp_code"];
    $password = $request_vars["password"];
    $open = $con->open();
   
    $employees = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    $emp_id = $employees{0 }->emp_id;

    $update_array = array(
        "emp_id" => $emp_id,
        "password" => $password
    );
    $con->update("tmp_employee", $update_array);
}
