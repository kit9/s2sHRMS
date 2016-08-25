<?php

session_start();
include '../../config/class.config.php';
$con = new Config();

date_default_timezone_set('UTC');

header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

$emp_code = '';
$replacement_date = '';
$date = '';
$date_two = '';
$raw_replacement_date = '';

if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
}

extract($_POST);

//Read existing record for logged in employee
if ($verb == "GET") {
    $query_one = "SELECT
            leave_application_temp.*, leave_policy.leave_title
    FROM
            leave_application_temp
    LEFT JOIN leave_policy ON leave_policy.leave_policy_id = leave_application_temp.leave_policy_id
    WHERE
            leave_application_temp.emp_code = '$emp_code'
    ORDER BY
            leave_application_temp.leave_application_temp_id DESC";

    $leave_application_temp = $con->QueryResult($query_one);
    if (count($leave_application_temp) > 0) {
        echo "{\"data\":" . json_encode($leave_application_temp) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}

//Update existing record for logged in employee
if ($verb == "POST") {
    extract($_POST);
    $open = $con->open();

    $raw_start_date_temp = $_POST["start_date_temp"];
    $raw_end_date_temp = $_POST["end_date_temp"];

    if ($raw_start_date_temp != '') {
        $start_time_array = explode(" ", $raw_start_date_temp);
        $StartdayStr = trim($start_time_array[2] . " " . $start_time_array[1] . " " . $start_time_array[3]);
        $date = date("Y-m-d", strtotime($StartdayStr));
    }

    if ($raw_end_date_temp != '') {
        $end_time_array = explode(" ", $raw_end_date_temp);
        $EnddayStr = trim($end_time_array[2] . " " . $end_time_array[1] . " " . $end_time_array[3]);
        $date_two = date("Y-m-d", strtotime($EnddayStr));
    }

    $raw_days = (strtotime($date_two) - strtotime($date)) / (60 * 60 * 24);
    $days_actual = $raw_days + 1;

    if ($replacement_date != '') {
        $replacement_date_array = explode(" ", $replacement_date);
        $replacement_filtered = trim($replacement_date_array[2] . " " . $replacement_date_array[1] . " " . $replacement_date_array[3]);
        $replacement_date = date("Y-m-d", strtotime($replacement_filtered));
    }

    //if employee has applied for that leave type already.
    if (count($leave_types) > 0) {
        $remaining_days = $leave_types{0}->remaining_days;
    } else {
        //If employee has no leave application of that type.
        $query_two = "SELECT total_days as remaining_days FROM leave_policy WHERE leave_policy_id='$leave_policy_id'";
        $leave_types = $con->QueryResult($query_two);
        $remaining_days = $leave_types{0}->remaining_days;
    }

    $update_array = array(
        "leave_application_temp_id" => $leave_application_temp_id,
        "start_date_temp" => $date,
        "end_date_temp" => $date_two,
        "total_days_temp" => $days_actual,
        "leave_policy_id" => $leave_policy_id,
        "remaining_days_temp" => $remaining_days,
        "replacement_date" => $replacement_date
    );
    $con->update("leave_application_temp", $update_array, $open);
}

if ($verb == "PUT") {
    $open = $con->open();
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $raw_start_date_temp = $request_vars["start_date_temp"];
    $raw_end_date_temp = $request_vars["end_date_temp"];
    $total_days_temp = $request_vars["total_days_temp"];
    $leave_policy_id = $request_vars["leave_policy_id"];
    $raw_replacement_date = $request_vars["replacement_date"];

    if ($raw_start_date_temp != '') {
        $start_time_array = explode(" ", $raw_start_date_temp);
        $StartdayStr = trim($start_time_array[2] . " " . $start_time_array[1] . " " . $start_time_array[3]);
        $date = date("Y-m-d", strtotime($StartdayStr));
    }

    if ($raw_end_date_temp != '') {
        $end_time_array = explode(" ", $raw_end_date_temp);
        $EnddayStr = trim($end_time_array[2] . " " . $end_time_array[1] . " " . $end_time_array[3]);
        $date_two = date("Y-m-d", strtotime($EnddayStr));
    }

    if ($raw_replacement_date != '') {
        $replacement_date_array = explode(" ", $raw_replacement_date);
        $replacement_filtered = trim($replacement_date_array[2] . " " . $replacement_date_array[1] . " " . $replacement_date_array[3]);
        $replacement_date = date("Y-m-d", strtotime($replacement_filtered));
    }

    $raw_days = (strtotime($date_two) - strtotime($date)) / (60 * 60 * 24);
    $days_actual = $raw_days + 1;

    $query_one = "SELECT remaining_days FROM leave_status_meta where leave_type_id='$leave_policy_id' AND emp_code='$emp_code'";
    $leave_types = $con->QueryResult($query_one);
    //if employee has applied for that leave type already.
    if (count($leave_types) > 0) {
        $remaining_days = $leave_types{0}->remaining_days;
    } else {
        //If employee has no leave application of that type.
        $query_two = "SELECT total_days as remaining_days FROM leave_policy WHERE leave_policy_id='$leave_policy_id'";
        $leave_types = $con->QueryResult($query_two);
        $remaining_days = $leave_types{0}->remaining_days;
    }
    //Find remaining days
    $insert_array = array(
        "emp_code" => $emp_code,
        "start_date_temp" => $date,
        "end_date_temp" => $date_two,
        "total_days_temp" => $days_actual,
        "leave_policy_id" => $leave_policy_id,
        "remaining_days_temp" => $remaining_days,
        "replacement_date" => $replacement_date
    );
    
    $result = $con->insert("leave_application_temp", $insert_array, $open);
}

//Delete existing record
if ($verb == "DELETE") {
    $open = $con->open();
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $leave_application_temp_id = $request_vars["leave_application_temp_id"];
    $delete_array = array("leave_application_temp_id" => $leave_application_temp_id);
    $con->delete("leave_application_temp", $delete_array);
}


