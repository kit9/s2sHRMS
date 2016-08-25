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
    if ($status == "SL") {
        $res = $con->SelectAllByCondition("leave_policy", "short_code='SL'");
        $leave_type_id = $res{0}->leave_policy_id;
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "CL") {
        $res = $con->SelectAllByCondition("leave_policy", " short_code='CL'");
        $leave_type_id = $res{0}->leave_policy_id;
        //Update leave applicaltion//
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;
            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "EL") {
        $res = $con->SelectAllByCondition("leave_policy", " short_code='EL'");
        $leave_type_id = $res{0}->leave_policy_id;
        //Update leave applicaltion//
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "detailemp_codes_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "LL") {
        //For leave type 'Leave in Leu' shortcode- ll.
        $res = $con->SelectAllByCondition("leave_policy", " short_code='LL'");
        $leave_type_id = $res{0}->leave_policy_id;
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "LWP") {
        //For leave type 'Lose of Payment' shortcode- lop.
        $res = $con->SelectAllByCondition("leave_policy", " short_code='LWP'");
        $leave_type_id = $res{0}->leave_policy_id;
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;
            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "T") {
        //For leave type 'Tour' shortcode- T.
        $res = $con->SelectAllByCondition("leave_policy", " short_code='T'");
        $leave_type_id = $res{0}->leave_policy_id;
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "LL") {
        //Leave Type 'Leave in Leu' shortcode-ll
        $res = $con->SelectAllByCondition("leave_policy", " short_code='LL'");
        $leave_type_id = $res{0}->leave_policy_id;
        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "T") {
        //Leave Type 'Tour' shortcode-T
        $res = $con->SelectAllByCondition("leave_policy", " short_code='T'");
        $leave_type_id = $res{0}->leave_policy_id;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "ML") {
        //Leave Type 'Tour' shortcode-T
        $res = $con->SelectAllByCondition("leave_policy", " short_code='ML'");
        $leave_type_id = $res{0}->leave_policy_id;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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
    } else if ($status == "PL") {
        //Leave Type 'Tour' shortcode-T
        $res = $con->SelectAllByCondition("leave_policy", " short_code='PL'");
        $leave_type_id = $res{0}->leave_policy_id;

        $rx = $con->existsByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
        if ($rx == 1) {
            $rxd = $con->SelectAllByCondition("leave_application_details", "emp_code='$temp_emp_code' AND details_date = '$temp_date'");
            $update_id = $rxd{0}->leave_application_details_id;

            $update_app_array = array(
                "leave_application_details_id" => $update_id,
                "emp_code" => $_POST["emp_code"],
                "details_date" => $_POST["date"],
                "details_no_of_days" => 1,
                "leave_type_id" => $leave_type_id,
                "status" => "approved"
            );
            $con->update("leave_application_details", $update_app_array);
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


