<?php
session_start();
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Determine company id 
if (isset($_SESSION["company_id"])) {
    $logged_company_id = $_SESSION["company_id"];
}

//Determine super user
if (isset($_SESSION["is_super"])) {
    $is_super = $_SESSION["is_super"];
}

if ($verb == "GET") {
    $arr = array();
    //Fetch company :: check if super user
    if ($is_super == "yes") {
        $rs = mysqli_query($con->open(), "SELECT * FROM company ORDER BY company_id");
    } else {
        $rs = mysqli_query($con->open(), "SELECT * FROM company WHERE company_id='$logged_company_id' ORDER BY company_id");
    }
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    echo "{\"data\":" . json_encode($arr) . "}";
}