<?php
/* Author : ASMA
 * Date: May 2015
 */
include '../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT * FROM payroll_employee_pf_loan order by PEL_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    $count = count($arr);
    
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
//print_r($verb);
if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
//    print_r($request_vars);
//    exit();
    $PEL_id = $request_vars["PEL_id"];
    $open = $con->open();
    $query = "DELETE FROM payroll_employee_pf_loan WHERE PEL_id='" . mysqli_real_escape_string($open, $PEL_id) . "'";
    $rs = mysqli_query($open, $query);
    if ($rs) {
        echo "" . $PEL_id . "";
        $con->close($open);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}