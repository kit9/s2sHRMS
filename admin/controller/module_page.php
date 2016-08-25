<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT mp.*,em.module FROM module_page mp, employee_module as em WHERE mp.rules_id=em.rules_id order by module_page_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $module_page_id = mysql_real_escape_string($_POST["module_page_id"]);
    $module_page_title = mysql_real_escape_string($_POST["module_page_title"]);
    $module_headline = mysql_real_escape_string($_POST["module_headline"]);
    $rules_id = mysql_real_escape_string($_POST["rules_id"]);
    $status = mysql_real_escape_string($_POST["status"]);
    $rs = mysqli_query($con->open(), "UPDATE module_page SET module_page_title = '" . $module_page_title . "', module_headline = '" . $module_headline . "', rules_id = '" . $rules_id . "', status = '" . $status . "' WHERE module_page_id = " . $module_page_id);
    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Module Page ID: " . $module_page_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);

    $module_page_title = mysql_real_escape_string($request_vars["module_page_title"]);
    $rules_id = mysql_real_escape_string($request_vars["rules_id"]);
    $module_headline = mysql_real_escape_string($request_vars["module_headline"]);
    $status = mysql_real_escape_string($request_vars["status"]);


    $sql = "insert into module_page(module_page_title,rules_id,module_headline,status) values('$module_page_title','$rules_id','$module_headline','$status')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $module_page_id = mysqli_insert_id($con->open());
        echo "" . $module_page_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $module_page_id = mysql_real_escape_string($request_vars["module_page_id"]);

    $sql = "DELETE FROM module_page WHERE module_page_id = '" . $module_page_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $module_page_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>