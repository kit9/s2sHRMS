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
$raw_shift_end_time = '';
$existing_company = array();
$alt_existing_awesome = array();
$late_buffer_minute = array();
$late = 0;

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Collect delete manually edited data flag
if (isset($_GET["delete_edited_data"])) {
    $delete_edited_data = $_GET["delete_edited_data"];
}

$dates = $con->SelectAll("dates");
/*
 * Read unread files by result 1 flag
 * Record with emp code
 * ASC order based on date and time
 * This is independent of record sequence in original txt file
 */

$attns = $con->SelectAllByCondition("attendance_raw", "result = '1' AND employee_id != '' ORDER BY date, time ASC");
foreach ($attns as $attn) {
    $time = date("H:i:s", strtotime($attn->time));
    $date = $attn->date;

    $emp_code = $attn->employee_id;

    $c_date = date_create($date);
    $f_date = date_format($c_date, "Y-m-d");

    /*
     * Find if existing record on the job card was manually edited.
     * If the flag is found to be true, then nothing will change for
     * this record. All the operation will be exited.
     */

    $manual_edit_flag = '';
    $manual_edit_info = $con->SelectAllByCondition("job_card", " emp_code='$emp_code' AND date='$f_date'");
    if (count($manual_edit_info) > 0) {
        $manual_edit_flag = $manual_edit_info{0}->is_manually_edit;
    }

    //Check if manually edited data delete flag is off
    if ($delete_edited_data == 0 && $manual_edit_flag == 1) {
        //Nothing happens here
    } else {


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
        $is_ot_eligible = $users{0}->is_ot_eligible;

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


        if (isset($emp_shifts{0}->shift_id)) {
            $shift_id = $emp_shifts{0}->shift_id;
        }
        if (isset($emp_shifts{0}->shift_title)) {
            $shift_title = $emp_shifts{0}->shift_title;
        }
        if (isset($emp_shifts{0}->saturday_start_time)) {
            $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
        }

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
            $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
            $shift_start_time = $defaults{0}->saturday_start_time;
            $shift_end_time = $defaults{0}->saturday_end_time;
            $sat_end_day = 1;

        } else {
            $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
            $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
            $sat_end_day = $emp_shifts{0}->sat_end_day;
        }


        /**
         * Find target working hours
         * Find out which one is bigger
         * Calculate accordingly
         */
        if ($shift_start_time < $shift_end_time){
            $target_working_hours_raw = strtotime($shift_end_time) - strtotime($shift_start_time);
            $target_working_hours_frmt = date("H:i:s", $target_working_hours_raw);
        } else if ($shift_end_time > $shift_start_time){
            $target_working_hours_raw = strtotime($shift_start_time) - strtotime($shift_end_time);
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


        //Collect buffer time and decide if late
        $late_buffer_minute = $con->SelectAll("attendance_meta");
        $entry_buffer_minute = '';
        if (count($late_buffer_minute) > 0) {
            $entry_buffer_minute = $late_buffer_minute{0}->late_buffer_minute;
        }

        //Build buffer in time format
        $buffer = "00:" . $entry_buffer_minute . ":00";

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
                        $ot_in_time = $data_exist_in_current_date{0}->in_time;
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
                            if ($time > $shift_start_time) {
                                //Calculate entry late in minute
                                $difference_raw = strtotime($time) - strtotime($shift_start_time);
                                $difference = date("H:i:s", $difference_raw);
                                //Now run condition to check late
                                if ($difference > $buffer) {
                                    $late = 1;
                                } else {
                                    $late = 0;
                                }
                            } else {
                                $late = 0;
                            }

                            $job_cards_array = array(
                                "emp_code" => $emp_code,
                                "date" => $f_date,
                                "in_time" => $time,
                                "company_id" => $company_id,
                                "jc_alt_company_id" => $alt_company_id,
                                "jc_staff_grade" => $emp_staff_grade,
                                "jc_designation" => $emp_designation,
                                "jc_department" => $emp_department,
                                "is_late" => $late
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
                    if ($time > $shift_start_time) {
                        //Calculate entry late in minute
                        $difference_raw = strtotime($time) - strtotime($shift_start_time);
                        $difference = date("H:i:s", $difference_raw);
                        //Now run condition to check late
                        if ($difference > $buffer) {
                            $late = 1;
                        } else {
                            $late = 0;
                        }
                    } else {
                        $late = 0;
                    }


                    $job_cards_array = array(
                        "emp_code" => $emp_code,
                        "date" => $date,
                        "in_time" => $time,
                        "company_id" => $company_id,
                        "jc_alt_company_id" => $alt_company_id,
                        "jc_staff_grade" => $emp_staff_grade,
                        "jc_designation" => $emp_designation,
                        "jc_department" => $emp_department,
                        "is_late" => $late
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
            /*
             * Find out in time and out time
             * Calculate OT, standard OT and update
             * that specific job card
             */
            $previous_date = strtotime("$f_date -1 day");
            $pre_date = date("Y-m-d", $previous_date);
            $job_cards_night_shift_ot = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$pre_date'");
            if (count($job_cards_night_shift_ot) > 0) {

                $in_time = $job_cards_night_shift_ot{0}->in_time;
                $out_time = $job_cards_night_shift_ot{0}->out_time;
                $job_card_ns_id = $job_cards_night_shift_ot{0}->job_card_id;

                if ($is_ot_eligible == 1) {
                    if ($in_time != $out_time) {
                        $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));

                        $o_et = explode(":", $office_end_time);
                        $office_e_h = $o_et[0];
                        $office_e_m = $o_et[1];

                        $o_out_time_arr = explode(":", $out_time);
                        $out_h = $o_out_time_arr[0];
                        $out_m = $o_out_time_arr[1];
                        $second_date_time_compair = date("Y-m-d", strtotime("$f_date +1 day"));

                        $out_time = date("H:i:s", strtotime($out_time));
                        if ($out_time > $office_end_time) {
                    
                            $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));
                      


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
                                        "job_card_id" => $job_card_ns_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => date("H:i", strtotime("00:00:00")),
                                        "standard_out" => $rand_out
                                    );
                                    $con->update("job_card", $update_array_ot);
                                } else {
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_ns_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => $standard_ot,
                                        "standard_out" => $frmt_out
                                    );
                                    $con->update("job_card", $update_array_ot);
                                }
                            } else {
                                if ($emp_staff_grade == 17) {
                                    $random_number = rand(1, 14);
                                    $rand_out = date("H:i:s", strtotime("+" . $random_number . " minutes", strtotime($end_time)));
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_ns_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => date("H:i", strtotime("00:00:00")),
                                        "standard_out" => $rand_out
                                    );
                                    $con->update("job_card", $update_array_ot);
                                } else {
                                    $update_array_ot = array(
                                        "job_card_id" => $job_card_ns_id,
                                        "ot_hours" => $final_ot,
                                        "standard_ot_hours" => $final_ot,
                                        "standard_out" => $out_time
                                    );
                                    $con->update("job_card", $update_array_ot);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            //Check if job card has value for employee code and date
            $job_cards = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$f_date'");
            $remarks = '';

            /*
             * if not exist;
             * insert in time
             * insert remark
             */
            if ($job_cards <= 0) {
                if ($time > $shift_start_time) {
                    // echo "working";
                    //Calculate entry late in minute
                    $difference_raw = strtotime($time) - strtotime($shift_start_time);
                    $difference = date("H:i:s", $difference_raw);
                    //Now run condition to check late
                    if ($difference > $buffer) {
                        $late = 1;
                    } else {
                        $late = 0;
                    }
                } else {
                    $late = 0;
                }

                $job_cards_array = array(
                    "emp_code" => $emp_code,
                    "date" => $f_date,
                    "in_time" => $time,
                    "second_date" => $f_date,
                    "company_id" => $company_id,
                    "jc_alt_company_id" => $alt_company_id,
                    "jc_staff_grade" => $emp_staff_grade,
                    "jc_designation" => $emp_designation,
                    "jc_department" => $emp_department,
                    "is_late" => $late
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

                    if ($is_ot_eligible == 1) {
                        //Collect in time and out time
                        $job_card = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$f_date'");
                        foreach ($job_card as $card) {
                            $out_time = $card->out_time;
                            $in_time = $card->in_time;
                        }

                        if ($in_time != $out_time) {
                            //make office end time
                            echo $office_end_time = date("H:i:s", strtotime("+$tw_hours hours $tw_minutes minutes $tw_seconds seconds", strtotime($in_time)));
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
}
$con->redirect("edit_1.php?permission_id=" . $permission_id);

