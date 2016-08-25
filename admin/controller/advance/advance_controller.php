<?php

/* Author : ASMA
 * Date: 23 March 15
 */

include '../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
$company_id = '';

if (isset($_GET["company_id"])) {
    $company_id = $_GET["company_id"];
}

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT * FROM payroll_employee_advance where company_id='$company_id' order by PEA_id DESC");
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


if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $PEA_id = $request_vars["PEA_id"];
    $open = $con->open();

    /*
     * Check if sanction started yet
     * If remaining account is not same with advance amount
     * then delete :: or else, dont delete
     */
    $advance_info = array();
    $PEA_paid_amount = '';
    $advance_info = $con->SelectAllByCondition("payroll_employee_advance", "PEA_id='$PEA_id'");
    if (count($advance_info) > 0) {
        $PEA_paid_amount = $advance_info{0}->PEA_paid_amount;
    }
    if ($PEA_paid_amount > 0) {
        //Nothing happens
    } else {
        $query = "DELETE FROM payroll_employee_advance WHERE PEA_id='" . mysqli_real_escape_string($open, $PEA_id) . "'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            //Delete all details data as well
            $delete_array = array(
                "PEA_id" => $PEA_id
            );
            $con->delete("advance_details", $delete_array);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo false;
        }
    }
}