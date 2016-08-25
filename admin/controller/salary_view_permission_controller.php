<?php

session_start();
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    if (isset($_GET["v"]) == "view") {
        $arr = $con->QueryResult("SELECT sp.*,g.staffgrade_title as staffgrade_from,g1.staffgrade_title as staffgrade_to FROM salary_view_permission sp left join staffgrad g on g.priority=sp.svp_sg_position_from  left join staffgrad g1 on g1.priority=sp.svp_sg_position_to");

        $count = count($arr);
        if ($count >= 1) {
            echo "{\"data\":" . json_encode($arr) . "}";
        } else {
            echo "{\"data\":" . "[]" . "}";
        }
    } else {
        $arr = $con->QueryResult("select staffgrade_title, priority FROM staffgrad");
        $count = count($arr);
        if ($count >= 1) {
            echo "{\"data\":" . json_encode($arr) . "}";
        } else {
            echo "{\"data\":" . "[]" . "}";
        }
    }
}
if ($verb == "POST") {
    $svp_emp_code = '';
    $svp_sg_position_from = '';
    $svp_sg_position_to = '';
    $date = date('Y-m-d H:i:s');
    $logged_in = $_SESSION["emp_code"];
    
    $update_array = array(
        
    );
}
if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $svp_id = $request_vars["svp_id"];
    $open = $con->open();

    $query = "DELETE FROM salary_view_permission WHERE svp_id='" . mysqli_real_escape_string($open, $svp_id) . "'";
    $rs = mysqli_query($open, $query);
    if ($rs) {
        echo "" . $svp_id . "";
        $con->close($open);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
