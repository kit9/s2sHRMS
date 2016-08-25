<?php
session_start();
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    //Fetch company
    $rs = mysqli_query($con->open(), "SELECT * FROM company ORDER BY company_id");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    
     $company_id = '';
     $company_title = '';
     $company_address = '';
     $company_phone1 = '';
     $company_email = '';    

     extract($_POST);

     $update_array = array(
        "company_id" => $company_id,
        "company_title" => $company_title,
        "company_address" => $company_address,
        "company_phone1" => $company_phone1,
        "company_email" => $company_email    
    );
    if ($con->update("company", $update_array) == 1) {

    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Company ID: " . $company_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $company_title = $request_vars["company_title"];
    $company_address = $request_vars["company_address"];
    $company_phone1 = $request_vars["company_phone1"];
    $company_email = $request_vars["company_email"];

    $insert_array = array(
        "company_title" => $company_title,
        "company_address" => $company_address,
        "company_phone1" => $company_phone1,
        "company_email" => $company_email    
    );

    $check_exist = $con->existsByCondition("company", "company_title='$company_title'");
    if ($check_exist == 1){
        $errors = array("error" => "yes", "message" => "Given Company Name Already Exists!");
        echo json_encode($errors);
    } else {
        if ($con->insert("company", $insert_array) == 1){

        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert Failed";
        }  
    }

    
    
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $company_id = $request_vars["company_id"];
    $array = array("company_id" => $company_id);
    $con->delete("company", $array);
}
?>