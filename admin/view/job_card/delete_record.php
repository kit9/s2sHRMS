<?php

session_start();
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
error_reporting(0);

/*
 * Author: Rajan Hossain, Tariqule, Jobayer Rabbi
 * Page: Delete existing attendance record in the job card
 * Importing class library
 * Call main class 
 * Connection String
 */

include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();
date_default_timezone_set('UTC');

//Declaring local variables as empty
$company_id = '';
$alt_company_id = '';
$emp_staff_grade = '';
$emp_designation = '';
$emp_department = '';
$raw_shift_end_time = '';
$existing_company = array();
$alt_existing_awesome = array();
$late_buffer_minute = array();
$late = 0;
$rewrite_flag = '';
$delete_edited_data = '';
$edit_status = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Collect rewrite flag
if (isset($_GET["rewrite_flag"])) {
    $rewrite_flag = $_GET["rewrite_flag"];
}

//Collect delete manually edited data flag
if (isset($_GET["delete_edited_data"])) {
    $delete_edited_data = $_GET["delete_edited_data"];
}


/*
 * Read unread files by result 1 flag
 * Record with emp code
 * ASC order based on date and time
 * This is independent of record sequence in original txt file
 */

$attns = $con->SelectAllByCondition("attendance_raw", "result = '1' AND employee_id != '' ORDER BY date, time ASC");
foreach ($attns as $attn) {
    $time = date("H:i:s", strtotime($attn->time));
    $date = $attn->date;

    $emp_code = $attn->employee_id;

    $c_date = date_create($date);
    $f_date = date_format($c_date, "Y-m-d");

    /*
     * Find the record for this date and employee code
     * Then delete the record.
     */
    $job_card = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$f_date'");
    if (count($job_card) > 0) {
        $job_card_id = $job_card{0}->job_card_id;
        if (isset($job_card{0}->is_manually_edit)) {
            $edit_status = $job_card{0}->is_manually_edit;
        }
        //Dont delete if delete request for manually edited data

        if ($edit_status == 1 & $delete_edited_data == 0) {
            
        } else {
            $delete_array = array(
                "job_card_id" => $job_card_id
            );
            $con->delete("job_card", $delete_array);
        }
    }
}

//When full loop is done, 
$con->redirect("jobcard.php?permission_id=" . $permission_id ."&delete_edited_data=" . $delete_edited_data);


