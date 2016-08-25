<?php
include '../../config/class.config.php';
$con = new Config();
date_default_timezone_set('UTC');
$emp_code = "";
$job_card_id = "";
$status = "";
$in_time = "";
$out_time = "";
extract($_POST);

$temp_emp_code = $_POST["emp_code"];
$temp_date = $_POST["date"];

$replacement_status = '';
$replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$temp_date' AND rw_emp_code='$temp_emp_code'");
if (count($replacement_weekend) > 0) {
    $replacement_weekend_id = $replacement_weekend{0}->replacement_weekend_id;
    $delete_array = array(
        "replacement_weekend_id" => $replacement_weekend_id
    );
    $con->delete("replacement_weekend", $delete_array);
}



if ($_POST["job_card_id"] == "0") {
    $temp_emp_code = $_POST["emp_code"];
    $temp_date = $_POST["date"];

    /*
     * Format applying date
     */
    $applying_date = date("Y-m-d", strtotime($temp_date));

    //Find employee's current company ID
    $current_company_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$temp_emp_code'");
    $joining_date = $current_company_info{0}->emp_dateofjoin;
    $company_id = $current_company_info{0}->company_id;

    //Find job_duration in month
    $date1 = date_create($applying_date);
    $date2 = date_create($joining_date);
    $diff = date_diff($date1, $date2);
    $job_duration = $diff->m + ($diff->y * 12);

    /*
     * Now compare with availability settings
     */
    $balance_flag = 1;
    if ($status == "SL") {
        $res = $con->SelectAllByCondition("leave_policy", "short_code='SL'");
        $leave_type_id = $res{0}->leave_policy_id;

        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "CL") {
        $res = $con->SelectAllByCondition("leave_policy", " short_code='CL'");
        $leave_type_id = $res{0}->leave_policy_id;
        //Update leave applicaltion//
        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "EL") {
        $res = $con->SelectAllByCondition("leave_policy", " short_code='EL'");
        $leave_type_id = $res{0}->leave_policy_id;
        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "LL") {
        //For leave type 'Leave in Leu' shortcode- ll.
        $res = $con->SelectAllByCondition("leave_policy", " short_code='LL'");
        $leave_type_id = $res{0}->leave_policy_id;
        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "LWP") {
        //For leave type 'Lose of Payment' shortcode- lop.
        $res = $con->SelectAllByCondition("leave_policy", " short_code='LWP'");
        $leave_type_id = $res{0}->leave_policy_id;
        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "T") {
        //For leave type 'Tour' shortcode- T.
        $res = $con->SelectAllByCondition("leave_policy", " short_code='T'");
        $leave_type_id = $res{0}->leave_policy_id;
        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "LL") {
        //Leave Type 'Leave in Leu' shortcode-ll
        $res = $con->SelectAllByCondition("leave_policy", " short_code='LL'");
        $leave_type_id = $res{0}->leave_policy_id;
        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "T") {
        //Leave Type 'Tour' shortcode-T
        $res = $con->SelectAllByCondition("leave_policy", " short_code='T'");
        $leave_type_id = $res{0}->leave_policy_id;

        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "ML") {
        //Leave Type 'Tour' shortcode-T
        $res = $con->SelectAllByCondition("leave_policy", " short_code='ML'");
        $leave_type_id = $res{0}->leave_policy_id;

        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    } else if ($status == "PL") {
        //Leave Type 'Tour' shortcode-T
        $res = $con->SelectAllByCondition("leave_policy", " short_code='PL'");
        $leave_type_id = $res{0}->leave_policy_id;

        $total_days = $res{0}->total_days;
        $available_after_months = $res{0}->available_after_months;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        /*
         * Find employment duration until applying date
         * Look for availability
         */
        if ($rx != 1) {
            if ($available_after_months > $job_duration) {
                /*
                 * An error message would be send to job card view page
                 */
            } else {
                /*
                 * Check for existing data 
                 * If exist ::
                 * Update leave status meta
                 * If not exist :: 
                 * Insert leave status meta
                 */
                $meta_info = array();
                $meta_info = $con->SelectAllByCondition("leave_status_meta", "emp_code='$temp_emp_code' AND leave_type_id = '$leave_type_id'");

                if (count($meta_info) > 0) {
                    /*
                     * update availed, remanining
                     */
                    $meta_info_id = $meta_info{0}->leave_status_meta_id;
                    $total_days = $meta_info{0}->total_days;
                    $availed_days = $meta_info{0}->availed_days;
                    $remaining_days = $meta_info{0}->remaining_days;

                    $total_availed_days = $availed_days + 1;
                    $total_remaining_days = $remaining_days - 1;

                    /*
                     * generate a balance flag
                     * Based on total remaining days
                     */
                    if ($total_remaining_days < 0) {
                        $balance_flag = 0;
                    }
                    if ($balance_flag == 1) {
                        $update_array = array(
                            "leave_status_meta_id" => $meta_info_id,
                            "availed_days" => $total_availed_days,
                            "remaining_days" => $total_remaining_days
                        );
                        $con->update("leave_status_meta", $update_array);
                    }
                } else {
                    //Insert array 
                    $insert_array = array(
                        "emp_code" => $temp_emp_code,
                        "leave_status_meta_id" => $meta_info_id,
                        "availed_days" => $total_availed_days,
                        "remaining_days" => $total_days - 1,
                        "total_days" => $total_days,
                        "availed_days" => 1,
                        "company_id" => $company_id
                    );
                    $con->insert("leave_status_meta", $insert_array);
                }

                //if there is no balance, no leave would be updated
                if ($balance_flag == 1) {

                    if ($rx == 1) {
                        
                    } else {
                        $app_array_master = array(
                            "application_date" => $_POST["date"],
                            "emp_code" => $_POST["emp_code"],
                            "start_date" => $_POST["date"],
                            "end_date" => $_POST["date"],
                            "no_of_days" => 1,
                            "status" => "approved"
                        );
                        $last_id = $con->insert_with_last_id("leave_application_master", $app_array_master);
                        if ($last_id > 0) {
                            $app_array_details = array(
                                "leave_application_master_id" => $last_id,
                                "emp_code" => $_POST["emp_code"],
                                "details_date" => $_POST["date"],
                                "details_no_of_days" => 1,
                                "leave_type_id" => $leave_type_id,
                                "status" => "approved"
                            );
                            if ($con->insert("leave_application_details", $app_array_details) == 1) {
                                $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
                            }
                        }
                    }
                }
            }
        }
    }
}


