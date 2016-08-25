<?php

include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    
    $arr = array();
    $arr = $con->SelectAll("tbl_course");
  
     echo "{\"data\":" .json_encode($arr). "}";   
    
}
if ($verb == "POST") { 
     $course_id = '';
     $course_name = '';
     $course_level = '';
     $u_id = '';
     extract($_POST);
     $array = array("course_id"=>$course_id,"course_name"=>$course_name,"course_level"=>$course_level,"");
}