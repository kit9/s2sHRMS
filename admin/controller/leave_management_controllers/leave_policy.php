<?php
include '../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $arr = $con->SelectAll("leave_policy");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
if ($verb == "POST") {
    //declaring variables 
    $application_id= '';
    $application_date = '';
    $employee_id = '';
    $start_date = '';
    $end_date = '';
    $no_of_days = '';
    $leave_type_id = '';
    $is_approved = '';
    $status = '';
    $approved_date = '';
    $approved_by_id = '';

    //Form values
    extract($_POST);

    $open = $con->open();
    $errors = array();
    $query1 = "SELECT leave_title FROM leave_policy WHERE leave_title='$leave_title'";
    $result = mysqli_query($open, $query1);

    if (mysqli_num_rows($result) == '0') {
        $query = "UPDATE leave_policy SET leave_title='$leave_title',
                  total_days='$total_days',
                  is_applicable_for_all='$is_applicable_for_all',
                  available_after_months='$available_after_months',
                  status='$status',
                  is_leave_cut_applicable='$is_leave_cut_applicable' 
                  WHERE leave_policy_id='$leave_policy_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } elseif (mysqli_num_rows($resul) == '1') {
        $query = "UPDATE leave_policy SET leave_title='$leave_title',
                  total_days='$total_days',
                  is_applicable_for_all='$is_applicable_for_all',
                  available_after_months='$available_after_months',
                  status='$status',
                  is_leave_cut_applicable='$is_leave_cut_applicable'
                  WHERE leave_policy_id='$leave_policy_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } else {
        $errors = array("error" => "yes", "message" => "Given Leave Title Already Exists!");
        echo json_encode($errors);
    }
}
if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $leave_title = $request_vars["leave_title"];
    $total_days = $request_vars["total_days"];
    $is_applicable_for_all = $request_vars["is_applicable_for_all"];
    $available_after_months = $request_vars["available_after_months"];
    $status = $request_vars["status"];
    $is_leave_cut_applicable = $request_vars["is_leave_cut_applicable"];
    $errors = array();
    $open = $con->open();
    // ****  TO DO... 24/04/2014  ****
    $query1 = "SELECT leave_title FROM leave_policy WHERE leave_title='" . mysqli_real_escape_string($open, $leave_title) . "'";
    $resul = mysqli_query($open, $query1);

    if (mysqli_num_rows($resul) == '0') {
        $query = "INSERT INTO leave_policy SET ";
        $query .= "leave_title='" . mysqli_real_escape_string($open, $leave_title) . "',";
        $query .= "total_days='" . mysqli_real_escape_string($open, $total_days) . "',";
        $query .= "is_applicable_for_all='" . mysqli_real_escape_string($open, $is_applicable_for_all) . "',";
        $query .= "available_after_months='" . mysqli_real_escape_string($open, $available_after_months) . "',";
        $query .= "status='" . mysqli_real_escape_string($open, $status) . "',";
        $query .= "is_leave_cut_applicable='" . mysqli_real_escape_string($open, $is_leave_cut_applicable) . "'";
        $result = mysqli_query($open, $query);

        if ($result) {
            $leave_policy_id = mysqli_insert_id($con->open());
            echo "" . $leave_policy_id . "";
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Insert Failed.";
        }
    } else {
        $errors = array("error" => "yes", "message" => "Given Leave Policy Title Already Exists!");
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
    $leave_policy_id = $request_vars["leave_policy_id"];
    $open = $con->open();
    $query = "DELETE FROM leave_policy WHERE leave_policy_id='" . mysqli_real_escape_string($open, $leave_policy_id) . "'";
    $rs = mysqli_query($open, $query);
    if ($rs) {
        echo "" . $leave_policy_id . "";
        $con->close($open);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>

