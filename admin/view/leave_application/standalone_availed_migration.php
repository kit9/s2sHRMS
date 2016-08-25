<?php
//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$today_array = explode("-", $formatted_today);
$today_year = $today_array[0];

$availed_days = 0;

//loop through status table
$status_array = $con->SelectAllByCondition("leave_status_meta", "year='$today_year' ORDER BY emp_code");

if (count($status_array) > 0) {
    foreach($status_array as $status_array){
       $leave_status_meta_id = $status_array->leave_status_meta_id;
       $status_array_remained = $status_array->total_days - $status_array->availed_days;
       $availed_days_array = array(
        "leave_status_meta_id" => $leave_status_meta_id,
        "remaining_days" => $status_array_remained
        );
       if ($con->update("leave_status_meta", $availed_days_array) == 1) {
       } else {
         echo "Soemthing went wrong";
     }
 }
}
