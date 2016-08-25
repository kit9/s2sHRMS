<?php
include '../../config/class.config.php';
$con= new Config();

$dates_id=$_POST["dates_id"];

$con->debug($dates_id);
$result= $con->delete("dates", array("dates_id"=>$dates_id));
 
echo $result;
?>

