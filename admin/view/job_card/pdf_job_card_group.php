<?php

session_start();
include("../../config/class.config.php");
date_default_timezone_set('UTC');
$con = new Config();
include("MPDF/mpdf.php");

//collect user type
if (isset($_SESSION["user_type"])) {
    $user_type = $_SESSION["user_type"];
}

if (isset($_SESSION["TO_Hours_Main"])) {
    $TO_Hours_Main = $_SESSION["TO_Hours_Main"];
} else {
    $TO_Hours_Main = '00:00:00';
}

/*
 * Function to sort the array
 */

function startDateCmp($a, $b) {
    return strcmp($a['date'], $b['date']);
}

//Collect necessary variables
$company_id = $_GET["company_id"];
$department_id = $_GET["department_id"];
$temp_start_date = $_GET["start_date"];
$temp_end_date = $_GET["end_date"];


$companies = $con->SelectAllByCondition("company", "company_id='$company_id'");
$company_title = $companies{0}->company_title;

//Now find employees
$tmp_info_query = "SELECT
	em.*, desg.designation_title,
	dept.department_title,
	stf.staffgrade_title
FROM    
	tmp_employee em
LEFT JOIN designation desg ON desg.designation_id = em.emp_designation  
LEFT JOIN department dept ON dept.department_id = em.emp_department 
LEFT JOIN staffgrad stf ON stf.staffgrade_id = em.emp_staff_grade ";
$tmp_info_query .= " WHERE em.company_id='$company_id'";

if ($department_id > 0) {
    $tmp_info_query .= " AND em.emp_department = '$department_id'";
}

$tmp_info = $con->QueryResult($tmp_info_query);


$i = 1;
if (count($tmp_info) > 0) {
    foreach ($tmp_info as $ti) {

        $emp_code = $ti->emp_code;
        $emp_staff_grade = $ti->emp_staff_grade;
        $is_ot_eligible = $ti->is_ot_eligible;

        $temp_end_date = date('Y-m-d', strtotime($_GET["end_date"])); //new DateTime();
        $temp_start_date = date('Y-m-d', strtotime($_GET["start_date"]));

        $job_cartArray = $con->SelectAllByAssoc("job_card", " emp_code='$emp_code' And date BETWEEN '$temp_start_date' AND '$temp_end_date'");
        $sl_date = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date BETWEEN '$temp_start_date' AND '$temp_end_date'");

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


        $emp_id = $ti->emp_id;

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
                            if ($is_ot_eligible == 1) {
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
                        if ($is_ot_eligible == 1) {
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
                            if ($is_ot_eligible == 1) {
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
                        if ($is_ot_eligible == 1) {
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

                    $x_time_array = explode(":", $tem_time_x);
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



        usort($job_cartArray, 'startDateCmp');
        $tmp_job_cardArray = $job_cartArray;

        $emp_code_pdf = $emp_code;
        $emp_name_pdf = $ti->emp_firstname . " " . $emp->emp_lastname;
        $emp_department_pdf = $ti->department_title;
        $emp_designation_pdf = $ti->designation_title;
        $emp_date_of_join_pdf = $ti->emp_dateofjoin;

        //Collecting deafult shift
        $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");

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

        $html .= "<h4 style=\"text-align:center;\">";
        $html .= $company_title;
        $html .= "</h4>";

        $html .= "<table style=\"width:60%; font-size:12px;\"><tr>";
        $html .= "<td><b>Code:</b></td>";
        $html .= "<td>" . $emp_code_pdf . "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td><b>Full Name:</b></td>";
        $html .= "<td>" . $emp_name_pdf . "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td><b>Department:</b></td>";
        $html .= "<td>" . $emp_department_pdf . "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td><b>Designation:</b></td>";
        $html .= "<td>" . $emp_designation_pdf . "</td>";
        $html .= "</tr>";

        $html .= "<tr>";
        $html .= "<td><b>Date of Join:</b></td>";
        $html .= "<td>" . $emp_date_of_join_pdf . "</td>";
        $html .= "</tr>";

        $html .= "</table>";

        $html .= "<br />";
        $html .= "<table style=\"font-size:11px; font-weight:bold;\"><tr><td>From: ";
        $html .= $temp_start_date;
        $html .= "</td>";
        $html .= "<td style = \"width: 48px;\"></td>";
        $html .= "<td>To: ";
        $html .= $temp_end_date;
        $html .= "</td></tr></table>";
        $html .= "<br/>";

        //Creating table header
        $html .= "<table style=\"width:100%; font-size:11px; border-collapse:collapse\">";
        $html .= "<tr style=\"background-color:silver\">";
        $html .= "<th style=\"border-width:1px; border-style:solid;\">Date</th>";
        $html .= "<th style=\"border-width:1px; border-style:solid;\">In Time</th>";
        $html .= "<th style=\"border-width:1px; border-style:solid;\">Date</th>";
        $html .= "<th style=\"border-width:1px; border-style:solid;\">Out Time</th>";

        if ($user_type == "super_admin") {
            $html .= "<th style=\"border-width:1px; border-style:solid;\">Total Hours</th>";
        }

        $html .= "<th style=\"border-width:1px; border-style:solid;\">OT Hours</th>";
        $html .= "<th style=\"border-width:1px; border-style:solid;\">Status</th>";
        $html .= "</tr>";

        //Building time, date, ot
        $i = 0;
        $count = count($tmp_job_cardArray);

        for ($i = 0; $i < $count; $i++) {
            $html .= "<tr>";
            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["date"] . "</td>";
            if ($tmp_job_cardArray["$i"]["status"] == "W" || $tmp_job_cardArray["$i"]["status"] == "H") {
                if ($_SESSION["user_type"] == "super_admin") {
                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["in_time"] . "</td>";
                } else {
                    if ($emp_staff_grade == 17) {
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                    } else {
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["in_time"] . "</td>";
                    }
                }
            } else {
                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["in_time"] . "</td>";
            }

            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["second_date"] . "</td>";

            //Checking user type and then showing out time
            if ($user_type == "super_admin") {
                if ($tmp_job_cardArray["$i"]["out_time"] != '') {
                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["out_time"] . "</td>";
                } else {
                    if ($raw_shift_end_time != '') {
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                    } else {
                        $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
                        $raw_shift_end_time = $defaults{0}->saturday_end_time;
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                    }
                }

                //Calculate Total Working Hours
                $working_hours = '';

                $in_time_wh = $tmp_job_cardArray["$i"]["in_time"];
                $out_time_wh = $tmp_job_cardArray["$i"]["out_time"];



                //Find working hours
                if ($in_time_wh < $out_time_wh) {
                    $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
                } else if ($in_time_wh > $out_time_wh) {
                    $working_hours = strtotime($in_time_wh) - strtotime($out_time_wh);
                }
                //Convert to long/int type
                $c_working_hours = $working_hours + 0;
                $working_hours_formatted = date("H:i:s", $c_working_hours);

                if ($tmp_job_cardArray["$i"]["in_time"] == '') {
                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                } else if ($tmp_job_cardArray["$i"]["out_time"] == '') {
                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                } else if ($working_hours_formatted <= "00:00:00") {
                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                } else {
                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">$working_hours_formatted</td>";
                }
            } else {
                if ($tmp_job_cardArray["$i"]["status"] == "W" || $tmp_job_cardArray["$i"]["status"] == "H") {
                    if ($tmp_job_cardArray["$i"]["standard_out"] != '') {
                        $holiday_intime = $tmp_job_cardArray["$i"]["in_time"];
                        $std_out_final = date("G:i:s", strtotime("$holiday_intime +9 hours"));
                        if ($tmp_job_cardArray["$i"]["standard_out"] > $std_out_final) {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $std_out_final . "</td>";
                        } else {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["standard_out"] . "</td>";
                        }
                    } else {
                        if ($tmp_job_cardArray["$i"]["out_time"] != '') {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["out_time"] . "</td>";
                        } else {
                            if ($raw_shift_end_time != '') {
                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">$raw_shift_end_time</td>";
                            } else {
                                $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
                                $raw_shift_end_time = $defaults{0}->saturday_end_time;
                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">$raw_shift_end_time</td>";
                            }
                        }
                    }
                } else {
                    if ($tmp_job_cardArray["$i"]["standard_out"] != '') {
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["standard_out"] . "</td>";
                    } else {
                        if ($tmp_job_cardArray["$i"]["out_time"] != '') {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["out_time"] . "</td>";
                        } else {
                            if ($raw_shift_end_time != '') {
                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">$raw_shift_end_time</td>";
                            } else {
                                $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");
                                $raw_shift_end_time = $defaults{0}->saturday_end_time;
                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">$raw_shift_end_time</td>";
                            }
                        }
                    }
                }
            }

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
            $f_date = $tmp_job_cardArray["$i"]["date"];
            $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$f_date' AND rw_emp_code='$emp_code_pdf'");
            if (count($replacement_weekend) > 0) {
                if (isset($replacement_weekend{0}->replacement_weekend_status)) {
                    $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
                }
            }

            if ($replacement_status != '') {
                if ($replacement_status == "P") {
                    if ($user_type == "super_admin") {
                        if ($tmp_job_cardArray["$i"]["ot_hours"] != '') {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["ot_hours"] . "</td>";
                        } else {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\"> 00:00:00 </td>";
                        }
                    } else {
                        if ($tmp_job_cardArray["$i"]["standard_ot_hours"] != '') {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["standard_ot_hours"] . "</td>";
                        } else {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\"> 00:00:00 </td>";
                        }
                    }
                } else if ($replacement_status == "W" || $replacement_status == "H") {
                    if ($user_type == "super_admin") {

                        if ($is_ot_eligible == 1) {
                            $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                            $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
                        } else {
                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
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
                        $tem_time_diff = date("H:i:s", $OT);

                        //$con->debug($tem_time_diff);
                        if ($tem_time_diff == 0) {

                            $tem_time_diff = "00:00:00";
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                        } else {
                            /*
                             * Check if ot is more than 7 hours
                             * Substract one hour from ot
                             * Show weekend ot
                             */
                            $tifin_time = explode(":", $tem_time_diff);
                            if ($tifin_time[0] >= 7) {
                                $array_tem_time_diff = explode(":", $tem_time_diff);
                                $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
                                $one_minute = 60;
                                $final_minutes = $total_minutes - 60;

                                $real_time = date('H:i', mktime(0, $final_minutes));
                                $real_time_array = array($real_time, "00");
                                $final = implode(":", $real_time_array);
                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
                            } else {
                                if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                                } else {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                                }
                            }
                        }
                    } else {
                        if ($emp_staff_grade == 17) {
                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                        } else {
                            //if the employee is ot eligible
                            if ($is_ot_eligible == 1) {
                                $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                                $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
                            } else {
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
                        $tem_time_diff = date("H:i:s", $OT);
                        //$con->debug($tem_time_diff);
                        if ($tem_time_diff == 0) {
                            $tem_time_diff = "00:00:00";
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                        } else {
                            /*
                             * Check if ot is more than 7 hours
                             * Substract one hour from ot
                             * Show weekend ot
                             */
                            $tifin_time = explode(":", $tem_time_diff);
                            if ($tifin_time[0] >= 7) {
                                $array_tem_time_diff = explode(":", $tem_time_diff);
                                $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
                                $one_minute = 60;
                                // $final_minutes = $total_minutes - 60;
                                $final_minutes = $total_minutes;
                                $real_time = date('H:i', mktime(0, $final_minutes));
                                $real_time_array = array($real_time, "00");
                                $final = implode(":", $real_time_array);

                                //Weekend standard OT
                                $std_tem_time_diff = date("H:i:s", strtotime("08:00:00"));
                                $final_value = date("H:i:s", strtotime("$final -1 hour"));

                                if ($final > $std_tem_time_diff) {
                                    //Weekend standard OT
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $std_tem_time_diff . "</td>";
                                } else {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
                                }
                            } else {
                                if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                                } else {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                                }
                            }
                        }
                    }
                }
            } else {

                //status is not w or h, check user and place ot
                if ($user_type == "super_admin") {
                    //........Calclulate Weekend OT...............
                    if ($tmp_job_cardArray["$i"]["status"] == "W" || $tmp_job_cardArray["$i"]["status"] == "H") {
                        if ($is_ot_eligible == 1) {
                            $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                            $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
                        } else {
                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
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
                        $tem_time_diff = date("H:i:s", $OT);

                        //$con->debug($tem_time_diff);
                        if ($tem_time_diff == 0) {

                            $tem_time_diff = "00:00:00";
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                        } else {
                            /*
                             * Check if ot is more than 7 hours
                             * Substract one hour from ot
                             * Show weekend ot
                             */
                            $tifin_time = explode(":", $tem_time_diff);
                            if ($tifin_time[0] >= 7) {
                                $array_tem_time_diff = explode(":", $tem_time_diff);
                                $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
                                $one_minute = 60;
                                // $final_minutes = $total_minutes - 60;
                                $final_minutes = $total_minutes;
                                $real_time = date('H:i', mktime(0, $final_minutes));
                                $real_time_array = array($real_time, "00");
                                $final = implode(":", $real_time_array);
                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
                            } else {
                                if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                                } else {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                                }
                            }
                        }
                    } else {
                        if ($tmp_job_cardArray["$i"]["ot_hours"] != '') {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["ot_hours"] . "</td>";
                        } else {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\"> 00:00:00 </td>";
                        }
                    }
                } else {
                    /*
                     * If user is not an admin
                     * Calculate and show weekend ot
                     */

                    //........Calclulate Weekend OT...............
                    if ($tmp_job_cardArray["$i"]["status"] == "W" || $tmp_job_cardArray["$i"]["status"] == "H") {

                        if ($emp_staff_grade == 17) {
                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
                        } else {
                            //if the employee is ot eligible
                            if ($is_ot_eligible == 1) {
                                $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                                $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
                            } else {
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
                        $tem_time_diff = date("H:i:s", $OT);
                        //$con->debug($tem_time_diff);
                        if ($tem_time_diff == 0) {
                            $tem_time_diff = "00:00:00";
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                        } else {
                            /*
                             * Check if ot is more than 7 hours
                             * Substract one hour from ot
                             * Show weekend ot
                             */
                            $tifin_time = explode(":", $tem_time_diff);
                            if ($tifin_time[0] >= 7) {
                                $array_tem_time_diff = explode(":", $tem_time_diff);
                                $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
                                $one_minute = 60;

                                $final_minutes = $total_minutes - 60;

                                $real_time = date('H:i', mktime(0, $final_minutes));
                                $real_time_array = array($real_time, "00");
                                $final = implode(":", $real_time_array);

                                //Weekend standard OT
                                $std_tem_time_diff = date("H:i:s", strtotime("08:00:00"));
                                $final_value = date("H:i:s", strtotime("$final -1 hour"));

                                if ($final > $std_tem_time_diff) {
                                    //Weekend standard OT
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $std_tem_time_diff . "</td>";
                                } else {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
                                }
                            } else {
                                if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
                                } else {
                                    $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
                                }
                            }
                        }
                    } else {
                        if ($tmp_job_cardArray["$i"]["standard_ot_hours"] != '') {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["standard_ot_hours"] . "</td>";
                        } else {
                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\"> 00:00:00 </td>";
                        }
                    }
                }
            }
            if ($replacement_status != '') {
                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $replacement_status . "</td>";
            } else {
                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tmp_job_cardArray["$i"]["status"] . "</td>";
            }

            if ($tmp_job_cardArray["$i"]["status"] == "H") {
                $total_H += 1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "W") {
                $total_W +=1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "A") {
                $total_A +=1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "P") {
                $total_P +=1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "SL") {
                $total_SL +=1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "CL") {
                $total_CL += 1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "EL") {
                $total_AL += 1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "LL") {
                $total_LL += 1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "LWP") {
                $total_LOP += 1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "T") {
                $total_T += 1;
            } else if ($tmp_job_cardArray["$i"]["status"] == "Late") {
                $total_Late += 1;
            }

            $html .= "</tr>";
        }
        $html .= "</table>";

        $html .= "<hr>";
        $html .= "<div style=\"font-size:11px;\">";
        $html .= "Holiday: ";
        $html .= $total_H;
        $html .= " || ";

        $html .= "Weekend: ";
        $html .= $total_W;
        $html .= " || ";

        //Total present and total late added to make total present
        //On the idea that  
        $html .= "Present: ";
        $html .= $total_P + $total_Late;
        $html .= " || ";

        $html .= "Total Late: ";
        $html .= $total_Late;
        $html .= " || ";

        $html .= "Absent:";
        $html .= $total_A;
        $html .= " || ";

        $html .= "Sick Leave: ";
        $html .= $total_SL;
        $html .= " || ";

        $html .= "Casual Leave: ";
        $html .= $total_CL;
        $html .= " || ";

        $html .= "LL: ";
        $html .= $total_LL;
        $html .= " || ";

        $html .= "LOP: ";
        $html .= $total_LOP;
        $html .= " || ";

        $html .= "T: ";
        $html .= $total_T;
        $html .= " || ";

        $html .= "Over Time: ";
        $html .= $TO_Hours_Main;
        $html .= "</div>";
        $html .= "<hr>";
        $html .= "<br /><br /><br /><br /><br /><br /><br />";
    }
    $i++;
}


$mpdf = new mPDF('c', 'A4', '', '', 32, 25, 27, 25, 16, 13);
$mpdf->SetDisplayMode('fullpage');
$mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
//$stylesheet = file_get_contents('../../../resource/css/bootstrap.css');
$mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
$mpdf->WriteHTML($html, 2);
$mpdf->Output('GroupJobCard.pdf', 'I');


