<?php
session_start();
/*
 * Page: Leave application approval
 * Author: Md Mahamodur Zaman Bhuyian
 * Date: 03-08-2016
 */
$leave_master = array();
$start_date = '';
$end_date = '';
$subject = "";
$message = "";
$company_array = array();
$company_id = '';

include '../../config/class.config.php';

$con = new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

//Collect configuration meta information
/**
 * Collect Leave application master ID from the sender grid
 * Collect Applicant Employee Code from the sender grid
 * Collect step ID
 * Three values will select a unique leave application from 
 * approval_workflow_status table
 * Then update the status for specfic row against three identifier
 */
//Review note from reviewer
$review_remark = '';

if (isset($_POST["master"])) {
    $master = $_POST["master"];
}



//prepareing necesarry backup using pure sql not programming so if you want to modify you have to able / capable of good skill in sql

$con->FlyPrepare("insert into leave_application_master_garbage
select * from leave_application_master where `leave_application_master_id`='" . $master . "' AND leave_application_master_id NOT IN (select leave_application_master_id from leave_application_master_garbage)");

$con->FlyPrepare("insert into leave_application_details_garbage
select * from leave_application_details where `leave_application_master_id`='" . $master . "' AND leave_application_details_id NOT IN (select `leave_application_details_id` from leave_application_details_garbage)");

$con->FlyPrepare("insert into approval_workflow_status_garbage
select * from approval_workflow_status where `leave_application_master_id`='" . $master . "' AND aws_id NOT IN (select `aws_id` from approval_workflow_status_garbage)");


$deleteparam = "";
$deleteparam .="DELETE from leave_application_master WHERE `leave_application_master_id`='" . $master . "';";
$deleteparam .="DELETE from leave_application_details WHERE `leave_application_master_id`='" . $master . "';";
$deleteparam .="DELETE from approval_workflow_status WHERE `leave_application_master_id`='" . $master . "'";


//echo $con->FlyPrepareMulti($deleteparam);
//
//exit();
if ($con->FlyPrepareMulti($deleteparam) == 1) {
//Generate success message
    $return_array = array(
        "error_msg" => "You have delete this application & it's Detail info succesfully.",
        "success_flag" => "yes"
    );
    echo "{\"data\":" . json_encode($return_array) . "}";
} else {
    $return_array = array(
        "error_msg" => "Leave Application Failed To Delete.",
        "success_flag" => "no"
    );
    echo "{\"data\":" . json_encode($return_array) . "}";
}
                        
