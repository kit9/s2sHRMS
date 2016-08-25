<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT u.ad_id,u.admin_name,u.admin_email,u.admin_password, u.admin_username,u.is_active,u.admin_type,u.role_type,u.user_id, c.user_typ,c.user_id FROM admin u, user_type_in c WHERE u.user_id=c.user_id");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $ad_id = mysql_real_escape_string($_POST["ad_id"]);
    $admin_name = mysql_real_escape_string($_POST["admin_name"]);
    $admin_email = mysql_real_escape_string($_POST["admin_email"]);
    $admin_password = mysql_real_escape_string($_POST["admin_password"]);
    $admin_username = mysql_real_escape_string($_POST["admin_username"]);
    $user_id = mysql_real_escape_string($_POST["user_id"]);
    $is_active = mysql_real_escape_string($_POST["is_active"]);
    $rs = mysqli_query($con->open(), "UPDATE admin SET admin_name = '" . $admin_name . "', admin_email = '" . $admin_email . "', admin_password = '" . $admin_password . "', admin_username = '" . $admin_username . "', user_id = '" . $user_id . "', is_active = '". $is_active."' WHERE ad_id = " . $ad_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Wing ID: " . $ad_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
    $admin_name = mysql_real_escape_string($request_vars["admin_name"]);
    $admin_email = mysql_real_escape_string($request_vars["admin_email"]);
    $admin_password = mysql_real_escape_string($request_vars["admin_password"]);
    $admin_username = mysql_real_escape_string($request_vars["admin_username"]);
    $user_id = mysql_real_escape_string($request_vars["user_id"]);
    $is_active = mysql_real_escape_string($request_vars["is_active"]);  


    $sql = "insert into admin(admin_name,admin_email,admin_password,admin_username, user_id,is_active) values('$admin_name','$admin_email','$admin_password', '$admin_username','$user_id','$is_active')";
   $query = "insert into admin_module(user_id,admin_email,admin_password) values ('$user_id', '$admin_email','$admin_password')" ;
    $rs = mysqli_query($con->open(), $sql);
      $rss = mysqli_query($con->open(), $query);
    if ($rs) {
        $ad_id = mysqli_insert_id($con->open());
        echo "" . $ad_id . "";
     }
      elseif ($rss) {
         $ad_id = mysqli_insert_id($con->open());
        echo "" . $ad_id . ""; 
            
        }  
      else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $ad_id = mysql_real_escape_string($request_vars["ad_id"]);

    $sql = "DELETE FROM wing WHERE ad_id = '" . $ad_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $ad_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>