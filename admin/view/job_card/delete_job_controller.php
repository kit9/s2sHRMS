<?php
include '../../config/class.config.php';
$con= new Config();

$job_card_id=$_POST["job_card_id"];
$result= $con->delete("job_card", array("job_card_id"=>$job_card_id));
 
echo $result;


