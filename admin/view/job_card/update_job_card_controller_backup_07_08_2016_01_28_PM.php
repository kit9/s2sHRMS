<?php

session_start();
include '../../config/class.config.php';
$con = new Config();
date_default_timezone_set('UTC');

$company_id = '';
$alt_company_id = '';

$job_card_id = $_POST["job_card_id"];
$out_time = $_POST["out_time"];
$in_time = $_POST["in_time"];
$date = $_POST["date"];
$emp_code = $_POST["emp_code"];
$status = $_POST["status"];


/*
 * Find the day type
 * Check it against calender
 * If W, then a different operation will be triggered
 * find employee code's company ID for this date
 * Find employee's alternate company ID if exists. 
 */

//Find date and time now
$today = date("Y/m/d H:i:s");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d H:i:s');

//Find logged in employee code
if (isset($_SESSION["emp_code"])) {
    $logged_emp_code = $_SESSION["emp_code"];
}

//Format date in hand
$f_date = date("Y-m-d", strtotime($date));

$emp_info = array();
$is_ot_eligible = '';
$emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
if (count($emp_info) > 0) {
    $is_ot_eligible = $emp_info{0}->is_ot_eligible;
}


//Find employee's company ID
$existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$f_date' AND ec_effective_end_date >= '$f_date' LIMIT 0,1");
if (count($existing_company) > 0) {
    $company_id = $existing_company{0}->ec_company_id;
} else {
    $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$f_date' AND ec_effective_end_date = '0000-00-00'");
    if (count($existing_company) > 0) {
        $company_id = $existing_company{0}->ec_company_id;
    }
}



//Find alternate attn policy
$alt_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$f_date' AND implement_end_date >= '$f_date' LIMIT 0,1");
if (count($alt_existing_awesome) > 0) {
    $alt_company_id = $alt_existing_awesome{0}->alt_company_id;
} else {
    $alt_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$f_date' AND implement_end_date = '0000-00-00'");
    if (count($alt_existing_awesome) > 0) {
        $alt_company_id = $alt_existing_awesome{0}->alt_company_id;
    }
}

//Assign alternate company id to main company id
if ($alt_company_id != '' && $alt_company_id != 0) {
    $company_id = $alt_company_id;
}


//Find day type
$type_query = "SELECT
	day_type.day_shortcode
FROM
	dates
LEFT JOIN day_type ON day_type.day_type_id = dates.day_type_id
WHERE
	company_id = '$company_id'
AND `date` = '$f_date'";
$output = $con->QueryResult($type_query);

if (count($output) > 0) {
    if (isset($output{0}->day_shortcode)) {
        $day_type = $output{0}->day_shortcode;
    }
}




/*
 * Logic operation:
 * if day type is week day in global calendar
 */
$check_exist = array();
if ($day_type == '' && ($status == "H" || $status == "W")) {
    $check_exist = $con->SelectAllByCondition("replacement_weekend", "rw_emp_code='$emp_code' AND replacement_weekend_date='$f_date'");
    if (count($check_exist) > 0) {
        echo $f_date;
        $rw_id = $check_exist{0}->replacement_weekend_id;
        $update_array = array(
            "replacement_weekend_id" => $rw_id,
            "replacement_weekend_status" => $status,
            "last_updated_by" => $logged_emp_code,
            "last_updated_at" => $formatted_today
        );
        $con->update("replacement_weekend", $update_array);
    } else {
        $insert_array = array(
            "rw_emp_code" => $emp_code,
            "replacement_weekend_date" => $f_date,
            "replacement_weekend_status" => $status,
            "created_by" => $logged_emp_code,
            "created_at" => $formatted_today
        );
        $con->insert("replacement_weekend", $insert_array);
    }
} else if (($day_type == "W" || $day_type == "H") && $status == "P") {
    /*
     * if day type is special type of P
     */
    $check_exist = $con->SelectAllByCondition("replacement_weekend", "rw_emp_code='$emp_code' AND replacement_weekend_date='$f_date'");
    if (count($check_exist) > 0) {
        $rw_id = $check_exist{0}->replacement_weekend_id;
        $update_array = array(
            "replacement_weekend_id" => $rw_id,
            "replacement_weekend_status" => $status,
            "last_updated_by" => $logged_emp_code,
            "last_updated_at" => $formatted_today
        );
        $con->update("replacement_weekend", $update_array);
    } else {
        $insert_array = array(
            "rw_emp_code" => $emp_code,
            "replacement_weekend_date" => $f_date,
            "replacement_weekend_status" => $status,
            "created_by" => $logged_emp_code,
            "created_at" => $formatted_today
        );
        $con->insert("replacement_weekend", $insert_array);
    }
} else if (($day_type == "W" || $day_type == "H") && $status == "W") {
    /*
     * if day type is special type of P
     */
    $check_exist = $con->SelectAllByCondition("replacement_weekend", "rw_emp_code='$emp_code' AND replacement_weekend_date='$f_date'");
    if (count($check_exist) > 0) {
        $rw_id = $check_exist{0}->replacement_weekend_id;
        $update_array = array(
            "replacement_weekend_id" => $rw_id,
            "replacement_weekend_status" => $status,
            "last_updated_by" => $logged_emp_code,
            "last_updated_at" => $formatted_today
        );
        $con->update("replacement_weekend", $update_array);
    } else {
        $insert_array = array(
            "rw_emp_code" => $emp_code,
            "replacement_weekend_date" => $f_date,
            "replacement_weekend_status" => $status,
            "created_by" => $logged_emp_code,
            "created_at" => $formatted_today
        );
        $con->insert("replacement_weekend", $insert_array);
    }
}

//------------------get the shift of Employee ----------------------------------------------//
$users = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code'");
$emp_id = $users{0}->emp_id;
$emp_staff_grade = $users{0}->emp_staff_grade;


/**
 * Find shift information for employee
 * for date being edited
 * If a shift was not found, default shift would be assigned
 */
$shifts = "SELECT
                        sp.shift_id,
                        sp.shift_title,
                        sp.saturday_start_time,
                        sp.saturday_end_time,
                        sp.sat_end_day,
                        esu.schedule_date
                FROM
                        employee_shifing_user AS esu,
                        shift_policy AS sp
                WHERE
                        esu.emp_id = '$emp_id'
                AND esu.schedule_date = '$f_date'
                AND esu.shift_id = sp.shift_id";

$emp_shifts = $con->QueryResult($shifts);


if (isset($emp_shifts{0}->shift_id)) {
    $shift_id = $emp_shifts{0}->shift_id;
}
if (isset($emp_shifts{0}->shift_title)) {
    $shift_title = $emp_shifts{0}->shift_title;
}
if (isset($emp_shifts{0}->saturday_start_time)) {
    $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
}
if (isset($emp_shifts{0}->saturday_end_time)) {
    $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;
}

if (isset($emp_shifts{0}->sat_end_day)) {
    $sat_end_day = $emp_shifts{0}->sat_end_day;
}

//Condition: user  is assigned to a shift or not. 
if (count($emp_shifts) <= 0) {
    //in case user is not assigned, collect default
    $defaults = $con->SelectAllByCondition("shift_policy", "shift_type='default'");
    $raw_shift_start_time = $defaults{0}->saturday_start_time;
    $raw_shift_end_time = $defaults{0}->saturday_end_time;
    $sat_end_day = $defaults{0}->sat_end_day;
}

$shift_start_time = "";
$shift_end_time = "";


$shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
$shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));




/**
 * Find target working hours
 * Find out which one is bigger
 * Calculate accordingly
 */
if ($shift_start_time < $shift_end_time) {
    $target_working_hours_raw = strtotime($shift_end_time) - strtotime($shift_start_time);
    $target_working_hours_frmt = date("H:i:s", $target_working_hours_raw);
} else if ($shift_start_time > $shift_end_time) {
    $target_working_hours_raw = strtotime($shift_end_time) - strtotime($shift_start_time);
    $target_working_hours_frmt = date("H:i:s", $target_working_hours_raw);
}


/**
 * Find hours, minutes, seconds
 * @var [array]
 */
$twh_array = explode(":", $target_working_hours_frmt);
$tw_hours = $twh_array[0];
$tw_minutes = $twh_array[1];
$tw_seconds = $twh_array[2];


/** start insert if the job card id is exists * */
if ($job_card_id == 0) {
    $in_time = date("H:i:s", strtotime($in_time));
    $out_time = date("H:i:s", strtotime($out_time));
    $second_date = "";

    /* Creating second date
     * if intime is bigger than out time
     */

    if ($in_time <= $out_time) {
        $second_date = $date;
    } else {
        //Creating date + 1 day
        $second_date = date("Y-m-d", strtotime("$date +1 day"));
    }

    //$out_date =date("Y-m-d", strtotime("$date +1 day"));
    //-------------- Start insert to job card by date --------------------------// 
    //make office end time
    //$office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));
    $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));

    //Seperate hour and minute from office end time
    $o_et = explode(":", $office_end_time);
    $office_e_h = $o_et[0];
    $office_e_m = $o_et[1];

    //$con->debug("working1");
    if ($in_time != $out_time) {
        //$office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));
        $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));


        //Seperate hour and minute from office end time
        $o_et = explode(":", $office_end_time);
        $office_e_h = $o_et[0];
        $office_e_m = $o_et[1];

        //Calculate OT if only staff is eligible
        if ($out_time > $office_end_time) {
            if ($date < $second_date) {

                //Format time
                $end_time = date("G:i:s", strtotime($office_end_time));
                $ot = strtotime($out_time) - strtotime($end_time);
                $f_ot = date("H:i", $ot);
                $con->debug("working3");
                //Calculate OT in 15 minutes buffer
                //finding total minutes
                //std ot buffer at begining
                $std_ot_minute_buffer = "00:30:00";

                //If actual ot is bigger than std minute
                //Generate it as OT
                //Otherwise assign zero
                if ($f_ot >= $std_ot_minute_buffer) {
                    $OT = $f_ot;
                } else {
                    $OT = "00:00:00";
                }

//                $t = EXPLODE(":", $f_ot);
//                $h = $t[0];
//                IF (ISSET($t[1])) {
//                    $m = $t[1];
//                } ELSE {
//                    $m = "00";
//                }
//                $mm = ($h * 60) + $m;
//
//                //Devide minutes with buffer 15
//                $first = $mm / 15;
//                $f_first = floor($first);
//                $floored_minute = $f_first * 15;
//
//                //Devide floored minuted with 15
//                $overtime_h = floor($floored_minute / 60);
//                $overtime_m = $floored_minute % 60;
//
//                //Counting final overtime
//                $time_array = array($overtime_h, $overtime_m);
//                
//                $OT = strtotime(implode(":", $time_array));
//                
                //Make final OT
                $final_ot = date("H:i:s", $OT);
                //End of 15 minute buffer processing
                //Standard out time and ot
                $std_ot = strtotime("2:00:00");
                $frmt_ot = date("H:i:s", $std_ot);

                if ($is_ot_eligible == 1) {
                    $con->debug("working_staff_grade_check");
                    //If standard ot exists
                    if ($final_ot > $frmt_ot) {
                        $standard_ot = $frmt_ot;
                        $con->debug("working5");
                        $std_diff = strtotime($final_ot) - strtotime($frmt_ot);
                        $frmt_diff = date("H:i:s", $std_diff);
                        $std_out = strtotime($out_time) - strtotime($frmt_diff);
                        $frmt_out = date("H:i:s", $std_out);

                        $con->debug("working_standard_ot");
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                            $insert_array_for_grrad_wise = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {

                            $insert_array_for_grrad_wise = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => $standard_ot,
                                "standard_out" => $frmt_out
                            );
                        }
                        $con->insert("job_card", $insert_array_for_grrad_wise);
                    } else {
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                            $insert_array_for_grrad_wise = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                            $con->insert("job_card", $insert_array_for_grrad_wise);
                        } else {
                            $insert_ot_with = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => $final_ot,
                                "standard_out" => $out_time
                            );
                            $con->insert("job_card", $insert_ot_with);
                        }
                    }
                } else {
                    $con->debug("entry_without_ot_1");
                    $insert_ot_without_ot = array(
                        "emp_code" => $emp_code,
                        "date" => $date,
                        "in_time" => $in_time,
                        "out_time" => $out_time,
                        "second_date" => $second_date
                    );
                    $con->insert("job_card", $insert_ot_without_ot);
                }
            } else {
                /*
                 * Calculate OT and standard ot
                 * Check if employee staff grade is ot eligible
                 */

                if ($is_ot_eligible == 1) {

                    $end_time = date("G:i:s", strtotime($office_end_time));
                    $ot = strtotime($out_time) - strtotime($end_time);
                    $f_ot = date("H:i", $ot);

                    //Calculate OT in 15 minutes buffer
                    //finding total minutes
                    //std ot buffer at begining
                    $std_ot_minute_buffer = "00:30:00";

                    //If actual ot is bigger than std minute
                    //Generate it as OT
                    //Otherwise assign zero
                    if ($f_ot >= $std_ot_minute_buffer) {
                        $OT = $f_ot;
                    } else {
                        $OT = "00:00:00";
                    }
//                    $t = EXPLODE(":", $f_ot);
//                    $h = $t[0];
//                    IF (ISSET($t[1])) {
//                        $m = $t[1];
//                    } ELSE {
//                        $m = "00";
//                    }
//                    $mm = ($h * 60) + $m;
//
//                    //Devide minutes with buffer 15
//                    $first = $mm / 15;
//                    $f_first = floor($first);
//                    $floored_minute = $f_first * 15;
//
//                    //Devide floored minuted with 15
//                    $overtime_h = floor($floored_minute / 60);
//                    $overtime_m = $floored_minute % 60;
//
//                    //Counting final overtime
//                    $time_array = array($overtime_h, $overtime_m);
//                    $OT = strtotime(implode(":", $time_array));
                    //Make final OT
                    $final_ot = date("H:i:s", $OT);
                    //End of 15 minute buffer processing
                    //Standard out time and ot
                    $std_ot = strtotime("2:00:00");
                    $frmt_ot = date("H:i:s", $std_ot);

                    if ($final_ot > $frmt_ot) {
                        /*
                         * if ot is not in the standard range
                         */
                        $standard_ot = $frmt_ot;
                        $con->debug("working5");
                        $std_diff = strtotime($final_ot) - strtotime($frmt_ot);
                        $frmt_diff = date("H:i:s", $std_diff);
                        $std_out = strtotime($out_time) - strtotime($frmt_diff);
                        $frmt_out = date("H:i:s", $std_out);

                        /*
                         * array with standard ot
                         */
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                            $insert_ot_without_ot = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {
                            $insert_ot_without_ot = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => $standard_ot,
                                "standard_out" => $frmt_out
                            );
                        }
                        $con->insert("job_card", $insert_ot_without_ot);
                    } else {
                        /*
                         * if ot is in the standard range
                         */
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));

                            $insert_ot_without_ot = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {
                            $insert_ot_without_ot = array(
                                "emp_code" => $emp_code,
                                "date" => $date,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "second_date" => $second_date,
                                "ot_hours" => $final_ot,
                                "standard_ot_hours" => $final_ot,
                                "standard_out" => $out_time
                            );
                        }
                        $con->insert("job_card", $insert_ot_without_ot);
                    }
                } else {
                    /*
                     * If employee is out of ot eligible
                     */
                    $insert_ot_without_ot = array(
                        "emp_code" => $emp_code,
                        "date" => $date,
                        "in_time" => $in_time,
                        "out_time" => $out_time,
                        "second_date" => $second_date
                    );
                    $con->insert("job_card", $insert_ot_without_ot);
                }
            }
        } else {
            $insert_array_without_ot = array(
                "emp_code" => $emp_code,
                "date" => $date,
                "in_time" => $in_time,
                "out_time" => $out_time,
                "second_date" => $second_date
            );
            $con->insert("job_card", $insert_array_without_ot);
        }
        //-------------- End insert to job card by date --------------------------//    
    }
} else {
    $in_time = date("H:i:s", strtotime($in_time));
    $out_time = date("H:i:s", strtotime($out_time));
    $second_date = '';
    if ($in_time <= $out_time) {
        $second_date = $date;
        $con->debug("same date" . $second_date);
    } else {
        $second_date = date("Y-m-d", strtotime("$date +1 day"));
        $con->debug("next date" . $second_date);
    }

    //-------------------- Start update the job card -----------------------------//
    //$office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));
    $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));

    //Seperate hour and minute from office end time
    $o_et = explode(":", $office_end_time);
    $office_e_h = $o_et[0];
    $office_e_m = $o_et[1];
    // $con->debug($office_e_h);
    if ($in_time != $out_time) {
        //make office end time
        // $office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));
        $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));

        //Seperate hour and minute from office end time
        $o_et = explode(":", $office_end_time);
        $office_e_h = $o_et[0];
        $office_e_m = $o_et[1];

        //Calculate OT if only staff is eligible

        if ($out_time > $office_end_time) {
            $con->debug("w13");
            if ($date < $second_date) {
                $con->debug("w14");
                //Format time
                $end_time = date("G:i:s", strtotime($office_end_time));
                $ot = strtotime($out_time) - strtotime($end_time);
                $f_ot = date("H:i", $ot);

                //Calculate OT in 15 minutes buffer
                //finding total minutes
                ////std ot buffer at begining
                $std_ot_minute_buffer = "00:30:00";

                //If actual ot is bigger than std minute
                //Generate it as OT
                //Otherwise assign zero
                if ($f_ot >= $std_ot_minute_buffer) {
                    $OT = $f_ot;
                } else {
                    $OT = "00:00:00";
                }
//                $t = EXPLODE(":", $f_ot);
//                $h = $t[0];
//                IF (ISSET($t[1])) {
//                    $m = $t[1];
//                } ELSE {
//                    $m = "00";
//                }
//                $mm = ($h * 60) + $m;
//
//                //Devide minutes with buffer 15
//                $first = $mm / 15;
//                $f_first = floor($first);
//                $floored_minute = $f_first * 15;
//
//                //Devide floored minuted with 15
//                $overtime_h = floor($floored_minute / 60);
//                $overtime_m = $floored_minute % 60;
//
//                //Counting final overtime
//                $time_array = array($overtime_h, $overtime_m);
//                $OT = strtotime(implode(":", $time_array));
                //Make final OT
                $final_ot = date("H:i:s", $OT);
                //End of 15 minute buffer processing
                //Standard out time and ot
                $std_ot = strtotime("2:00:00");
                $frmt_ot = date("H:i:s", $std_ot);

                if ($is_ot_eligible == 1) {
                    if ($final_ot > $frmt_ot) {
                        $standard_ot = $frmt_ot;

                        $std_diff = strtotime($final_ot) - strtotime($frmt_ot);
                        $frmt_diff = date("H:i:s", $std_diff);
                        $std_out = strtotime($out_time) - strtotime($frmt_diff);
                        $frmt_out = date("H:i:s", $std_out);
                        $con->debug("workingUpdate1");
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => $standard_ot,
                                "standard_out" => $frmt_out
                            );
                        }
                        $con->update("job_card", $update_array_ot);
                    } else {
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => $final_ot,
                                "standard_out" => $out_time
                            );
                        }
                        $con->update("job_card", $update_array_ot);
                    }
                } else {
                    $update_array_ot = array(
                        "job_card_id" => $job_card_id,
                        "in_time" => $in_time,
                        "second_date" => $second_date,
                        "out_time" => $out_time
                    );
                    $con->update("job_card", $update_array_ot);
                }
            } else {


                //Just for test\
                $end_time = date("G:i:s", strtotime($office_end_time));
                $ot = strtotime($out_time) - strtotime($end_time);
                $f_ot = date("H:i", $ot);

                //Calculate OT in 15 minutes buffer
                //finding total minutes
                //std ot buffer at begining
                $std_ot_minute_buffer = "00:30:00";

                //If actual ot is bigger than std minute
                //Generate it as OT
                //Otherwise assign zero
                if ($f_ot >= $std_ot_minute_buffer) {
                    $OT = $f_ot;
                } else {
                    $OT = "00:00:00";
                }
//                $t = EXPLODE(":", $f_ot);
//                $h = $t[0];
//                IF (ISSET($t[1])) {
//                    $m = $t[1];
//                } ELSE {
//                    $m = "00";
//                }
//                $mm = ($h * 60) + $m;
//
//                //Devide minutes with buffer 15
//                $first = $mm / 15;
//                $f_first = floor($first);
//                $floored_minute = $f_first * 15;
//
//                //Devide floored minuted with 15
//                $overtime_h = floor($floored_minute / 60);
//                $overtime_m = $floored_minute % 60;
//
//                //Counting final overtime
//                $time_array = array($overtime_h, $overtime_m);
//                $OT = strtotime(implode(":", $time_array));
                //Make final OT
                $final_ot = date("H:i:s", $OT);
                //End of 15 minute buffer processing
                //Standard out time and ot
                $std_ot = strtotime("2:00:00");
                $frmt_ot = date("H:i:s", $std_ot);

                if ($is_ot_eligible == 1) {
                    if ($final_ot > $frmt_ot) {
                        $standard_ot = $frmt_ot;

                        $std_diff = strtotime($final_ot) - strtotime($frmt_ot);
                        $frmt_diff = date("H:i:s", $std_diff);
                        $std_out = strtotime($out_time) - strtotime($frmt_diff);
                        $frmt_out = date("H:i:s", $std_out);
                        $con->debug("workingUpdate1");
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => $standard_ot,
                                "standard_out" => $frmt_out
                            );
                        }
                        $con->update("job_card", $update_array_ot);
                    } else {
                        if ($emp_staff_grade == 17) {
                            $random_number = rand(1, 14);
                            $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));

                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                "standard_out" => $rand_out
                            );
                        } else {
                            $update_array_ot = array(
                                "job_card_id" => $job_card_id,
                                "in_time" => $in_time,
                                "out_time" => $out_time,
                                "ot_hours" => $final_ot,
                                "second_date" => $second_date,
                                "standard_ot_hours" => $final_ot,
                                "standard_out" => $out_time
                            );
                        }
                        $con->update("job_card", $update_array_ot);
                    }
                } else {

                    $update_array_non_ot_exist_jcard = array(
                        "job_card_id" => $job_card_id,
                        "in_time" => $in_time,
                        "out_time" => $out_time
                    );
                    $con->update("job_card", $update_array_non_ot_exist_jcard);
                }
            }
        } else {
            //Adding zero time when there is no OT
            $zero_time = date("H:i:s", strtotime("00:00:00"));
            $update_array_withoutot = array(
                "job_card_id" => $job_card_id,
                "in_time" => $in_time,
                "second_date" => $second_date,
                "out_time" => $out_time,
                "ot_hours" => $zero_time,
                "standard_ot_hours" => $zero_time,
                "standard_out" => $out_time
            );
            $con->update("job_card", $update_array_withoutot);
        }
    }
    //-------------------- End update the job card -----------------------------//
}
