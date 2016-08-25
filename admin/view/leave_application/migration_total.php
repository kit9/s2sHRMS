<?php
//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

/*
 * Loop against employee
 * For each employee, run another loop
 * Find joining date of each employee
 * Only if the joining date is bigger than Januray 1st
 * Find total possible working days upto December 31st. 
 */

/*
 * Nested loop: inside employee loop
 * Loop through all leave types
 * Find total working days.
 * Formula to be applied - (available days of a leave type/365)*total working days
 * Pro rate basis is inserted into database
 */

/*
 * If the joining date is before this year, then all employees should be able to 
 * have all days avaialable in this year. Default, based on the available after settings,
 * validation will be checked in time of application.
 * Normally, employee will see all available days for him on pro rate basis. 
 */

/*
 * After all this has been done succesfully,
 * then carried forward values for employees from the previous year 
 * will be fecthed and updated for each leave type and for each employee 
 */

/*
 * I will also need to develop a machanism for updating leave status for all employee
 * for each year. This machanism should be consistent and should be working with exquisite accuracy. 
 */

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

/*
 * Fetch all employees
 */

$employees = array();
$leave_types = array();

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$today_array = explode("-", $formatted_today);
$today_year = $today_array[0];

$build_first_date = $today_year . "-01-01";
$build_last_date = $today_year . "-12-31";
$first_day = date("Y-m-d", strtotime($build_first_date));
$last_day = date("Y-m-d", strtotime($build_last_date));


$employees = $con->SelectAll("tmp_employee");
if (count($employees) > 0) {
    foreach ($employees as $tmp) {
        
        $emp_code = $tmp->emp_code;
        $joining_date_raw = $tmp->emp_dateofjoin;
        $joining_date = date("Y-m-d", strtotime($joining_date_raw));

        if ($joining_date > $first_day) {
            $datetime1 = date_create($joining_date);
            $datetime2 = date_create($build_last_date);
            $interval = date_diff($datetime1, $datetime2);
            $total_days_working = $interval->format('%R%a days');

            //now fetch all leave type
            $leave_types = $con->SelectAll("leave_policy");
            if (count($leave_types) > 0) {
                foreach ($leave_types as $leave_all) {
                    $total_days = $leave_all->total_days;
                    $is_pro_rate_base = $leave_all->is_pro_rate_base;
                    $leave_type_id = $leave_all->leave_policy_id;

                    if ($is_pro_rate_base == "true") {
                        //Calculate pro rate based total days
                        $pro_rate_based_total_days = ceil(($total_days / 365) * $total_days_working);
                        //Update status table with pro-rate
                        if ($pro_rate_based_total_days != '') {
                            $existing_status_of_leave_type = $con->SelectAllByCondition("leave_status_meta", "emp_code='$emp_code' AND leave_type_id='$leave_type_id' AND year = '$today_year'");
                            if (count($existing_status_of_leave_type) > 0) {
                                $leave_status_meta_id = $existing_status_of_leave_type{0}->leave_status_meta_id;
                                $status_update_array = array(
                                    "leave_status_meta_id" => $leave_status_meta_id,
                                    "total_days" => $pro_rate_based_total_days,
                                    "remaining_days" => $pro_rate_based_total_days
                                );
                                if ($con->update("leave_status_meta", $status_update_array) == 1) {
                                    //echo "Update pro rate successfully done!";
                                } else {
                                    //echo "Updating pro rate failed for some reasons.";
                                }
                            } else {
                                $status_insert_array = array(
                                    "emp_code" => $emp_code,
                                    "leave_type_id" => $leave_type_id,
                                    "year" => $today_year,
                                    "total_days" => $pro_rate_based_total_days,
                                    "remaining_days" => $pro_rate_based_total_days
                                );
                                if ($con->insert("leave_status_meta", $status_insert_array) == 1) {
                                   // echo "New status data is successfully saved.";
                                } else {
                                   // echo "New status data failed to be saved.";
                                }
                            }
                        }
                    } else {
                        //update status table with total days (without pro rate)
                        $existing_status_of_leave_type = $con->SelectAllByCondition("leave_status_meta", "emp_code='$emp_code' AND leave_type_id='$leave_type_id' AND year = '$today_year'");
                        if (count($existing_status_of_leave_type) > 0) {
                            $leave_status_meta_id = $existing_status_of_leave_type{0}->leave_status_meta_id;
                            $status_update_array = array(
                                "leave_status_meta_id" => $leave_status_meta_id,
                                "total_days" => $total_days,
                                "remaining_days" => $total_days
                            );
                            if ($con->update("leave_status_meta", $status_update_array) == 1) {
                                //echo "Update pro rate successfully done!";
                            } else {
                                //echo "Updating pro rate failed for some reasons.";
                            }
                        } else {
                            $status_insert_array = array(
                                "emp_code" => $emp_code,
                                "leave_type_id" => $leave_type_id,
                                "year" => $today_year,
                                "total_days" => $total_days,
                                "remaining_days" => $total_days
                            );
                            if ($con->insert("leave_status_meta", $status_insert_array) == 1) {
                               //echo "New status data is successfully saved.";
                            } else {
                                //echo "New status data failed to be saved.";
                            }
                        }
                    }
                }
            }
        } else {
            //now fetch all leave type
            $leave_types = $con->SelectAll("leave_policy");
            if (count($leave_types) > 0) {
                foreach ($leave_types as $leave_all) {
                    $total_days = $leave_all->total_days;
                    $leave_type_id = $leave_all->leave_policy_id;
                    //update status table with total days (without pro rate)
                    $existing_status_of_leave_type = $con->SelectAllByCondition("leave_status_meta", "emp_code='$emp_code' AND leave_type_id='$leave_type_id' AND year = '$today_year'");
                    if (count($existing_status_of_leave_type) > 0) {
                        $leave_status_meta_id = $existing_status_of_leave_type{0}->leave_status_meta_id;
                        $status_update_array = array(
                            "leave_status_meta_id" => $leave_status_meta_id,
                            "total_days" => $total_days,
                            "remaining_days" => $total_days 
                        );
                        if ($con->update("leave_status_meta", $status_update_array) == 1) {
                            //echo "Update pro rate successfully done!";
                        } else {
                            //echo "Updating pro rate failed for some reasons.";
                        }
                    } else {
                        $status_insert_array = array(
                            "emp_code" => $emp_code,
                            "leave_type_id" => $leave_type_id,
                            "year" => $today_year,
                            "total_days" => $total_days,
                            "remaining_days" => $total_days
                        );
                        if ($con->insert("leave_status_meta", $status_insert_array) == 1) {
                            //echo "New status data is successfully saved.";
                        } else {
                            //echo "New status data failed to be saved.";
                        }
                    }
                }
            }
        }
    }
}
//$con->redirect("migration_availed.php?permission_id=" . $permission_id);





    