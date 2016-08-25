<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT * FROM employee_module order by rules_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    extract($_POST);
    $update_array = array(
        "rules_id" => $rules_id,
        "module" => $module,
        "status" => $status
    );
    $con->update("employee_module", $update_array);
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $module = $request_vars["module"];
    $status = $request_vars["status"];
    $insert_array = array(
        "module" => $module,
        "status" => $status
    );
    $con->insert("employee_module", $insert_array);
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $rules_id = $request_vars["rules_id"];
    $delete_array = array('rules_id' => $rules_id);
    $con->delete("employee_module", $delete_array);
}
?>


