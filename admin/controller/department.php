<?php

include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
$open = $con->open();

if ($verb == "GET") {
    $arr = array();
    $arr = $con->QueryResult("SELECT * FROM department ORDER BY department_id DESC");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
if ($verb == "POST") {
    //declaring variables 
    $department_id = '';
    $department_title = '';
    $status = '';

    //Form values
    extract($_POST);

    //Update Array
    $update_array = array(
        "department_id" => $department_id,
        "department_title" => $department_title,
        "status" => $status
    );

    if ($con->update("department", $update_array) == 1) {
        
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update Failed.";
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $department_title = $request_vars["department_title"];
    $status = $request_vars["status"];
    $errors = array();

    //Update Array
    $insert_array = array(
        "department_title" => $department_title,
        "status" => $status
    );

    $check_exist = $con->existsByCondition("department", "department_title = '$department_title'");
    if ($check_exist == 1) {
        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
        echo json_encode($errors);
    } else {
        if ($con->insert("department", $insert_array) == 1) {
            
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert Failed.";
        }
    }
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $department_id = $request_vars["department_id"];
    $array = array("department_id" => $department_id);
    $con->delete("department", $array);
}

?>
