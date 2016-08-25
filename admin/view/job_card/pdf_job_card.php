<?php

session_start();
include("../../config/class.config.php");
date_default_timezone_set('UTC');
$con = new Config();
include("../../config/jobcard.php");
$extra = new jobcard();
if ($_SESSION["emp_code_jcard"] != '') {
    $emp_code = $_SESSION["emp_code_jcard"];
}

//collect user type
if (isset($_SESSION["user_type"])) {
    $user_type = $_SESSION["user_type"];
}

$total_H = $_SESSION["total_H"];
$total_W = $_SESSION["total_W"];
$total_P = $_SESSION["total_P"];
$total_A = $_SESSION["total_A"];
$total_SL = $_SESSION["total_SL"];
$total_CL = $_SESSION["total_CL"];
$total_LL = $_SESSION["total_LL"];
$total_LOP = $_SESSION["total_LOP"];
$total_T = $_SESSION["total_T"];
$total_Late = $_SESSION["total_Late"];

if (isset($_SESSION["TO_Hours_Main"])) {
    $TO_Hours_Main = $_SESSION["TO_Hours_Main"];
} else {
    $TO_Hours_Main = '00:00:00';
}

$temp_start_date = $_SESSION["s_date"];
$temp_end_date = $_SESSION["e_date"];


$tmp_job_cardArray = ($_SESSION["tmp_job_cardArray"]);


if ($_GET["action"] == 'pdf') {
    include("MPDF/mpdf.php");
    $emp = array();
    $open = $con->open();
    $query_mod = "SELECT
	em.*, desg.designation_title,
	dept.department_title,
	stf.staffgrade_title
FROM    
	tmp_employee em

LEFT JOIN designation desg ON desg.designation_id = em.emp_designation  
LEFT JOIN department dept ON dept.department_id = em.emp_department 
LEFT JOIN staffgrad stf ON stf.staffgrade_id = em.emp_staff_grade

WHERE em.emp_code = '$emp_code'";
    $result11 = mysqli_query($open, $query_mod);
    while ($rows11 = mysqli_fetch_object($result11)) {
        $emp[] = $rows11;
    }
//    echo $query_mod;
//    exit();

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

WHERE em.emp_code = '$emp_code'";
          
        $result11 = mysqli_query($open, $query_mod);
        while ($rows11 = mysqli_fetch_object($result11)) {
            $emp[] = $rows11;
        }
    }


    $company_id = $emp{0}->company_id;
    $emp_code_pdf = $emp{0}->emp_code;
    $emp_name_pdf = $emp{0}->emp_firstname . " " . $emp{0}->emp_lastname;
    $emp_department_pdf = $emp{0}->department_title;
    $emp_designation_pdf = $emp{0}->designation_title;
    $emp_date_of_join_pdf = $emp{0}->emp_dateofjoin;
    $emp_staff_grade = $emp{0}->emp_staff_grade;

    $companies = $con->SelectAllByCondition("company", "company_id='$company_id'");
    $company_title = $companies{0}->company_title;

    $raw_shift_end_time = $_SESSION["raw_shift_end_time"];
    //Collecting deafult shift
    $defaults = $con->SelectAllByCondition("shift_policy", "shift_id='5'");


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
    $html .= "<table style=\"width:100%; font-size:11px;\">";
    $html .= "<tr style=\"border-width:1px; border-style:thin;\">";
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

            //Calculate working hours
            //Calculate Total Working Hours
            $working_hours = '';

            //check if in time is smaller than out time
            $in_time_wh = $tmp_job_cardArray["$i"]["in_time"];
            $out_time_wh = $tmp_job_cardArray["$i"]["out_time"];


            //Find working hours
            if ($in_time_wh < $out_time_wh) {
                $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
            } else if ($in_time_wh > $out_time_wh) {
                $working_hours = strtotime($in_time_wh) - strtotime($out_time_wh);
            }

            $working_hours_formatted = date("H:i:s", $working_hours);

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

//                    if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
//                        $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
//                        $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
//                    } else {
//                        $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
//                        $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
//                    }
                    
                    $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                    $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
                    

                    $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
                    $temp_time = date("H:i:s", $tem_time_diff_wb);
                    //OT calculate to be in 15 minutes buffer
                    //Calculate OT in 15 minutes buffer
                    //finding total minutes
                    //this function does all buffer calculation in jobcard class under config directory
                    $OT = $extra->TimeDifference($temp_time);
                    //OT Generated From jobcard class
//                    $std_ot_minute_buffer = "00:30:00";
//
//                    //If actual ot is bigger than std minute
//                    //Generate it as OT
//                    //Otherwise assign zero
//                    if ($temp_time >= $std_ot_minute_buffer) {
//                        $OT = $temp_time;
//                    } else {
//                        $OT = "00:00:00";
//                    }
//                    $t = EXPLODE(":", $temp_time);
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
                    //$tem_time_diff = date("H:i:s", $OT);
                    $tem_time_diff =$OT;

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
                        //$extra->OtDuductionBYHour($tem_time_diff)
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $extra->OtDuductionBYHour($tem_time_diff) . "</td>";
                        
//                        $tifin_time = explode(":", $tem_time_diff);
//                        if ($tifin_time[0] >= 7) {
//                            $array_tem_time_diff = explode(":", $tem_time_diff);
//                            $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
//                            $one_minute = 60;
//                            $final_minutes = $total_minutes - 60;
//
//                            $real_time = date('H:i', mktime(0, $final_minutes));
//                            $real_time_array = array($real_time, "00");
//                            $final = implode(":", $real_time_array);
//                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
//                        } else {
//                            if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
//                            } else {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
//                            }
//                        }
                    }
                } else {
//                    if ($emp_staff_grade == 17) {
//                        $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
//                        $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
//                    } else {
//                        //if the employee is ot eligible
//                        if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
//                            $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
//                            $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
//                        } else {
//                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
//                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
//                        }
//                    }
                    
                    $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                    $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
                    

                    $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
                    $temp_time = date("H:i:s", $tem_time_diff_wb);
                    //OT calculate to be in 15 minutes buffer
                    //Calculate OT in 15 minutes buffer
                    //this function does all buffer calculation in jobcard class under config directory
                    $OT = $extra->TimeDifference($temp_time);
                    //OT Generated From jobcard class
                    //finding total minutes
//                    $std_ot_minute_buffer = "00:30:00";
//
//                    //If actual ot is bigger than std minute
//                    //Generate it as OT
//                    //Otherwise assign zero
//                    if ($temp_time >= $std_ot_minute_buffer) {
//                        $OT = $temp_time;
//                    } else {
//                        $OT = "00:00:00";
//                    }
//                    $t = EXPLODE(":", $temp_time);
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
                    //$tem_time_diff = date("H:i:s", $OT);
                    $tem_time_diff =$OT;
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
                        
                        //$extra->OtDuductionBYHour($tem_time_diff);
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $extra->OtDuductionBYHour($tem_time_diff) . "</td>";
//                        $tifin_time = explode(":", $tem_time_diff);
//                        if ($tifin_time[0] >= 7) {
//                            $array_tem_time_diff = explode(":", $tem_time_diff);
//                            $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
//                            $one_minute = 60;
//                            // $final_minutes = $total_minutes - 60;
//                            $final_minutes = $total_minutes;
//                            $real_time = date('H:i', mktime(0, $final_minutes));
//                            $real_time_array = array($real_time, "00");
//                            $final = implode(":", $real_time_array);
//
//                            //Weekend standard OT
//                            $std_tem_time_diff = date("H:i:s", strtotime("08:00:00"));
//                            $final_value = date("H:i:s", strtotime("$final -1 hour"));
//
//                            if ($final > $std_tem_time_diff) {
//                                //Weekend standard OT
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $std_tem_time_diff . "</td>";
//                            } else {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
//                            }
//                        } else {
//                            if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
//                            } else {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
//                            }
//                        }
                    }
                }
            }
        } else {

            //status is not w or h, check user and place ot
            if ($user_type == "super_admin") {
                //........Calclulate Weekend OT...............
                if ($tmp_job_cardArray["$i"]["status"] == "W" || $tmp_job_cardArray["$i"]["status"] == "H") {
//                    if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
//                        $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
//                        $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
//                    } else {
//                        $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
//                        $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
//                    }
                    
                    $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                    $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));

                    $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
                    $temp_time = date("H:i:s", $tem_time_diff_wb);
                    //OT calculate to be in 15 minutes buffer
                    //Calculate OT in 15 minutes buffer
                    //this function does all buffer calculation in jobcard class under config directory
                    $OT = $extra->TimeDifference($temp_time);
                    //OT Generated From jobcard class
                    //finding total minutes
//                    $std_ot_minute_buffer = "00:30:00";
//
//                    //If actual ot is bigger than std minute
//                    //Generate it as OT
//                    //Otherwise assign zero
//                    if ($temp_time >= $std_ot_minute_buffer) {
//                        $OT = $temp_time;
//                    } else {
//                        $OT = "00:00:00";
//                    }

//                    $t = EXPLODE(":", $temp_time);
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
                    //$tem_time_diff = date("H:i:s", $OT);
                    $tem_time_diff =$OT;

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
                        
                        //$extra->OtDuductionBYHour($tem_time_diff);
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $extra->OtDuductionBYHour($tem_time_diff) . "</td>";
//                        $tifin_time = explode(":", $tem_time_diff);
//                        if ($tifin_time[0] >= 7) {
//                            $array_tem_time_diff = explode(":", $tem_time_diff);
//                            $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
//                            $one_minute = 60;
//                            // $final_minutes = $total_minutes - 60;
//                            $final_minutes = $total_minutes;
//                            $real_time = date('H:i', mktime(0, $final_minutes));
//                            $real_time_array = array($real_time, "00");
//                            $final = implode(":", $real_time_array);
//                            $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
//                        } else {
//                            if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
//                            } else {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
//                            }
//                        }
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

//                    if ($emp_staff_grade == 17) {
//                        $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
//                        $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
//                    } else {
//                        //if the employee is ot eligible
//                        if ($emp_staff_grade >= 16 && $emp_staff_grade <= 22) {
//                            $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
//                            $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));
//                        } else {
//                            $temp_in_time_g = date("H:i:s", strtotime("00:00:00"));
//                            $temp_out_time_g = date("H:i:s", strtotime("00:00:00"));
//                        }
//                    }
                    
                    $temp_in_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["in_time"]));
                    $temp_out_time_g = date("H:i:s", strtotime($tmp_job_cardArray["$i"]["out_time"]));

                    $tem_time_diff_wb = strtotime($temp_out_time_g) - strtotime($temp_in_time_g);
                    $temp_time = date("H:i:s", $tem_time_diff_wb);
                    //OT calculate to be in 15 minutes buffer
                    //Calculate OT in 15 minutes buffer
                    //finding total minutes

                    //this function does all buffer calculation in jobcard class under config directory
                    $OT = $extra->TimeDifference($temp_time);
                    //OT Generated From jobcard class
//                    $std_ot_minute_buffer = "00:30:00";
//
//                    //If actual ot is bigger than std minute
//                    //Generate it as OT
//                    //Otherwise assign zero
//                    if ($temp_time >= $std_ot_minute_buffer) {
//                        $OT = $temp_time;
//                    } else {
//                        $OT = "00:00:00";
//                    }

//                    $t = EXPLODE(":", $temp_time);
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
                    //$tem_time_diff = date("H:i:s", $OT);
                    $tem_time_diff =$OT;
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
                        
                        //$extra->OtDuductionBYHour($tem_time_diff)
                        $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $extra->OtDuductionBYHour($tem_time_diff) . "</td>";
//                        $tifin_time = explode(":", $tem_time_diff);
//                        if ($tifin_time[0] >= 7) {
//                            $array_tem_time_diff = explode(":", $tem_time_diff);
//                            $total_minutes = ($array_tem_time_diff[0] * 60) + $array_tem_time_diff[1] + ($array_tem_time_diff[2] / 60);
//                            $one_minute = 60;
//
//                            $final_minutes = $total_minutes - 60;
//
//                            $real_time = date('H:i', mktime(0, $final_minutes));
//                            $real_time_array = array($real_time, "00");
//                            $final = implode(":", $real_time_array);
//
//                            //Weekend standard OT
//                            $std_tem_time_diff = date("H:i:s", strtotime("08:00:00"));
//                            $final_value = date("H:i:s", strtotime("$final -1 hour"));
//
//                            if ($final > $std_tem_time_diff) {
//                                //Weekend standard OT
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $std_tem_time_diff . "</td>";
//                            } else {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $final . "</td>";
//                            }
//                        } else {
//                            if ($temp_in_time_g == "00:00:00" AND $temp_out_time_g == "00:00:00") {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">00:00:00</td>";
//                            } else {
//                                $html .= "<td align=\"center\"  style=\"border-width:1px; border-style:solid;\">" . $tem_time_diff . "</td>";
//                            }
//                        }
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

    $mpdf = new mPDF('c', 'A4', '', '', 32, 25, 27, 25, 16, 13);
    $mpdf->SetDisplayMode('fullpage');
    $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
    //$stylesheet = file_get_contents('../../../resource/css/bootstrap.css');
    $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
    $mpdf->WriteHTML($html, 2);
    $mpdf->Output('mpdf.pdf', 'I');
}

    