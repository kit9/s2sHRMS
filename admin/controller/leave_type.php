<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $arr = $con->SelectAll("leave_type");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}

if ($verb == "POST") {
    //declaring variables 
    $leave_type_id = '';
    $leave_type_title = '';
    $status = '';

    //Form values
    extract($_POST);

    $open = $con->open();
    $errors = array();
    $query_leave_type = "SELECT leave_type_title FROM leave_type WHERE leave_type_title='$leave_type_title'";
    $result = mysqli_query($open, $query_leave_type);
    //Update or create
    if (mysqli_num_rows($result) == '0') {
        $query = "UPDATE leave_type SET leave_type_title='$leave_type_title',status='$status' WHERE leave_type_id='$leave_type_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } elseif (mysqli_num_rows($result) == '1') {
        $query = "UPDATE leave_type SET leave_type_title='$leave_type_title',status='$status' WHERE leave_type_id='$leave_type_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } else {
        $errors = array("error" => "yes", "message" => "Given Leave Type Title Already Exists!");
        echo json_encode($errors);
    }
}
if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $leave_type_title = $request_vars["leave_type_title"];
    $status = $request_vars["status"];
    $errors = array();
    $open = $con->open();
    // ****  TO DO... 24/04/2014  ****
    $query_leave_type = "SELECT leave_type_title FROM leave_type WHERE leave_type_title='" . mysqli_real_escape_string($open, $leave_type_title) . "'";
    $result = mysqli_query($open, $query_leave_type);

    if (mysqli_num_rows($result) == '0') {
        $query = "INSERT INTO leave_type SET ";
        $query .= "leave_type_title='" . mysqli_real_escape_string($open, $leave_type_title) . "',";
        $query .= "status='" . mysqli_real_escape_string($open, $status) . "'";
        $result_2 = mysqli_query($open, $query);

        if ($result_2) {
            $leave_type_id = mysqli_insert_id($con->open());
            echo "" . $leave_type_id . "";
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert Failed.";
        }
    } else {
        $errors = array("error" => "yes", "message" => "Given Department Title Already Exists!");
        echo json_encode($errors);
    }

}
if ($verb == "DELETE") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    $leave_type_id = $request_vars["leave_type_id"];
    $open = $con->open();
    $query = "DELETE FROM leave_type WHERE leave_type_id='" . mysqli_real_escape_string($open, $leave_type_id) . "'";
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
