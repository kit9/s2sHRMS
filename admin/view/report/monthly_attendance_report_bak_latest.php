<?php
/* Author : Rajan
 * Date: 2nd Feb 15
 */
session_start();
//Importing class library
include('../../config/class.config.php');
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
date_default_timezone_set('UTC');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';

$is_ot_eligible = "";
$night_allowance_eligible = "";
$tiffin_allowance_eligible = "";

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if (isset($_POST["generate_report"])) {

    //Extract post array
    extract($_POST);

    //Set form validations
    if ($company_id == '') {
        $err = 'Please select a company.';
    } else if ($start_date == '') {
        $err = 'Please select a year.';
    } else if ($end_date == '') {
        $err = "Please select a year.";
    } else {

        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        /*
         * Generate sub-header array
         */
        $sub_header_array = array("Empployee Id", "Name", "Department", "Designation", "DOJ", "Total Absent", "Total Leave");
        $master_array = array();
        $data_array = array();


        array_push($sub_header_array, "CL", "SL", "EL", "OL", "Date of Leave", "Date of Absent", "Date of Late", "Total Late", "Extra Hours", "Deficit Hours", "Remarks");

        //Add tiffin and night allowance column for unit 2 or company 3 employees
        if ($company_id == 3) {
            array_push($sub_header_array, "Tiffin Allowance", "Night Allowance");
        }

        //Generate main array
        $detailed_query = "SELECT
        e.emp_id,
        e.emp_code,
        e.emp_dateofjoin,
        e.emp_firstname,
        e.tiffin_allowance_eligible,
        e.night_allowance_eligible,
        d.designation_title,
        dep.department_title,
        sg.staffgrade_title
        FROM
        tmp_employee as e
        LEFT JOIN
        designation d ON e.emp_designation = d.designation_id
        LEFT JOIN
        department dep ON dep.department_id = e.emp_department
        LEFT JOIN
        subsection AS sub ON sub.subsection_id = e.emp_subsection
        LEFT JOIN
        staffgrad as sg on sg.staffgrade_id = e.emp_staff_grade
        WHERE e.company_id = '$company_id'";

        if ($department_id > 0 || $department_id != '') {
            $detailed_query .= " AND emp_department = '$department_id'";
        }

        $detailed_query .= " ORDER BY dep.department_title";
        $query_result = $con->QueryResult($detailed_query);

        /*
         * Build all dates
         */
        $all_dates = $con->SelectAllByCondition("dates", "date BETWEEN '$start_date' AND '$end_date' AND company_id = '$company_id'");

        /*
         * Build only work days against selected company
         * Find total working hours in these days
         */

        $total_working_days = '';
        $all_dates_working = $con->SelectAllByCondition("dates", "date BETWEEN '$start_date' AND '$end_date' AND company_id = '$company_id' AND day_type_id = 1");
        if (count($all_dates_working) > 0) {
            $total_working_days = count($all_dates_working);
        }

        foreach ($query_result as $data) {

            $extra_hours = 0;
            $deficit_hours = 0;

            $xhours = 0;
            $xMinutes = 0;
            $xSecond = 0;

            $data_array = array();

            //Build it as an string
            $emp_code = "$data->emp_code";
            $emp_id = $data->emp_id;
            //echo "<hr />";
            //Full Name
            if (isset($data->emp_firstname)) {
                $full_name = $data->emp_firstname;
            } else {
                $full_name = " ";
            }


            $night_allowance_eligible = $data->night_allowance_eligible;
            $tiffin_allowance_eligible = $data->tiffin_allowance_eligible;

            //Department
            if (isset($data->department_title)) {
                $department_title = $data->department_title;
            } else {
                $department_title = " ";
            }

            //Designation
            if (isset($data->designation_title)) {
                $designation = $data->designation_title;
            } else {
                $designation = " ";
            }

            //Date of join
            if (isset($data->emp_dateofjoin)) {
                $date_of_join = date("d-m-Y", strtotime($data->emp_dateofjoin));
            } else {
                $date_of_join = " ";
            }


            /*
             * Calculate number of absent
             * Calculate number of leave
             * Between two dates selected
             */

            $absent_count = 0;
            $leave_count = 0;
            $CL_Count = 0;
            $SL_Count = 0;
            $EL_Count = 0;
            $OE_count = 0;
            $OL = 0;
            $late_count = 0;
            $absent_date_collection = "";
            $late_date_collection = "";
            $leave_date_collection = "";
            $tiffin_allowance = 0;
            $night_allowance = 0;
            $final_target_hours = 0;

            if (count($all_dates) > 0) {
                foreach ($all_dates as $date) {
                    $date_in_hand = date("Y-m-d", strtotime($date->date));
                    $day_type_id = $date->day_type_id;


                    /**
                     * Find day short code
                     */
                    $day_info = $con->SelectAllByCondition("day_type", "day_type_id = '$day_type_id'");
                    if (isset($day_info{0}->day_shortcode)) {
                        $day_shortcode = $day_info{0}->day_shortcode;
                    } else {
                        $day_shortcode = '';
                    }



                    /*
                     * Now fetch job card details for this date
                     * Since we are looking for absent data
                     * Only weekdays absent ont he job card table will be counted
                     */

                    $job_card_info = array();
                    $job_card_info = $con->SelectAllByCondition("job_card", "emp_code= '$emp_code' AND date = '$date_in_hand'");

                    $leave_info = array();
                    $leave_info = $con->SelectAllByCondition("leave_application_details", "emp_code='$emp_code' AND details_date='$date_in_hand'");


                    /**
                     * Look for alternate attendance policy
                     * Avoid alternate weekend before making absent data
                     */
                    $ne_company_id = '';
                    $ne_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$date_in_hand' AND implement_end_date >= '$date_in_hand' LIMIT 0,1");
                    if (count($ne_existing_awesome) > 0) {
                        $ne_company_id = $ne_existing_awesome{0}->alt_company_id;
                    } else {
                        $ne_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$date_in_hand' AND implement_end_date = '0000-00-00'");
                        if (count($ne_existing_awesome) > 0) {
                            $ne_company_id = $ne_existing_awesome{0}->alt_company_id;
                        }
                    }

                    /**
                     * For an alternat weekend policy, find weekend
                     */
                    if ($ne_company_id > 0) {
                        $weekend_info = $con->SelectAllByCondition("dates", "company_id='$ne_company_id' and date='$date_in_hand'");
                        if (count($weekend_info) > 0) {
                            $day_type_id = $weekend_info{0}->day_type_id;
                        }
                    }


                    if ($day_type_id == 1 && count($job_card_info) <= 0 && count($leave_info) <= 0) {
                        $absent_count += 1;
                        $absent_date_collection .= date("d", strtotime($date_in_hand)) . ",";
                    } else {

                    }



                    //Calculate number of days in leave approved
                    if (count($leave_info) > 0) {

                       
                        $leave_date_collection .= date("d", strtotime($date_in_hand)) . ",";

                        //Collect leave type
                        $leave_type_id = $leave_info{0}->leave_type_id;
                        if (isset($leave_info{0}->is_half)) {
                            $is_half = $leave_info{0}->is_half;
                        } else {
                            $is_half = '';
                        }

                        //If this leave is half day, add 0.5
                        if ($is_half == "yes"){
                            $leave_count += 0.5;
                        } else {
                         $leave_count += 1;
                        }
                        


                        //Collect short code
                        $leave_type_info = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$leave_type_id'");
                        $short_code = $leave_type_info{0}->short_code;

                        /**
                         * For a half day leave, add .5
                         */
                        //Collect CL total
                        if ($short_code == "CL") {
                            if ($is_half == 'yes') {
                                $CL_Count += 0.5;
                            } else {
                                $CL_Count += 1;
                            }
                        }

                        //Collect SL total
                        if ($short_code == "SL") {
                            if ($is_half == 'yes') {
                                $SL_Count += 0.5;
                            } else {
                                $SL_Count += 1;
                            }
                        }

                        //Collect Al total
                        if ($short_code == "EL") {
                            if ($is_half == 'yes') {
                                $EL_Count += 0.5;
                            } else {
                                $EL_Count += 1;
                            }
                        }

                        /*
                         * Collect other leave total
                         * Sum them up into OL (Other Leave)
                         */

                        if ($short_code == "LWP") {
                            if ($is_half == 'yes') {
                                $OL += 0.5;
                            } else {
                                $OL += 1;
                            }
                        }

                        if ($short_code == "ML") {
                            if ($is_half == 'yes') {
                                $OL += 0.5;
                            } else {
                                $OL += 1;
                            }
                        }

                        if ($short_code == "PL") {
                            if ($is_half == '') {
                                $OL += 0.5;
                            } else {
                                $OL += 1;
                            }
                        }
                    }

                    //Collect late info
                    if (isset($job_card_info{0}->is_late)) {
                        $is_late = $job_card_info{0}->is_late;
                    } else {
                        $is_late = 0;
                    }


                    if ($is_late == '1') {
                        $late_count += 1;
                        $late_date_collection .= date("d", strtotime($date_in_hand)) . ",";
                    }
                    /*
                     * Calculate working hours
                     * if working hours is more than or equal 6 hours
                     * then 1 hour will be substracted from total working hours as tiffin time
                     */

                    //check if in time is smaller than out time
                    if (isset($job_card_info{0}->in_time)) {
                        $in_time_wh = $job_card_info{0}->in_time;
                    } else {
                        $in_time_wh = "00:00:00";
                    }

                    if (isset($job_card_info{0}->out_time)) {
                        $out_time_wh = $job_card_info{0}->out_time;
                    } else {
                        $out_time_wh = "00:00:00";
                    }

                    if ($out_time_wh != "00:00:00" && $in_time_wh != "00:00:00") {
                        //Find working hours 
                        if ($in_time_wh < $out_time_wh) {
                            $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
                        } else if ($in_time_wh > $out_time_wh) {
                            $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
                        } else {
                            $working_hours = '';
                        }
                    } else {
                        $working_hours = '';
                    }

                    if ($working_hours != '') {
                        $working_hours_formatted_ori = date("H:i:s", $working_hours);
                    } else {
                        $working_hours_formatted_ori = "00:00:00";
                    }

                    /**
                     * [$tiffin_eligible_hours description]
                     * [$night_allowance_hours description]
                     * @var [string]
                     * Working hours in holiday/weekend will be totally truncated
                     */
                    if ($day_shortcode == "W" || $day_shortcode == "H") {
                        $tiffin_allowance += 0;
                        $night_allowance += 0;
                    } else {
                        $tiffin_eligible_hours = date("H:i:s", strtotime("06:00:00"));
                        if ($in_time_wh < $out_time_wh) {
                            if ($tiffin_allowance_eligible == 1) {
                                if ($working_hours_formatted_ori != '00:00:00' && $working_hours_formatted_ori >= $tiffin_eligible_hours) {
                                    $tiffin_allowance += 1;
                                }
                            } else {
                                $tiffin_allowance += 0;
                            }
                        } else if ($in_time_wh > $out_time_wh) {
                            if ($night_allowance_eligible == 1) {
                                if ($working_hours_formatted_ori != '00:00:00' && $working_hours_formatted_ori >= $tiffin_eligible_hours) {
                                    $night_allowance += 1;
                                }
                            } else {
                                $night_allowance += 0;
                            }
                        }
                    }

                    $working_hours_formatted = $working_hours_formatted_ori;

                    //Break down time string into hours, minutes and seconds
                    $x_time_array = explode(":", $working_hours_formatted);
                    $xhours +=$x_time_array[0];
                    $xMinutes +=$x_time_array[1];
                    $xSecond += $x_time_array[2];

                    /**
                     * Find shift_information
                     * Compare with emp id and date
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
                    AND esu.schedule_date = '$date_in_hand'
                    AND esu.shift_id = sp.shift_id";
                    
//                    echo "Employee code: ";
//                    echo $emp_code;
//                    echo "<br />";

                    //Query executor
                    $emp_shifts = $con->QueryResult($shifts);


                    //Find schedule information
                    if (count($emp_shifts) > 0) {
                        $raw_shift_start_time = $emp_shifts{0}->saturday_start_time;
                        $raw_shift_end_time = $emp_shifts{0}->saturday_end_time;
                    } else {
                        $raw_shift_start_time = "";
                        $raw_shift_end_time = "";
                    }


                    $shift_start_time = "";
                    $shift_end_time = "";
                    $target_working_hours_frmt = "";

                    if (count($emp_shifts) <= 0) {
                        $defaults = $con->SelectAllByCondition("shift_policy", "shift_type='default'");
                        $shift_start_time = $defaults{0}->saturday_start_time;
                        $shift_end_time = $defaults{0}->saturday_end_time;
                        $sat_end_day = 1;
                    } else {
                        $shift_start_time = date("H:i:s", strtotime($raw_shift_start_time));
                        $shift_end_time = date("H:i:s", strtotime($raw_shift_end_time));
                        $sat_end_day = $emp_shifts{0}->sat_end_day;
                    }

                    if ($shift_start_time < $shift_end_time) {
                        $target_working_hours_raw = strtotime($shift_end_time) - strtotime($shift_start_time);
                        $target_working_hours_frmt = date("H:i:s", $target_working_hours_raw);
                    } else if ($shift_end_time > $shift_start_time) {
                        $target_working_hours_raw = strtotime($shift_start_time) - strtotime($shift_end_time);
                        $target_working_hours_frmt = date("H:i:s", $target_working_hours_raw);
                    }
                    

                    $tw_hour_array = explode(":", $target_working_hours_frmt);
                    $tw_hours = $tw_hour_array[0];
                    
                    if (count($tw_hour_array) > 1){
                        $tw_minutes = $tw_hour_array[1];
                    } else {
                        $tw_minutes = 0;
                    }

                    if (count($tw_hour_array) > 2){
                        $tw_seconds = $tw_hour_array[2];
                    } else {
                        $tw_seconds = 0;
                    }
                    

                    /**
                     * Find hour value of minutes
                     */
                    if ($tw_minutes > 0) {
                        if ($tw_minutes == 15) {
                            $additional_hours = 15 / 60;
                        } else if ($tw_minutes == 30) {
                            $additional_hours = 30 / 60;
                        } else if ($tw_minutes == 45) {
                            $additional_hours = 45 / 60;
                        }
                    } else {
                        $additional_hours = 0;
                    }

                    /**
                     * Generate target working hour from broken
                     * Hour and minuted information
                     */

                    $final_hour_a_day = $tw_hours + $additional_hours;
                    /**
                     * Add if not absent, not leave
                     */
                    if ($day_type_id == 1 && count($job_card_info) <= 0 && count($leave_info) <= 0) {
                        //no hour is added for absent
                    } else if (count($leave_info) > 0) {
                        //no hours is added for leave
                    } else if($day_type_id != 1){
                        //For weekend/holiday, no hours is added
                    } else {
                        $final_target_hours += $final_hour_a_day;
                    }
                }
            }


            $remarks = "";

            /*
             * Total working hours
             */

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

            /*
             * Now mitigate absent days from working hour check
             * Multiply total absent days with 9 hours
             * Substract results from 
             */

            /**
             * Break Down Final Target Hours
             * Find additional minutes                     
             */
            $target_hour_array = explode(".", $final_target_hours);

            /**
             * Check length of this array
             * If just one element, $tar minutes empty
             */


            $tar_hours = $target_hour_array[0];
            if (count($target_hour_array) > 1) {
                $tar_minutes = $target_hour_array[1];
            } else {
                $tar_minutes = 0;
            }

            if ($tar_minutes == 25) {
                $actual_minute = 15;
            } else if ($tar_minutes == 5) {
                $actual_minute = 30;
            } else if ($tar_minutes == 75) {
                $actual_minute = 45;
            } else {
                $actual_minute = 0;
            }                       

            $total_working_hours_raw = $final_target_hours;
            $diff_tminute = 0;
            if ($xhours > $tar_hours) {
                $extra_hours_raw = $xhours - $tar_hours;
                //Add addtional minute from extracted additional minute
                if ($actual_minute > 0) {
                        //Target minute is bigger
                    if ($actual_minute > $xMinutes){
                        //Difference
                        $diff_tminute = $actual_minute - $xMinutes;  
                    } else if ($actual_minute < $xMinutes) {
                        $diff_tminute = $xMinutes - $actual_minute;
                    } else {
                        $diff_tminute = 0;
                    }
                } else {
					//If there is no additional minute in target working hours
					$diff_tminute =  $xMinutes; 
                }

               //Add minute and second to it
                $extra_hours = $extra_hours_raw . ":" . $diff_tminute . ":" . $xSecond;

            } else if ($xhours < $tar_hours) {

                $deficit_hours_raw = $total_working_hours_raw - $xhours;
                
                $deficit_hours_array = explode(".", $deficit_hours_raw);
                $def_hour = $deficit_hours_array[0];

                if (count($deficit_hours_array) > 1){
                    $def_minute = $deficit_hours_array[1];
                    if ($def_minute == 25){
                        $act_def_minute = 15;
                    } else if ($def_minute == 5){
                        $act_def_minute = 30;
                    } else if ($def_minute == 75){
                        $act_def_minute = 45;
                    }
                } else{
                    $act_def_minute = 0;
                }

                
                //Add minute and second to it
                if ($xMinutes > 0) {
                    $current_minute = 60 - $xMinutes;
                    $current_hours = $def_hour - 1;

                    if ($xSecond > 0) {
                        $current_second = 60 - $xSecond;
                        $current_minute = $current_minute - 1;
                    }
                }

                if ($xMinutes <= 0 && $xSecond > 0) {
                    $current_second = 60 - $xSecond;
                    $current_minute = $current_minute - 1;
                    $current_hours = $def_hour;
                }

                if ($xMinutes <= 0 && $xSecond <= 0) {
                    $current_hours = $def_hour;
                    $current_minute = "00";
                    $current_second = "00";
                }

                //Now remaining minutes from def hour to be calculated with 
                //current minute
                $current_minute_now = $current_minute + $act_def_minute;
                if ($current_minute > 60){
                    //Add 1 hour to current hour
                    $current_hour_final_now = $current_hours + 1;

                    //Find the different from 60
                    $diff_from_hour = $current_minute_now - 60;
                    $current_minute = $diff_from_hour;
                    $current_hours = $current_hour_final_now; 
                } else {
                    $current_minute = $current_minute_now; 
                }

                $deficit_hours = $current_hours . ":" . $current_minute . ":" . $current_second;

            }

            //Remove last comma
            $absent_formatted = trim($absent_date_collection, ",");
            $late_formatted = trim($late_date_collection, ",");
            $leave_formatted = trim($leave_date_collection, ",");

            //Now push these variables in into master array
            array_push($data_array, $emp_code, $full_name, $department_title, $designation, $date_of_join, $absent_count, $leave_count, $CL_Count, $SL_Count, $EL_Count, $OL, $leave_formatted, $absent_formatted, $late_formatted, $late_count, $extra_hours, $deficit_hours, $remarks);


            /*
             * Updated business logic:
             * Tiffin will be applicable to night shift as well. 
             * Tiffin will be counted on total present days, if working hours are more than 6 hours
             * Night allowance will be application only to night shift.
             * Formula $total_tiffin_allowance = $tiffin_allowance +  $night_allowance;
             * tiffin_allowance will be replaced by $total_tiffin_allowance in data array.
             */
            $total_tiffin_allowance = $tiffin_allowance + $night_allowance;


            //For unit 2 or company 3, tiffin and night allowance will be added
            if ($company_id == 3) {
                array_push($data_array, $total_tiffin_allowance, $night_allowance);
            }


            array_push($master_array, $data_array);
        }

        //Merge header array with the master array
        array_unshift($master_array, $sub_header_array);

        
        $count = count($master_array);
        $countCol = count($master_array[0]);

        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
        $rowCount = 0;

        $st_date = date("d-m-Y", strtotime($start_date));
        $en_date = date("d-m-Y", strtotime($end_date));

        //Collect company info
        $companies = $con->SelectAllByCondition("company", "company_id='$company_id'");
        $company_title = $companies{0}->company_title;
        for ($i = 1; $i <= $count; $i++) {
            for ($j = 0; $j <= $countCol - 1; $j++) {
                $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
                $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Report: $st_date TO $en_date");
                $cWorkSheet->setCellValueByColumnAndRow($j, $i + 4, $master_array["$rowCount"]["$j"]);
            }
            $rowCount++;
        }

        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
        $filename = $company_id . rand(0, 9999999) . "MonthlyAttendanceReport.xlsx";
        $objWriter->save("$filename");
        header("location:$filename");
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Monthly Attendance Report</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <form method="post">
            <div class="col-md-6">
                <label for="Full name">Company:</label><br/>
                <input type="text" id="company_id" name="company_id" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="col-md-6">
                <label for="Department">Department:</label><br/>
                <input type="text" id="department_id" name="department_id" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <label for="Start Date">Start Date:</label><br/>
                <input type="text" id="start_date" class="emp_datepicker" value="<?php echo $start_date; ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="col-md-6">
                <label for="Start Date">End Date:</label><br/>
                <input id="end_date" type="text" class="emp_datepicker"  value="<?php echo $end_date; ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <input type="submit" class="k-button" value="Generate Report" name="generate_report">
            </div>
            <div class="clearfix"></div>
            <br />
        </form>
    </div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
<script>
    $(document).ready(function () {
        $("#start_date").kendoDatePicker();
        $("#end_date").kendoDatePicker();
        jQuery("#company_id").kendoComboBox({
            placeholder: "Select company...",
            dataTextField: "company_title",
            dataValueField: "company_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/company_global.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");

        jQuery("#department_id").kendoComboBox({
            placeholder: "Select Department...",
            dataTextField: "department_title",
            dataValueField: "department_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/department.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");
    });
</script>

