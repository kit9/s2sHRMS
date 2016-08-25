<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $arr = $con->SelectAll("shift_policy");
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
    $shift_id = mysql_real_escape_string($request_vars["shift_id"]);
    $sql = "DELETE FROM shift_policy WHERE shift_id = '" . $shift_id . "'";
    $rs = mysqli_query($con->open(), $sql);
    if ($rs) {
        echo "" . $shift_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }

}

