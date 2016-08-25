<?php
session_start();
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
error_reporting(0);

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

//Declaring local variables as empty
$company_id = '';
$alt_company_id = '';
$emp_staff_grade = '';
$emp_designation = '';
$emp_department = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

$dates = $con->SelectAll("dates");
$attns = $con->SelectAllByCondition("attendance_raw", " result='1'");


foreach ($attns as $attn) {

    $time = date("H:i:s", strtotime($attn->time));
    $date = $attn->date;
    $emp_code = $attn->employee_id;

    $c_date = date_create($date);
    $f_date = date_format($c_date, "Y-m-d");

    /**
     * Condition :: find company ID from emp_company table
     * Company ID for that date is collected and assigned to original variable. 
     * Collected company ID is stored in the database. 
     */
    
    $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$f_date' AND ec_effective_end_date >= '$f_date' LIMIT 0,1");
    if (count($existing_company) > 0) {
        $company_id = $existing_company{0}->ec_company_id;
    } else {
        $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$f_date' AND ec_effective_end_date = '0000-00-00'");
        if (count($existing_company) > 0) {
            $company_id = $existing_company{0}->ec_company_id;
        }
    }

    /*
     * Find alternate attn policy if exists. 
     * Store it for the regarding employeee for regarding date 
     */
    
    $alt_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$f_date' AND implement_end_date >= '$f_date' LIMIT 0,1");
    if (count($alt_existing_awesome) > 0) {
        $alt_company_id = $alt_existing_awesome{0}->alt_company_id;
    } else {
        $alt_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$f_date' AND implement_end_date = '0000-00-00'");
        if (count($alt_existing_awesome) > 0) {
            $alt_company_id = $alt_existing_awesome{0}->alt_company_id;
        }
    }

    /*
     * Get additional info of this employee
     * Later this information will be coming from 
     * Disntinct table :: emp_department, emp_designation, emp_staff_grade  
     */
    
    $users = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    $emp_id = $users{0}->emp_id;
    $emp_staff_grade = $users{0}->emp_staff_grade;
    $emp_designation = $users{0}->emp_designation;
    $emp_department = $users{0}->emp_designation;

    //Build session for emp_staff grade :: no idea why
    $_SESSION["emp_staff_grade"] = $emp_staff_grade;
    $staff_grade = $_SESSION["emp_staff_grade"];

    /*
     * Find shift info for emp_code
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
                AND esu.schedule_date = '$date'
                AND esu.shift_id = sp.shift_id";

    $emp_shifts = $con->QueryResult($shifts);
    $shift_id = $emp_shifts{0}->shift_id;
    $shift_title = $emp_shifts{0}->shift_title;
    $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;

    //Condition: user  is assigned to a shift or not. 
    if ($raw_shift_end_time != '') {
        //user is assigned
        $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;
    } else {
        //in case user is not assigned, collect default
        $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
        $raw_shift_end_time = $defaults{0}->saturday_end_time;
    }

    $shift_start_time = "";
    $shift_end_time = "";

    if (count($emp_shifts) <= 0) {
        $res1 = "SELECT  esu.*,sp.* FROM employee_shifing_user as esu, shift_policy as sp where esu.emp_id ='$emp_id' order by esu.schedule_date desc limit 1";
        $res1result = $con->QueryResult($res1);
        if (count($res1result) >= 1) {
            $shift_id = $res1result{0}->shift_id;
            $shift_title = $res1result{0}->shift_title;
            $raw_shift_start_time = $res1result{0}->saturday_start_time;
            $raw_shift_end_time = $res1result{0}->saturday_end_time;
            $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
            $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
            $sat_end_day = $emp_shifts{0}->sat_end_day;
        } else {
            $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
            $shift_start_time = $defaults{0}->saturday_start_time;
            $shift_end_time = $defaults{0}->saturday_end_time;
            $sat_end_day = 1;
        }
    } else {
        $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
        $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
        $sat_end_day = $emp_shifts{0}->sat_end_day;
    }

    if ($sat_end_day > 1) {
        if ($time < $shift_start_time) {
            $std_time = 1;
            $shift_start_time_arr = explode(":", $shift_start_time);
            $time_arr = explode(":", $time);
            //get the time difference is one hour
            $time_diff = $shift_start_time_arr[0] - $time_arr[0];

            if ($time_diff <= $std_time) {

                $checkExists = $con->existsByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");

                if ($checkExists == 0) {
                    $job_cards_array = array(
                        "emp_code" => $emp_code,
                        "date" => $f_date,
                        "in_time" => $time,
                        "company_id" => $company_id,
                        "jc_alt_company_id" => $alt_company_id,
                        "jc_staff_grade" => $emp_staff_grade,
                        "jc_designation" => $emp_designation,
                        "jc_department" => $emp_department
                    );
                    $con->insert("job_card", $job_cards_array);
                } else {
                    $data_exist_in_current_date = $con->SelectAllByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");
                    $job_card_id = $data_exist_in_current_date{0}->job_card_id;
                    $job_cards_array = array(
                        "job_card_id" => $job_card_id,
                        "emp_code" => $emp_code,
                        "second_date" => $f_date,
                        "out_time" => $time
                    );
                    $con->update("job_card", $job_cards_array);
                }
            } else {
                $previous_date = strtotime("$f_date -1 day");
                $pre_date = date("Y-m-d", $previous_date);
                $data_exist_in_previous_date = $con->existsByCondition("job_card", " emp_code='$emp_code' AND date='$pre_date'");

                if ($data_exist_in_previous_date >= 1) {
                    $data_exist_in_prev_date = $con->SelectAllByCondition("job_card", " emp_code='$emp_code' AND date='$pre_date'");
                    $job_card_id = $data_exist_in_prev_date{0}->job_card_id;
                    $job_cards_array = array(
                        "job_card_id" => $job_card_id,
                        "emp_code" => $emp_code,
                        "second_date" => $f_date,
                        "out_time" => $time
                    );
                    $con->update("job_card", $job_cards_array);
                    
                } else {
                    $checkExists = $con->existsByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");
                    if ($checkExists == 0) {
                        $job_cards_array = array(
                            "emp_code" => $emp_code,
                            "date" => $f_date,
                            "in_time" => $time,
                            "company_id" => $company_id,
                            "jc_alt_company_id" => $alt_company_id,
                            "jc_staff_grade" => $emp_staff_grade,
                            "jc_designation" => $emp_designation,
                            "jc_department" => $emp_department
                        );
                        $con->insert("job_card", $job_cards_array);
                    }
                }
            }
        } else if ($time > $shift_end_time) {
            $checkExists = $con->existsByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");

            if ($checkExists >= 1) {
                $data_exist_in_current_date = $con->SelectAllByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");
                $job_card_id = $data_exist_in_current_date{0}->job_card_id;

                $job_cards_array = array(
                    "job_card_id" => $job_card_id,
                    "emp_code" => $emp_code,
                    "second_date" => $f_date,
                    "out_time" => $time
                );
                $con->update("job_card", $job_cards_array);
            } else {
                $job_cards_array = array(
                    "emp_code" => $emp_code,
                    "date" => $f_date,
                    "in_time" => $time,
                    "company_id" => $company_id,
                    "jc_alt_company_id" => $alt_company_id,
                    "jc_staff_grade" => $emp_staff_grade,
                    "jc_designation" => $emp_designation,
                    "jc_department" => $emp_department
                );
                $con->insert("job_card", $job_cards_array);
            }
        } else if ($time >= $shift_start_time && $time <= $shift_end_time) {
            $checkExists = $con->existsByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");
            if ($checkExists >= 1) {
                $data_exist_in_current_date = $con->SelectAllByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");
                $job_card_id = $data_exist_in_current_date{0}->job_card_id;
                $job_cards_array = array(
                    "job_card_id" => $job_card_id,
                    "emp_code" => $emp_code,
                    "second_date" => $f_date,
                    "out_time" => $time
                );
                $con->update("job_card", $job_cards_array);
            } else {
                $job_cards_array = array(
                    "emp_code" => $emp_code,
                    "date" => $date,
                    "in_time" => $time,
                    "company_id" => $company_id,
                    "jc_alt_company_id" => $alt_company_id,
                    "jc_staff_grade" => $emp_staff_grade,
                    "jc_designation" => $emp_designation,
                    "jc_department" => $emp_department
                );
                $con->insert("job_card", $job_cards_array);
            }
        }
        //Set non existing second date and outtime
        $existing_in_time_date = $con->SelectAllByCondition("job_card", " emp_code='$emp_code' AND date='$f_date' AND ISNULL(out_time)");
        if (count($existing_in_time_date) >= 1) {
            $job_cards_array = array(
                "job_card_id" => $existing_in_time_date{0}->job_card_id,
                "emp_code" => $emp_code,
                "second_date" => date("Y-m-d", strtotime("$f_date +1 day"))
            );
            $con->update("job_card", $job_cards_array);
        }
        
        
        
        //end out time for second date and shift time//
    } else {
        //check if job card has value for employee code and date
        $job_cards = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$f_date'");
        $remarks = '';
        /*
         * if not exist;
         * insert in time
         * insert remark
         */
        if ($job_cards <= 0) {
            $job_cards_array = array(
                "emp_code" => $emp_code,
                "date" => $f_date,
                "in_time" => $time,
                "second_date" => $f_date,
                "company_id" => $company_id,
                "jc_alt_company_id" => $alt_company_id,
                "jc_staff_grade" => $emp_staff_grade,
                "jc_designation" => $emp_designation,
                "jc_department" => $emp_department
            );
            $con->insert("job_card", $job_cards_array);
            //Now fetch job_card id
        } else {

            $job_cards = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$f_date'");
            foreach ($job_cards as $job_card) {
                $job_card_id = $job_card->job_card_id;
            }
            //Update the row with Out time info
            $update_array = array(
                "job_card_id" => $job_card_id,
                "out_time" => $time
            );
            $con->update("job_card", $update_array);
            //Check if ot is enabled
            if ($emp_code != '') {
                $emp_ot = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
                foreach ($emp_ot as $eot) {
                    $staff_grade = $eot->emp_staff_grade;
                }

                if ($staff_grade >= 16 && $staff_grade <= 22) {
                    //Collect in time and out time
                    $job_card = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$f_date'");
                    foreach ($job_card as $card) {
                        $out_time = $card->out_time;
                        $in_time = $card->in_time;
                    }

                    if ($in_time != $out_time) {
                        //make office end time
                        $office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));
                        //Seperate hour and minute from office end time
                        $o_et = explode(":", $office_end_time);
                        $office_e_h = $o_et[0];
                        $office_e_m = $o_et[1];

                        if ($out_time > $office_end_time) {
                            //Format time
                            $end_time = date("G:i:s", strtotime($office_end_time));
                            $ot = strtotime($out_time) - strtotime($end_time);
                            $f_ot = date("H:i", $ot);

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
                            $std_ot = strtotime("2:00:00");
                            $frmt_ot = date("H:i:s", $std_ot);

                            if ($final_ot > $frmt_ot) {
                                $standard_ot = $frmt_ot;
                                $final_ot;
                                $std_diff = strtotime($final_ot) - strtotime($frmt_ot);
                                $frmt_diff = date("H:i:s", $std_diff);
                                $std_out = strtotime($out_time) - strtotime($frmt_diff);
                                $frmt_out = date("H:i:s", $std_out);

                                //calculate standard ot for staff grade 1:: trainee
                                if ($staff_grade == 17) {
                                    $random_number = rand(1, 14);
                                    $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                        "standard_out" => $rand_out
                                    );
                                } else {
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => $standard_ot,
                                        "standard_out" => $frmt_out
                                    );
                                }
                                $con->update("job_card", $update_array_ot);
                            } else {
                                //calculate standard ot for staff grade 1:: trainee
                                if ($staff_grade == 17) {
                                    $random_number = rand(1, 14);
                                    $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => date("H:i:s", strtotime("00:00:00")),
                                        "standard_out" => $rand_out
                                    );
                                } else {
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => $final_ot,
                                        "standard_out" => $out_time
                                    );
                                }
                                $con->update("job_card", $update_array_ot);
                            }
                        }
                    }
                }
            }
        }
    }

    $attendance_status_update_array = array("attendance_id" => $attn->attendance_id, "result" => "2");
    $update_attn_after_calculate = $con->update("attendance_raw", $attendance_status_update_array);
}



$update_intime_emp_Job_carts_array = $con->SelectAll("job_card");
foreach ($update_intime_emp_Job_carts_array as $jb) {
    //find the shift employee
    $date = $jb->date;
    $c_date = date_create($date);
    $f_date = date_format($c_date, "Y-m-d");
    $emp_code = $jb->emp_code;
    $job_card_id = $jb->job_card_id;

    $users = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code'");
    $emp_id = $users{0}->emp_id;
    $emp_staff_grade = $users{0}->emp_staff_grade;
    $shifts = "select sp.shift_id, sp.shift_title, sp.saturday_start_time, sp.saturday_end_time,sp.sat_end_day,esu.schedule_date from employee_shifing_user as esu,shift_policy as sp where esu.emp_id ='$emp_id' AND esu.schedule_date='$date' AND esu.shift_id = sp.shift_id";
    $emp_shifts = $con->QueryResult($shifts);
    $shift_id = $emp_shifts{0}->shift_id;
    $shift_title = $emp_shifts{0}->shift_title;
    $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
    $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;

    // $con->debug($shifts);

    $shift_start_time = "";
    $shift_end_time = "";

    if (count($emp_shifts) <= 0) {
        $res1 = "SELECT  esu.*,sp.* FROM employee_shifing_user as esu, shift_policy as sp where esu.emp_id ='$emp_id' order by esu.schedule_date desc limit 1";
        $res1result = $con->QueryResult($res1);
        if (count($res1result) >= 1) {
            $shift_id = $res1result{0}->shift_id;
            $shift_title = $res1result{0}->shift_title;
            $raw_shift_start_time = $res1result{0}->saturday_start_time;
            $raw_shift_end_time = $res1result{0}->saturday_end_time;
            $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
            $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
            $sat_end_day = $emp_shifts{0}->sat_end_day;
        } else {
            $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
            $shift_start_time = $defaults{0}->saturday_start_time;
            $shift_end_time = $defaults{0}->saturday_end_time;
            $sat_end_day = 1;
        }
    } else {
        $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
        $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
        $sat_end_day = $emp_shifts{0}->sat_end_day;
    }

    //get the first date //
    $f_date = $jb->date;
    $second_time_date = $jb->second_date;
    $in_time = $jb->in_time;
    $out_time = $jb->out_time;
}


$Shift_emp_Job_carts_array = $con->SelectAll("job_card");
foreach ($Shift_emp_Job_carts_array as $jb) {

    //find the shift employee
    $date = $jb->date;
    $c_date = date_create($date);
    $f_date = date_format($c_date, "Y-m-d");
    $emp_code = $jb->emp_code;
    $job_card_id = $jb->job_card_id;

    /*
     * Find shift info for emp_code
     */
    //------------------get the shift of Employee ----------------------------------------------//

    $users = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code'");
    $emp_id = $users{0}->emp_id;
    $emp_staff_grade = $users{0}->emp_staff_grade;
    $shifts = "select sp.shift_id, sp.shift_title, sp.saturday_start_time, sp.saturday_end_time,sp.sat_end_day,esu.schedule_date from employee_shifing_user as esu,shift_policy as sp where esu.emp_id ='$emp_id' AND esu.schedule_date='$date' AND esu.shift_id = sp.shift_id";
    $emp_shifts = $con->QueryResult($shifts);
    $shift_id = $emp_shifts{0}->shift_id;
    $shift_title = $emp_shifts{0}->shift_title;
    $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
    $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;

    $shift_start_time = "";
    $shift_end_time = "";

    if (count($emp_shifts) <= 0) {
        $res1 = "SELECT  esu.*,sp.* FROM employee_shifing_user as esu, shift_policy as sp where esu.emp_id ='$emp_id' order by esu.schedule_date desc limit 1";
        $res1result = $con->QueryResult($res1);
        if (count($res1result) >= 1) {
            $shift_id = $res1result{0}->shift_id;
            $shift_title = $res1result{0}->shift_title;
            $raw_shift_start_time = $res1result{0}->saturday_start_time;
            $raw_shift_end_time = $res1result{0}->saturday_end_time;
            $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
            $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
            $sat_end_day = $emp_shifts{0}->sat_end_day;
        } else {
            $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
            $shift_start_time = $defaults{0}->saturday_start_time;
            $shift_end_time = $defaults{0}->saturday_end_time;
            $sat_end_day = 1;
        }
    } else {
        $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
        $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
        $sat_end_day = $emp_shifts{0}->sat_end_day;
    }

    if ($sat_end_day > 1) {
        //get the first date //
        //$f_date = $jb->date;
        $second_time_date = $jb->second_date;
        $in_time = $jb->in_time;
        $out_time = $jb->out_time;
        // get the second date //

        if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
            if ($in_time != $out_time) {
                $office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));
                $o_et = explode(":", $office_end_time);
                $office_e_h = $o_et[0];
                $office_e_m = $o_et[1];

                $o_out_time_arr = explode(":", $out_time);
                $out_h = $o_out_time_arr[0];
                $out_m = $o_out_time_arr[1];
                $second_date_time_compair = date("Y-m-d", strtotime("$f_date +1 day"));

                $out_time = date("H:i:s", strtotime($out_time));
                if ($out_time > $office_end_time) {
                    if ($f_date < $second_time_date) {
                        $office_end_time = date("H:i:s", strtotime("+9 hours", strtotime($in_time)));

                        $end_time = date("G:i:s", strtotime($office_end_time));
                        $ot = strtotime($out_time) - strtotime($end_time);
                        $f_ot = date("H:i", $ot);

                        $t = EXPLODE(":", $f_ot);
                        $h = $t[0];
                        IF (ISSET($t[1])) {
                            $m = $t[1];
                        } ELSE {
                            $m = "00";
                        }
                        $mm = ($h * 60) + $m;
                        $first = $mm / 15;
                        $f_first = floor($first);
                        $floored_minute = $f_first * 15;
                        $overtime_h = floor($floored_minute / 60);
                        $overtime_m = $floored_minute % 60;

                        $time_array = array($overtime_h, $overtime_m);
                        $OT = strtotime(implode(":", $time_array));
                        $final_ot = date("H:i:s", $OT);


                        $std_ot = strtotime("2:00:00");
                        $frmt_ot = date("H:i:s", $std_ot);

                        if ($final_ot > $frmt_ot) {
                            $standard_ot = $frmt_ot;

                            $std_diff = strtotime($final_ot) - strtotime($frmt_ot);
                            $frmt_diff = date("H:i:s", $std_diff);
                            $std_out = strtotime($out_time) - strtotime($frmt_diff);
                            $frmt_out = date("H:i:s", $std_out);

                            //Checkin if the employee is of staff grade 
                            if ($emp_staff_grade == 17) {
                                $random_number = rand(1, 14);
                                $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                                $update_array_ot = array(
                                    "job_card_id" => $jb->job_card_id,
                                    "ot_hours" => $final_ot,
                                    "standard_ot_hours" => date("H:i", strtotime("00:00:00")),
                                    "standard_out" => $rand_out
                                );
                            } else {
                                $update_array_ot = array(
                                    "job_card_id" => $jb->job_card_id,
                                    "ot_hours" => $final_ot,
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
                                    "job_card_id" => $jb->job_card_id,
                                    "ot_hours" => $final_ot,
                                    "standard_ot_hours" => date("H:i", strtotime("00:00:00")),
                                    "standard_out" => $rand_out
                                );
                            } else {
                                $update_array_ot = array(
                                    "job_card_id" => $jb->job_card_id,
                                    "ot_hours" => $final_ot,
                                    "standard_ot_hours" => $final_ot,
                                    "standard_out" => $out_time
                                );
                            }
                            $con->update("job_card", $update_array_ot);
                        }
                    }
                }
            }
        }
    }
}
$con->redirect("edit_1.php");
