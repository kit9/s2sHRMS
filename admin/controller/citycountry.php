<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $arr = $con->SelectAll("country");
    $count = count($arr);
    if ($count >= 1) {
        echo "{\"data\":" . json_encode($arr) . "}";
    } else {
        echo "{\"data\":" . "[]" . "}";
    }
}
if ($verb == "POST") {
    //declaring variables 
    $country_id = '';
    $country_name = '';
    $status = '';

    //Form values
    extract($_POST);

    $open = $con->open();
    $errors = array();
    $query1 = "SELECT country_name FROM country WHERE country_name='$country_name'";
    $resul = mysqli_query($open, $query1);

    if (mysqli_num_rows($resul) == '0') {
        $query = "UPDATE country SET country_name='$country_name',status='$status' WHERE country_id='$country_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            echo json_encode($rs);
            $con->close($open);
        } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed!";
        }
    } elseif (mysqli_num_rows($resul) == '1') {
        $query = "UPDATE country SET country_name='$country_name',status='$status' WHERE country_id='$country_id'";
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

    $country_name = $request_vars["country_name"];
    $status = $request_vars["status"];
    $errors = array();
    $open = $con->open();
  
    $query1 = "SELECT country_name FROM country WHERE country_name='" . mysqli_real_escape_string($open, $country_name) . "'";
    $resul = mysqli_query($open, $query1);

    if (mysqli_num_rows($resul) == '0') {
        $query = "INSERT INTO country SET ";
        $query .= "country_name='" . mysqli_real_escape_string($open, $country_name) . "',";
        $query .= "status='" . mysqli_real_escape_string($open, $status) . "'";
        $result = mysqli_query($open, $query);

        if ($result) {
            $country_id = mysqli_insert_id($con->open());
            echo "" . $country_id . "";
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
    $country_id = $request_vars["country_id"];
    $open = $con->open();
    $query = "DELETE FROM country WHERE country_id='" . mysqli_real_escape_string($open, $country_id) . "'";
    $rs = mysqli_query($open, $query);
    if ($rs) {
        echo "" . $country_id . "";
        $con->close($open);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>
