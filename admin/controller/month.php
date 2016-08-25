<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(), "SELECT m.*,y.year_name FROM month_master m, year as y WHERE m.year_id= y.year_id");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}
?>

