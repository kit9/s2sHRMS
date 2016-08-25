<?php

session_start();
/*
 * Page: Leave application reject
 * Author: Rajan Hossain
 * Date: 28-01-2015
 */

include '../../config/class.config.php';
include '../../config/class.mail.php';

$con = new Config();
$mail = new MailSettings();
$open = $con->open();
date_default_timezone_set('UTC');
header("Content-type tion/json");
$verb = $_SERVER["REQUEST_METHOD"];
/**
 * Collect Leave application master ID from the sender grid
 * Collect Applicant Employee Code from the sender grid
 * Collect step ID
 * Three values will select a unique leave application from 
 * approval_workflow_status table
 * Then update the status for specfic row against three identifier
 */
$status = "rejected";

//Review note from reviewer
$review_remark = '';

$configuration_info = array();
$configuration_info = $con->SelectAll("configuration_meta");

if (count($configuration_info) > 0) {
    //Find approval rule
    $approval_type = $configuration_info{0}->leave_approval_type;
    $ends_at_first_rejection = $configuration_info{0}->la_ends_at_rejection;
}

if (isset($_POST["aws_id"])) {
    $aws_id = $_POST["aws_id"];
}

if (isset($_POST["review_remark"])){
	$review_remark = $_POST["review_remark"];
}


/*
 * Seperate entire code block
 * For ends at first rejection
 * If ends_at_first_rejection is enabled 
 */

/*
 * if ends_at_first_rejection id disabled
 */
if ($ends_at_first_rejection == 0) {
    $leave_status_update_array = array(
        "aws_id" => $aws_id,
        "aws_status" => $status
    );

    if ($con->update("approval_workflow_status", $leave_status_update_array) == 1) {

        $status = $con->SelectAllByCondition("approval_workflow_status", "aws_id='$aws_id'");
        $aws_emp_code = $status{0}->aws_emp_code;
        $lam_id = $status{0}->leave_application_master_id;
        $aws_step = $status{0}->aws_id;

        //Identify max step no from all steps for that leave master ID
        $max_step_query = "SELECT MAX(aws_step) as max_step FROM approval_workflow_status WHERE leave_application_master_id = '$lam_id'";
        $max_steps = $con->QueryResult($max_step_query);
        $max_step = $max_steps{0}->max_step;

        $next_step_query = $con->QueryResult("SELECT * FROM approval_workflow_status WHERE leave_application_master_id = '$lam_id' AND aws_step > $aws_step LIMIT 0,1");
        if (count($next_step_query) > 0) {
            $aws_next_id = $next_step_query{0}->aws_id;
            $update_next_step_array = array(
                "aws_id" => $aws_next_id,
                "is_reviewed" => 'yes'
            );
            $output = $con->update("approval_workflow_status", $update_next_step_array);
            //On succesfull update trigger an email to next reviewer
            if ($output == 1) {
                //Find email ID for next reviewer
                $next_email = '';
                $next_emp_info = $con->update("tmp_employee", "emp_code='$next_aws_sup_emp_code'");
                if (count($next_emp_info) > 0) {
                    if (isset($next_emp_info{0}->emp_email_office)) {
                        $next_email = $next_emp_info{0}->emp_email_office;
                        $replyto = '';

                        $emp_name = $applicants{0}->emp_firstname;
                        //Find leave details
                        $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$lam_id'");
                        $start_date = $leave_master{0}->start_date;
                        $end_date = $leave_master{0}->end_date;

                        $subject = "Your Leave Application is Rejected!";
                        $message = "Dear " . $emp_name . ", <br /><br />";
                        if ($start_date == $end_date) {
                            $message .= "Your leave application for " . $start_date . " ";
                            $message .= "is rejected.";
                        } else {
                            $message .= "Your leave application from " . $start_date . " to " . $end_date;
                            $message .= " is rejected.";
                        }
                        $message .= "<br /><br />";
                        $message .= "Regards, <br />";
                        $message .= "Shore to Shore Human Resource.";
                        
                        //Trigger mail function
                        if ($statusChecker == TRUE && $mailto != '') {
                            $mail->SendMail($mailto, $subject, $message, $emp_name);
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

            $leave_details_query = "SELECT AWS.leave_application_master_id, LAD.leave_type_id, LAD.details_no_of_days
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
                    if ($totalLeaveDay > 0 AND $leaveTypeID > 0) {
                        /*
                         * Update leave application master ID
                         */
                        $lam_array = array(
                            "leave_application_master_id" => $lam_id,
                            "status" => "rejected",
			    "review_remark" => $review_remark
                        );
                        $con->update("leave_application_master", $lam_array);
                        /*
                         * Update leave application details table
                         */
                        $lad_update_query = "UPDATE leave_application_details SET status='rejected' WHERE leave_application_master_id='$lam_id'";
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
                $mailto = $applicants{0}->emp_email_office;
                if (isset($mailto)) {

                    $replyto = '';
                    $emp_name = $applicants{0}->emp_firstname;

                    //Find leave details
                    $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$lam_id'");
                    $start_date = $leave_master{0}->start_date;
                    $end_date = $leave_master{0}->end_date;

                    $subject = "Your Leave Application is Rejected!";
                    $message = "Dear " . $emp_name . ", <br /><br />";
                    if ($start_date == $end_date) {
                        $message .= "Your leave application for " . $start_date . " ";
                        $message .= "is rejected.";
                    } else {
                        $message .= "Your leave application from " . $start_date . " to " . $end_date;
                        $message .= " is rejected.";
                    }

                    $message .= "<br /><br />";
                    $message .= "Regards, <br />";
                    $message .= "Shore to Shore Human Resource";

                    //Trigger mail function
                    if ($statusChecker == TRUE && $mailto != '') {
                        $mail->SendMail($mailto, $subject, $message, $emp_name);
                    }
                }
            }
        }
        //Generate success message
        $return_array = array(
            "error_msg" => "You have rejected this application succesfully.",
            "success_flag" => "yes"
        );
        echo "{\"data\":" . json_encode($return_array) . "}";
    } else {
        //if update fails initially
        $return_array = array("error_msg" => "Application rejection failed.");
        echo "{\"data\":" . json_encode($return_array) . "}";
    }
} else {
    /*
     * If ends_at_first_rejection is enabled
     */
    $leave_status_update_array = array(
        "aws_id" => $aws_id,
        "aws_status" => $status
    );



    if ($con->update("approval_workflow_status", $leave_status_update_array) == 1) {

        $status = $con->SelectAllByCondition("approval_workflow_status", "aws_id='$aws_id'");
        $aws_emp_code = $status{0}->aws_emp_code;
        $lam_id = $status{0}->leave_application_master_id;
        $aws_step = $status{0}->aws_step;

        //Identify max step no from all steps for that leave master ID
        $max_step_query = "SELECT MAX(aws_step) as max_step FROM approval_workflow_status WHERE leave_application_master_id = '$lam_id'";
        $max_steps = $con->QueryResult($max_step_query);
        $max_step = $max_steps{0}->max_step;


        //Supervisor's step no.
        $step_no_query = "SELECT aws_step FROM approval_workflow_status WHERE aws_id = '$aws_id'";
        $step_no_obj = $con->QueryResult($step_no_query);
        $step_no = $step_no_obj{0}->aws_step;

        $arrayLeaveDetails = array();
        $totalLeaveDay = 0;
        $leaveTypeID = 0;

        $leave_details_query = "SELECT AWS.leave_application_master_id, LAD.leave_type_id, LAD.details_no_of_days
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
                if ($totalLeaveDay > 0 AND $leaveTypeID > 0) {
                    /*
                     * Update leave application master ID
                     */
                    $lam_array = array(
                        "leave_application_master_id" => $lam_id,
                        "status" => "rejected",
			"review_remark" => $review_remark
                    );
                    $con->update("leave_application_master", $lam_array);
                    /*
                     * Update leave application details table
                     */
                    $lad_update_query = "UPDATE leave_application_details SET status='rejected' WHERE leave_application_master_id='$lam_id'";
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
            $mailto = $applicants{0}->emp_email_office;
            if (isset($mailto)) {

                $replyto = '';
                $emp_name = $applicants{0}->emp_firstname;

                //Find leave details 
                $leave_master = $con->SelectAllByCondition("leave_application_master", "leave_application_master_id='$lam_id'");
                $start_date = $leave_master{0}->start_date;
                $end_date = $leave_master{0}->end_date;

                $subject = "Your Leave Application is Rejected!";
                $message = "Dear " . $emp_name . ", <br /><br />";
                if ($start_date == $end_date) {
                    $message .= "Your leave application for " . $start_date . " ";
                    $message .= "is rejected.";
                } else {
                    $message .= "Your leave application from " . $start_date . " to " . $end_date;
                    $message .= " is rejected.";
                }
                $message .= "<br /><br />";
                $message .= "Regards, <br />";
                $message .= "Shore to Shore Human Resource";

                //Trigger mail function
                if ($statusChecker == TRUE && $mailto != '') {
                    $mail->SendMail($mailto, $subject, $message, $emp_name);
                }
            }
        }

        //Generate success message
        $return_array = array(
            "error_msg" => "You have rejected this application succesfully.",
            "success_flag" => "yes"
        );
        echo "{\"data\":" . json_encode($return_array) . "}";
    } else {
        //if update fails initially
        $return_array = array("error_msg" => "Application rejection failed.");
        echo "{\"data\":" . json_encode($return_array) . "}";
    }
}

    
