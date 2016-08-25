<?php

include '../../config/class.config.php';
$con = new Config();
date_default_timezone_set('UTC');
$emp_code = "";
$job_card_id = "";
$status = "";
$in_time = "";
$out_time = "";
$temp_emp_code = '';
$zero = "0000-00-00";
$company_id = '';
$day_type_id = '';

extract($_POST);

if ($_POST["job_card_id"] == "0") {
    $temp_emp_code = $_POST["emp_code"];
    $temp_date = $_POST["date"];
    //Update leave applicaltion//
    $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' and details_date='$temp_date'");

    if ($rx == 1) {
        $rxd = $con->SelectAllByCondition("leave_application_details", " emp_code='$temp_emp_code' and details_date='$temp_date'");
        $update_id = $rxd{0}->leave_application_details_id;
        $con->delete("leave_application_details", array("leave_application_details_id" => $update_id));
    }
} else {
    $res = $con->delete("job_card", array("job_card_id" => $_POST["job_card_id"]));
}

//Also, delete the data from replacement table.
$temp_emp_code = $_POST["emp_code"];
$temp_date = $_POST["date"];

$rw_id = '';
//Look at the replacement weekend table
$replacement_data = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$temp_date' AND rw_emp_code='$temp_emp_code'");
if (count($replacement_data) > 0) {
    $rw_id = $replacement_data{0}->replacement_weekend_id;
    $delete_array = array(
        "replacement_weekend_id" => $rw_id
    );
    $con->delete("replacement_weekend", $delete_array);
}

//Find date and time now
$today = date("Y/m/d H:i:s");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d H:i:s');

//Find logged in employee code
if (isset($_SESSION["emp_code"])) {
    $logged_emp_code = $_SESSION["emp_code"];
}


//Find employee's company ID
$existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$temp_emp_code' AND ec_effective_start_date <= '$temp_date' AND ec_effective_end_date >= '$temp_date' LIMIT 0,1");
if (count($existing_company) > 0) {
    $company_id = $existing_company{0}->ec_company_id;
} else {
    $existing_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$temp_emp_code' AND ec_effective_start_date <= '$temp_date' AND ec_effective_end_date = '0000-00-00'");
    if (count($existing_company) > 0) {
        $company_id = $existing_company{0}->ec_company_id;
    }
}

//Find alternate attn policy
$alt_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$temp_emp_code' AND implement_from_date <= '$temp_date' AND implement_end_date >= '$temp_date' LIMIT 0,1");
if (count($alt_existing_awesome) > 0) {
    $alt_company_id = $alt_existing_awesome{0}->alt_company_id;
} else {
    $alt_existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$temp_emp_code' AND implement_from_date <= '$temp_date' AND implement_end_date = '0000-00-00'");
    if (count($alt_existing_awesome) > 0) {
        $alt_company_id = $alt_existing_awesome{0}->alt_company_id;
    }
}

//Assign alternate company id to main company id
if ($alt_company_id != '' && $alt_company_id != 0) {
    $company_id = $alt_company_id;
}

//Find day type
$type_query = "SELECT
	day_type.day_shortcode
FROM
	dates
LEFT JOIN day_type ON day_type.day_type_id = dates.day_type_id
WHERE
	company_id = '$company_id'
AND `date` = '$temp_date'";
$output = $con->QueryResult($type_query);

if (count($output) > 0) {
    if (isset($output{0}->day_shortcode)) {
        $day_type = $output{0}->day_shortcode;
    }
}

$check_exist = array();
if (($day_type == 'W' || $day_type == 'W') && $status == "A") {
    $check_exist = $con->SelectAllByCondition("replacement_weekend", "rw_emp_code='$temp_emp_code' AND replacement_weekend_date='$temp_date'");
    if (count($check_exist) > 0) {
        $rw_id = $check_exist{0}->replacement_weekend_id;
        $update_array = array(
            "replacement_weekend_id" => $rw_id,
            "replacement_weekend_status" => $status,
            "last_updated_by" => $logged_emp_code,
            "last_updated_at" => $formatted_today
        );
        $con->update("replacement_weekend", $update_array);
    } else {
        $insert_array = array(
            "rw_emp_code" => $temp_emp_code,
            "replacement_weekend_date" => $temp_date,
            "replacement_weekend_status" => $status,
            "created_by" => $logged_emp_code,
            "created_at" => $formatted_today
        );
        $con->insert("replacement_weekend", $insert_array);
    }
}




/*
 * First I need to check the date
 * Determine the company ID for this employee in 
 * the date on hand.
 * With the company ID, I need to determine the
 * date is weekend or not.
 * If weekend, then update the replacement date table.
 */

$company_query = "SELECT
			ec_company_id
		FROM
			emp_company
		WHERE
			ec_emp_code = '$temp_emp_code'
		AND (
			(
				ec_effective_start_date <= '$temp_date'
				AND ec_effective_end_date >= '$temp_date'
			)
			OR (
				ec_effective_start_date <= '$temp_date'
				AND ec_effective_end_date = '$zero'
			) LIMIT 0,1";

$company_result = $con->QueryResult($company_query);
if (count($company_result) > 0) {
    $company_id = $company_result{0}->ec_company_id;
}

$dates = $con->SelectAllByCondition("dates", "date='$temp_date' AND company_id='$company_id'");
if (count($dates) > 0) {
    $day_type_id = $dates{0}->day_type_id;
}

//Populate replacement table
if ($day_type_id == 2) {
    $replacements = $con->SelectAllByCondition("replacement_date", "emp_code='$temp_emp_code' AND replacement_date='$temp_date' LIMIT 0,1");
    if (count($replacements) <= 0) {
        $replacement_date_array = array(
            "replacement_date" => $temp_date,
            "emp_code" => $temp_emp_code
        );
        $con->insert("replacement_date", $replacement_date_array);
    }
}


