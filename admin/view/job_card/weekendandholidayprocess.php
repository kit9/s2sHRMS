<?php
include '../../config/class.config.php';
$con = new Config();
date_default_timezone_set('UTC');
//   [emp_code] => RPAC0039
//    [job_card_id] => 0
//    [status] => SL
//    [date] => 2014-04-01
//    [in_time] => 00:00:00
//    [out_time] => 00:00:00

$job_card_id = $_POST["job_card_id"];
$out_time = $_POST["out_time"];
$in_time = $_POST["in_time"];
$date = $_POST["date"];
$emp_code = $_POST["emp_code"];


//Employee Code
//$emp_codes = $con->SelectAllByCondition("job_card", "job_card_id='$job_card_id'");
//foreach ($emp_codes as $emp_code) {
//    $emp_code = $emp_code->emp_code;
//}
//Check staff grade ID
$employees = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
$emp_staff_grade = $employees{0}->emp_staff_grade;

/** start insert if the job card id is exists * */

    if ($job_card_id == 0) {
        $in_time = date("H:i:s", strtotime($in_time));
        $out_time = date("H:i:s", strtotime($out_time));

        if ($in_time <= $out_time) {
            $second_date = $date;
        } else {
            $second_date = date("Y-m-d", strtotime("$date +1 day"));
        }
        //-------------- Start insert to job card by date --------------------------// 
        //make office end time
        if ($in_time != $out_time) {
            //make office end time
                //Format time

                $ot = strtotime($out_time) - strtotime($end_time);
                
                $f_ot = date("H:i:s", $ot);
                $tiffinTime = date("H:i:s", strtotime("07:00:00"));
                if($f_ot > $tifin_time)
                {
                    $f_ot = date("H:i:s",  strtotime("$f_ot -1 hour"));
                }
                    
                $con->debug("working3");
                //Calculate OT in 15 minutes buffer
                //finding total minutes
                $t = EXPLODE(":", $f_ot);
                $h = $t[0];
                IF (ISSET($t[1])) {
                    $m = $t[1];
                } ELSE {
                    $m = "00";
                }
                $mm = ($h * 60) + $m;

                //Devide minutes with buffer 15
                $first = $mm / 15;
                $f_first = floor($first);
                $floored_minute = $f_first * 15;

                //Devide floored minuted with 15
                $overtime_h = floor($floored_minute / 60);
                $overtime_m = $floored_minute % 60;

                //Counting final overtime
                $time_array = array($overtime_h, $overtime_m);
                $OT = strtotime(implode(":", $time_array));
                //Make final OT
                $final_ot = date("H:i:s", $OT);
                //End of 15 minute buffer processing
                //Standard out time and ot
                

                
                $con->debug("working4");
                
                    $con->debug("working9");
                    $insert_ot_with = array(
                        "emp_code" => $emp_code,
                        "date" => $date,
                        "in_time" => $in_time,
                        "out_time" => $out_time,
                        "ot_hours" => $final_ot,
                        "standard_ot_hours" => $final_ot,
                        "standard_out" => $out_time,
                        "second_date" => $second_date
                    );
                    $con->insert("job_card", $insert_ot_with);
                

            //-------------- End insert to job card by date --------------------------//    
        }
    } else {
        //-------------------- Start update the job card -----------------------------//
        //$in_time = date("H:i:s", strtotime($in_time));
        $out_time = date("H:i:s", strtotime($out_time));

        if ($in_time <= $out_time) {
            $second_date = $date;
        } else {
            $second_date = date("Y-m-d", strtotime("$date +1 day"));
        }
        //make office end time
//Seperate hour and minute from office end time
        // $con->debug($office_e_h);
        if ($in_time != $out_time) {
            //make office end time
            $ot = strtotime($out_time) - strtotime($end_time);
                
                $f_ot = date("H:i:s", $ot);
                $tiffinTime = date("H:i:s", strtotime("07:00:00"));
                if($f_ot > $tifin_time)
                {
                    $f_ot = date("H:i:s",  strtotime("$f_ot -1 hour"));
                }
                    
                $con->debug("working3");
                //Calculate OT in 15 minutes buffer
                //finding total minutes
                $t = EXPLODE(":", $f_ot);
                $h = $t[0];
                IF (ISSET($t[1])) {
                    $m = $t[1];
                } ELSE {
                    $m = "00";
                }
                $mm = ($h * 60) + $m;

                //Devide minutes with buffer 15
                $first = $mm / 15;
                $f_first = floor($first);
                $floored_minute = $f_first * 15;

                //Devide floored minuted with 15
                $overtime_h = floor($floored_minute / 60);
                $overtime_m = $floored_minute % 60;

                //Counting final overtime
                $time_array = array($overtime_h, $overtime_m);
                $OT = strtotime(implode(":", $time_array));
                //Make final OT
                $final_ot = date("H:i:s", $OT);
                //End of 15 minute buffer processing
                //Standard out time and ot
                 $update_array_ot = array(
                            "job_card_id" => $job_card_id,
                            "second_date" => $second_date,
                            "out_time" => $out_time,
                            "ot_hours" => $final_ot,
                            "standard_ot_hours" => $standard_ot,
                            "standard_out" => $frmt_out
                        );

            //Calculate OT if only staff is eligible
        }
        //-------------------- End update the job card -----------------------------//
    }



    /** end insert if the job card id is exists * */
    //$job_card = $con->SelectAllByCondition("job_card", " job_card_id='$job_card_id'");
   //echo $job_card{0}->in_time . "," . $job_card{0}->out_time . "," . $job_card{0}->ot_hours;    