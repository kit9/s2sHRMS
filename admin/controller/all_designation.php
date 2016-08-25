<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
$open = $con->open();

$arr = array();
$arr = $con->SelectAll("designation");
echo "{\"data\":" . json_encode($arr) . "}";

