<?php
session_start();
include("../../config/class.config.php");
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
date_default_timezone_set('UTC');
$con = new Config();
$open = $con->open();
error_reporting(0);
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}


$employees = $con->SelectAll("tmp_employee");

$err = "";
$emp_code_temp = '';
$msg = '';
$emp_code = '';
$start_date = "";
$end_date = "";
$temp_start_date = "";
$temp_end_date = "";
$job_cartArray = array();
$totalx_A = "";
$totalx_SL = "";
$totalx_CL = "";
$totalx_AL = "";
$totalx_LL = "";
$totalx_LOP = "";
$totalx_T = "";
$totalx_P = "";
$totalx_W = "";
$totalx_H = "";
$struck_off_flag = 1;
$is_ot_eligible = '';

/**
*Find global rules for attendance
*/
$weekend_ot_all_additional = '';
$weekend_ot_tiffin_cut = '';
$weekend_ot_tifin_cut_in_hour = '';
$weekend_ot_std_limit = '';
$weekend_ot_tiffin_top_hour = '';
$attendance_meta = array();

$attendance_meta = $con->SelectAll("attendance_meta");
$weekend_ot_all_additional = $attendance_meta{0}->weekend_ot_all_additional; 
$weekend_ot_tiffin_cut = $attendance_meta{0}->weekend_ot_tiffin_cut;
$weekend_ot_tifin_cut_in_hour= $attendance_meta{0}->weekend_ot_tifin_cut_in_hour;
$weekend_ot_std_limit = $attendance_meta{0}->weekend_ot_std_limit;
$weekend_ot_tiffin_top_hour = $attendance_meta{0}->weekend_ot_tiffin_top_hour;


if (isset($_POST["SearchOT"])) {
    extract($_POST);

    $emp_code = $_POST["emp_code"];
    $_SESSION["emp_code_jcard"] = $emp_code;


    //Fetch employee ot eligible status
    $emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    //Check into tmp table. If not found, then look into struck off table
    if (count($emp_info) > 0) {
        $is_ot_eligible = $emp_info{0}->is_ot_eligible;
    } else {
        $struck_info = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code'");
        if (count($struck_info) > 0) {
            $is_ot_eligible = $struck_info{0}->is_ot_eligible;
        }
    }
     

    $temp_end_date = date('Y-m-d', strtotime($_POST["end_date"])); //new DateTime();
    $temp_start_date = date('Y-m-d', strtotime($_POST["start_date"]));
    $_SESSION["s_date"] = $temp_start_date;
    $_SESSION["e_date"] = $temp_end_date;


    $job_cartArray = $con->SelectAllByAssoc("job_card", " emp_code='$emp_code' And date BETWEEN '$temp_start_date' AND '$temp_end_date'");

    /*
     * Check if imp_date is empty or not
     * If imp date is not empty then, collect the date. 
     * See if the imp date is in range of start date and end date.
     * If in range- then do follwing
     * Smaller dates than imp date should be coming from date table.
     * Company will be employer company. 
     * Dates from imp date and larger will be alt company
     */


    $sl_date = $con->SelectAllByCondition("dates", "company_id='2' AND date BETWEEN '$temp_start_date' AND '$temp_end_date'");

    $array_job_dates = array();
    $array_sl_dates = array();

    if (count($job_cartArray) >= 1) {
        foreach ($job_cartArray as $jb) {
            array_push($array_job_dates, $jb["date"]);
        }
    }
    foreach ($sl_date as $sd) {
        array_push($array_sl_dates, $sd->date);
    }


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

    $users = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code'");
    $emp_id = $users{0}->emp_id;

    $shifts = "select sp.shift_id, sp.shift_title, sp.saturday_start_time, sp.saturday_end_time,sp.sat_end_day from employee_shifing_user as esu,shift_policy as sp where esu.emp_id ='$emp_id' AND esu.shift_id = sp.shift_id";
    $emp_shifts = $con->QueryResult($shifts);


    $shift_id = $emp_shifts{0}->shift_id;
    $shift_title = $emp_shifts{0}->shift_title;
    $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
    $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;
    $sat_end_day = $emp_shifts{0}->sat_end_day;

    //Storing shift raw end time in session
    $_SESSION["raw_shift_end_time"] = $raw_shift_end_time;


    if ($sat_end_day > 1) {
        $_SESSION["night_shift_emp"] = 1;
    } else {
        $_SESSION["night_shift_emp"] = 0;
    }
    $x = 0;
    foreach ($job_cartArray as $jca) {

        //$con->debug($jca["in_time"]);
        $strFirstTime = date("H:i:s", strtotime($jca["in_time"]));
        $strSecondTime = date("H:i:s", strtotime("00:00:00"));
        if ($strFirstTime != $strSecondTime) {
            $tm_date_x = $jca["date"];

            // Start if date is weekend //

            /*
             * Condition :: find company ID for calendar dates array
             * This array was generated based on alternate attendance settings
             * Inside the array, only one date is traced by the date from job card array
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
                    //$neq_company_id = $jca["company_id"];
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


            // Start if date is weekend //
            $temp_result_x = $con->existsByCondition("dates", " company_id='$neq_company_id' AND date='$tm_date_x' AND day_type_id='2'");
            $temp_result_H = $con->existsByCondition("dates", " company_id='$neq_company_id' AND date='$tm_date_x' AND (day_type_id='3' OR day_type_id='4')");
            if ($temp_result_x == 1) {
                $job_cartArray[$x]["status"] = "W";
            } else if ($temp_result_H == 1) {
                $job_cartArray[$x]["status"] = "H";
            } else {
                //Before assigning as present check whether he is late or not
                $late_status_array = array();
                $late_status = '';
                $query = "select * from job_card where date = '$tm_date_x' and emp_code='$emp_code'";
                $late_status_array = $con->QueryResult($query);
                if (count($late_status_array) > 0) {
                    $late_status = $late_status_array{0}->is_late;
                    if ($late_status == 1) {
                        $job_cartArray[$x]["status"] = "Late";
                    } else {
                        $job_cartArray[$x]["status"] = "P";
                    }
                }
            }
            // End if date is weekend //
        }
        if ($strFirstTime == $strSecondTime) {
            $tm_date = $jca["date"];

            //eq :: equal

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
            $temp_weekend = $con->existsByCondition("dates", "company_id='$eq_company_id' AND date='$tm_date' AND day_type_id='2'");
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
             * Replacement weekend status should now be assigned
             */
            $replacement_status = '';
            $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$tm_date' AND rw_emp_code='$emp_code'");
            if (count($replacement_weekend) > 0) {
                if (isset($replacement_weekend{0}->replacement_weekend_status) > 0) {
                    $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
                }
            }
            if ($replacement_status != '') {
                $job_cartArray[$x]["status"] = $replacement_status;
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
                    $job_cartArray[$x]["status"] = " ";
                }
            }
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

        //Fetch weekend replacement data
        $replacement_status = '';
        $f_date = $job_cartArray[$y]["date"];

        $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$f_date' AND rw_emp_code='$emp_code'");
        if (count($replacement_weekend) > 0) {
            $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
        }

        /*
         * Replacement status W: Weekend OT rule
         * Replacement status P: genereal OT rule :: to be collected from job_card table
         */

        if ($replacement_status != '') {
            //If replacenment status is "P"
            if ($replacement_status == "P") {
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
            } else if ($replacement_status == "W" || $replacement_status == "H") {
                /*
                 * Same weekend rule for general weekend
                 */
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
                            //Total OT for weekend shouldn't be calculated for not ot eligible
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
                        //Total OT for weekent shouldnt be calculated for not ot eligible
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

                //$con->debug($tem_time_x);


                $x_time_array = explode(":", $tem_time_x);
                //$xhours += $x_time_array[0];
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
            }
        } else {
            /*
             * if replacement data is empty
             * general business rule for weekend
             * and normal ot calculation will be followed
             */

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
                            //Total OT for weekend shouldn't be calculated for not ot eligible
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
                        //Total OT for weekent shouldnt be calculated for not ot eligible
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

                //$con->debug($tem_time_x);


                $x_time_array = explode(":", $tem_time_x);
                //$xhours += $x_time_array[0];
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

if (isset($_POST["btnExcel"])) {
    extract($_POST);
    if ($_POST["tmp_employee_code"] != "") {
        $emp_code_temp = $_POST["tmp_employee_code"];
        $emp = array();
        $query_mod = "SELECT em.*,dg.designation_title,dep.department_title,st.staffgrade_title FROM tmp_employee em, designation as dg,department as dep, staffgrad as st WHERE em.emp_designation= dg.designation_id AND em.emp_staff_grade= st.staffgrade_id AND em.emp_department= dep.department_id AND emp_code ='$emp_code_temp' order by emp_id DESC";
        $result11 = mysqli_query($open, $query_mod);
        while ($rows11 = mysqli_fetch_object($result11)) {
            $emp[] = $rows11;
        }

        if (count($emp) <= 0) {
            $query_mod = "SELECT
	em.*, desg.designation_title,
	dept.department_title,
	stf.staffgrade_title
FROM    
	struck_off em

LEFT JOIN designation desg ON desg.designation_id = em.emp_designation  
LEFT JOIN department dept ON dept.department_id = em.emp_department 
LEFT JOIN staffgrad stf ON stf.staffgrade_id = em.emp_staff_grade

WHERE em.emp_code = '$emp_code_temp'";
            $result11 = mysqli_query($open, $query_mod);
            while ($rows11 = mysqli_fetch_object($result11)) {
                $emp[] = $rows11;
            }
        }

        $emp_code_xls = $emp{0}->emp_code;
        $emp_name_xls = $emp{0}->emp_firstname . " " . $emp{0}->emp_lastname;
        $emp_department_xls = $emp{0}->department_title;
        $emp_designation_xls = $emp{0}->designation_title;
        $emp_date_of_join = $emp{0}->emp_dateofjoin;
        $emp_staff_grade = $emp{0}->emp_staff_grade;

        $raw_shift_end_time = $_SESSION["raw_shift_end_time"];

        //---------------- time -------------------//
        //---------------- time -------------------//


        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
        //Cell coloring
        //echo "<table border='1'>";
        $cWorkSheet->setCellValueByColumnAndRow(0, 1, "r-pac (Bangladesh) Limited");
        $cWorkSheet->setCellValueByColumnAndRow(0, 2, "JOB Card");
        $cWorkSheet->setCellValueByColumnAndRow(0, 4, "Employee Code");
        $cWorkSheet->setCellValueByColumnAndRow(1, 4, ":");
        $cWorkSheet->setCellValueByColumnAndRow(2, 4, "$emp_code_xls");
        $cWorkSheet->setCellValueByColumnAndRow(0, 5, "Name");
        $cWorkSheet->setCellValueByColumnAndRow(1, 5, ":");
        $cWorkSheet->setCellValueByColumnAndRow(2, 5, "$emp_name_xls");
        $cWorkSheet->setCellValueByColumnAndRow(0, 6, "Designation");
        $cWorkSheet->setCellValueByColumnAndRow(1, 6, ":");
        $cWorkSheet->setCellValueByColumnAndRow(2, 6, "$emp_designation_xls");
        $cWorkSheet->setCellValueByColumnAndRow(0, 7, "Department");
        $cWorkSheet->setCellValueByColumnAndRow(1, 7, ":");
        $cWorkSheet->setCellValueByColumnAndRow(2, 7, "$emp_department_xls");
        $cWorkSheet->setCellValueByColumnAndRow(0, 8, "D.O.J");
        $cWorkSheet->setCellValueByColumnAndRow(1, 8, ":");
        $cWorkSheet->setCellValueByColumnAndRow(2, 8, "$emp_date_of_join");

        $cWorkSheet->setCellValueByColumnAndRow(0, 11, "SL No");
        $cWorkSheet->setCellValueByColumnAndRow(1, 11, "In Date");
        $cWorkSheet->setCellValueByColumnAndRow(2, 11, "In Time");
        $cWorkSheet->setCellValueByColumnAndRow(3, 11, "Out Date");
        $cWorkSheet->setCellValueByColumnAndRow(4, 11, "Out Time");
        $cWorkSheet->setCellValueByColumnAndRow(5, 11, "OT");

        $cWorkSheet->setCellValueByColumnAndRow(6, 11, "Status");
        if (isset($_SESSION["tmp_job_cardArray"])) {
            $job_car_arr = $_SESSION["tmp_job_cardArray"];
            $countRowX = count($job_car_arr);
            $xx = 0;
            $totalx_A = 0;
            $totalx_SL = 0;
            $totalx_CL = 0;
            $totalx_AL = 0;

            $totalx_P = 0;
            $totalx_W = 0;
            $totalx_H = 0;
            $yy = 1;

            for ($row = 12; $row < $countRowX + 12; $row++) {
                if ($job_car_arr["$xx"]["status"] == "H") {
                    $totalx_H += 1;
                } else if ($job_car_arr["$xx"]["status"] == "W") {
                    $totalx_W +=1;
                } else if ($job_car_arr["$xx"]["status"] == "A") {
                    $totalx_A +=1;
                } else if ($job_car_arr["$xx"]["status"] == "P") {
                    $totalx_P +=1;
                } else if ($job_car_arr["$xx"]["status"] == "SL") {
                    $totalx_SL +=1;
                } else if ($job_car_arr["$xx"]["status"] == "CL") {
                    $totalx_CL += 1;
                } else if ($job_car_arr["$xx"]["status"] == "AL") {
                    $totalx_AL += 1;
                } else if ($job_car_arr["$xx"]["status"] == "LL") {
                    $totalx_LL += 1;
                } else if ($job_car_arr["$xx"]["status"] == "LOP") {
                    $totalx_LOP += 1;
                } else if ($job_car_arr["$xx"]["status"] == "T") {
                    $totalx_T += 1;
                }

                $cWorkSheet->setCellValueByColumnAndRow(0, $row, $yy);
                $cWorkSheet->setCellValueByColumnAndRow(1, $row, $job_car_arr["$xx"]["date"]);
                $cWorkSheet->setCellValueByColumnAndRow(2, $row, $job_car_arr["$xx"]["in_time"]);
                $cWorkSheet->setCellValueByColumnAndRow(3, $row, $job_car_arr["$xx"]["second_date"]);


                if ($_SESSION["user_type"] == "super_admin") {
                    if ($job_car_arr["$xx"]["out_time"] != '') {
                        $cWorkSheet->setCellValueByColumnAndRow(4, $row, $job_car_arr["$xx"]["out_time"]);
                    } else {
                        if ($raw_shift_end_time != '') {
                            $cWorkSheet->setCellValueByColumnAndRow(4, $row, $raw_shift_end_time);
                        } else {
                            $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
                            $raw_shift_end_time = $defaults{0}->saturday_end_time;
                            $cWorkSheet->setCellValueByColumnAndRow(4, $row, $raw_shift_end_time);
                        }
                    }

                    if ($job_car_arr["$xx"]["status"] == "W" || $job_car_arr["$xx"]["status"] == "H") {
                        if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
                            $temp_in_time_g = date("H:i:s", strtotime($job_car_arr["$xx"]["in_time"]));
                            $temp_out_time_g = date("H:i:s", strtotime($job_car_arr["$xx"]["out_time"]));
                        } else {
                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                        }

                        $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
                        $temp_time = date("H:i:s", $tem_time_diff_wb);

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
                        $tem_time_diff = date("H:i:s", $OT);
                        if ($tem_time_diff == 0) {
                            $cWorkSheet->setCellValueByColumnAndRow(5, $row, "00:00:00");
                        } else {

                            $tifin_time = explode(":", $tem_time_diff);
                            if ($tifin_time[0] >= 7) {

                                $cWorkSheet->setCellValueByColumnAndRow(5, $row, date("H:i:s", strtotime("$tem_time_diff -1 hour")));
                            } else {
                                $cWorkSheet->setCellValueByColumnAndRow(5, $row, $tem_time_diff);
                            }
                        }
                    } else {
                        if ($job_car_arr["$xx"]["ot_hours"] == "") {
                            $cWorkSheet->setCellValueByColumnAndRow(5, $row, "00:00:00");
                        } else {
                            $cWorkSheet->setCellValueByColumnAndRow(5, $row, $job_car_arr["$xx"]["ot_hours"]);
                        }
                    }
                } else {

                    //If user is not a super user
                    //If user doesnt have an standard out
                    if ($job_car_arr["$xx"]["standard_out"] != '') {
                        //For weekend standard out :: calculate 9 hours.
                        if ($job_car_arr["$xx"]["status"] == "W" || $job_car_arr["$xx"]["status"] == "H") {
                            $holiday_intime = $job_car_arr["$xx"]["in_time"];
                            $holiday_outime = $job_car_arr["$xx"]["out_time"];
                            $std_out_final = date("G:i:s", strtotime("$holiday_intime +9 hours"));
                            if ($holiday_outime > $std_out_final) {
                                $cWorkSheet->setCellValueByColumnAndRow(4, $row, $std_out_final);
                            } else {
                                $cWorkSheet->setCellValueByColumnAndRow(4, $row, $job_car_arr["$xx"]["standard_out"]);
                            }
                        } else {
                            //for normal working days
                            $cWorkSheet->setCellValueByColumnAndRow(4, $row, $job_car_arr["$xx"]["standard_out"]);
                        }
                    } else {
                        if ($job_car_arr["$xx"]["out_time"] != '') {
                            $cWorkSheet->setCellValueByColumnAndRow(4, $row, $job_car_arr["$xx"]["out_time"]);
                        } else {
                            if ($raw_shift_end_time != '') {
                                $cWorkSheet->setCellValueByColumnAndRow(4, $row, "$raw_shift_end_time");
                            } else {
                                $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
                                $raw_shift_end_time = $defaults{0}->saturday_end_time;
                                $cWorkSheet->setCellValueByColumnAndRow(4, $row, $raw_shift_end_time);
                            }
                        }
                    }

                    if ($job_car_arr["$xx"]["status"] == "W" || $job_car_arr["$xx"]["status"] == "H") {
                        //collect emp staff grade
                        if ($emp_staff_grade == 17) {
                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                        } else {

                            if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
                                $temp_in_time_g = date("H:i:s", strtotime($job_car_arr["$xx"]["in_time"]));
                                $temp_out_time_g = date("H:i:s", strtotime($job_car_arr["$xx"]["out_time"]));
                            } else {
                                $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                                $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                            }
                        }

                        $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
                        $temp_time = date("H:i:s", $tem_time_diff_wb);

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
                        $tem_time_diff = date("H:i:s", $OT);

                        if ($tem_time_diff == 0) {
                            $cWorkSheet->setCellValueByColumnAndRow(5, $row, "00:00:00");
                        } else {

                            $tifin_time = explode(":", $tem_time_diff);
                            if ($tifin_time[0] >= 7) {
//Weekend standard OT
                                $std_tem_time_diff = date("H:i:s", strtotime("08:00:00"));
                                $final_value = date("H:i:s", strtotime("$tem_time_diff -1 hour"));

//Check if std ot is more than preset limit
                                if ($final_value > $std_tem_time_diff) {
                                    $cWorkSheet->setCellValueByColumnAndRow(5, $row, $std_tem_time_diff);
                                } else {
                                    $cWorkSheet->setCellValueByColumnAndRow(5, $row, $final_value);
                                }
                            } else {
//$con->debug(date("H:i:s", strtotime($tem_time_diff)));
                                $cWorkSheet->setCellValueByColumnAndRow(5, $row, $tem_time_diff);
                            }
                        }
                    } else {
                        if ($job_car_arr["$xx"]["standard_ot_hours"] == "") {
                            $cWorkSheet->setCellValueByColumnAndRow(5, $row, "00:00:00");
                        } else {
                            $cWorkSheet->setCellValueByColumnAndRow(5, $row, $job_car_arr["$xx"]["standard_ot_hours"]);
                        }
                    }
                }
                //$cWorkSheet->setCellValueByColumnAndRow(5, $row, '-');
                $cWorkSheet->setCellValueByColumnAndRow(6, $row, $job_car_arr["$xx"]["status"]);
                if ($countRowX == $xx) {
                    break;
                }
                $xx++;
                $yy++;
            }
        }
        $rowSX = $countRowX + 14;
        $total_OT_HOURS = "";
        if (isset($_SESSION["TO_Hours_Main"])) {
            $total_OT_HOURS = $_SESSION["TO_Hours_Main"];
        }
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX, "Summary:");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 1, "Present:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 1, "$totalx_P");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 2, "Absent:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 2, "$totalx_A");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 3, "Sick Leave:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 3, "$totalx_SL");

        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 4, "Anual Leave:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 4, "$totalx_AL");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 5, "Casual Leave:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 5, "$totalx_CL");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 6, "LL");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 6, "$totalx_LL");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 7, "LOP");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 7, "$totalx_LOP");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 8, "Tour");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 8, "$totalx_T");

        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 9, "Week Day:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 9, "$totalx_W");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 10, "Holiday");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 10, "$totalx_H");
        $cWorkSheet->setCellValueByColumnAndRow(0, $rowSX + 11, "Total OT:");
        $cWorkSheet->setCellValueByColumnAndRow(1, $rowSX + 11, "$total_OT_HOURS");

        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
        // Write the Excel file to filename some_excel_file.xlsx in the current directory
        $downloadFilename = $emp_code_temp . "_" . rand(999, 99999999999) . "job_card.xlsx";
        unset($_SESSION["TO_Hours_Main"]);
        $objWriter->save("$downloadFilename");
        $_SESSION["downloadFile"] = $downloadFilename;
    }
}
?>

<?php include '../view_layout/header_view.php'; ?>
<style type="text/css">
    .unique_color{
        background-color: lightblue;
        padding-top:10px;
        padding-left: 10px;
        margin-left:-10px;
        height:45px;
        margin-top: -10px;
        margin-bottom:-10px;
        margin-right:-10px;
    }
</style>
<script language="JavaScript">
    $(document).ready(function () {
        $("#test").hide();
        // Listen for click on toggle checkbox :: multiple_leave_types
        $('#delete_existing_data').click(function (event) {
            if (this.checked) {
                $("#important_notice").show(500);
                //Replace existing link button
                //Show the link to delete existing records
                $("#delete_record").show();
                //Hide link to process without deleting existing record
                $("#keep_record").hide();
                $("#test").show(500);
                $("#delete_manually_edited_data").prop("checked", false);

            } else {
                $("#delete_edited_data").hide();
                $("#test").hide(500);
                $("#important_notice").hide(500);
                //Hide the link to delete existing records
                $("#delete_record").hide();
                //Show link to process without deleting existing record
                $("#keep_record").show();

            }
        });
        //Trigger to delete manually edited data. 
        $("#delete_manually_edited_data").click(function () {
            if (this.checked) {
                $("#delete_edited_data").show();
                $("#delete_record").hide();
                $("#keep_record").hide();
            } else {
                $("#delete_record").show();
                $("#delete_edited_data").hide();
                $("#keep_record").hide();
            }
        });
    });</script> 
<div class="col-md-12">
    <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
        <div class="k-button" style="background-color: lightgoldenrodyellow;">    
            <input type="checkbox" id="delete_existing_data" name="delete_existing_data" style="margin-top:8px;" /> &nbsp; <font style="color:darkgray; margin-top:-5px;">Delete Existing Data</font>
        </div>
        <div id="test" class="k-button" style="background-color: lightgoldenrodyellow;">    
            <input type="checkbox" id="delete_manually_edited_data" name="delete_manually_edited_data" style="margin-top:8px;" /> &nbsp; <font style="color:darkgray; margin-top:-5px;">Delete Manually Edited Data</font>
        </div>
        <!--Trigger if rewrite is not checked :: send rewrite flag as 0-->
        <a id="keep_record" href="txt_process.php?permission_id=<?php echo $permission_id; ?>&rewrite_flag=0&delete_edited_data=0" class="k-button pull-right" style="text-decoration:none;">Update Attendance Data</a>
        <!--Trigger if rewrite is checked :: send rewrite flag as 1-->
        <a id="delete_record" href="txt_process.php?permission_id=<?php echo $permission_id; ?>&rewrite_flag=1&delete_edited_data=0" class="k-button pull-right" style="text-decoration:none; display: none; background-color: lightgoldenrodyellow;">Update Attendance Data</a>
        <a id="delete_edited_data" href="txt_process.php?permission_id=<?php echo $permission_id; ?>&rewrite_flag=1&delete_edited_data=1" class="k-button pull-right" style="text-decoration:none; display: none; background-color: orange;">Update Attendance Data</a>  
        <div class="clearfix"></div><br />

        <div id="important_notice" class="col-md-12"  style="display: none; text-align: justify; border:1px solid red;color:darkgray; font-size: 13px; border-radius: 5px; height: 60px; background-color: lightgoldenrodyellow;">
            <font color='red'>Important!</font> If you select this option, this will rewrite existing data (in time, out time, over time, and attendance status) for dates and employee codes in this text file you are about to process.
            If you have manually edited any information of this specific records, those information will not be re-written unless 'Delete Manually Edited Data' option is checked. Any other records unrelated to dates and employee
            codes in this text file, will not be changed. 
        </div>
        <div class="clearfix"></div>
        <br />
    <?php endif; ?>
    <?php if ($_SESSION["emp_code"] == "S2S0465_IGNORE"): ?>

    <?php else: ?>    
        <div class="widget" style="background-color: white;">
            <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Job Card Process</h6></div>
            <div class="widget-body" style="background-color: white;">
                <div class="row">
                    <form  method="post">
                        <div class="col-md-2" style="margin-left: -15px;">
                            <label style="padding-left:5px;">Employee Code</label>
                            <span id="jaxax">
                                <script>
                                    $(document).ready(function () {
                                        var interval;
                                        var htmls;
                                        window.setInterval(function () {
                                            var jexa = $('#jaxax').attr('class');
                                            var checkallstring = $('#emp_code').val();
                                            if (checkallstring == "")
                                            {
                                                $('#jaxax').attr('class', '0');
                                            }
                                            if (jexa != "forsting")
                                            {
                                                if (checkallstring !== "")
                                                {
                                                    //ajax start
                                                    $.ajax({
                                                        url: "../../controller/leave_management_controllers/hr_leave_management/emp_for_leave_controller.php?emp_code=" + checkallstring + "",
                                                        type: "GET",
                                                        dataType: "JSON",
                                                        success: function (data) {
                                                            var objects = data.data;
                                                            console.log(objects);
                                                            var htmls = '';
                                                            htmls += '<br /><div style="border: 1px solid silver; border-radius:5px; margin-left:-5px;">';
                                                            $.each(objects, function () {
                                                                htmls += '<div class="col-md-6"><b>Company Name:</b></div><div class="col-md-6">' + this.company_title + ' ';
                                                                htmls += '</div><div class="clearfix"></div>';
                                                                htmls += '<div class="col-md-6"><b>Full Name:</b></div><div class="col-md-6">' + this.emp_firstname + ' ';
                                                                htmls += '</div><div class="clearfix"></div>';
                                                                htmls += '<div class="col-md-6"><b>Department:</b></div><div class="col-md-6">' + this.department_title + ' ';
                                                                htmls += '</div><div class="clearfix"></div>';
                                                                htmls += '<div class="col-md-6"><b>Designation:</b></div><div class="col-md-6">' + this.designation_title + ' ';
                                                                htmls += '</div><div class="clearfix"></div>';
                                                            });
                                                            htmls += '</div>';
                                                            $("#emp_info_container").html(htmls);
                                                        }});
                                                    $('#jaxax').attr("class", "forsting");
                                                    //ajax end
                                                }
                                                else
                                                {
                                                    $('#jaxax').attr("class", "1");
                                                }
                                            }
                                        }, 1000);
                                    });
                                </script>
                            </span>
                            <div style="padding-left:5px">
                                <input class="k-textbox" id="emp_code" name="emp_code" value="<?php echo $emp_code; ?>" list="employees">
                                <datalist id="employees">
                                    <?php if (count($employees) >= 1): ?>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo $emp->emp_code; ?>">
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                </datalist> 
                            </div>
                        </div>
                        <div class="col-md-1"></div>
                        <div class="col-md-3">
                            <label>Start date</label>
                            <div><?php echo $con->DateTimePicker("start_date", "start_date", $start_date, "", ""); ?></div>
                        </div>
                        <div class="col-md-3">
                            <label>End date</label>
                            <div>
                                <?php echo $con->DateTimePicker("end_date", "end_date", $end_date, "", ""); ?>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <input value="Search" type="submit" id="SearchOT" class="k-button" name="SearchOT" style="width: 120px; margin-top: 20px; height:30px;"/>
                        </div>
                        <div class="clearfix"></div>
                        <div id="emp_info_container"></div>
                    </form>
                </div>
                <div class="clearfix"></div>
                <br /><br />
                <div class="row">
                    <?php if ($con->hasPermissionExport($permission_id) == "yes"): ?>
                        <a href="pdf_job_card.php?emp_code=<?php echo $emp_code; ?>&action=pdf" class="k-button" style="text-decoration:none; height: 35px; padding-top:4px;" target="_blank">Export to PDF</a>
                    <?php endif; ?>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
                <br /><br />
                <div class="row">
                    <div id="example" class="k-content">
                        <table id="grid" style="font-size: 14px;">
                            <colgroup>
                                <col style="width:130px"/>
                                <col style="width:150px" />
                                <col style="width:130px"/>
                                <?php if (isset($_SESSION["user_type"])): ?>
                                    <?php if ($_SESSION["user_type"] == "super_admin"): ?>
                                        <col style="width:140px" />
                                        <col style="width:138px" />
                                        <col style="width:100px">
                                    <?php else: ?>
                                        <col style="width:140px" />
                                        <col style="width:100px" />
                                    <?php endif; ?>
                                <?php endif; ?>
                                <col style="width:60px"/>
                                <?php if (isset($_SESSION["user_type"])): ?>
                                    <?php if ($_SESSION["user_type"] == "super_admin"): ?>
                                        <col style="width:100px" />
                                    <?php endif; ?>
                                <?php endif; ?>

                            </colgroup>
                            <thead>
                                <tr>
                                    <th data-field="date">Date</th>
                                    <th data-field="in_time">In Time</th>
                                    <th data-field="second_date">Date</th>
                                    <?php if (isset($_SESSION["user_type"])): ?>
                                        <?php if ($_SESSION["user_type"] == "super_admin"): ?>
                                            <th data-field="out_time">Out Time</th>
                                            <th data-field="working_hours">Total Hours</th>
                                            <th data-field="ot_hours">Over Time</th>
                                        <?php else: ?>
                                            <th data-field="standard_out_time">Out Time</th>
                                            <th data-field="standard_ot_hours">Over Time</th>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <th data-field="status">Status</th>
                                    <?php if (isset($_SESSION["user_type"])): ?>

                                        <?php if ($_SESSION["user_type"] == "super_admin"): ?>
                                            <th data-field="action">Action</th>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $total_A = 0;
                                $total_SL = 0;
                                $total_CL = 0;
                                $total_AL = 0;
                                $total_P = 0;
                                $total_W = 0;
                                $total_H = 0;
                                $total_OT = 0;
                                $replacement_status = '';
                                $total_Late = 0;

                                ?>
                                <?php if (isset($_SESSION["tmp_job_cardArray"])): ?>

                                    <?php if (count($_SESSION["tmp_job_cardArray"]) >= 1): ?>
                                        <?php foreach ($_SESSION["tmp_job_cardArray"] as $jr): ?>
                                            <?php if ($jr["is_manually_edit"] == 1): ?>

                                                <tr  id="<?php echo $jr["emp_id"]; ?>_tr" style="background-color:">
                                                <?php else: ?>
                                                <tr class="test" id="<?php echo $jr["emp_id"]; ?>_tr">
                                                <?php endif; ?>
                                                <td id="<?php echo $jr["emp_id"]; ?>_date"><div <?php
                                                    if ($jr["is_manually_edit"] == 1) {
                                                        echo 'class = "unique_color"';
                                                    }
                                                    ?>  id="<?php echo $jr["emp_id"]; ?>_datetime"><?php echo $jr["date"]; ?></div></td>
                                                <td>
                                                    <div <?php
                                                    if ($jr["is_manually_edit"] == 1) {
                                                        echo 'class = "unique_color"';
                                                    }
                                                    ?> id="<?php echo $jr["emp_id"]; ?>_in_time"><?php
                                                        if ($_SESSION["user_type"] != "super_admin") {

                                                            $emp_code_jcard = $_SESSION["emp_code_jcard"];
                                                            $staffs = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code_jcard'");
                                                            $emp_staff_grade = $staffs{0}->emp_staff_grade;

                                                            
                                                            if ($weekend_ot_all_additional == 1) {
                                                                if ($jr["status"] == "W" || $jr["status"] == "H") {
                                                                    echo "00:00:00";
                                                                } else {
                                                                    echo $jr["in_time"];
                                                                }
                                                            } else {
                                                                echo $jr["in_time"];
                                                            }
                                                        } else {
                                                            echo $jr["in_time"];
                                                        }
                                                        ?>


                                                    </div>
                                                    <div style="display:none;" id="<?php echo $jr["emp_id"]; ?>_night_shift"><?php
                                                        if (isset($_SESSION["night_shift_emp"])) {
                                                            echo $_SESSION["night_shift_emp"];
                                                        } else {
                                                            echo "0";
                                                        }
                                                        ?></div>
                                                    <div style="display: none;" id="<?php echo $jr["emp_id"]; ?>_in_time_pick">

                                                        <input  id="<?php echo $jr["emp_id"]; ?>_in_time_picker" />

                                                    </div>
                                                </td>
                                                <td id="<?php echo $jr["emp_id"]; ?>_second_date"><div <?php
                                                    if ($jr["is_manually_edit"] == 1) {
                                                        echo 'class = "unique_color"';
                                                    }
                                                    ?> id="<?php echo $jr["emp_id"]; ?>_second_datetime"><?php echo $jr["second_date"]; ?></div></td>
                                                        <?php if (isset($_SESSION["user_type"])): ?>

                                                    <?php if ($_SESSION["user_type"] == "super_admin"): ?>
                                                        <td><div <?php
                                                            if ($jr["is_manually_edit"] == 1) {
                                                                echo 'class = "unique_color"';
                                                            }
                                                            ?>  id="<?php echo $jr["emp_id"]; ?>_out_time"><?php
                                                                if ($jr["out_time"] != '') {
                                                                    echo $jr["out_time"];
                                                                } else {
                                                                    echo "00:00:00";
                                                                }
                                                                ?></div>

                                                            <div style="display:none;" id="<?php echo $jr["emp_id"]; ?>_out_time_pick">
                                                                <input  id="<?php echo $jr["emp_id"]; ?>_out_time_picker" />
                                                            </div>

                                                        </td>

                                                        <!--Total Working Hours-->
                                                        <td>
                                                            <div <?php
                                                            if ($jr["is_manually_edit"] == 1) {
                                                                echo 'class = "unique_color"';
                                                            }
                                                            ?>><?php
                                                                    //Calculate Total Working Hours
                                                                    $working_hours = '';

                                                                    //check if in time is smaller than out time
                                                                    $in_time_wh = $jr["in_time"];
                                                                    $out_time_wh = $jr["out_time"];


                                                                    //Find working hours 
                                                                    if ($in_time_wh < $out_time_wh) {
                                                                        $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
                                                                    } else if ($in_time_wh > $out_time_wh) {
                                                                        $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
                                                                    }


                                                                    if ($out_time_wh <= 0) {
                                                                        echo "00:00:00";
                                                                    } else {

                                                                        if ($working_hours != '') {
                                                                            echo $working_hours_formatted = date("H:i:s", $working_hours);
                                                                        } else {
                                                                            echo "00:00:00";
                                                                        }
                                                                    }
                                                                    ?></div>
                                                        </td>
                                                        <td>
                                                            <div <?php
                                                            if ($jr["is_manually_edit"] == 1) {
                                                                echo 'class = "unique_color"';
                                                            }
                                                            ?> id="<?php echo $jr["emp_id"]; ?>_ot_hours">


                                                                <?php
                                                                /*
                                                                 * look at replacement_weekend data. 
                                                                 * if exist, then find day type.
                                                                 * if day type is P then find normal OT
                                                                 * if day type is W, then calculate full OT
                                                                 */

                                                                /*
                                                                 * Fetch info from replacement weekend
                                                                 */
                                                                $replacement_status = '';
                                                                $f_date = $jr["date"];
                                                                $frmt_date = date("Y-m-d", strtotime($f_date));
                                                                $code = $jr["emp_code"];
                                                                $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$f_date' AND rw_emp_code='$code'");
                                                                if (count($replacement_weekend) > 0) {
                                                                    $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
                                                                }

                                                                if ($replacement_status != '') {

                                                                    if ($replacement_status == "P") {
                                                                        if ($jr["ot_hours"] == "") {
                                                                            echo "00:00:00";
                                                                        } else {

                                                                            echo $jr["ot_hours"];
                                                                        }
                                                                    } else if ($replacement_status == "W" || $replacement_status == "H") {
                                                                        if ($is_ot_eligible == 1) {
                                                                            $temp_in_time_g = date("H:i:s", strtotime($jr["in_time"]));
                                                                            $temp_out_time_g = date("H:i:s", strtotime($jr["out_time"]));

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
                                                                            $tem_time_diff = date("H:i:s", $OT);

                                                                            if ($tem_time_diff == 0) {
                                                                                echo "00:00:00";
                                                                            } else {

                                                                                $tifin_time = explode(":", $tem_time_diff);
                                                                                if ($tifin_time[0] >= 7) {

                                                                                    echo date("H:i:s", strtotime("$tem_time_diff -1 hour"));
                                                                                } else {
//$con->debug(date("H:i:s", strtotime($tem_time_diff)));
                                                                                    if ($_SESSION["user_type"] != "super_admin") {
                                                                                        
                                                                                    }
                                                                                    echo $tem_time_diff;
                                                                                }
                                                                            }
                                                                        } else {
                                                                            echo "00:00:00";
                                                                        }
                                                                    } else if ($replacement_status == "A") {
                                                                        echo "00:00:00";
                                                                    }
                                                                } else {

                                                                    if ($jr["status"] == "W" || $jr["status"] == "H") {
                                                                        if ($is_ot_eligible == 1) {
                                                                            $temp_in_time_g = date("H:i:s", strtotime($jr["in_time"]));
                                                                            $temp_out_time_g = date("H:i:s", strtotime($jr["out_time"]));

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
                                                                            $tem_time_diff = date("H:i:s", $OT);

                                                                            if ($tem_time_diff == 0) {
                                                                                echo "00:00:00";
                                                                            } else {

                                                                                $tifin_time = explode(":", $tem_time_diff);
                                                                                if ($tifin_time[0] >= 7) {

                                                                                    echo date("H:i:s", strtotime("$tem_time_diff -1 hour"));
                                                                                } else {
//$con->debug(date("H:i:s", strtotime($tem_time_diff)));
                                                                                    if ($_SESSION["user_type"] != "super_admin") {
                                                                                        
                                                                                    }
                                                                                    echo $tem_time_diff;
                                                                                }
                                                                            }
                                                                        } else {
                                                                            echo "00:00:00";
                                                                        }
                                                                    } else {

                                                                        if ($jr["ot_hours"] == "") {
                                                                            echo "00:00:00";
                                                                        } else {

                                                                            echo $jr["ot_hours"];
                                                                        }
                                                                    }
                                                                }
                                                                ?>



                                                            </div>

                                                        </td>

                                                    <?php else: ?>
                                                        <td>

                                                            <?php
                                                            $emp_code_jcard = $_SESSION["emp_code_jcard"];
                                                            $staffs = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code_jcard'");
                                                            $emp_staff_grade = $staffs{0}->emp_staff_grade;

                                                            if ($emp_staff_grade == '') {
                                                                $struck_staff_grade = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code_jcard'");
                                                                $emp_staff_grade = $struck_staff_grade{0}->emp_staff_grade;
                                                            }

                                                            $users = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code_jcard'");
                                                            $emp_id = $users{0}->emp_id;

                                                            $shifts = "select sp.shift_id, sp.shift_title, sp.saturday_start_time, sp.saturday_end_time,sp.sat_end_day from employee_shifing_user as esu,shift_policy as sp where esu.emp_id ='$emp_id' AND esu.shift_id = sp.shift_id";
                                                            $emp_shifts = $con->QueryResult($shifts);


                                                            $shift_id = $emp_shifts{0}->shift_id;
                                                            $shift_title = $emp_shifts{0}->shift_title;
                                                            $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
                                                            $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;
                                                            $sat_end_day = $emp_shifts{0}->sat_end_day;

                                                            if ($jr["status"] == "W" || $jr["status"] == "H") {
                                                                if ($emp_staff_grade == 17) {
                                                                    echo "00:00:00";
                                                                } else {
                                                                    
																	//Check weekend configuration
																	if($weekend_ot_all_additional == 1){
																		echo "00:00:00";
																	} else {
                                                                    	if ($jr["standard_out"] == '') {
                                                                        	if ($jr["out_time"] == '') {

                                                                            	echo "00:00:00";
                                                                        	} else {
                                                                            	echo $jr["out_time"];
                                                                        	}
                                                                    	} else {
                                                                        	echo $jr["standard_out"];
                                                                    	}
																	}
                                                                }
                                                            } else {
                                                                if ($jr["standard_out"] == '') {
                                                                    if ($jr["out_time"] == '') {
                                                                        if ($raw_shift_end_time != '') {
                                                                            echo $raw_shift_end_time;
                                                                        } else {
                                                                            echo "00:00:00";
                                                                        }
                                                                    } else {
                                                                        echo $jr["out_time"];
                                                                    }
                                                                } else {
                                                                    echo $jr["standard_out"];
                                                                }
                                                            }
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <?php
                                                            $replacement_status = '';
                                                            $f_date = $jr["date"];
                                                            $frmt_date = date("Y-m-d", strtotime($f_date));
                                                            $code = $jr["emp_code"];
                                                            $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$f_date' AND rw_emp_code='$code'");
                                                            if (count($replacement_weekend) > 0) {
                                                                $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
                                                            }

                                                            if ($replacement_status != '') {
                                                                if ($replacement_status == "P") {
                                                                    if ($jr["standard_ot_hours"] == "") {
                                                                        echo "00:00:00";
                                                                    } else {
                                                                        echo $jr["standard_ot_hours"];
                                                                    }
                                                                } else if ($replacement_status == "W" || $replacement_status == 'H') {

                                                                    $emp_code_jcard = $_SESSION["emp_code_jcard"];
                                                                    $staffs = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code_jcard'");
                                                                    $emp_staff_grade = $staffs{0}->emp_staff_grade;
                                                                    if ($emp_staff_grade == '') {
                                                                        $struck_staff_grade = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code_jcard'");
                                                                        $emp_staff_grade = $struck_staff_grade{0}->emp_staff_grade;
                                                                    }

                                                                    /*
                                                                     * This part is not changed
                                                                     * Because for standard users, there will be nothing showed 
                                                                     * for weekend or holidays. 
                                                                     */
                                                                    if ($emp_staff_grade == 17) {
                                                                        echo "00:00:00";
                                                                    } else {

                                                                        if ($is_ot_eligible == 1) {
                                                                            $temp_in_time_g = date("H:i:s", strtotime($jr["in_time"]));
                                                                            $temp_out_time_g = date("H:i:s", strtotime($jr["out_time"]));

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
                                                                            $tem_time_diff = date("H:i:s", $OT);

//$con->debug($tem_time_diff);
                                                                            if ($tem_time_diff == 0) {
                                                                                echo "00:00:00";
                                                                            } else {

																			    //Check tiffin time configuration
                                                                                $tifin_time = explode(":", $tem_time_diff);
                                                                                if ($weekend_ot_tiffin_cut == 1 && $tifin_time[0] >= $weekend_ot_tiffin_top_hour) {
               																		//Standard Weekend
                                                                                    $std_tem_time_diff = date("H:i:s", strtotime($weekend_ot_std_limit));
                                                                                    $final_value = date("H:i:s", strtotime("$tem_time_diff -$weekend_ot_tifin_cut_in_hour hour"));
                                          
																					//Check weekend ot configuration
                                                                                    if ($weekend_ot_all_additional == 1){								 					
																						if ($final_value > $std_tem_time_diff) {
                                                                                        	echo $std_tem_time_diff;
                                                                                    	} else {
                                                                                       		 echo $final_value;
                                                                                    	}
																					}else {
																							echo $final_value; 
																					}	
                                                                                } else {
																					//Check weekend OT configuration
																					if ($weekend_ot_all_additional == 1){
																						  echo $weekend_ot_std_limit;
																			
																					} else {
                                                                                   		echo $tem_time_diff;
																					}                              
                                                                                }
                                                                            }
                                                                        } else {
                                                                            echo "00:00:00";
                                                                        }
                                                                    }
                                                                } else if ($replacement_status == "A") {
                                                                    echo "00:00:00";
                                                                }
                                                            } else {

                                                                if ($jr["status"] == "W" || $jr["status"] == "H") {

                                                                    $emp_code_jcard = $_SESSION["emp_code_jcard"];
                                                                    $staffs = $con->SelectAllByCondition("tmp_employee", " emp_code='$emp_code_jcard'");
                                                                    $emp_staff_grade = $staffs{0}->emp_staff_grade;
                                                                    if ($emp_staff_grade == '') {
                                                                        $struck_staff_grade = $con->SelectAllByCondition("struck_off", "emp_code='$emp_code_jcard'");
                                                                        $emp_staff_grade = $struck_staff_grade{0}->emp_staff_grade;
                                                                    }
                                                                    if ($emp_staff_grade == 17) {
                                                                        echo "00:00:00";
                                                                    } else {

                                                                        if ($is_ot_eligible == 1) {
                                                                            $temp_in_time_g = date("H:i:s", strtotime($jr["in_time"]));
                                                                            $temp_out_time_g = date("H:i:s", strtotime($jr["out_time"]));

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
                                                                            $tem_time_diff = date("H:i:s", $OT);

//$con->debug($tem_time_diff);
                                                                            if ($tem_time_diff == 0) {
                                                                                echo "00:00:00";
                                                                            } else {

                                                                                 $tifin_time = explode(":", $tem_time_diff);
                                                                                 if ($weekend_ot_tiffin_cut == 1 && $tifin_time[0] >= $weekend_ot_tiffin_top_hour) {
               																		//Standard Weekend
                                                                                    $std_tem_time_diff = date("H:i:s", strtotime($weekend_ot_std_limit));
                                                                                    $final_value = date("H:i:s", strtotime("$tem_time_diff -$weekend_ot_tifin_cut_in_hour hour"));
                                          
																					//Check weekend ot configuration
                                                                                    if ($weekend_ot_all_additional == 1){								 					
																						if ($final_value > $std_tem_time_diff) {
                                                                                        	echo $std_tem_time_diff;
                                                                                    	} else {
                                                                                       		 echo $final_value;
                                                                                    	}
																					}else {
																							echo $final_value; 
																					}
                                                                               } else {
                                                                                
																					if ($weekend_ot_all_additional== 1){
																						echo $weekend_ot_std_limit;
                                                                                   
																				    } else {
                                                                                   		echo $tem_time_diff;
																					}
                                                                                }
                                                                            }
                                                                        } else {
                                                                            echo "00:00:00";
                                                                        }
                                                                    }
                                                                } else {

                                                                    if ($jr["standard_ot_hours"] == "") {
                                                                        echo "00:00:00";
                                                                    } else {
                                                                        echo $jr["standard_ot_hours"];
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </td>
                                                    <?php endif; ?>

                                                <?php endif; ?>
                                                <td><div style="display: none;" id="emp_code_<?php echo $jr["emp_id"]; ?>"><?php echo $jr["emp_code"]; ?></div>

                                                    <div <?php
                                                    if ($jr["is_manually_edit"] == 1) {
                                                        echo 'class = "unique_color"';
                                                    }
                                                    ?> id="<?php echo $jr["emp_id"]; ?>_status">
                                                        <?php
                                                        /*
                                                         * Fetch info from replacement weekend
                                                         */
                                                        $replacement_status = '';
                                                        $f_date = $jr["date"];
                                                        $frmt_date = date("Y-m-d", strtotime($f_date));
                                                        $code = $jr["emp_code"];
                                                        $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$f_date' AND rw_emp_code='$code'");
                                                        if (count($replacement_weekend) > 0) {
                                                            $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
                                                        }
                                                        ?>
                                                        <?php if ($replacement_status != ''): ?>
                                                            <?php
                                                            echo $replacement_status;
                                                            ?>
                                                        <?php else: ?>
                                                            <?php echo $jr["status"]; ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div id="<?php echo $jr["emp_id"]; ?>_status_dropdown">

                                                        <?php $arr = array("P", "A", "W", "H", "SL", "CL", "EL", "LWP", "ML", "PL"); ?>


                                                        <div id="<?php echo $jr["emp_id"]; ?>_status_div" style="display: none;">
                                                            <select  id="<?php echo $jr["emp_id"]; ?>_status_dropdown">
                                                                <?php foreach ($arr as $a): ?>
                                                                    <option  <?php
                                                                    if ($a == $jr["status"]) {
                                                                        echo "  selected='selected'  ";
                                                                    }
                                                                    ?> value="<?php echo $a; ?>"><?php echo $a; ?></option>
                                                                    <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div></td>


                                                <?php if (isset($_SESSION["user_type"])): ?>

                                                    <?php if ($_SESSION["user_type"] == "super_admin"): ?>
                                                        <td role="gridcell">
                                                            <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
                                                                <a id="<?php echo $jr["emp_id"]; ?>_click" class="k-button k-button-icontext k-grid-edit" href="javascript:void(0);">
                                                                    <span class="k-icon k-edit"></span>
                                                                    Edit
                                                                </a>

                                                                <a style="display:none;" id="<?php echo $jr["emp_id"]; ?>_update_click" class="k-button k-button-icontext k-grid-edit" href="javascript:void(0);">
                                                                    <span class="k-icon k-edit"></span>
                                                                    Update
                                                                </a>
                                                            <?php endif; ?>

                                                            <script type="text/javascript">
                                                                $(document).ready(function () {
                                                                    $(document).on('click', '#<?php echo $jr["emp_id"]; ?>_click', function () {
                                                                        $("#<?php echo $jr["emp_id"]; ?>_status").hide();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_status_div").show();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_in_time").hide();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_out_time").hide();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_in_time_pick").show();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_out_time_pick").show();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_in_time_picker").val($("#<?php echo $jr["emp_id"]; ?>_in_time").html());
                                                                        $("#<?php echo $jr["emp_id"]; ?>_out_time_picker").val($("#<?php echo $jr["emp_id"]; ?>_out_time").html());
                                                                        $("#<?php echo $jr["emp_id"]; ?>_click").hide();
                                                                        $("#<?php echo $jr["emp_id"]; ?>_update_click").show();
                                                                    });
                                                                    $(document).on('click', '#<?php echo $jr["emp_id"]; ?>_update_click', function () {
                                                                        var StatusValue_<?php echo $jr["emp_id"]; ?> = $("#<?php echo $jr["emp_id"]; ?>_status_dropdown option:selected").val();
                                                                        var emp_code_<?php echo $jr["emp_id"]; ?> = $("#emp_code_<?php echo $jr["emp_id"]; ?>").html();
                                                                        var job_card_id_<?php echo $jr["emp_id"]; ?> =<?php
                                    if (isset($jr["job_card_id"])) {
                                        echo $jr["job_card_id"];
                                    } else {
                                        echo "0";
                                    }
                                    ?>;
                                                                        var d_<?php echo $jr["emp_id"]; ?>_night_shift = $("#<?php echo $jr["emp_id"]; ?>_night_shift").html();
                                                                        var d_<?php echo $jr["emp_id"]; ?>_second_date = $("#<?php echo $jr["emp_id"]; ?>_second_date").html();
                                                                        var date_<?php echo $jr["emp_id"]; ?> = $("#<?php echo $jr["emp_id"]; ?>_datetime").html();
                                                                        var job_card_in_time_<?php echo $jr["emp_id"]; ?> = $("#<?php echo $jr["emp_id"]; ?>_in_time_picker").val();
                                                                        var job_card_out_time_<?php echo $jr["emp_id"]; ?> = $("#<?php echo $jr["emp_id"]; ?>_out_time_picker").val();
                                                                        console.log(date_<?php echo $jr["emp_id"]; ?>);
                                                                        if (StatusValue_<?php echo $jr["emp_id"]; ?> === "SL" || StatusValue_<?php echo $jr["emp_id"]; ?> === "CL" || StatusValue_<?php echo $jr["emp_id"]; ?> === "EL" || StatusValue_<?php echo $jr["emp_id"]; ?> === "LL" || StatusValue_<?php echo $jr["emp_id"]; ?> === "LWP" || StatusValue_<?php echo $jr["emp_id"]; ?> === "T" || StatusValue_<?php echo $jr["emp_id"]; ?> === "ML" || StatusValue_<?php echo $jr["emp_id"]; ?> === "PL") {
                                                                            $.ajax({
                                                                                type: "POST",
                                                                                url: "leave_controller.php",
                                                                                data: {emp_code: emp_code_<?php echo $jr["emp_id"]; ?>, job_card_id: job_card_id_<?php echo $jr["emp_id"]; ?>, status: StatusValue_<?php echo $jr["emp_id"]; ?>, date: date_<?php echo $jr["emp_id"]; ?>, in_time: job_card_in_time_<?php echo $jr["emp_id"]; ?>, out_time: job_card_out_time_<?php echo $jr["emp_id"]; ?>, second_date: d_<?php echo $jr["emp_id"]; ?>_second_date, night_shift: d_<?php echo $jr["emp_id"]; ?>_night_shift},
                                                                                success: function (response) {
                                                                                    console.log(response);
                                                                                    //console.log(response);                                                      
                                                                                    jQuery('#SearchOT').click();
                                                                                },
                                                                                error: function (a, b, c) {
                                                                                    //alert(a.responseText);
                                                                                }

                                                                            });
                                                                        } else if (StatusValue_<?php echo $jr["emp_id"]; ?> === "A") {
                                                                            $.ajax({
                                                                                type: "POST",
                                                                                url: "absent_controller.php",
                                                                                data: {emp_code: emp_code_<?php echo $jr["emp_id"]; ?>, job_card_id: job_card_id_<?php echo $jr["emp_id"]; ?>, status: StatusValue_<?php echo $jr["emp_id"]; ?>, date: date_<?php echo $jr["emp_id"]; ?>, in_time: job_card_in_time_<?php echo $jr["emp_id"]; ?>, out_time: job_card_out_time_<?php echo $jr["emp_id"]; ?>, second_date: d_<?php echo $jr["emp_id"]; ?>_second_date, night_shift: d_<?php echo $jr["emp_id"]; ?>_night_shift},
                                                                                success: function (response) {
                                                                                    console.log(response);
                                                                                    //console.log(response);                                                      
                                                                                    jQuery('#SearchOT').click();
                                                                                },
                                                                                error: function (a, b, c) {
                                                                                    //alert(a.responseText);
                                                                                }

                                                                            });
                                                                        } else if (StatusValue_<?php echo $jr["emp_id"]; ?> === "P" || StatusValue_<?php echo $jr["emp_id"]; ?> === "W" || StatusValue_<?php echo $jr["emp_id"]; ?> === "H") {
                                                                            $.ajax({
                                                                                type: "POST",
                                                                                url: "update_job_card_controller.php",
                                                                                data: {emp_code: emp_code_<?php echo $jr["emp_id"]; ?>, job_card_id: job_card_id_<?php echo $jr["emp_id"]; ?>, status: StatusValue_<?php echo $jr["emp_id"]; ?>, date: date_<?php echo $jr["emp_id"]; ?>, in_time: job_card_in_time_<?php echo $jr["emp_id"]; ?>, out_time: job_card_out_time_<?php echo $jr["emp_id"]; ?>, second_date: d_<?php echo $jr["emp_id"]; ?>_second_date, night_shift: d_<?php echo $jr["emp_id"]; ?>_night_shift},
                                                                                success: function (response) {
                                                                                    console.log(response);
                                                                                    //console.log(response); 

                                                                                    jQuery('#SearchOT').click();
                                                                                },
                                                                                error: function (a, b, c) {
                                                                                    //alert(a.responseText);
                                                                                }

                                                                            });
                                                                        }
                                                                    });
                                                                });</script>
                                                        </td>

                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <?php
                                                if ($jr["status"] == "H") {
                                                    $total_H += 1;
                                                } else if ($jr["status"] == "W") {
                                                    $total_W +=1;
                                                } else if ($jr["status"] == "A") {
                                                    $total_A +=1;
                                                } else if ($jr["status"] == "P") {
                                                    $total_P +=1;
                                                } else if ($jr["status"] == "SL") {
                                                    $total_SL +=1;
                                                } else if ($jr["status"] == "CL") {
                                                    $total_CL += 1;
                                                } else if ($jr["status"] == "EL") {
                                                    $total_AL += 1;
                                                } else if ($jr["status"] == "LL") {
                                                    $total_LL += 1;
                                                } else if ($jr["status"] == "LWP") {
                                                    $total_LOP += 1;
                                                } else if ($jr["status"] == "T") {
                                                    $total_T += 1;
                                                } else if ($jr["status"] == "Late") {
                                                    $total_Late += 1;
                                                }
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    <?php endif; ?> 
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div class="clearfix"></div>
                        <script>
                            $(document).ready(function () {
                                $("#grid").kendoGrid({
                                    pageable: {
                                        refresh: true,
                                        input: true,
                                        numeric: false,
                                        pageSize: 40,
                                        pageSizes: true,
                                        pageSizes: [40, 100, 200],
                                    },
                                    sortable: true,
                                    groupable: true
                                });
                            });
                        </script>
                    </div>

                    <div class="clearfix"></div>
                    <div>

                        <?php
                        $_SESSION["total_H"] = $total_H;
                        $_SESSION["total_W"] = $total_W;
                        $_SESSION["total_P"] = $total_P;
                        $_SESSION["total_A"] = $total_A;
                        $_SESSION["total_SL"] = $total_SL;
                        $_SESSION["total_CL"] = $total_CL;
                        $_SESSION["total_LL"] = $total_LL;
                        $_SESSION["total_LOP"] = $total_LOP;
                        $_SESSION["total_T"] = $total_T;
                        $_SESSION["total_Late"] = $total_Late;
                        ?>
                        ----------------------------------------------------------------------------------------------------------------------------
                        <br/>
                        <b>Total:</b><b>Holiday:&nbsp;</b><?php echo $total_H; ?>&nbsp;||
                        <b>Weekend:&nbsp;</b><?php echo $total_W; ?>&nbsp;||
                        <b>Present:&nbsp;</b><?php echo $total_P + $total_Late; ?>&nbsp;||
                        <b>Late:&nbsp;</b><?php echo $total_Late; ?>&nbsp;||
                        <b>Absent:&nbsp;</b><?php echo $total_A; ?>&nbsp;||
                        <b>Sick Leave:&nbsp;</b><?php echo $total_SL; ?>&nbsp;||
                        <b>Casual Leave:&nbsp;</b><?php echo $total_CL; ?>&nbsp; ||
                        <b>Earned Leave:&nbsp;</b><?php echo $total_AL; ?>&nbsp; ||
                        <b>LWP:&nbsp;</b><?php echo $total_LOP; ?>&nbsp; ||
                        <b>Total OT: </b> <?php
                        if (isset($_SESSION["TO_Hours_Main"])) {
                            echo $_SESSION["TO_Hours_Main"];
                        } else {
                            echo "";
                        }
                        ?>
                        <br/>
                        ----------------------------------------------------------------------------------------------------------------------------

                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include '../view_layout/footer_view.php'; ?>

