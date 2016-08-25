<?php

/* Author : Rajan Hossain
 * Date: 16 March 15
 * Assumption: All salary information is ready before this process happens
 */
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';
$zero = "0000-00-00";

/*
 * Selected month can not be out of date range.
 * In such case, salary month identifier will be inaccurate.
 */

/*
 * Look to job card table
 * Find employee's overtime hours in those dates
 * Only if the employee is OT eligible
 * Find total OT 
 * Within process request date, total OT should be 
 * updated in the payroll additional table.
 * at each process update procedure would be same
 *  
 */

if (isset($_POST["SearchOT"])) {
    extract($_POST);
    $emp_code = $_POST["emp_code"];
    $_SESSION["emp_code_jcard"] = $emp_code;

    //Fetch staff grade
    $emp_company = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    // Check into tmp table. If not found, then look into struck off table
    if (count($emp_company) > 0) {
        $emp_staff_grade = $emp_company{0}->emp_staff_grade;
    } else {
        $struck_company = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code'");
        if (count($struck_company) > 0) {
            $emp_staff_grade = $struck_company{0}->emp_staff_grade;
        }
    }

    //User selected date ranges
    $temp_start_date = date('Y-m-d', strtotime($_POST["start_date"]));
    $temp_end_date = date('Y-m-d', strtotime($_POST["end_date"]));

    //I have no idea why am I making this two sessions
    $_SESSION["s_date"] = $temp_start_date;
    $_SESSION["e_date"] = $temp_end_date;


    //Fetch all the job cards for that employee in that date range
    $job_cartArray = $con->SelectAllByAssoc("job_card", "emp_code='$emp_code' And date BETWEEN '$temp_start_date' AND '$temp_end_date'");

    //Fetch all the dates :: hardcode a company ID to avoid duplicity
    $sl_date = $con->SelectAllByCondition("dates", "company_id='2' AND date BETWEEN '$temp_start_date' AND '$temp_end_date'");

    $array_job_dates = array();
    $array_sl_dates = array();

    //Build an array with dates from job card table
    if (count($job_cartArray) >= 1) {
        foreach ($job_cartArray as $jb) {
            array_push($array_job_dates, $jb["date"]);
        }
    }

    //Build an array with dates from dates table
    foreach ($sl_date as $sd) {
        array_push($array_sl_dates, $sd->date);
    }

    //Compare two arrays :: find out the differences
    $nonExistsJobCardDateArray = array_diff($array_sl_dates, $array_job_dates);

    foreach ($nonExistsJobCardDateArray as $nex) {

        /*
         * Identify company for this date
         * First look to alternate table :: yes :: assign company
         * Then look to main emp company table :: yes :: assign company
         */

        /*
         * First check alternate attn policy for the date in hand
         * Then look into main company database to find main company
         */

        $ne_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$nex' AND implement_end_date >= '$nex' LIMIT 0,1");
        if (count($ne_existing_awesome) > 0) {
            $ne_company_id = $ne_existing_awesome{0}->alt_company_id;
        } else {
            $ne_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$nex' AND implement_end_date = '0000-00-00'");
            if (count($ne_existing_awesome) > 0) {
                $ne_company_id = $ne_existing_awesome{0}->alt_company_id;
            } else {
                $ne_existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$nex' AND ec_effective_end_date >= '$nex' LIMIT 0,1");
                if (count($ne_existing_company) > 0) {
                    $ne_company_id = $ne_existing_company{0}->ec_company_id;
                } else {
                    $ne_existing_company = $con->SelectAllByCondition("emp_company", " ec_emp_code='$emp_code' AND ec_effective_start_date <= '$nex' AND ec_effective_end_date = '0000-00-00'");
                    if (count($ne_existing_company) > 0) {
                        $ne_company_id = $ne_existing_company{0}->ec_company_id;
                    } else {
                        $struck_company = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code'");
                        if (count($struck_company) > 0) {
                            $ne_company_id = $struck_company{0}->company_id;
                        }
                    }
                }
            }
        }

        //Assign a null array if there is no data in job card
        if ($job_cartArray == '') {
            $job_cartArray = array();
        }

        $temp_array = array("job_card_id" => '', "company_id" => $ne_company_id, "emp_id" => "", "emp_code" => $emp_code, "date" => $nex, "second_date" => $nex, "day_type" => "", "in_time" => "00:00:00", "out_time" => "00:00:00", "buffer_minute" => "", "total_hours" => "", "ot_hours" => "00:00:00", "standard_ot_hours" => "00:00:00", "remarks" => "", "standard_out" => "00:00:00", "status" => '');
        array_push($job_cartArray, $temp_array);
    }


    //No idea starts :: I have no idea what I am doing here too
    $users = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code'");
    $emp_id = $users{0}->emp_id;
    $shifts = "select sp.shift_id, sp.shift_title, sp.saturday_start_time, sp.saturday_end_time,sp.sat_end_day from employee_shifing_user as esu,shift_policy as sp where esu.emp_id ='$emp_id' AND esu.shift_id = sp.shift_id";
    $emp_shifts = $con->QueryResult($shifts);

    if (count($shifts) > 0) {
        $shift_id = $emp_shifts{0}->shift_id;
        $shift_title = $emp_shifts{0}->shift_title;
        $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
        $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;
        $sat_end_day = $emp_shifts{0}->sat_end_day;
    }
    //Storing shift raw end time in session
    $_SESSION["raw_shift_end_time"] = $raw_shift_end_time;
    if ($sat_end_day > 1) {
        $_SESSION["night_shift_emp"] = 1;
    } else {
        $_SESSION["night_shift_emp"] = 0;
    }

    $x = 0;
    foreach ($job_cartArray as $jca) {

        $strFirstTime = date("H:i:s", strtotime($jca["in_time"]));
        $strSecondTime = date("H:i:s", strtotime("00:00:00"));
        if ($strFirstTime != $strSecondTime) {

            $tm_date_x = $jca["date"];

            // Start if date is weekend //

            /*
             * Condition :: find company ID for calendar dates array
             * This array was generated based on alternate attendance settings
             * Iside the array, only one date is traced by the date from job card array
             * Company ID for that date is collected and assigned to original variable. 
             * Collected company ID effects weekend planning. 
             * 
             */
            $neq_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$tm_date_x' AND implement_end_date >= '$tm_date_x' LIMIT 0,1");
            if (count($neq_existing_awesome) > 0) {
                $neq_company_id = $neq_existing_awesome{0}->alt_company_id;
            } else {
                $neq_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$tm_date_x' AND implement_end_date = '0000-00-00'");
                if (count($neq_existing_awesome) > 0) {
                    $neq_company_id = $neq_existing_awesome{0}->alt_company_id;
                } else {
                    $neq_existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$tm_date_x' AND ec_effective_end_date >= '$tm_date_x' LIMIT 0,1");
                    if (count($neq_existing_company) > 0) {
                        $neq_company_id = $neq_existing_company{0}->ec_company_id;
                    } else {
                        $neq_existing_company = $con->SelectAllByCondition("emp_company", " ec_emp_code='$emp_code' AND ec_effective_start_date <= '$tm_date_x' AND ec_effective_end_date = '0000-00-00'");
                        if (count($neq_existing_company) > 0) {
                            $neq_company_id = $neq_existing_company{0}->ec_company_id;
                        } else {
                            $struck_company = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code'");
                            if (count($struck_company) > 0) {
                                $neq_company_id = $struck_company{0}->company_id;
                            }
                        }
                    }
                }
            }

            //Determining day type
            $temp_result_x = $con->existsByCondition("dates", " company_id='$neq_company_id' AND date='$tm_date_x' AND day_type_id='2'");
            $temp_result_H = $con->existsByCondition("dates", " company_id='$neq_company_id' AND date='$tm_date_x' AND (day_type_id='3' OR day_type_id='4')");
            if ($temp_result_x == 1) {
                $job_cartArray[$x]["status"] = "W";
            } else if ($temp_result_H == 1) {
                $job_cartArray[$x]["status"] = "H";
            } else {
                $job_cartArray[$x]["status"] = "P";
            }
        }

        if ($strFirstTime == $strSecondTime) {
            $tm_date = $jca["date"];

            /*
             * Condition :: find company ID for calendar dates array
             * This array was generated based on alternate attendance settings
             * Iside the array, only one date is traced by the date from job card array
             * Company ID for that date is collected and assigned to original variable. 
             * Collected company ID effects weekend planning. 
             */

            $eq_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$tm_date' AND implement_end_date >= '$tm_date' LIMIT 0,1");
            if (count($eq_existing_awesome) > 0) {
                $eq_company_id = $eq_existing_awesome{0}->alt_company_id;
            } else {
                $eq_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$tm_date' AND implement_end_date = '0000-00-00'");
                if (count($eq_existing_awesome)) {
                    $eq_company_id = $eq_existing_awesome{0}->alt_company_id;
                } else {
                    //If no alternate company, then find the main company
                    $eq_existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$tm_date' AND ec_effective_end_date >= '$tm_date' LIMIT 0,1");
                    if (count($eq_existing_company) > 0) {
                        $eq_company_id = $eq_existing_company{0}->ec_company_id;
                    } else {
                        $eq_existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$tm_date' AND ec_effective_end_date = '0000-00-00'");
                        if (count($eq_existing_company) > 0) {
                            $eq_company_id = $eq_existing_company{0}->ec_company_id;
                        } else {
                            $struck_company = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code'");
                            if (count($struck_company) > 0) {
                                $eq_company_id = $struck_company{0}->company_id;
                            }
                        }
                    }
                }
            }


            //-------------- start check the weekend for ------//
            $temp_weekend = $con->existsByCondition("dates", " company_id='$eq_company_id' AND date='$tm_date' AND day_type_id='2'");
            if ($temp_weekend == 1) {
                $job_cartArray[$x]["status"] = "W";
            }


            //-------------- end check the weekend for ------//
            //------------- start check the holiday --------------//
            $temp_arr = array("start_date" => $tm_date);
            $temp_result = $con->existsByCondition("dates", "company_id='$eq_company_id' AND date='$tm_date' AND (day_type_id='3' OR day_type_id='4')");
            if ($temp_result == 1) {
                $job_cartArray[$x]["status"] = "H";
            }

            //------------- end check the holiday --------------//
            //--------------- start check the leave For employee code-------------//
            $temp_leave = $con->existsByCondition("leave_application_details", " details_date='$tm_date' AND status='approved' AND emp_code='$emp_code'");
            if ($temp_leave == 1) {
                $skLeaveString = "SELECT
                lp.short_code
                FROM
                leave_application_details as la,
                leave_policy  as lp
                WHERE
                la.leave_type_id =lp.leave_policy_id AND la.details_date='$tm_date' AND la.status='approved' AND la.emp_code='$emp_code'";
                $res_leave = $con->QueryResult($skLeaveString);
                $job_cartArray[$x]["status"] = $res_leave{0}->short_code;
            }

            /*
             * New modification
             * Check employee's joining date
             * Compare with the this date in hand
             * Any date before the joining date should be 
             * displayed with the status A.
             */

            $joining_date = '';
            $joining_date_fetch = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code' LIMIT 0,1");
            if (count($joining_date_fetch) > 0) {
                $joining_date = date("Y-m-d", strtotime($joining_date_fetch{0}->emp_dateofjoin));
                if ($tm_date < $joining_date) {
                    $job_cartArray[$x]["status"] = "A";
                }
            }
            //--------------- End check the leave For employee code-------------//
        }
        $x++;
    }

    $y = 0;
    $z = 1;
    $Total_OTHOURS = 0;
    $xhours = 0;
    $xMinutes = 0;
    $xSecond = 0;

    foreach ($job_cartArray as $jaac) {
        $job_cartArray[$y]["emp_id"] = $z;
        if ($job_cartArray[$y]["job_card_id"] == "") {
            $job_cartArray[$y]["job_card_id"] = "0";
        }
        if ($job_cartArray[$y]["status"] == "") {
            $job_cartArray[$y]["status"] = "A";
        }
        if ($job_cartArray[$y]["status"] == "W" || $job_cartArray[$y]["status"] == "H") {
            if ($_SESSION["user_type"] != "super_admin") {
                if ($emp_staff_grade == 17) {
                    $temp_in_time_g = date("00:00:00");
                    $temp_out_time_g = date("00:00:00");
                } else {
                    //check if employee is OT eligible
                    if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
                        $temp_in_time_g = date("H:i:s", strtotime($job_cartArray[$y]["in_time"]));
                        $temp_out_time_g = date("H:i:s", strtotime($job_cartArray[$y]["out_time"]));
                    } else {
                        //Total OT for weekent shouldnt be calculated for not ot eligible
                        $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                        $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                    }
                }
            } else {
                //Check if emp is ot eligible
                if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
                    $temp_in_time_g = date("H:i:s", strtotime($job_cartArray[$y]["in_time"]));
                    $temp_out_time_g = date("H:i:s", strtotime($job_cartArray[$y]["out_time"]));
                } else {
                    //Total OT for weekend shouldnt be calculated for not ot eligible
                    $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                    $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                }
            }

            $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
            $temp_time = date("H:i:s", $tem_time_diff_wb);

            //OT calculate to be in 15 minutes buffer
            //Calculate OT in 15 minutes buffer
            //finding total minutes
            $t = EXPLODE(":", $temp_time);
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
            $tem_time_x = date("H:i:s", $OT);
            $x_time_array = explode(":", $tem_time_x);

            //Set standard OT for weekend
            $temp = $x_time_array[0] - 1;
            $temp_minutes = $x_time_array[1];
            $temp_second = $x_time_array[2];
            $array_now = array($temp, $temp_minutes, $temp_second);
            $temp_time_array = implode(":", $array_now);
            $mod_now = date("H:i:s", strtotime($temp_time_array));
            $std = date("H:i:s", strtotime("8:00:00"));

            if ($_SESSION["user_type"] != "super_admin") {
                if ($mod_now > $std) {
                    $xhours += 8;
                    $xMinutes += 00;
                    $xSecond += 00;
                } else {
                    if ($x_time_array[0] >= 7) {
                        $xhours +=$x_time_array[0] - 1;
                    } else {
                        $xhours +=$x_time_array[0];
                    }
                    $xMinutes +=$x_time_array[1];
                    $xSecond += $x_time_array[2];
                }
            } else {
                if ($x_time_array[0] >= 7) {
                    $xhours +=$x_time_array[0] - 1;
                } else {
                    $xhours +=$x_time_array[0];
                }
                $xMinutes +=$x_time_array[1];
                $xSecond += $x_time_array[2];
            }
        } else {
            if ($job_cartArray[$y]["status"] == "P") {
                if ($_SESSION["user_type"] == "super_admin") {
                    $x_time = $job_cartArray[$y]["ot_hours"];
                    $x_time_array = explode(":", $x_time);
                    $xhours +=$x_time_array[0];
                    $xMinutes +=$x_time_array[1];
                    $xSecond += $x_time_array[2];
                } else {
                    if ($job_cartArray[$y]["status"] == "H" || $job_cartArray[$y]["status"] == "W") {
                        if ($emp_staff_grade == 17) {
                            $x_time = '00:00:00';
                        } else {
                            $x_time = $job_cartArray[$y]["standard_ot_hours"];
                        }
                    } else {
                        $x_time = $job_cartArray[$y]["standard_ot_hours"];
                    }

                    $x_time_array = explode(":", $x_time);
                    $xhours +=$x_time_array[0];
                    $xMinutes +=$x_time_array[1];
                    $xSecond += $x_time_array[2];
                }
            }
        }
        $y++;
        $z++;
    }
    $tem_x_hours_add = 0;
    if ($xSecond >= 60) {
        $tem_x_minute_add = $xSecond / 60;
        $tem_x_minute_arr = explode(".", $tem_x_minute_add);
        $xMinutes = $xSecond + $tem_x_minute_arr[0];
        $temp_second_multipy = $xSecond - ($tem_x_minute_arr[0] * 60);
        $xSecond = $temp_second_multipy;
    }
    if ($xMinutes >= 60) {
        $tem_x_hours_add = $xMinutes / 60;
        $tem_x_hours_arr = explode(".", $tem_x_hours_add);
        $xhours = $xhours + $tem_x_hours_arr[0];
        $temp_min_multipy = $xMinutes - ($tem_x_hours_arr[0] * 60);
        $xMinutes = $temp_min_multipy;
    }
    $_SESSION["TO_Hours_Main"] = $xhours . ":" . $xMinutes;

    function startDateCmp($a, $b) {
        return strcmp($a['date'], $b['date']);
    }

    usort($job_cartArray, 'startDateCmp');
    $_SESSION["tmp_job_cardArray"] = $job_cartArray;
}

