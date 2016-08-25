<?php

include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
$open = $con->open();

if ($verb == "GET") {
    $arr = array();
    //$rsQuery = "SELECT d.designation_id, d.designation_title, d.subsection_id,su.subsection_title,d.status,dep.department_id,dep.department_title FROM designation d
    //LEFT JOIN subsection su ON d.subsection_id = su.subsection_id,department dep WHERE d.department_id = dep.department_id order by d.designation_id DESC";
    $rsQuery = "select * from designation";
    $rs = mysqli_query($con->open(), $rsQuery);
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {

    $designation_id = '';
    $department_id = '';
    $designation_title = '';
    $subsection_id = '';

    extract($_POST);

    $update_array = array(
        "designation_id" => $designation_id,
        "designation_title" => $designation_title,
        "department_id" => $department_id,
        "subsection_id" => $subsection_id,
        "status" => $status
    );

    if ($con->update("designation", $update_array) == 1) {
        
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed";
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $designation_title = $request_vars["designation_title"];
    $department_id = $request_vars["department_id"];
    $subsection_id = $request_vars["subsection_id"];
    $status = $request_vars["status"];


    $insert_array = array(
        "designation_title" => $designation_title,
        "department_id" => $department_id,
        "subsection_id" => $subsection_id,
        "status" => $status
    );

    $check_exist = $con->existsByCondition("designation", "designation_title='$designation_title'");
    if ($check_exist == 1) {
        $errors = array("error" => "yes", "message" => "Given Designation Already Exists!");
        echo json_encode($errors);
    } else {
        if ($con->insert("designation", $insert_array) == 1) {
            
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert failed";
        }
    }
}

if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $designation_id = $request_vars["designation_id"];
    $array = array("designation_id" => $designation_id);
    $con->delete("designation", $array);
}
?>