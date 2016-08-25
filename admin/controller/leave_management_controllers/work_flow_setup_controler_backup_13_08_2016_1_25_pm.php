<?php

include '../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $query = "select approval_workflow_settings.*, department.department_title, tmp_employee.emp_firstname, emp_email_office FROM approval_workflow_settings
inner join department on department.department_id = approval_workflow_settings.department_id
inner Join tmp_employee on tmp_employee.emp_code = approval_workflow_settings.emp_code
ORDER BY approval_workflow_settings.leave_workflow_id DESC";
 
    $arr = $con->QueryResult($query);
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
//if ($verb == "POST") {
//    //declaring variables 
//    $department_id = '';
//    $department_title = '';
//    $status = '';
//
//    //Form values
//    extract($_POST);
//
//    $open = $con->open();
//    $errors = array();
//    $query1 = "SELECT department_title FROM department WHERE department_title='$department_title'";
//    $resul = mysqli_query($open, $query1);
//
//    if (mysqli_num_rows($resul) == '0') {
//        $query = "UPDATE department SET department_title='$department_title',status='$status' WHERE department_id='$department_id'";
//        $rs = mysqli_query($open, $query);
//        if ($rs) {
//            echo json_encode($rs);
//            $con->close($open);
//        } else {
//            header("HTTP/1.1 500 Internal Server Error");
//            echo "Update failed!";
//        }
//    } elseif (mysqli_num_rows($resul) == '1') {
//        $query = "UPDATE department SET department_title='$department_title',status='$status' WHERE department_id='$department_id'";
//        $rs = mysqli_query($open, $query);
//        if ($rs) {
//            echo json_encode($rs);
//            $con->close($open);
//        } else {
//            header("HTTP/1.1 500 Internal Server Error");
//            echo "Update failed!";
//        }
//    } else {
//        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
//        echo json_encode($errors);
//    }
//}
//if ($verb == "PUT") {
//    $request_vars = Array();
//    parse_str(file_get_contents('php://input'), $request_vars);
//
//    $department_title = $request_vars["department_title"];
//    $status = $request_vars["status"];
//    $errors = array();
//    $open = $con->open();
//    // ****  TO DO... 24/04/2014  ****
//    $query1 = "SELECT department_title FROM department WHERE department_title='" . mysqli_real_escape_string($open, $department_title) . "'";
//    $resul = mysqli_query($open, $query1);
//
//    if (mysqli_num_rows($resul) == '0') {
//        $query = "INSERT INTO department SET ";
//        $query .= "department_title='" . mysqli_real_escape_string($open, $department_title) . "',";
//        $query .= "status='" . mysqli_real_escape_string($open, $status) . "'";
//        $result = mysqli_query($open, $query);
//
//        if ($result) {
//            $department_id = mysqli_insert_id($con->open());
//            echo "" . $department_id . "";
//            $con->close($open);
//        } else {
//            header("HTTP/1.1 500 Internal Server Error");
//            echo "Insert Failed.";
//        }
//    } else {
//        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
//        echo json_encode($errors);
//    }
//}
if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    
//    print_r($request_vars);
//    exit();
    
    $emp_code_auth = $request_vars["emp_code_auth"];
    $open = $con->open();
    $query = "DELETE FROM approval_workflow_settings WHERE emp_code_auth='" . mysqli_real_escape_string($open, $emp_code_auth) . "'";
    $rs = mysqli_query($open, $query);
    if ($rs) {
        echo "" . $department_id . "";
        $con->close($open);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>
