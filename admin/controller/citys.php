<?php

include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT cn.city_id, cn.city_name, cn.country_id, cen.country_name,cn.status FROM city cn,country cen WHERE cn.country_id=cen.country_id order by city_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    
    $city_id = '';
    $country_id = '';
    $city_name = '';
    $status = '';

    extract($_POST);

    $update_array = array(
        "city_id" => $city_id,
        "country_id" => $country_id,
        "city_name" => $city_name,
        "status" => $status
    );
    if ($con->update("city", $update_array) == 1) {
        
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update Failed";
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $city_name = $request_vars["city_name"];
    $country_id = $request_vars["country_id"];
    $status = $request_vars["status"];

    $insert_array = array(
        "country_id" => $country_id,
        "city_name" => $city_name,
        "status" => $status
    );

    $check_exist = $con->existsByCondition("city", "city_name='$city_name'");
    if ($check_exist == 1){
        $errors = array("error" => "yes", "message" => "Given City Name Already Exists!");
        echo json_encode($errors);
    } else {
        if ($con->insert("city", $insert_array) == 1) {
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert Failed";
        }
    }
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $city_id = $request_vars["city_id"];
    $array = array("city_id" => $city_id);
    $con->delete("city", $array);
}

?>