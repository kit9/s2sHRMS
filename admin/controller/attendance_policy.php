<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Reading database
if ($verb == "GET") {
    $arr = array();
    $arr = $con->SelectAll("attendance_policy");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}

//Deleting a row
if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $department_id = $request_vars["department_id"];
    $open = $con->open();
    $query = "DELETE FROM department WHERE department_id='" . mysqli_real_escape_string($open, $department_id) . "'";
    $rs = mysqli_query($open, $query);
    if ($rs) {
        echo "" . $department_id . "";
        $con->close($open);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>
