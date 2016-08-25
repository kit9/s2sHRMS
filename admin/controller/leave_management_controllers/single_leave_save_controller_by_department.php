<?php

session_start();
include '../../config/class.config.php';
include '../../config/class.mail.php';

$con = new Config();
$mail = new MailSettings();

date_default_timezone_set('UTC');

$return_array = array();
$master_delete_array = array();
$master_details_array = array();
$is_half = '';
$day_part = '';
$is_HOD_fetch = array();
$is_HOD = '';
$HOD_supervisor_id = '';
$HOD_sup_emp_code_fetch = array();
$HOD_sup_emp_code = '';
$workflow_steps = array();
$leaves = array();
$is_applicable_for_all = '';
$is_leave_cut_applicable = '';
$is_pro_rate_base = '';
$is_carried_forward = '';
$is_wh_included = '';
$available_after_months = '';
$replacement_date = '';
$reason = '';
$approval_type = '';
$mail_limit = '';

header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];
extract($_POST);

/**
 * Fetch configuration data
 */
$configuration_info = array();
$configuration_info = $con->SelectAll("configuration_meta");

if (count($configuration_info) > 0) {
    //Find approval rule
    $approval_type = $configuration_info{0}->leave_approval_type;

    if ($approval_type == 'individual') {
        $mail_limit = 1;
    } else {
        $mail_limit = '';
    }
}


/* check for existing aplication in the same date
 * If exists then, give warning.
 */

if (isset($_SESSION["company_id"])) {
    $company_id = $_SESSION["company_id"];
}


/*
 * find details for leave type id
 * find details for emp code
 * find join date
 * Compare rules
 * Proceed or return with error message
 */

if ($leave_type_id != '') {
    $leaves = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$leave_type_id'");
    if (count($leaves) > 0) {
        $is_applicable_for_all = $leaves{0}->is_applicable_for_all;
        $is_leave_cut_applicable = $leaves{0}->is_leave_cut_applicable;
        $is_pro_rate_base = $leaves{0}->is_pro_rate_base;
        $is_carried_forward = $leaves{0}->is_carried_forward;
        $is_wh_included = $leaves{0}->is_wh_included;
        $available_after_months = $leaves{0}->available_after_months;
    }
}

if ($emp_code != '') {
    $employee_info_array = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    $joining_date = $employee_info_array{0}->emp_dateofjoin;
}

if ($start_date == '') {
    $return_array = array("error_msg" => "Please select start date");
} else if ($end_date == '') {
    $return_array = array("error_msg" => "Please select end date");
} else if ($leave_type_id == 0) {
    $return_array = array("error_msg" => "Please select a leave type");
} else if ($total_days <= 0) {
    $return_array = array("error_msg" => "Number of total days was not calculated properly. Check your selected date range carefully.");
} else {

    /**
     * For ease of modificatio
     */
    //Format today
    $today = date("Y/m/d");
    $sys_date = date_create($today);
    $formatted_today = date_format($sys_date, 'Y-m-d');

    //Format start date
    $startDate = date("Y-m-d", strtotime($start_date));

    //Format end date
    $endDate = date("Y-m-d", strtotime($end_date));

    //Format replacement date
    if ($replacement_date != '') {
        $replacement_date = date("Y-m-d", strtotime($replacement_date));
    }

    //Find department ID of this employee against emp_code
    $department = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    $department_id = $department{0}->emp_department;

    $query = "SELECT * from leave_application_master where (start_date BETWEEN '$startDate' AND '$endDate' OR end_date BETWEEN '$startDate' AND '$endDate') AND emp_code='$emp_code'";
    $applications = $con->QueryResult($query);

    $date1 = date_create($formatted_today);
    $date2 = date_create($joining_date);
    $diff = date_diff($date1, $date2);
    $job_duration = $diff->m + ($diff->y * 12);

    //Decide if this leave type is available
    if ($available_after_months != '' || $available_after_months != 0) {
        if ($job_duration < $available_after_months) {
            $availability_flag = 0;
        } else {
            $availability_flag = 1;
        }
    } else {
        $availability_flag = 1;
    }

    if ($availability_flag == 0) {
        $return_array = array("error_msg" => "You are not authorized to avail this leave type till you complete <b>" . $available_after_months . " months.</b>");
    } else if ($startDate > $endDate) {
        $return_array = array("error_msg" => "Invalid date range selection. Start date can not be larger than end date.");
    } else if (count($applications) > 0) {
        $return_array = array("error_msg" => "Date range you selected conflicts with another leave application you submitted. Please check leave history.");
    } else if ($is_half == 'yes' && $startDate != $endDate) {
        $return_array = array("error_msg" => "You have selected half day leave. Selected start date and end date can not be different. For a half day leave, total day should be maximum one.");
    } else {
        $app_array = array(
            "company_id" => $company_id,
            "emp_code" => $emp_code,
            "start_date" => $startDate,
            "end_date" => $endDate,
            "no_of_days" => $total_days,
            "status" => "pending",
            "application_date" => $formatted_today,
            "reasons" => $reason
        );
        $last_id = $con->insert_with_last_id("leave_application_master", $app_array);
        $dates = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date between '$startDate' AND '$endDate'");

        foreach ($dates as $date) {
            $frm_start_date = date_create($date->date);
            $startDate = date_format($frm_start_date, 'Y-m-d');

            if ($last_id != 0) {
                /*
                 * Business logic for weekend/holiday inclusion
                 * if weekend inclusion is false, then no weekend
                 * should be stored in details table
                 * The specific date in the loop with such specific date type
                 * will be included. 
                 */

                $dates = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date='$startDate'");
                if (count($dates) > 0) {
                    $day_type_id = $dates{0}->day_type_id;
                }
                if ($is_wh_included == "false") {
                    if ($day_type_id != 2 && $day_type_id != 3 && $day_type_id != 4) {
                        $details_array = array(
                            "leave_application_master_id" => $last_id,
                            "leave_type_id" => $leave_type_id,
                            "details_date" => $startDate,
                            "details_no_of_days" => $total_days,
                            "status" => "pending",
                            "is_half" => $is_half,
                            "day_part" => $day_part,
                            "emp_code" => $emp_code,
                            "replacement_date" => $replacement_date
                        );
                        $con->insert("leave_application_details", $details_array);
                    }
                } else {
                    $details_array = array(
                        "leave_application_master_id" => $last_id,
                        "leave_type_id" => $leave_type_id,
                        "details_date" => $startDate,
                        "details_no_of_days" => $total_days,
                        "status" => "pending",
                        "is_half" => $is_half,
                        "day_part" => $day_part,
                        "emp_code" => $emp_code
                    );
                    $con->insert("leave_application_details", $details_array);
                }
            }
        }

        /*
         * Populate leave_workflow status table
         * Leave application master ID is saved to track the application
         * Find total number of approval steps from workflow settings table. 
         * A loop will create status for each step. 
         * For all the steps, relative department ID, empcode and approval status would be saved
         * Department ID, Leave Application master ID, emp_code will be unique identifier for each of the steps. 
         */

        /*
         * Business Logic
         * Check if the employee is HOD
         * If HOD, then fetch super visor ID from tmp table.
         * Save this supervisor code as aws_sup_emp_code
         */

        //Format today
        $today = date("Y/m/d");
        $sys_date = date_create($today);
        $formatted_today = date_format($sys_date, 'Y-m-d');

        //Collect HOD info
        $is_HOD_fetch = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code' LIMIT 0,1");
        if (count($is_HOD_fetch) > 0) {
            $is_HOD = $is_HOD_fetch{0}->is_HOD;
            $HOD_supervisor_id = $is_HOD_fetch{0}->supervisor_id;
        }

        /*
         * I super-visor ID check is off, then  
         */
        
        /**
         * Possible change log: 
         * HOD condition section is to be used when leave application 
         * is configred to be without any dynamic approval chain in place 
         */
        
        $is_HOD = '';
        if ($is_HOD == 'yes') {
            if ($HOD_supervisor_id != '') {
                $HOD_sup_emp_code_fetch = $con->SelectAllByCondition("tmp_employee", "emp_id = '$HOD_supervisor_id'");
                $HOD_sup_emp_code = $HOD_sup_emp_code_fetch{0}->emp_code;

                //Generate the array
                $status_insert_array_HOD = array(
                    'aws_department_id' => $department_id,
                    'aws_emp_code' => $emp_code,
                    'aws_step' => 1,
                    'aws_sup_emp_code' => $HOD_sup_emp_code,
                    'aws_status' => "pending",
                    'leave_application_master_id' => $last_id,
                    "created_at" => $formatted_today
                );

                //Insert the array and generate success message
                if ($con->insert("approval_workflow_status", $status_insert_array_HOD) == 1) {

                    //Find applicants
                    $hod_supervisor = $con->SelectAllByCondition("tmp_employee", "emp_code='$HOD_sup_emp_code'");
                    $hod_emp_name = $hod_supervisor{0}->emp_firstname;

                    if (isset($hod_supervisor{0}->emp_email_office)) {
                        /**
                         * Only the first person will get an email
                         */
                        $mailto = $hod_supervisor{0}->emp_email_office;

                        //Find leave details
                        $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$last_id'");
                        $ap_start_date = $leave_master{0}->start_date;
                        $ap_end_date = $leave_master{0}->end_date;

                        $replyto = '';
                        $subject = "A Leave Application Awaiting Your Review!";
                        $message = "Dear " . $hod_emp_name . ", <br /><br />";
                        if ($ap_start_date == $ap_end_date) {
                            $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                            $message .= " has submitted a leave application for " . $ap_start_date . " ";
                            $message .= "which is waiting for your review.";
                        } else {
                            $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                            $message .= " has submitted a leave application from " . $ap_start_date . " to " . $ap_end_date;
                            $message .= " which is waiting for review.";
                        }

                        $message .= "<br /><br />";
                        $message .= "Shore to Shore Human Resource";

                        if ($mailto != '') {
                            $mail->SendMail($mailto, $subject, $message, $hod_emp_name);
                        }
                    }
                    $return_array = array(
                        "error_msg" => "A leave request is submitted. Once reviewed, you should recieve a confirmation email.",
                        "success_flag" => "yes"
                    );
                } else {
                    /*
                     * On failure, delete the master data
                     * On failure delete the details data
                     */
                    $master_delete_array = array("leave_application_master_id" => $last_id);
                    if ($con->delete("leave_application_master", $master_delete_array) == 1) {
                        $details_delete_array = array("leave_application_master_id" => $last_id);
                        if ($con->delete("leave_application_details", $details_delete_array) == 1) {
                            $return_array = array(
                                "error_msg" => "Leave request failed. Something went wrong!"
                            );
                        }
                    }
                }
            } else {
                /*
                 * On failure, delete the master data
                 * On failure delete the details data
                 */
                $master_delete_array = array("leave_application_master_id" => $last_id);
                if ($con->delete("leave_application_master", $master_delete_array) == 1) {
                    $details_delete_array = array("leave_application_master_id" => $last_id);
                    if ($con->delete("leave_application_details", $details_delete_array) == 1) {
                        $return_array = array(
                            "error_msg" => "Leave request failed. Probably a supervisor is not yet assigned to your department. Please contact your supervisor for further assistance."
                        );
                    }
                }
            }
        } else {
            $mail_flag = 1;
            $workflow_steps = $con->SelectAllByCondition("approval_workflow_settings", "department_id='$department_id'");

            if (count($workflow_steps) > 0) {
                foreach ($workflow_steps as $steps) {

                    //Generate local variables
                    $step_no = $steps->step;
                    $sup_emp_code = $steps->emp_code;

                    /**
                     * Check if this supervisor is the applicant himself
                     * If equal, then fetch his supervisor
                     * Save supervisor from tmp as reviewer of this application
                     * Forward email to this supervisor as well
                     */
                    if ($emp_code == $sup_emp_code) {
                        $HOD_sup_emp_code_fetch = $con->SelectAllByCondition("tmp_employee", "emp_id = '$HOD_supervisor_id'");
                        $HOD_sup_emp_code = $HOD_sup_emp_code_fetch{0}->emp_code;
                        $sup_emp_code = $HOD_sup_emp_code;
                    }

                    //Generate the array
                    $status_insert_array = array(
                        'aws_department_id' => $department_id,
                        'aws_emp_code' => $emp_code,
                        'aws_step' => $step_no,
                        'aws_sup_emp_code' => $sup_emp_code,
                        'aws_status' => "pending",
                        'leave_application_master_id' => $last_id,
                        "created_at" => $formatted_today
                    );

                    //Insert the array and generate success message
                    if ($con->insert("approval_workflow_status", $status_insert_array) == 1) {
                        //Find the supervisor
                        $supervisers = $con->SelectAllByCondition("tmp_employee", "emp_code='$sup_emp_code'");
                        $sup_name = $supervisers{0}->emp_firstname;

                        if (isset($supervisers{0}->emp_email_office)) {
                            if ($mail_limit == 1 && $mail_flag == 1) {

                                /**
                                 * Only the first person will get an email
                                 */
                                $mailto = $supervisers{0}->emp_email_office;
                                if ($mailto != '') {

                                    //Find applicants
                                    $applicants = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
                                    $emp_name = $applicants{0}->emp_firstname;

                                    //Find leave details
                                    $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$last_id'");
                                    $ap_start_date = $leave_master{0}->start_date;
                                    $ap_end_date = $leave_master{0}->end_date;
                                    $replyto = '';
                                    $subject = "A Leave Application Awaiting Your Review!";
                                    $message = "Dear " . $sup_name . ", <br /><br />";
                                    if ($ap_start_date == $ap_end_date) {
                                        $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                                        $message .= " has submitted a leave application for " . $ap_start_date . " ";
                                        $message .= "which is waiting for your review.";
                                    } else {
                                        $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                                        $message .= " has submitted a leave application from " . $ap_start_date . " to " . $ap_end_date;
                                        $message .= " which is waiting for review.";
                                    }

                                    $message .= "<br /><br />";
                                    $message .= "Shore to Shore Human Resource";

                                    if ($mailto != '') {
                                        $mail->SendMail($mailto, $subject, $message, $sup_name);
                                        $mail_flag = 0;
                                    }
                                }
                            } else if ($mail_limit != 1 AND $mail_flag != 0) {
                                /**
                                 * Every one will get an email
                                 */
                                $mailto = $supervisers{0}->emp_email;
                                if ($mailto != '') {

                                    //Find applicants
                                    $applicants = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
                                    $emp_name = $applicants{0}->emp_firstname;

                                    //Find leave details
                                    $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$last_id'");
                                    $ap_start_date = $leave_master{0}->start_date;
                                    $ap_end_date = $leave_master{0}->end_date;
                                    $replyto = '';
                                    $subject = "A Leave Application Awaiting Your Review!";
                                    $message = "Dear " . $sup_name . ", <br /><br />";
                                    if ($ap_start_date == $ap_end_date) {
                                        $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                                        $message .= " has submitted a leave application for " . $ap_start_date . " ";
                                        $message .= "which is waiting for your review.";
                                    } else {
                                        $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                                        $message .= " has submitted a leave application from " . $ap_start_date . " to " . $ap_end_date;
                                        $message .= " which is waiting for review.";
                                    }

                                    $message .= "<br /><br />";
                                    $message .= "Shore to Shore Human Resource";

                                    if ($mailto != '') {
                                        $con->sent_mail_without_attatchment($mailto, $replyto, $subject, $message);
                                        $mail_flag = 0;
                                    }
                                }
                            }
                        }


                        $return_array = array(
                            "error_msg" => "A leave request is submitted. Once reviewed, you should recieve a confirmation email.",
                            "success_flag" => "yes"
                        );
                    } else {

                        /*
                         * On failure, delete the master data
                         * On failure delete the details data
                         */
                        $master_delete_array = array("leave_application_master_id" => $last_id);
                        if ($con->delete("leave_application_master", $master_delete_array) == 1) {
                            $details_delete_array = array("leave_application_master_id" => $last_id);
                            if ($con->delete("leave_application_details", $details_delete_array) == 1) {
                                $return_array = array(
                                    "error_msg" => "Leave request failed. Something went wrong!"
                                );
                            }
                        }
                    }
                }
            } else {
                /*
                 * On failure, delete the master data
                 * On failure delete the details data
                 */
                $master_delete_array = array("leave_application_master_id" => $last_id);
                if ($con->delete("leave_application_master", $master_delete_array) == 1) {
                    $details_delete_array = array("leave_application_master_id" => $last_id);
                    if ($con->delete("leave_application_details", $details_delete_array) == 1) {
                        $return_array = array(
                            "error_msg" => "Leave request failed. Probably a supervisor is not yet assigned to your department. Please contact your supervisor for further assistance."
                        );
                    }
                }
            }
            //End of approval workflow status
        }
    }
}

$error_array = array("error_msg" => "Something went wrong!");
if (count($return_array) > 0) {
    echo "{\"data\":" . json_encode($return_array) . "}";
} else {
    echo "{\"data\":" . json_encode($error_array) . "}";
}
