<?php

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
/*
 * Author: Rajan Hossain, Tariqule, Jobayer Rabbi
 * Page: Text File Process
 * Importing class library
 * Call main class 
 * Connection String
 */

include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();
date_default_timezone_set('UTC');


//Checking if logged in


$dates = $con->SelectAll("dates");
$attns = $con->SelectAllByCondition("attendance_raw", "employee_id='RPAC0019'");

//$con->debug($attns);




$shifts = "SELECT
        *
FROM
        employee_shifing_user AS esu,
        shift_policy AS sp
LEFT JOIN tmp_employee ON tmp_employee.emp_id = '494'
WHERE
        esu.emp_id = '494'
AND esu.shift_id = sp.shift_id";

$emp_shifts = $con->QueryResult($shifts);

//$con->debug($emp_shifts);

//$emp_shifts = json_encode($emp_shifts,true);
//$emp_shifts = json_decode($emp_shifts);

$arrWhole = array();

//foreach ($emp_shifts AS $Shifts) {
//    $arrWhole[]['shift_start_day'] = $Shifts->shift_start_day;
//    $arrWhole[(count($arrWhole) - 1)]['shift_end_day'] = $Shifts->shift_end_day;
//    
//}


foreach($attns AS $Attnds){
    $Attnds->date;
    foreach ($emp_shifts AS $Shifts) {
        if($Attnds->date == $Shifts->schedule_date){
            $startTime = '';
            $endTime = '';
            $arrWhole[$Attnds->date]['Date'] = $Attnds->date;
            if(!in_array($Attnds->time,$arrWhole[$Attnds->date]['Time'])){
                $arrWhole[$Attnds->date]['Time'][] = $Attnds->time;
            }
            
            if(count($arrWhole[$Attnds->date]['Time']) > 1){
                $startTime = min($arrWhole[$Attnds->date]['Time']);
                $endTime = max($arrWhole[$Attnds->date]['Time']);
                $arrWhole[$Attnds->date]['startTime'] = $startTime;
                $arrWhole[$Attnds->date]['endTime'] = $endTime;
                $arrWhole[$Attnds->date]['TIMESTART'] = strtotime($startTime);
                $arrWhole[$Attnds->date]['TIMEEND'] = strtotime($endTime);
                $arrWhole[$Attnds->date]['timeDiff'] = strtotime($endTime) - strtotime($startTime);
                
                
                if($arrWhole[$Attnds->date]['timeDiff'] > 32400){
                    $arrWhole[$Attnds->date]['overTime'] = 'yes';
                    $overTime = $arrWhole[$Attnds->date]['timeDiff'] - 32400;
                    $arrWhole[$Attnds->date]['OTPulse'] = floor($overTime / 900);
                    $arrWhole[$Attnds->date]['OTPulseNotFloored'] = $overTime / 900;
                    
                    //exploading time
                    if($startTime != "" AND $arrWhole[$Attnds->date]['timeDiff'] >= 39600){
                        $exStartTime = explode(':', $startTime);
                        $hour = $exStartTime[0];
                        $minu = $exStartTime[1];
                        $seco = $exStartTime[2];
                        $timeAftrOT = mktime($hour, $minu, ($seco + 39600), 0, 0, 0);
                        $arrWhole[$Attnds->date]['timeAftrOT'] = 'Re:: ' . date("H:i:s", $timeAftrOT);
                    } else {
                        $arrWhole[$Attnds->date]['timeAftrOT'] = $endTime;
                    }
                    
                } else {
                    $arrWhole[$Attnds->date]['overTime'] = 'no';
                }
            } else {
                $startTime = min($arrWhole[$Attnds->date]['Time']);
                $arrWhole[$Attnds->date]['startTime'] = $startTime;
                $arrWhole[$Attnds->date]['TIMESTART'] = strtotime($startTime);
                $arrWhole[$Attnds->date]['overTime'] = 'no';
            }
            
            $arrWhole[$Attnds->date]['saturday_start_time'] = $Shifts->saturday_start_time;
            $arrWhole[$Attnds->date]['saturday_end_time'] = $Shifts->saturday_end_time;
            $arrWhole[$Attnds->date]['sat_end_day'] = $Shifts->sat_end_day;
        }
    }
}
//echo "Here";
//$arrWhole = array_unique($arrWhole);
$con->debug($arrWhole);

//echo "<pre>";
//print_r($arrWhole);
//echo "</pre>";
