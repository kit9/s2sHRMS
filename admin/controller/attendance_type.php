<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $arr = $con->SelectAll("attendance_type");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
if ($verb == "POST") {
    //declaring variables 
    $attendance_type_id = '';
    $attendance_type_code = '';
    $attendance_type_name = '';
    $attendance_type_description = '';
    $color_code = '';

    //Form values
    extract($_POST);

    $open = $con->open();
    $errors = array();
    $query1 = "SELECT attendance_type_code FROM attendance_type WHERE attendance_type_code='$attendance_type_code'";
    $resul = mysqli_query($open, $query1);

    if (mysqli_num_rows($resul) == '0') {
        $query = "UPDATE attendance_type SET
                attendance_type_code='$attendance_type_code',
                attendance_type_name='$attendance_type_name',
                attendance_type_description = '$attendance_type_description',
                status='$status'
                WHERE attendance_type_id='$attendance_type_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } elseif (mysqli_num_rows($resul) == '1') {
        $query = "UPDATE department SET department_title='$department_title',status='$status' WHERE department_id='$department_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } else {
        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
        echo json_encode($errors);
    }
}
if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $department_title = $request_vars["department_title"];
    $status = $request_vars["status"];
    $errors = array();
    $open = $con->open();
    // ****  TO DO... 24/04/2014  ****
    $query1 = "SELECT department_title FROM department WHERE department_title='" . mysqli_real_escape_string($open, $department_title) . "'";
    $resul = mysqli_query($open, $query1);

    if (mysqli_num_rows($resul) == '0') {
        $query = "INSERT INTO department SET ";
        $query .= "department_title='" . mysqli_real_escape_string($open, $department_title) . "',";
        $query .= "status='" . mysqli_real_escape_string($open, $status) . "'";
        $result = mysqli_query($open, $query);

        if ($result) {
            $department_id = mysqli_insert_id($con->open());
            echo "" . $department_id . "";
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert Failed.";
        }
    } else {
        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
        echo json_encode($errors);
    }
    //$object_array = array("country_name"=>$country_name,"c_nationality"=>$c_nationality);
    //$insert = $con->insert("tbl_country",$object_array);
//    if($insert == 0) {
//        header("HTTP/1.1 500 Internal Server Error");
//    }  
}
if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $department_id = $request_vars["department_id"];
    $open = $con->open();
    $query = "DELETE FROM department WHERE department_id='" . mysqli_real_escape_string($open, $department_id) . "'";
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

