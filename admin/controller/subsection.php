<?php

include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT s.subsection_id, s.subsection_title, s.department_id, dep.department_title,s.status FROM subsection s,department dep WHERE s.department_id=dep.department_id order by subsection_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    extract($_POST);

    $update_array = array(
        "subsection_id" => $subsection_id,
        "subsection_title" => $subsection_title,
        "department_id" => $department_id,
        "status" => $status
    );

    if ($con->update("subsection", $update_array) == 1) {
        
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Subsection ID: " . $subsection_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $subsection_title = $request_vars["subsection_title"];
    $department_id = $request_vars["department_id"];
    $status = $request_vars["status"];

    $insert_array = array(
        "subsection_title" => $subsection_title,
        "department_id" => $department_id,
        "status" => $status
    );

    $check_exist = $con->existsByCondition("subsection", "subsection_title = '$subsection_title'");
    if ($check_exist == 1) {
        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
        echo json_encode($errors);
    } else {

        if ($con->insert("subsection", $insert_array) == 1) {
            
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for Subsection ID: " . $subsection_id;
        }
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $subsection_id = mysqli_real_escape_string($request_vars["subsection_id"]);

    $sql = "DELETE FROM subsection WHERE subsection_id = '" . $subsection_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $subsection_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>