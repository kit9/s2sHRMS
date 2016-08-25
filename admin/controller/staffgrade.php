<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
$open = $con->open();

if ($verb == "GET") {
    $arr = array();
    $arr = $con->QueryResult("select * from staffgrad order by staffgrade_id DESC");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
if ($verb == "POST") {
    //declaring variables 
    $staffgrade_id = '';
    $staffgrade_title = '';
    $status = '';

    //Form values
    extract($_POST);
    
    $update_array = array(
        "staffgrade_id" => $staffgrade_id,
        "staffgrade_title" => $staffgrade_title,
        "status" => $status 
    );

    if ($con->update("staffgrad", $update_array) == 1){
   
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update Failed";
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $staffgrade_title = $request_vars["staffgrade_title"];
    $status = $request_vars["status"];
    $errors = array();
   
    $insert_array = array(
        "staffgrade_title" => $staffgrade_title,
        "status" => $status 
    );
    
    $check_exist = $con->existsByCondition("staffgrad", "staffgrade_title='$staffgrade_title'");
    if ($check_exist == 1){
        $errors = array("error" => "yes", "message" => "Given Staff Grade Title Already Exists!");
        echo json_encode($errors);
    } else {
        if ($con->insert("staffgrad", $insert_array) == 1){
            
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update Failed";
        }
    }
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $staffgrade_id = $request_vars["staffgrade_id"];
    $array = array("staffgrade_id" => $staffgrade_id);
    $con->delete("staffgrad", $array);
}

?>

