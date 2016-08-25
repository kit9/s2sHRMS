<?php

include '../config/class.config.php';
$con = new Config();
$open = $con->open();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT * FROM holiday order by holiday_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $sdob = substr($_POST["start_date"], 0, 15);
    $startdatep = date('Y-m-d', strtotime($sdob));

    $edob = substr($_POST["end_date"], 0, 15);
    $enddatep = date('Y-m-d', strtotime($edob));

    $holiday_id = mysqli_real_escape_string($open, $_POST["holiday_id"]);
    $holiday_title = mysqli_real_escape_string($open, $_POST["holiday_title"]);
    $holiday_type = mysqli_real_escape_string($open, $_POST["holiday_type"]);
    $start_date = mysqli_real_escape_string($open, $_POST["start_date"]);
    $end_date = mysqli_real_escape_string($open, $_POST["end_date"]);
    $no_of_days = mysqli_real_escape_string($open, $_POST["no_of_days"]);
    $is_applicable_for_all = mysqli_real_escape_string($open, $_POST["is_applicable_for_all"]);
    $company_id = mysqli_real_escape_string($open, $_POST["company_id"]);
    $status = mysqli_real_escape_string($open, $_POST["status"]);
    $rs = mysqli_query($con->open(), "UPDATE holiday SET holiday_title = '" . $holiday_title . "', holiday_type = '" . $holiday_type . "', start_date = '" . $startdatep . "' , end_date = '" . $enddatep . "', no_of_days = '" . $no_of_days . "', is_applicable_for_all = '" . $is_applicable_for_all . "', company_id = '" . $company_id . "', status = '" . $status . "' WHERE holiday_id = " . $holiday_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for holiday ID: " . $holiday_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $sdob = substr($request_vars["start_date"], 0, 15);
    $startdate = date('Y-m-d', strtotime($sdob));
    $edob = substr($request_vars["end_date"], 0, 15);
    $enddate = date('Y-m-d', strtotime($edob));
    $holiday_title = mysqli_real_escape_string($open, $request_vars["holiday_title"]);
    $holiday_type = mysqli_real_escape_string($open, $request_vars["holiday_type"]);
    $no_of_days = mysqli_real_escape_string($open, $request_vars["no_of_days"]);
    $is_applicable_for_all = mysqli_real_escape_string($open, $request_vars["is_applicable_for_all"]);
    $company_id = mysqli_real_escape_string($open, $request_vars["company_id"]);
    $status = mysqli_real_escape_string($open, $request_vars["status"]);
    $sql = "insert into holiday(holiday_title,holiday_type,start_date,end_date,no_of_days,is_applicable_for_all,company_id,status) values('$holiday_title','$holiday_type','$startdate', '$enddate', '$no_of_days', '$is_applicable_for_all','$company_id','$status')";
    $rs = mysqli_query($con->open(), $sql);
    if ($rs) {
        $holiday_id = mysqli_insert_id($con->open());
        echo "" . $holiday_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $holiday_id = mysqli_real_escape_string($open,$request_vars["holiday_id"]);
    $sql = "DELETE FROM holiday WHERE holiday_id = '" . $holiday_id . "'";
    $rs = mysqli_query($con->open(), $sql);
    if ($rs) {
        echo "" . $holiday_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>

