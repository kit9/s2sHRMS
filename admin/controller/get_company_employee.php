<?php
include '../config/class.web.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
//$id = '';
$id = $_POST['rbo_id'];
    $arr = array();
    $table_array = array("rbo_employee", "rbo", "employee");
    $field_array = array("rbo_employee" => "id", "rbo" => "RBO_id,RBO_name", "employee" => "Employee_id,First_name,Email_address");
    $jsonArr = $con->one_many_relation($table_array, $field_array, " AND rbo_employee.RBO_id='$id'", "json");
    $count = count(json_decode($jsonArr));
    if ($count >= 1) {
        echo "{\"data\":" . $jsonArr . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }

