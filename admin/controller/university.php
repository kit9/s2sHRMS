<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT u.u_id, u.u_name, u.u_website, u.u_address, u.u_phone, u.country_id, c.country_name,u.city_id,u.u_image,u.is_active,cn.city_name FROM tbl_university u,tbl_country c, tbl_city cn WHERE u.country_id=c.country_id AND u.city_id=cn.city_id ");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }

    echo "{\"data\":" . json_encode($arr) . "}";
}

if ($verb == "POST") {
    $u_id = mysql_real_escape_string($_POST["u_id"]);
    $u_name = mysql_real_escape_string($_POST["u_name"]);
    $u_address = mysql_real_escape_string($_POST["u_address"]);
    $u_website = mysql_real_escape_string($_POST["u_website"]);
    $u_image = mysql_real_escape_string($_POST["u_image"]);
    $u_phone = mysql_real_escape_string($_POST["u_phone"]);
    $country_id = mysql_real_escape_string($_POST["country_id"]);
    $city_id = mysql_real_escape_string($_POST["city_id"]);
    $is_active = mysql_real_escape_string($_POST["is_active"]);
    $rs = mysqli_query($con->open(), "UPDATE tbl_university SET u_name = '" . $u_name . "', country_id = '" . $country_id . "', city_id = '" . $city_id . "', u_address = '" . $u_address . "', u_website = '" . $u_website . "', u_image = '" . $u_image . "', u_phone = '" . $u_phone . "',is_active = '".$is_active."' WHERE u_id = " . $u_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Wing ID: " . $u_id;
    }
}

if ($verb == "PUT") {
    $request_vars = Array();

    parse_str(file_get_contents('php://input'), $request_vars);
     
    echo "<pre>";
    print_r($request_vars);
    echo "</pre>";
    $u_name = mysql_real_escape_string($request_vars["u_name"]);
    $u_address = mysql_real_escape_string($request_vars["u_address"]);
    $u_website = mysql_real_escape_string($request_vars["u_website"]);
    $u_phone = mysql_real_escape_string($request_vars["u_phone"]);
    $u_image = mysql_real_escape_string($request_vars["u_image"]);
    $country_id = mysql_real_escape_string($request_vars["country_id"]);
    $city_id = mysql_real_escape_string($request_vars["city_id"]);
    $is_active = mysql_real_escape_string($request_vars["is_active"]);  


    $sql = "insert into tbl_university(u_name,u_address,u_website,u_image, u_phone,country_id,city_id,is_active) values('$u_name','$u_address','$u_website', '$u_image','$u_phone','$country_id','$city_id','$is_active')";

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