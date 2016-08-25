<?php
include ('../../config/class.config.php');
$con = new Config();
error_reporting(0);
extract($_POST);
//echo "<pre>";
//print_r($_POST);
//echo "</pre>";
 $arr = array_values($_POST);
 
// $con->debug($_POST);
$day_title ='';
$holiday_title ='';


$update_array = array("dates_id"=>$arr[0],"day_type_id"=>$arr[1],"holiday_id"=>$arr[2]);
if($con->update("dates", $update_array) == 1) {
    
//    $con->debug($arr[2]);
   
  $dates_n = $con->SelectAllByCondition("dates", " dates_id='$dates_id'");
  $day_title= $con->SelectAllByCondition("day_type", " day_type_id='$day_type_id'");
  $holiday_title= $con->SelectAllByCondition("holiday", " holiday_id='$holiday_id'");
//  $con->debug($day_title);
//  $con->debug($holiday_title);
  
   
   echo $day_title{0}->day_title  . "," . $holiday_title{0}->holiday_type;
}
else{
    echo "status is not updated";
}