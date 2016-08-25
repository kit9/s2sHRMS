<?php
include '../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT da.*,dy.day_title,h.holiday_type FROM dates da, day_type as dy,holiday as h WHERE da.day_type_id= dy.day_type_id AND da.holiday_id= h.holiday_id");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $dates_id = mysql_real_escape_string($_POST["dates_id"]);
    $master_id = mysql_real_escape_string($_POST["master_id"]);
    $date = mysql_real_escape_string($_POST["date"]);
    $day_type_id = mysql_real_escape_string($_POST["day_type_id"]);
    $holiday_id = mysql_real_escape_string($_POST["holiday_id"]);
    $rs = mysqli_query($con->open(), "UPDATE dates SET master_id = '" . $master_id . "', date = '" . $date . "', day_type_id = '" . $day_type_id . "', holiday_id = '" . $holiday_id."' WHERE dates_id = " . $dates_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for dates ID: " . $dates_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
//    echo "<pre>";
//    print_r($request_vars);
//    echo "</pre>";
//    exit();
   $designation_title = mysql_real_escape_string($request_vars["designation_title"]);
   $department_id = mysql_real_escape_string($request_vars["department_id"]);
    $subsection_id = mysql_real_escape_string($request_vars["subsection_id"]);
    $status = mysql_real_escape_string($request_vars["status"]);  


    $sql = "insert into designation(designation_title,department_id,subsection_id,status) values('$designation_title','$department_id','$subsection_id','$status')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $designation_id = mysqli_insert_id($con->open());
        echo "" . $designation_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $designation_id = mysql_real_escape_string($request_vars["designation_id"]);

    $sql = "DELETE FROM dates WHERE dates_id = '" . $dates_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $dates_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>