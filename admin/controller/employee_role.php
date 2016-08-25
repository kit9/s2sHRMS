<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT * FROM employee_role order by em_role_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $em_role_id = mysqli_real_escape_string($con->open(), $_POST["em_role_id"]);
    $role_type = mysqli_real_escape_string($con->open(), $_POST["role_type"]);
    $status = mysqli_real_escape_string($con->open(), $_POST["status"]);
    $rs = mysqli_query($con->open(), "UPDATE employee_role SET role_type = '" . $role_type . "', status = '" . $status."' WHERE em_role_id = " . $em_role_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Role ID: " . $em_role_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
//    echo "<pre>";
//    print_r($request_vars);
//    echo "</pre>";
//    exit();
   $role_type = mysqli_real_escape_string($con->open(),$request_vars["role_type"]);
   $status = mysqli_real_escape_string($con->open(),$request_vars["status"]);  


    $sql = "insert into employee_role(role_type,status) values('$role_type','$status')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $em_role_id = mysqli_insert_id($con->open());
        echo "" . $em_role_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $em_role_id = mysqli_real_escape_string($con->open(),$request_vars["em_role_id"]);

    $sql = "DELETE FROM employee_role WHERE em_role_id = '" . $em_role_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $em_role_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>