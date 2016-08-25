<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT s.*,co.company_title FROM shift_pattern s, company as co WHERE s.company_id=co.company_id order by shift_pattern_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $shift_pattern_id = mysql_real_escape_string($_POST["shift_pattern_id"]);
    $pattern = mysql_real_escape_string($_POST["pattern"]);
    $description = mysql_real_escape_string($_POST["description"]);
    $no_of_shift = mysql_real_escape_string($_POST["no_of_shift"]);
    $company_id = mysql_real_escape_string($_POST["company_id"]);
    $status = mysql_real_escape_string($_POST["status"]);
    $rs = mysqli_query($con->open(), "UPDATE shift_pattern SET pattern = '" . $pattern . "', description = '" . $description . "' , no_of_shift = '" . $no_of_shift . "', company_id = '" . $company_id . "', status = '" . $status."' WHERE shift_pattern_id= " . $shift_pattern_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Shift ID: " . $shift_pattern_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
//    echo "<pre>";
//    print_r($request_vars);
//    echo "</pre>";
//    exit();
   $pattern = mysql_real_escape_string($request_vars["pattern"]);
   $description = mysql_real_escape_string($request_vars["description"]);
   $no_of_shift = mysql_real_escape_string($request_vars["no_of_shift"]);
   $company_id = mysql_real_escape_string($request_vars["company_id"]);
   $status = mysql_real_escape_string($request_vars["status"]);  


    $sql = "insert into shift_pattern(pattern,description,no_of_shift,company_id,status) values('$pattern','$description','$no_of_shift','$company_id','$status')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $shift_pattern_id = mysqli_insert_id($con->open());
        echo "" . $shift_pattern_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $shift_pattern_id = mysql_real_escape_string($request_vars["shift_pattern_id"]);

    $sql = "DELETE FROM shift_pattern WHERE shift_pattern_id = '" . $shift_pattern_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $shift_pattern_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>