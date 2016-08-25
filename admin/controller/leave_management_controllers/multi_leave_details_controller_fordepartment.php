<?php

session_start();
include '../../config/class.config.php';
include '../../config/class.mail.php';
$con = new Config();
$mail = new MailSettings();

//define request type and return type
header("Content-type: application/json");
$emp_code = '';
$master_start_date = '';
$master_end_date = '';
$total_days = '';
$company_id = '';
$temps = array();
$replacement_date_temp = '';

$replacement_date = '';
$leaves = array();

$is_applicable_for_all = '';
$is_leave_cut_applicable = '';
$is_pro_rate_base = '';
$is_carried_forward = '';
$is_wh_included = '';
$available_after_months = '';
$reason = '';
$mail_limit = '';
$approval_type = '';

extract($_POST);

$startDate = date("Y-m-d", strtotime($master_start_date));
$endDate = date("Y-m-d", strtotime($master_end_date));

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

//Fetch company ID from session
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
$formatted_today = date("Y-m-d");
if ($emp_code != '') {
    $employee_info_array = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    $joining_date = $employee_info_array{0}->emp_dateofjoin;
}

//Find job duration in months
$date1 = date_create($formatted_today);
$date2 = date_create($joining_date);
$diff = date_diff($date1, $date2);
$job_duration = $diff->m + ($diff->y * 12);

if ($master_start_date == '') {
    $return_array = array("error_msg" => "Please select start date.");
} else if ($master_end_date == '') {
    $return_array = array("error_msg" => "Please select end date.");
} else if ($startDate > $endDate) {
    $return_array = array("error_msg" => "Invalid primary date range. Start date can not be larger than end date.");
} else {
    $query = "SELECT * from leave_application_master where (start_date BETWEEN '$startDate' AND '$endDate' OR end_date BETWEEN '$startDate' AND '$endDate') AND emp_code='$emp_code'";
    $applications = $con->QueryResult($query);

    //If data   exists in this date range
    if (count($applications) > 0) {
        $return_array = array("error_msg" => "Date range you selected conflicts with another leave application you submitted. Please check leave history.");
    } else {
        $statusCheck = TRUE;
        $temps = $con->SelectAllByCondition("leave_application_temp", "emp_code='$emp_code'");
        if (count($temps) > 0) {
            $total_days_temp = 0;
            foreach ($temps as $temp) {
                $start_date_temp = $temp->start_date_temp;
                $end_date_temp = $temp->end_date_temp;
                $total_days_temp += $temp->total_days_temp;
                $temp_leave_type_id = $temp->leave_policy_id;

                //Dates for application
                $formatted_start_date_temp = date("Y-m-d", strtotime($start_date_temp));
                $formatted_end_date_temp = date("Y-m-d", strtotime($end_date_temp));


                /*
                 * Apply leave type's availability business logic here
                 */
                if ($temp_leave_type_id != '') {
                    $leaves = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$temp_leave_type_id'");
                    $is_applicable_for_all = $leaves{0}->is_applicable_for_all;
                    $is_leave_cut_applicable = $leaves{0}->is_leave_cut_applicable;
                    $is_pro_rate_base = $leaves{0}->is_pro_rate_base;
                    $is_carried_forward = $leaves{0}->is_carried_forward;
                    $is_wh_included = $leaves{0}->is_wh_included;
                    $available_after_months = $leaves{0}->available_after_months;
                }

                if ($total_days_temp < 1) {
                    $return_array = array("error_msg" => "Please select secondary leave type and date range to apply with multiple leave types.");
                    $statusCheck = FALSE;
                } else if ($formatted_start_date_temp < $startDate) {
                    $return_array = array("error_msg" => "Secondary leave date range is out of primary date range selection. Secondary start date is smaller than primary start date.");
                    $statusCheck = FALSE;
                } else if ($formatted_end_date_temp > $endDate) {
                    $return_array = array("error_msg" => "Secondary leave date range is out of primary date range selection. Secondary end date is larger than primary end date.");
                    $statusCheck = FALSE;
                } else if ($formatted_start_date_temp > $formatted_end_date_temp) {
                    $return_array = array("error_msg" => "Invalid secondary date range. Start date can not be larger than end date.");
                    $statusCheck = FALSE;
                } else if ($job_duration < $available_after_months) {
                    $return_array = array("error_msg" => "You are not authorized to avail this leave type till you complete <b>" . $available_after_months . " months.</b>");
                    $statusCheck = FALSE;
                }
            }

            if ($statusCheck) {
                if ($total_days != $total_days_temp) {
                    //if date range of the temp table and detailed date range is not same.
                    $return_array = array("error_msg" => "Primary date range and secondary date range doesn't match. See if you have selected secondary leave typs and dates properly. This is mendatory since you are trying to apply for multiple leave types.");
                } else {
                    //For no error, save the records and update the master and details table directly from here. 
                    //Format today
                    $formatted_today = date("Y-m-d");
                    $master_array = array(
                        "company_id" => $company_id,
                        "emp_code" => $emp_code,
                        "start_date" => $startDate,
                        "end_date" => $endDate,
                        "no_of_days" => $total_days,
                        "status" => "pending",
                        "application_date" => $formatted_today,
                        "reasons" => $reason,
                    );

                    $last_id = $con->insert_with_last_id("leave_application_master", $master_array);
                    //Collect data from temp table
                    foreach ($temps as $temp) {

                        $temp_leave_type_id = $temp->leave_policy_id;
                        $start_date_temp = $temp->start_date_temp;
                        $end_date_temp = $temp->end_date_temp;
                        $total_days_temp = $temp->total_days_temp;
                        $replacement_date_temp = $temp->replacement_date;

                        //format collected dates
                        $formatted_start_date_temp = date("Y-m-d", strtotime($start_date_temp));
                        $formatted_end_date_temp = date("Y-m-d", strtotime($end_date_temp));

                        if ($replacement_date_temp != "0000-00-00") {
                            $replacement_date = date("Y-m-d", strtotime($replacement_date_temp));
                        } else {
                            $replacement_date = '';
                        }


                        //Collect data from dates againt temp date start and temp date end
                        $temp_dates = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date between '$formatted_start_date_temp' AND '$formatted_end_date_temp'");
                        foreach ($temp_dates as $tdate) {
                            $date_one = $tdate->date;
                            $cdate = date_create($date_one);
                            $fdate = date_format($cdate, 'Y-m-d');

                            /*
                             * Business logic for weekend/holiday inclusion
                             * if weekend inclusion is false, then no weekend
                             * should be stored in details table
                             * The specific date in the loop with such specific date type
                             * will be included. 
                             */

                            $dates = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date='$fdate'");
                            if (count($dates) > 0) {
                                $day_type_id = $dates{0}->day_type_id;
                            }

                            if ($is_wh_included == "false") {
                                if ($day_type_id != 2 && $day_type_id != 3 && $day_type_id != 4) {
                                    $details_array = array(
                                        "leave_application_master_id" => $last_id,
                                        "leave_type_id" => $temp_leave_type_id,
                                        "details_date" => $fdate,
                                        "details_no_of_days" => $total_days_temp,
                                        "status" => "pending",
                                        "emp_code" => $emp_code,
                                        "replacement_date" => $replacement_date
                                    );
                                    if ($con->insert("leave_application_details", $details_array) == 1) {

                                        //Delete data from temp table
                                        $delete_array = array("emp_code" => $emp_code);
                                        $con->delete("leave_application_temp", $delete_array);

                                        //Everything went well, generate success message
                                        $return_array = array(
                                            "error_msg" => "Leave request is sucessfully submitted. Once reviewed, you will recieve an email with confirmation details.",
                                            "success_flag" => "yes"
                                        );
                                    } else {
                                        $return_array = array("error_msg" => "Something went wrong. Leave request is not submitted. Please contact with your supervisor.");
                                    }
                                }
                            } else {
                                $details_array = array(
                                    "leave_application_master_id" => $last_id,
                                    "leave_type_id" => $temp_leave_type_id,
                                    "details_date" => $fdate,
                                    "details_no_of_days" => $total_days_temp,
                                    "status" => "pending",
                                    "emp_code" => $emp_code,
                                    "replacement_date" => $replacement_date
                                );
                                if ($con->insert("leave_application_details", $details_array) == 1) {

                                    //Delete data from temp table
                                    $delete_array = array("emp_code" => $emp_code);
                                    $con->delete("leave_application_temp", $delete_array);

                                    //Everything went well, generate success message
                                    $return_array = array(
                                        "error_msg" => "Leave request is sucessfully submitted. Once reviewed, you will recieve an email with confirmation details.",
                                        "success_flag" => "yes"
                                    );
                                } else {
                                    $return_array = array("error_msg" => "Something went wrong. Leave request is not submitted. Please contact with your supervisor.");
                                }
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

                    //Find department ID of this employee against emp_code
                    $department = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
                    $department_id = $department{0}->emp_department;

                    //Start of approval workflow status
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
                                        "error_msg" => "Leave request failed. Probably a supervisor is not yet assigned to applicant's department. Please contact administrator for further assistance."
                                    );
                                }
                            }
                        }
                    } else {
                        $mail_flag = 1;
                        echo "working";
                        $workflow_steps = $con->SelectAllByCondition("approval_workflow_settings", "department_id='$department_id'");
                        if (count($workflow_steps) > 0) {
                            foreach ($workflow_steps as $steps) {

                                //Generate local variables
                                $step_no = $steps->step;
                                $sup_emp_code = $steps->emp_code;

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

                                        echo "working";
                                        echo $mail_limit;

                                        //if individual approval type is on
                                        if ($mail_limit == 1 && $mail_flag == 1) {
                                            $mailto = $supervisers{0}->emp_email_office;
                                            echo $mailto;

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

                                            $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                                            $message .= " has submitted a leave application from " . $ap_start_date . " to " . $ap_end_date;
                                            $message .= " which is waiting for review.";

                                            $message .= "<br /><br />";
                                            $message .= "Regards, <br />";
                                            $message .= "Shore to Shore Human Resource";

                                            if ($mailto != '') {
                                                $mail->SendMail($mailto, $subject, $message, $sup_name);
                                                $mail_flag = 0;
                                            }
                                        } else if ($mail_limit != 1 AND $mail_flag != 0) {
                                            //If individual approval type is turned off.
                                            $mailto = $supervisers{0}->emp_email_office;

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

                                            $message .= $emp_name . " (Employee Code- " . $emp_code . ")";
                                            $message .= " has submitted a leave application from " . $ap_start_date . " to " . $ap_end_date;
                                            $message .= " which is waiting for review.";

                                            $message .= "<br /><br />";
                                            $message .= "Regards, <br />";
                                            $message .= "Shore to Shore Human Resource";
                                            if ($mailto != '') {
                                                $mail->SendMail($mailto, $subject, $message, $sup_name);
                                            }
                                        }
                                        $return_array = array(
                                            "error_msg" => "A leave request is submitted. Once reviewed, you should recieve a confirmation email.",
                                            "success_flag" => "yes"
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
                        //End of approval workflow status
                    }
                }
            }
        } else {
            $return_array = array("error_msg" => "Please select secondary leave type and date range to apply with multiple leave types.");
        }
    }
}
$error_array = array("error_msg" => "Something went wrong!");
//Finally, build the JSON
if (count($return_array) > 0) {
    echo "{\"data\":" . json_encode($return_array) . "}";
} else {
    echo "{\"data\":" . json_encode($error_array) . "}";
}





