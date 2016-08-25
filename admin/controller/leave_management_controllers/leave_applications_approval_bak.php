<?php

session_start();
/*
 * Page: Leave application approval
 * Author: Rajan Hossain
 * Date: 28-01-2015
 */
$leave_master = array();
$start_date = '';
$end_date = '';
$subject = "";
$message = "";
$company_array = array();
$company_id = '';

include '../../config/class.config.php';
include '../../config/class.mail.php';

$con = new Config();
$open = $con->open();
$mail = new MailSettings();
date_default_timezone_set('UTC');
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Collect configuration meta information
$configuration_info = array();
$configuration_info = $con->SelectAll("configuration_meta");
if (count($configuration_info) > 0) {
    //Find approval rule
    $approval_type = $configuration_info{0}->leave_approval_type;
    $ends_at_first_rejection = $configuration_info{0}->la_ends_at_rejection;
}

/**
 * Collect Leave application master ID from the sender grid
 * Collect Applicant Employee Code from the sender grid
 * Collect step ID
 * Three values will select a unique leave application from 
 * approval_workflow_status table
 * Then update the status for specfic row against three identifier
 */
if (isset($_POST["aws_id"])) {
    $aws_id = $_POST["aws_id"];
    $status = "approved";
}

$leave_status_update_array = array(
    "aws_id" => $aws_id,
    "aws_status" => $status
);


if ($con->update("approval_workflow_status", $leave_status_update_array) == 1) {

    $status = $con->SelectAllByCondition("approval_workflow_status", "aws_id='$aws_id'");
    $aws_emp_code = $status{0}->aws_emp_code;
    $lam_id = $status{0}->leave_application_master_id;
    $aws_step = $status{0}->aws_step;

    //Collect company ID from leave application
    $company_array = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$lam_id'");
    if (isset($company_array{0}->company_id)) {
        $company_id = $company_array{0}->company_id;
    }

    //Identify max step no from all steps for that leave master ID
    $max_step_query = "SELECT MAX(aws_step) as max_step FROM approval_workflow_status WHERE leave_application_master_id = '$lam_id'";
    $max_steps = $con->QueryResult($max_step_query);
    $max_step = $max_steps{0}->max_step;

    if ($approval_type == 'individual') {
        /*
         * If configuration meta is set to individual review procedure
         */
        $next_step_query = $con->QueryResult("SELECT * FROM approval_workflow_status WHERE leave_application_master_id = '$lam_id' AND aws_step > $aws_step LIMIT 0,1");
        if (count($next_step_query) > 0) {
            $aws_next_id = $next_step_query{0}->aws_id;
            $next_aws_sup_emp_code = $next_step_query{0}->aws_sup_emp_code;

            $update_next_step_array = array(
                "aws_id" => $aws_next_id,
                "is_reviewed" => 'yes'
            );
            $output = $con->update("approval_workflow_status", $update_next_step_array);
            if ($output == 1) {
                //Find email ID for next reviewer
                $next_email = '';
                $next_emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$next_aws_sup_emp_code'");
                if (count($next_emp_info) > 0) {
                    if (isset($next_emp_info{0}->emp_email_office)) {
                        $next_email = $next_emp_info{0}->emp_email_office;
                        $replyto = '';
                        $emp_name_next = $next_emp_info{0}->emp_firstname;

                        //Find application information
                        $applicant_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$aws_emp_code'");
                        if (count($applicant_info) > 0){
                            $aws_emp_name = $applicant_info{0}->emp_firstname;
                        }
                        
                        //Find leave details
                        $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$lam_id'");
                        $start_date = $leave_master{0}->start_date;
                        $end_date = $leave_master{0}->end_date;

                        $subject = "A Leave Application Needs to be Reviewed.";
                        $message = "Applicant: " . $aws_emp_name . " (" . $aws_emp_code . ") <br /><br />";
                        if ($start_date == $end_date) {
                            $message .= "This leave application for " . $start_date . " ";
                            $message .= "was approved by previous reviewer.";
                        } else {
                            $message .= "This leave application for " . $start_date . " to " . $end_date;
                            $message .= "was approved by previous reviewer.";
                        }
                        
                        $message .= "<br /><br />";
                        $message .= "Regards, <br />";
                        $message .= "Shore to Shore Human Resource.";

                        //Trigger mail function
                        if ($next_email != '') {
                             $mail->SendMail($next_email, $subject, $message, $emp_name_next);
                        }
                    }
                }
            }
        }
    }

    

    //Supervisor's step no.
    $step_no_query = "SELECT aws_step FROM approval_workflow_status WHERE aws_id = '$aws_id'";
    $step_no_obj = $con->QueryResult($step_no_query);
    $step_no = $step_no_obj{0}->aws_step;

    if ($max_step == $step_no) {

        $arrayLeaveDetails = array();
        $totalLeaveDay = 0;
        $leaveTypeID = 0;

        $leave_details_query = "SELECT AWS.leave_application_master_id, LAD.leave_type_id, LAD.details_no_of_days, LAD.details_date 
        FROM `approval_workflow_status` AS AWS
        LEFT JOIN leave_application_details AS LAD ON LAD.leave_application_master_id = AWS.leave_application_master_id
        WHERE AWS.leave_application_master_id=$lam_id
        GROUP BY LAD.leave_application_master_id, LAD.leave_type_id";

        $resultLeaveDetails = mysqli_query($con->open(), $leave_details_query);
        if ($resultLeaveDetails) {
            while ($resultLeaveDetailsObj = mysqli_fetch_object($resultLeaveDetails)) {
                $arrayLeaveDetails[] = $resultLeaveDetailsObj;
            }
        }
        if (count($arrayLeaveDetails)) {
            $statusChecker = TRUE;
            foreach ($arrayLeaveDetails AS $LeaveDetails) {
                $totalLeaveDay = $LeaveDetails->details_no_of_days;
                $leaveTypeID = $LeaveDetails->leave_type_id;
                $leave_date = $LeaveDetails->details_date;
                $leave_year = date("Y", strtotime($leave_date));

                if ($totalLeaveDay > 0 AND $leaveTypeID > 0) {
                    $status_exist = $con->SelectAllByCondition("leave_status_meta", "leave_type_id = $leaveTypeID AND emp_code = '$aws_emp_code'");

                    if (count($status_exist) > 0) {
                        $sqlUpdateLeaveStatus = "UPDATE `leave_status_meta` SET
                    availed_days = (availed_days + $totalLeaveDay),
                    remaining_days = (remaining_days - $totalLeaveDay), year = $leave_year, company_id = $company_id
                    WHERE leave_type_id = $leaveTypeID "
                                . "AND emp_code = '$aws_emp_code'";


                        $resultUpdateLeaveStatus = mysqli_query($con->open(), $sqlUpdateLeaveStatus);
                        if (!$resultUpdateLeaveStatus) {
                            $statusChecker = FALSE;
                        }
                    } else {
                        //if first application and there is no data in the leave status meta table
                        $leave_policies = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$leaveTypeID'");
                        $total_days = $leave_policies{0}->total_days;
                        $remaining_days = $total_days - $totalLeaveDay;
                        $status_array = array(
                            "availed_days" => $totalLeaveDay,
                            "remaining_days" => $remaining_days,
                            "total_days" => $total_days,
                            "emp_code" => $aws_emp_code,
                            "leave_type_id" => $leaveTypeID,
                            "year" => $leave_year,
                            "company_id" => $company_id
                        );
                        $con->insert("leave_status_meta", $status_array);
                    }
                    /*
                     * Update leave application master ID
                     */
                    $lam_array = array(
                        "leave_application_master_id" => $lam_id,
                        "status" => "approved"
                    );
                    $con->update("leave_application_master", $lam_array);

                    /*
                     * Update leave application details table
                     */

                    $lad_update_query = "UPDATE leave_application_details SET status='approved' WHERE leave_application_master_id='$lam_id'";
                    $lad_update_query_execute = mysqli_query($open, $lad_update_query);
                    if (!$lad_update_query_execute) {
                        
                    }
                }
            }

            /*
             * Now that everything went well,
             * trigger an email to user
             * Collect data to build the email
             */

            $applicants = $con->SelectAllByCondition("tmp_employee", "emp_code='$aws_emp_code'");

            if (isset($applicants{0}->emp_email_office)) {
                $mailto = $applicants{0}->emp_email_office;
                
                $replyto = '';
                if ($mailto != '') {
                    $emp_name = $applicants{0}->emp_firstname;
                    //Find leave details
                    $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$lam_id'");
                    $start_date = $leave_master{0}->start_date;
                    $end_date = $leave_master{0}->end_date;

                    $subject = "Your Leave Application is Approved!";
                    $message = "Dear " . $emp_name . ", <br /><br />";
                    if ($start_date == $end_date) {
                        $message .= "Your leave application for " . $start_date . " ";
                        $message .= "is approved.";
                    } else {
                        $message .= "Your leave application from " . $start_date . " to " . $end_date;
                        $message .= " is approved.";
                    }
                    
                    $message .= "<br /><br />";
                    $message .= "Regards, <br />";
                    $message .= "Shore to Shore Human Resource.";
                    if ($statusChecker == TRUE) {
                       $mail->SendMail($mailto, $subject, $message, $emp_name);
                    }
                }
            }

            //Generate success message
            $return_array = array(
                "error_msg" => "You have approved this application succesfully.",
                "success_flag" => "yes"
            );
        }
    }
    //Generate success message
    $return_array = array(
        "error_msg" => "You have approved this application succesfully.",
        "success_flag" => "yes"
    );
    echo "{\"data\":" . json_encode($return_array) . "}";
} else {
    //if update fails initially
    $return_array = array("error_msg" => "Application approval failed.");
    echo "{\"data\":" . json_encode($return_array) . "}";
}

                        