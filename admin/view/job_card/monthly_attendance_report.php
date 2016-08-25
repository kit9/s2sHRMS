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

//if ($con->hasPermissionView($permission_id) != "yes") {
//    $con->redirect("../dashboard/index.php");
//}

$deficit_hours = '';
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

        /*
         * Collect all leave type
         * CL, SL, EL will be included
         */
//        $leave_types = array();
//        $leave_types = $con->SelectAllByCondition("leave_policy", "short_code IN ('CL', 'SL', 'EL')");
//        if (count($leave_types) > 0) {
//            foreach ($leave_types as $leave) {
//                if (isset($leave->leave_title)) {
//                    array_push($sub_header_array, $leave->leave_title);
//                } else {
//                    array_push($sub_header_array, "");
//                }
//            }
//        }

        array_push($sub_header_array, "CL", "SL", "EL", "OL", "Date of Leave", "Date of Absent", "Date of Late", "Total Late", "Extra Hours", "Deficit Hours", "Remarks");

        //Generate main array
        $detailed_query = "SELECT
        e.emp_code,
        e.emp_dateofjoin,
        e.emp_firstname,
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
            //echo "<hr />";
            //Full Name
            if (isset($data->emp_firstname)) {
                $full_name = $data->emp_firstname;
            } else {
                $full_name = " ";
            }

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


            //Subsection
//          if (isset($data->subsection_title)) {
//                $subsection_title = $data->subsection_title;
//          } else {
            //                $subsection_title = " ";
            //}
            //Date of join
            if (isset($data->emp_dateofjoin)) {
                $date_of_join = date("d-m-Y", strtotime($data->emp_dateofjoin));
            } else {
                $date_of_join = " ";
            }

            //Staff grade
            //            if (isset($data->staffgrade_title)) {
            //                $staff_grade = $data->staffgrade_title;
            //            } else {
            //                $staff_grade = " ";
            //            }

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
            $late_count = 0;
            $absent_date_collection = "";
            $late_date_collection = "";
            $leave_date_collection = "";

            if (count($all_dates) > 0) {
                foreach ($all_dates as $date) {
                    $date_in_hand = date("Y-m-d", strtotime($date->date));
                    $day_type_id = $date->day_type_id;



                    /*
                     * Now fetch job card details for this date
                     * Since we are looking for absent data
                     * Only weekdays absent ont he job card table will be counted
                     */

                    $job_card_info = array();
                    $job_card_info = $con->SelectAllByCondition("job_card", "emp_code= '$emp_code' AND date = '$date_in_hand'");

                    $leave_info = array();
                    $leave_info = $con->SelectAllByCondition("leave_application_details", "emp_code='$emp_code' AND details_date='$date_in_hand'");

                    if ($day_type_id == 1 && count($job_card_info) <= 0 && count($leave_info) <= 0) {
                        $absent_count += 1;
                        $absent_date_collection .= date("d", strtotime($date_in_hand)) . ",";
                    } else {
                        
                    }
                    
               
                    
                    //Calculate number of days in leave approved
                    if (count($leave_info) > 0) {
                        
                        $leave_count += 1;
                        $leave_date_collection .= date("d", strtotime($date_in_hand)) . ",";

                        //Collect leave type
                        $leave_type_id = $leave_info{0}->leave_type_id;

                        //Collect short code
                        $leave_type_info = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$leave_type_id'");
                        $short_code = $leave_type_info{0}->short_code;

                        //Collect CL total
                        if ($short_code == "CL") {
                            $CL_Count += 1;
                        }

                        //Collect SL total
                        if ($short_code == "SL") {
                            $SL_Count += 1;
                        }

                        //Collect Al total
                        if ($short_code == "EL") {
                            $EL_Count += 1;
                        }

                        /*
                         * Collect other leave total
                         * Sum them up into OL (Other Leave)
                         */

                        if ($short_code == "LWP") {
                            $OL += 1;
                        }
                        if ($short_code == "ML") {
                            $OL += 1;
                        }
                        if ($short_code == "PL") {
                            $OL += 1;
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

                    //echo $in_time_wh;
                    //echo "<br />";

                    if (isset($job_card_info{0}->out_time)) {
                        $out_time_wh = $job_card_info{0}->out_time;
                    } else {
                        $out_time_wh = "00:00:00";
                    }

                    // echo $out_time_wh;
                    //echo "<br />";

                    if ($out_time_wh != "00:00:00" && $in_time_wh != "00:00:00") {
                        //Find working hours 
                        if ($in_time_wh < $out_time_wh) {
                            $working_hours = strtotime($out_time_wh) - strtotime($in_time_wh);
                        } else if ($in_time_wh > $out_time_wh) {
                            $working_hours = strtotime($in_time_wh) - strtotime($out_time_wh);
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

                    //  echo $working_hours_formatted_ori;
                    //  echo "<br />";
                    /*
                     * Now substract tiffin hours
                     * if more than 6 hours
                     * 1 hours would be substracted
                     */

//                    $tiffin_eligible_hours = date("H:i:s", strtotime("06:00:00"));
//                    if ($working_hours_formatted_ori != '00:00:00' && $working_hours_formatted_ori >= $tiffin_eligible_hours) {
//                        $working_hours_formatted = date("H:i:s", strtotime("$working_hours_formatted_ori -1 hour"));
//                    } else {
//                        $working_hours_formatted = '00:00:00';
//                    }

                     $working_hours_formatted = $working_hours_formatted_ori;
            

                    // echo $working_hours_formatted;
                    // echo "<hr/>";
                    //Break down time string into hours, minutes and seconds
                    $x_time_array = explode(":", $working_hours_formatted);
                    $xhours +=$x_time_array[0];
                    $xMinutes +=$x_time_array[1];
                    $xSecond += $x_time_array[2];
                }

                //exit();
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
             * Multiply total absent days with 8 hours
             * Substract results from 
             */

             $total_working_hours_raw = $total_working_days * 9;


            if ($absent_count > 0) {
                $absent_hour_mitigation = $absent_count * 9;
                $total_working_hours_without_absent = $total_working_hours_raw - $absent_hour_mitigation;
                $total_working_hours_raw = $total_working_hours_without_absent;
            }


            /*
             * Now mitigate absent days from working hour check
             * Multiply total leave days with 8 hours
             * Substract results from 
             */
            
             
            if ($leave_count > 0) {
                $leave_hour_mitigation = $leave_count * 9;
                $total_working_hours_without_leave = $xhours + $leave_hour_mitigation;
                $xhours =  $total_working_hours_without_leave;
                
            }

            echo $total_working_hours_raw;
           echo "<hr/>";
           echo $xhours;

            

           exit();
          
            if ($xhours > $total_working_hours_raw) {



                // echo "<hr>total work hours $xhours";
                //  echo "target hours $total_working_hours_raw<hr>";

                $extra_hours_raw = $xhours - $total_working_hours_raw;
                //Add minute and second to it
                $extra_hours = $extra_hours_raw . ":" . $xMinutes . ":" . $xSecond;
            } else if ($xhours < $total_working_hours_raw) {
            

              
                $deficit_hours_raw = $total_working_hours_raw - $xhours;
                //Add minute and second to it
                if ($xMinutes > 0) {
                    $current_minute = 60 - $xMinutes;
                    $current_hours = $deficit_hours_raw - 1;

                    if ($xSecond > 0) {
                        $current_second = 60 - $xSecond;
                        $current_minute = $current_minute - 1;
                    }
                }

                if ($xMinutes <= 0 && $xSecond > 0) {
                    $current_second = 60 - $xSecond;
                    $current_minute = $current_minute - 1;
                    $current_hours = $deficit_hours_raw;
                }

                if ($xMinutes <= 0 && $xSecond <= 0) {
                    $current_hours = $deficit_hours_raw;
                    $current_minute = "00";
                    $current_second = "00";
                }

                $deficit_hours = $current_hours . ":" . $current_minute . ":" . $current_second;
            }

            // echo "extra<br />";
            //   echo $extra_hours;
            //  echo "<hr>";
            //  echo $deficit_hours;
            //Now check leave days from working hour check
            //
            //Remove last comma
            $absent_formatted = trim($absent_date_collection, ",");
            $late_formatted = trim($late_date_collection, ",");
            $leave_formatted = trim($leave_date_collection, ",");

            //Now push these variables in into master array
            array_push($data_array, $emp_code, $full_name, $department_title, $designation, $date_of_join, $absent_count, $leave_count, $CL_Count, $SL_Count, $EL_Count, $OE_count, $leave_formatted, $absent_formatted, $late_formatted, $late_count, $extra_hours, $deficit_hours, $remarks);
            array_push($master_array, $data_array);
        }

        //exit();
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


