<?php
include '../../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");

if (isset($_GET['leave_type_id'])) {
    $leave_type_id = $_GET["leave_type_id"];
    $emp_code = $_GET["emp_code"];
}

/*
 * Collect data from status meta
 * If empty, then from main planning
 */
$query_one = "SELECT remaining_days FROM leave_status_meta WHERE leave_type_id='$leave_type_id' AND emp_code='$emp_code'";
$leave_types = $con->QueryResult($query_one);

//if employee has applied for that leave type already.
if (count($leave_types) > 0) {
    echo "{\"data\":" . json_encode($leave_types) . "}";
} else {
    //If employee has no leave application of that type.
    $query_two = "SELECT total_days as remaining_days FROM leave_policy WHERE leave_policy_id='$leave_type_id'";
    $leave_types = $con->QueryResult($query_two);
    echo "{\"data\":" . json_encode($leave_types) . "}";
}
