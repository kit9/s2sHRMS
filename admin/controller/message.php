<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT m.msg_id, m.message, m.msg_title, m.msg_time, m.std_id, m.u_id, m.c_id, m.u_id, m.attachment, s.std_id, u.u_id, c.c_id, u.is_active FROM tbl_message m,tbl_student s, tbl_university u, tbl_consultant c WHERE m.std_id=s.std_id AND m.c_id=c.c_id AND m.u_id=u.u_id AND m.u_id=u.u_id");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $msg_id = mysql_real_escape_string($_POST["msg_id"]);
    $message = mysql_real_escape_string($_POST["message"]);
    $msg_title = mysql_real_escape_string($_POST["msg_title"]);
    $msg_time = mysql_real_escape_string($_POST["msg_time"]);
    $std_id = mysql_real_escape_string($_POST["std_id"]);
    $u_id = mysql_real_escape_string($_POST["u_id"]);
    $c_id = mysql_real_escape_string($_POST["c_id"]);
    $attachment = mysql_real_escape_string($_POST["attachment"]);
    $is_active = mysql_real_escape_string($_POST["is_active"]);
    $rs = mysqli_query($con->open(), "UPDATE tbl_message SET message= '" . $message . "', msg_title = '" . $msg_title . "', std_id = '" . $std_id . "', u_id = '" . $u_id . "', c_id = '" . $c_id . "', attachment = '" . $attachment . "', is_active = '".$is_active."' WHERE msg_id = " . $msg_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Wing ID: " . $msg_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
    echo "<pre>";
    print_r($request_vars);
    echo "</pre>";
    $message = mysql_real_escape_string($request_vars["message"]);
    $msg_title = mysql_real_escape_string($request_vars["msg_title"]);
    $msg_time = mysql_real_escape_string($request_vars["msg_time"]);
    $std_id = mysql_real_escape_string($request_vars["std_id"]);
    $u_id = mysql_real_escape_string($request_vars["u_id"]);
    $c_id = mysql_real_escape_string($request_vars["c_id"]);
     $is_active = mysql_real_escape_string($request_vars["is_active"]);  


    $sql = "insert into tbl_message(message,msg_title,msg_time,u_image, u_phone,country_id,city_id,is_active) values('$message','$msg_title','$msg_time', '$u_image','$u_phone','$country_id','$city_id','$is_active')";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $u_id = mysqli_insert_id($con->open());
        echo "" . $u_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $u_id = mysql_real_escape_string($request_vars["u_id"]);

    $sql = "DELETE FROM wing WHERE u_id = '" . $u_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $u_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>