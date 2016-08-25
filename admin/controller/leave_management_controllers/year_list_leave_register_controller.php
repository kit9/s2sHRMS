<?php

include '../../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $query_years = "SELECT year_name FROM year ORDER BY year_name";
    $years = $con->QueryResult($query_years);
    $array = array_unique($years, SORT_REGULAR);
    $an_array = array();
    foreach ($array as $arr){
        array_push($an_array, $arr);
    }
    echo "{\"data\":" . json_encode($an_array) . "}";
}

