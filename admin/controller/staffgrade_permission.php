<?php
/* Author: Asma
 * 
 */
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
$open = $con->open();

if ($verb == "GET") {
    $arr = array();
    if (isset($_GET["v"]) == "from") {
        $arr = $con->QueryResult("SELECT g.staffgrade_id as svp_sg_position_from,g.staffgrade_title as staffgrade_from FROM staffgrad g order by g.staffgrade_id DESC");
        $count = count($arr);
        if ($count >= 1) {
            echo "{\"data\":" . json_encode($arr) . "}";
        } else {
            echo "{\"data\":" . "[]" . "}";
        }
    } else {
        $arr = $con->QueryResult("SELECT g.staffgrade_id as svp_sg_position_to,g.staffgrade_title as staffgrade_to FROM staffgrad g order by g.staffgrade_id DESC");
        $count = count($arr);
        if ($count >= 1) {
            echo "{\"data\":" . json_encode($arr) . "}";
        } else {
            echo "{\"data\":" . "[]" . "}";
        }
    }
}
