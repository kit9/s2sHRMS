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
    $rules_id = mysql_real_escape_string($_POST["rules_id"]);
    $module = mysql_real_escape_string($_POST["module"]);
    $status = mysql_real_escape_string($_POST["status"]);
    $rs = mysqli_query($con->open(), "UPDATE employee_module SET module= '" . $module . "', status = '" . $status."' WHERE rules_id = " . $rules_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Module ID: " . $rules_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
//    echo "<pre>";
//    print_r($request_vars);
//    echo "</pre>";
//    exit();
   $module = mysql_real_escape_string($request_vars["module"]);
   $status = mysql_real_escape_string($request_vars["status"]);  

   $sql = "insert into employee_module(module,status) values('$module','$status')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $rules_id = mysqli_insert_id($con->open());
        echo "" . $rules_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $rules_id = mysql_real_escape_string($request_vars["rules_id"]);

    $sql = "DELETE FROM employee_module WHERE rules_id = '" . $rules_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $rules_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>