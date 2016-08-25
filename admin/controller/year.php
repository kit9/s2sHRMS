<?php
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {  
    $arr = array();
     $rs = mysqli_query($con->open(), "SELECT * FROM year order by year_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    $count = count($arr);
    if($count>=1)
    {
        echo "{\"data\":" .json_encode($arr). "}";  
    }
    else
    {
        echo "{\"data\":"."[]"."}";  
    }
  
}