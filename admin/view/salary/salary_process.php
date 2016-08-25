<?php
/* Author : Rajan Hossain
 * Date: 16 March 15
 * Assumption: All salary information is ready before this process happens
 */
session_start();
//Importing class library
include ('../../config/class.config.php');
date_default_timezone_set('UTC');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';
$zero = "0000-00-00";

//Fetch company details
$companies = array();
$companies = $con->SelectAll("company");

/*
 * Selected month can not be out of date range.
 * In such case, salary month identifier will be inaccurate.
 */

$pf_info = '';
$salary_header_id = '';
$pf_percentage = '';
$PES_PSH_id = '';
$PES_amount = '';
$salaries_pf_apply_on_amount = array();
$pf_salary_amount = '';
$pf_amount = '';
$formatted_start_date = '';
$formatted_end_date = '';
$x_time_array = array();
$x_hours = '';
$x_minutes = '';
$x_seconds = '';
$xMinutes = '';
$xSecond = '';
$xhours = '';
$deductible_leaves_deduction = '';
$is_gross = '';
$d_leave_total = '';
$total_present = '';
$total_weekend = '';
$total_leave = '';
$over_time = '';
$total_absent = '';
$total_holiday = '';
$insert_array_present = array();
$alt_company_id = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_SESSION["emp_code"])) {
    $emp_logged_in = $_SESSION["emp_code"];
}

$currentTime = date("Y-m-d H-i-s");

if (isset($_POST["salary_process"])) {
    extract($_POST);

    if ($company_id == 0) {
        $err = "Please select a company.";
    } else if ($year == '') {
        $err = "Please select a year.";
    } else if ($start_date == '') {
        $err = "Please select start date.";
    } else if ($end_date == '') {
        $err = "Please select end date.";
    } else if ($month == '') {
        $err = "Please select a month";
    } else {
        /**
         * Check fiscal month setup for company
         */
        $fiscal_month_info = array();
        $start_from = '';
        $ends_to = '';
        $fiscal_month_info = $con->SelectAllByCondition("fiscal_month_setup", "company_id = '$company_id'");
        if (count($fiscal_month_info) > 0) {
            $start_from = $fiscal_month_info{0}->start_from;
            $ends_to = $fiscal_month_info{0}->ends_to;
            $starts_from_previous_month = $fiscal_month_info{0}->starts_from_previous_month;
        }

        /**
         * Formate date to find day
         * compare selected date range
         * if out of scope, through an error message
         */
        $collected_year = $year;
        $collected_month = $month;
        $assumed_day = '01';

        $date_array = array($collected_year, $month, $assumed_day);
        $generated_date = implode("-", $date_array);
        $formatted_date = date("Y-m-d", strtotime($generated_date));

        $formatted_start_date = date("Y-m-d", strtotime($start_date));
        $formatted_end_date = date("Y-m-d", strtotime($end_date));

        $datetime1 = date_create($start_date);
        $datetime2 = date_create($end_date);
        $interval = date_diff($datetime1, $datetime2);

        $calender_days_g = $interval->format('%a');
        $calendar_days = $calender_days_g + 1;

        //Employee fetch query
        $employees_query = "select * from  payroll_employee_salary where PES_company_id='$company_id'";
        if ($designation_id > 0) {
            $employees_query .= " AND PES_employee_code in ( SELECT emp_code from tmp_employee WHERE emp_designation = '$designation_id')";
        } else if ($department_id > 0) {
            $employees_query .= " AND PES_employee_code in( SELECT emp_code from tmp_employee WHERE emp_department = '$department_id') ";
        }
        $employees = $con->QueryResult($employees_query);


        //$employees = $con->SelectAllByCondition("payroll_employee_salary", "PES_company_id='$company_id' AND GROUP BY PES_employee_code");
        if (count($employees) > 0) {
            foreach ($employees as $emp) {
                $emp_code = $emp->PES_employee_code;
                $emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");

                if (count($emp_info) > 0) {
                    $is_pf_eligible = $emp_info{0}->is_pf_eligible;
                    $pf_effective_from = date("Y-m-d", strtotime($emp_info{0}->pf_effective_from));
                    $joining_date = date("Y-m-d", strtotime($emp_info{0}->emp_dateofjoin));
                }



                /*
                 * Bring basic salary definition from salary table
                 * Collect provident fund settings info from pf table
                 * collect pf effective date for this employee
                 * Compare pf eligibility for employee
                 * If eligible then calculate PF for employee based on settings.
                 * Store PF for employee
                 * Collect advance info from advance data for this employee
                 * Calculate installment amount for this employee
                 * Store in the deduction category for this employee.    
                 */

                $salaries = $con->SelectAllByCondition("payroll_employee_salary", "PES_employee_code='$emp_code'");
                foreach ($salaries as $salary) {

                    $PES_PSH_id = $salary->PES_PSH_id;
                    $PES_amount = $salary->PES_amount;

                    /*
                     * look for salary header is its monthly
                     * if only monthly, then it  should enter the data
                     * into the payroll table in this process
                     * if not monthly, then this would not be part of this 
                     * forwarding amount. 
                     */

                    $salary_headers = array();
                    $is_monthly = '';
                    $salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_id='$PES_PSH_id'");
                    if (count($salary_headers) > 0) {
                        $is_monthly = $salary_headers{0}->PSH_is_monthly;
                        $is_gross = $salary_headers{0}->PSH_is_gross;
                    }

                    /*
                     * loof if there is data already
                     * if there is data, then update
                     * Check if data is checked for employee module and monthly
                     * If both, then run a general insert as following.
                     * If else, then look for data in previous month
                     * Find the amount and store it for the current month too
                     */
                    if ($is_monthly == 'yes') {
                        $payroll_data = array();
                        $payroll_id = '';
                        $payroll_data = $con->SelectAllByCondition("payroll", "payroll_emp_code='$emp_code' AND PES_PSH_id= '$PES_PSH_id' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
                        if (count($payroll_data) > 0) {
                            $payroll_id = $payroll_data{0}->payroll_id;
                            $update_array = array(
                                "payroll_id" => $payroll_id,
                                "payroll_emp_code" => $emp_code,
                                "PES_PSH_id" => $PES_PSH_id,
                                "payroll_salary_original" => $PES_amount,
                                "payroll_salary_finalized" => $PES_amount,
                                "payroll_salary_year" => $year,
                                "payroll_salary_month" => $month,
                                "created_at" => $currentTime,
                                "created_by" => $emp_logged_in,
                                "payroll_is_gross" => $is_gross,
                                "company_id" => $company_id
                            );
                            $update_salary = $con->update("payroll", $update_array);
                            if ($update_salary == 1) {
                                //  echo "Salary information update succesfull.";
                            } else {
                                // echo "Salary information update failed.";
                            }
                        } else {
                            //store data to payroll table
                            $insert_array = array(
                                "payroll_emp_code" => $emp_code,
                                "PES_PSH_id" => $PES_PSH_id,
                                "payroll_salary_original" => $PES_amount,
                                "payroll_salary_finalized" => $PES_amount,
                                "payroll_salary_year" => $year,
                                "payroll_salary_month" => $month,
                                "created_at" => $currentTime,
                                "created_by" => $emp_logged_in,
                                "payroll_is_gross" => $is_gross,
                                "company_id" => $company_id
                            );
                            $con->insert("payroll", $insert_array);
                        }
                    }
                }

                $check_exist = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                /*
                 * Look if this employee is PF eligible
                 * If yes, look at his effective date for PF eligibility
                 */
                if ($is_pf_eligible == 'yes' && $formatted_date >= $pf_effective_from) {
                    $pf_info = $con->SelectAllByCondition("provident_fund", "company_id='$company_id'");

                    if (count($pf_info) > 0) {
                        $salary_header_id = $pf_info{0}->salary_component_id;
                        $pf_percentage = $pf_info{0}->pf_main;
                    }

                    //Find salary amount on which pf percentage will be applied
                    $salaries_pf_apply_on_amount = $con->SelectAllByCondition("payroll_employee_salary", "PES_employee_code='$emp_code' AND PES_PSH_id='$salary_header_id'");
                    $pf_salary_amount = $salaries_pf_apply_on_amount{0}->PES_amount;
                    $pf_amount = ($pf_percentage / 100) * $pf_salary_amount;

                    /*
                     * check if there is already a pf for this month
                     * if yes, then update pf information original amount
                     */

                    if (count($check_exist) > 0) {
                        $payroll_additional_id = $check_exist{0}->payroll_additional_id;
                        $update_array_additional = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "payroll_additional_pf_original" => $pf_amount,
                            "payroll_additional_pf_finalized" => $pf_amount,
                            "last_updated_at" => $currentTime,
                            "last_updated_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );
                        $update_array_additional_result = $con->update("payroll_additional", $update_array_additional);
                        if ($update_array_additional_result == 1) {
                            //echo "Provident fund update succesfull!";
                        } else {
                            //  echo "Provident fund update failed";
                        }
                    } else {
                        $insert_array_additional = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_pf_original" => $pf_amount,
                            "payroll_additional_pf_finalized" => $pf_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "created_at" => $currentTime,
                            "created_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );

                        $pf_insert = $con->insert("payroll_additional", $insert_array_additional);
                        if ($pf_insert == 1) {
                            // echo "PF is inserted succesfully";
                        } else {
                            // echo "PF insertion failed";
                        }
                    }
                }

                //look if there is an installment to be  
                $advances = array();
                $installment_amount = '';
                $pa_id = '';
                $pa_data = array();
                $update_installment = '';

                $check_exist_advance = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                //$advances = $con->SelectAllByCondition("payroll_employee_advance", "PEA_employee_code='$emp_code'");

                if ($month <= 9) {
                    $month_modified = "0";
                    $month_modified .= $month;
                } else {
                    $month_modified = $month;
                }

                $advances = $con->SelectAllByCondition("advance_details", "ad_emp_code='$emp_code' AND ad_year='$year' AND ad_month='$month_modified'");
                if (count($advances) > 0) {
                    $installment_amount = $advances{0}->amount_per_installment;

                    if (count($check_exist_advance) > 0) {
                        $pa_id = $check_exist_advance{0}->payroll_additional_id;
                        $update_with_installment = array(
                            "payroll_additional_id" => $pa_id,
                            "payroll_additional_advance_original" => $installment_amount,
                            "payroll_additional_advance_finalized" => $installment_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "last_updated_at" => $currentTime,
                            "last_updated_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );
                        $update_installment = $con->update("payroll_additional", $update_with_installment);
                        if ($update_installment == 1) {
                            // echo "Advance update successfully";
                        } else {
                            //  echo "Advance update failed!";
                        }
                    } else {
                        $insert_with_installment = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_advance_original" => $installment_amount,
                            "payroll_additional_advance_finalized" => $installment_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "created_at" => $currentTime,
                            "created_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );

                        $insert_with_installment_result = $con->insert("payroll_additional", $insert_with_installment);
                        if ($insert_with_installment_result == 1) {
                            // echo "Advance insertion successfull.";
                        } else {
                            //  echo "Advance insertion failed!";
                        }
                    }
                }


                /*
                 * Insert or update PF Loans here
                 */
                $check_exist_pf_loan = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                $pfloan_data = $con->SelectAllByCondition("pfloan_details", "pfloan_emp_code='$emp_code' AND pfloan_year='$year' AND pfloan_month='$month_modified'");
                if (count($pfloan_data) > 0) {
                    $installment_amount = $pfloan_data{0}->amount_per_installment;
                    if (count($check_exist_pf_loan) > 0) {
                        $pa_id = $check_exist_pf_loan{0}->payroll_additional_id;
                        $update_with_installment_pfloan = array(
                            "payroll_additional_id" => $pa_id,
                            "pa_pf_loan_original" => $installment_amount,
                            "pa_pf_loan_finalized" => $installment_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "last_updated_at" => $currentTime,
                            "last_updated_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );

                        $pf_update_installment = $con->update("payroll_additional", $update_with_installment_pfloan);
                        if ($pf_update_installment == 1) {
                            // echo "Advance update successfully";
                        } else {
                            //  echo "Advance update failed!";
                        }
                    } else {
                        $insert_with_installment_pfloan = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "pa_pf_loan_original" => $installment_amount,
                            "pa_pf_loan_finalized" => $installment_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "last_updated_at" => $currentTime,
                            "last_updated_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );
                        $pf_insert_installment = $con->insert("payroll_additional", $insert_with_installment_pfloan);
                        if ($pf_insert_installment == 1) {
                            // echo "Advance update successfully";
                        } else {
                            //  echo "Advance update failed!";
                        }
                    }
                }



                /*
                 * End of PF Loan Insert
                 */

                //Look if there is a tax data in the tax module
                $taxes = array();
                $check_exist_tax = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                $taxes = $con->SelectAllByCondition("payroll_employee_tax", "PET_employee_code='$emp_code' LIMIT 0,1");
                if (count($taxes) > 0) {
                    //Fetch tax amount
                    $PET_employee_tax_amount = $taxes{0}->PET_employee_tax_amount;
                    if (count($check_exist_tax) > 0) {
                        $pa_id = $check_exist_tax{0}->payroll_additional_id;
                        $update_with_tax = array(
                            "payroll_additional_id" => $pa_id,
                            "payroll_additional_tax_original" => $PET_employee_tax_amount,
                            "payroll_additional_tax_finalized" => $PET_employee_tax_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "last_updated_at" => $currentTime,
                            "last_updated_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );

                        $update_tax = $con->update("payroll_additional", $update_with_tax);
                        if ($update_installment == 1) {
                            // echo "Advance update successfully";
                        } else {
                            //  echo "Advance update failed!";
                        }
                    } else {
                        $insert_with_tax = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_tax_original" => $installment_amount,
                            "payroll_additional_tax_finalized" => $installment_amount,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_salary_year" => $year,
                            "created_at" => $currentTime,
                            "created_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );
                        $insert_with_taxt_result = $con->insert("payroll_additional", $insert_with_tax);
                        if ($insert_with_taxt_result == 1) {
                            // echo "Advance insertion successfull.";
                        } else {
                            //  echo "Advance insertion failed!";
                        }
                    }
                }

                /*
                 * end of advance here
                 */

                /*
                 * Overall attendance process
                 * Total workday, total leave, total weekend, total holiday
                 * Now starts the code to total ot process
                 * Collect variables
                 * Variables to consider: emp_code, formatted_start_date, formatted_end_date
                 */

                $total_present = 0;
                $total_absent = 0;
                $total_holiday = 0;
                $total_leave = 0;
                $total_weekend = 0;
                $d_leave_total = 0;
                $x_hours = 0;
                $total_ot = 0;
                $xhours = 0;
                $xMinutes = 0;
                $total_present_weekend = 0;
                $total_present_holiday = 0;



                $dates = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date BETWEEN '$formatted_start_date' AND '$formatted_end_date'");
                if (count($dates) > 0) {
                    foreach ($dates as $date) {
                        $single_date = date("Y-m-d", strtotime($date->date));


                        /*
                         * Check for alternate attn policy
                         * If exists, then find out day type against alternate company id
                         */

                        $alternate_company = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$single_date' AND implement_end_date >= '$single_date' LIMIT 0,1");
                        if (count($alternate_company) > 0) {
                            if (isset($alternate_company{0}->alt_company_id)) {
                                $alt_company_id = $alternate_company{0}->alt_company_id;
                            }
                        } else {
                            //Check again
                            $alt_company_second_check = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$single_date' AND implement_end_date = '0000-00-00' LIMIT 0,1");
                            if (count($alt_company_second_check) > 0) {
                                if (isset($alternate_company{0}->alt_company_id)) {
                                    $alt_company_id = $alt_company_second_check{0}->alt_company_id;
                                }
                            }
                        }

                        //If there is an alternate company, assign it as company
                        if ($alt_company_id != '' || $alt_company_id > 0) {
                            $company_id = $alt_company_id;
                        }

                        //If there is alternate day type, then assign it as day type.
                        $dates_for_type = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date='$single_date'");
                        if (count($dates_for_type) > 0) {
                            $day_type_id = $dates_for_type{0}->day_type_id;
                        } else {
                            $day_type_id = $date->day_type_id;
                        }


                        $short_code_query = $con->SelectAllByCondition("day_type", "day_type_id='$day_type_id'");
                        $short_code = $short_code_query{0}->day_shortcode;
                        $job_cards = $con->SelectAllByCondition("job_card", "emp_code='$emp_code' AND date='$single_date'");

                        if (count($job_cards) > 0) {
                            foreach ($job_cards as $jc) {

                                /*
                                 * Count total present within that date range
                                 * This is the block where the days are not weekend.
                                 */

                                if ($short_code != "W" && $short_code != "H") {
                                    $present = 1;
                                    $total_present += $present;
                                }

                                if ($short_code == "W") {
                                    $present_weekend = 1;
                                    $total_present_weekend += $present_weekend;
                                }

                                if ($short_code == "H") {
                                    $present_holiday = 1;
                                    $total_present_holiday += $present_holiday;
                                }

                                /*
                                 * Store or update Total Present in the database.
                                 */
                                $standard_ot_hours = $jc->standard_ot_hours;
                                if ($standard_ot_hours != '' || $standard_ot_hours > date("H:i:s", strtotime("00:00:00"))) {
                                    if ($short_code == "W" || $short_code == "H") {

                                        $in_time = $jc->in_time;
                                        $out_time = $jc->out_time;

                                        $std = date("H:i:s", strtotime("8:00:00"));

                                        if ($in_time < $out_time) {
                                            $std_over_time_raw = strtotime($out_time) - strtotime($in_time);
                                            $std_over_time = date("H:i:s", $std_over_time_raw);
                                        } else {
                                            $std_over_time_raw = strtotime($in_time) - strtotime($out_time);
                                            $std_over_time = date("H:i:s", $std_over_time_raw);
                                        }


                                        //OT calculate to be in 15 minutes buffer
                                        //Calculate OT in 15 minutes buffer
                                        //finding total minutes

                                        $t = EXPLODE(":", $std_over_time);
                                        $h = $t[0];
                                        IF (ISSET($t[1])) {
                                            $m = $t[1];
                                        } ELSE {
                                            $m = "00";
                                        }
                                        $mm = ($h * 60) + $m;

                                        //Devide minutes with buffer 15
                                        $first = $mm / 15;
                                        $f_first = floor($first);
                                        $floored_minute = $f_first * 15;

                                        //Devide floored minuted with 15
                                        $overtime_h = floor($floored_minute / 60);
                                        $overtime_m = $floored_minute % 60;

                                        //Counting final overtime
                                        $time_array = array($overtime_h, $overtime_m);
                                        $OT = strtotime(implode(":", $time_array));

                                        //Make final OT
                                        $tem_time_x = date("H:i:s", $OT);

                                        $z_array = explode(":", $tem_time_x);
                                        $z_hour = $z_array[0];
                                        $z_minute = $z_array[1];

                                        if ($z_hour >= 7) {
                                            $z_hour = $z_hour - 1;
                                        }

                                        $std_over_time_raw_mod = $z_hour . ":" . $z_minute;
                                        $std_over_time = date("H:i:s", strtotime($std_over_time_raw_mod));

                                        if ($std_over_time > $std) {
                                            $std_over_time = "08:00:00";
                                        }

                                        $x_time_array = explode(":", $std_over_time);
                                        $xhours +=$x_time_array[0];
                                        $xMinutes +=$x_time_array[1];
                                        $xSecond += $x_time_array[2];
                                    } else {

                                        $standard_ot_hours = $jc->standard_ot_hours;
                                        $x_time_array = explode(":", $standard_ot_hours);
                                        $xhours +=$x_time_array[0];
                                        $xMinutes +=$x_time_array[1];
                                        $xSecond += $x_time_array[2];
                                    }
                                }
                            }
                        } else {
                            //Find the leave application approved for this user
                            $leave_applications = array();
                            $leave_type_id = '';
                            $leave_applications = $con->SelectAllByCondition("leave_application_details", "emp_code='$emp_code' AND details_date='$single_date' AND status='approved'");
                            if (count($leave_applications) > 0) {
                                /*
                                 * Find leave cut applicability and find deduction for that.
                                 * Find leave and type id and see if leave cut is applicable.  
                                 */
                                $leave_type_id = $leave_applications{0}->leave_type_id;
                                $is_leave_cut_applicable = '';

                                if ($leave_type_id != '') {
                                    $leave_cut_info = array();
                                    $leave_cut_info = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$leave_type_id'");
                                    if (count($leave_cut_info) > 0) {
                                        if (isset($check_exist{0}->is_leave_cut_applicable)) {
                                            $is_leave_cut_applicable = $check_exist{0}->is_leave_cut_applicable;
                                        }
                                        if ($is_leave_cut_applicable == 'yes') {
                                            $d_leave = 1;
                                            $d_leave_total += $d_leave;
                                        }
                                    }
                                }

                                //Calculate leave
                                $leave = 1;
                                $total_leave += $leave;
                            } else if ($short_code == "W") {
                                $weekend = 1;
                                $total_weekend += $weekend;
                            } else if ($short_code == "H") {
                                $holiday = 1;
                                $total_holiday += $holiday;
                            } else {
                                /*
                                 * Store/update absent into database
                                 */
                                if ($short_code != "W" && $short_code != "H" && count($leave_applications) <= 0) {
                                    $absent = 1;
                                    $total_absent += $absent;
                                }
                            }
                        }
                    }

                    //Build total OT
                    $tem_x_hours_add = 0;
                    if ($xSecond >= 60) {
                        $tem_x_minute_add = $xSecond / 60;
                        $tem_x_minute_arr = explode(".", $tem_x_minute_add);
                        $xMinutes = $xSecond + $tem_x_minute_arr[0];
                        $temp_second_multipy = $xSecond - ($tem_x_minute_arr[0] * 60);
                        $xSecond = $temp_second_multipy;
                    }

                    if ($xMinutes >= 60) {
                        $tem_x_hours_add = $xMinutes / 60;
                        $tem_x_hours_arr = explode(".", $tem_x_hours_add);
                        $xhours = $xhours + $tem_x_hours_arr[0];
                        $temp_min_multipy = $xMinutes - ($tem_x_hours_arr[0] * 60);
                        $xMinutes = $temp_min_multipy;
                    }



                    /*
                     * Update database with OT
                     */
                    $total_ot = $xhours . ":" . $xMinutes;
                    $check_exist_ot = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_ot) > 0) {
                        $payroll_additional_id = $check_exist_ot{0}->payroll_additional_id;

                        /*
                         * Calculate OT payment based on basic payment and given formula
                         * formula : (basic/104)*overtime hours 
                         */

                        //Collect salary header ID for calculating OT
                        $overtime_on_salary = array();
                        $overtime_on_amount = '';

                        $query = "SELECT om.*, pes.* FROM overtime_meta om
                        INNER JOIN payroll_employee_salary pes ON
                        pes.PES_PSH_id=om.overtime_meta_psh_id
                        WHERE PES_employee_code='$emp_code'";
                        $overtime_on_salary = $con->QueryResult($query);
                        if (count($overtime_on_salary) > 0) {
                            $overtime_on_amount = $overtime_on_salary{0}->PES_amount;
                        }

                        //Calculate OT payment
                        $over_time_payment = ($overtime_on_amount / 104) * $total_ot;
                        $update_total_oth_array = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_oth_original" => $total_ot,
                            "payroll_additional_oth_finalized" => $total_ot,
                            "payroll_additional_ot_original" => $over_time_payment,
                            "payroll_additional_ot_finalized" => $over_time_payment,
                            "last_updated_at" => $currentTime,
                            "last_updated_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );
                        $update_total_oth_array_result = $con->update("payroll_additional", $update_total_oth_array);
                    } else {

                        /*
                         * Calculate OT payment based on basic payment and given formula
                         * formula : (basic/104)*overtime hours 
                         */

                        $overtime_on_salary = array();
                        $overtime_on_amount = '';

                        $query = "SELECT om.*, pes.* FROM overtime_meta om
                        INNER JOIN payroll_employee_salary pes ON
                        pes.PES_PSH_id=om.overtime_meta_psh_id
                        WHERE PES_employee_code='$emp_code'";
                        $overtime_on_salary = $con->QueryResult($query);
                        if (count($overtime_on_salary) > 0) {
                            $overtime_on_amount = $overtime_on_salary{0}->PES_amount;
                        }

                        //Calculate OT payment
                        $over_time_payment = ($overtime_on_amount / 104) * $total_ot;


                        $insert_total_oth_array = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "payroll_additional_oth_original" => $total_ot,
                            "payroll_additional_ot_original" => $over_time_payment,
                            "created_at" => $currentTime,
                            "created_by" => $emp_logged_in,
                            "company_id" => $company_id
                        );

                        $insert_ot_result = $con->insert("payroll_additional", $insert_total_oth_array);
                        if ($insert_ot_result == 1) {
                            // echo 'OT hours inserttion succesfull.';
                        } else {
                            // echo 'OT insertion failed.';
                        }
                    }
                }

                /*
                 * Total absent
                 */
                if ($total_absent != '') {
                    $check_exist_absent = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_absent) > 0) {
                        $payroll_additional_id = $check_exist_absent{0}->payroll_additional_id;
                        $update_array_absent = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_total_absent_original" => $total_absent,
                            "pa_total_absent_finalized" => $total_absent,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_absent) == 1) {
                            // echo "Total Absent Succesfully Updated. ";
                        } else {
                            //echo "Total Absent Update Failed.";
                        }
                    } else {
                        $insert_array_absent = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_total_absent_original" => $total_absent,
                            "pa_total_absent_finalized" => $total_absent,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_absent) == 1) {
                            //echo "Total Absent Successfully Inserted.";
                        } else {
                            //echo "Total Absent Insertion Failed";
                        }
                    }
                }

                /*
                 * Total present
                 */


                if ($total_present != '') {
                    $check_exist_present = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_present) > 0) {
                        $payroll_additional_id = $check_exist_present{0}->payroll_additional_id;
                        $update_array_present = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_total_present_original" => $total_present,
                            "pa_total_present_finalized" => $total_present,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_present) == 1) {
                            //echo "Total Present Succesfully Updated";
                        } else {
                            //  echo "Total Present Update Failed.";
                        }
                    } else {
                        $insert_array_present = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_total_present_original" => $total_present,
                            "pa_total_present_finalized" => $total_present,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_present) == 1) {
                            echo "Total Present Successfully Inserted.";
                        } else {
                            // echo "Total Present Insertion Failed";
                        }
                    }
                }

                /*
                 * Total Weekend
                 */
                if ($total_weekend != '' || $total_present_weekend != '') {
                    $check_exist_weekend = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_weekend) > 0) {
                        $payroll_additional_id = $check_exist_weekend{0}->payroll_additional_id;
                        $update_array_weekend = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_total_weekend_original" => $total_weekend + $total_present_weekend,
                            "pa_total_weekend_finalized" => $total_weekend + $total_present_weekend,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_weekend) == 1) {
                            //echo "Total Weekend Succesfully Updated";
                        } else {
                            //  echo "Total Weekend Update Failed.";
                        }
                    } else {
                        $insert_array_weekend = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_total_weekend_original" => $total_weekend + $total_present_weekend,
                            "pa_total_present_finalized" => $total_weekend + $total_present_weekend,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_weekend) == 1) {
                            // echo "Total Weekend Successfully Inserted.";
                        } else {
                            //echo "Total Weekend Insertion Failed";
                        }
                    }
                }

                /*
                 * Total Leave
                 */
                if ($total_leave != '') {
                    $check_exist_leave = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_leave) > 0) {
                        $payroll_additional_id = $check_exist_leave{0}->payroll_additional_id;
                        $update_array_leave = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_total_leave_original" => $total_leave,
                            "pa_total_leave_finalized" => $total_leave,
                            "pa_deductible_leave_total_original" => $d_leave_total,
                            "pa_deductible_leave_total_finalized" => $d_leave_total,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_leave) == 1) {
                            //   echo "Total Leave Succesfully Updated";
                        } else {
                            // echo "Total Leave Update Failed.";
                        }
                    } else {
                        $insert_array_leave = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_total_leave_original" => $total_leave,
                            "pa_total_leave_finalized" => $total_leave,
                            "pa_deductible_leave_total_original" => $d_leave_total,
                            "pa_deductible_leave_total_finalized" => $d_leave_total,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_leave) == 1) {
                            //echo "Total Leave Successfully Inserted.";
                        } else {
                            // echo "Total Leave Insertion Failed";
                        }
                    }
                }

                /*
                 * Total leave
                 */
                if ($total_holiday != '' || $total_present_holiday != '') {
                    $check_exist_holiday = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_holiday) > 0) {
                        $payroll_additional_id = $check_exist_holiday{0}->payroll_additional_id;
                        $update_array_holiday = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_total_holiday_original" => $total_holiday + $total_present_holiday,
                            "pa_total_holiday_finalized" => $total_holiday + $total_present_holiday,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_holiday) == 1) {
                            // echo "Total Holiday Succesfully Updated";
                        } else {
                            // echo "Total Holiday Update Failed.";
                        }
                    } else {
                        $insert_array_holiday = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_total_holiday_original" => $total_holiday + $total_present_holiday,
                            "pa_total_holiday_finalized" => $total_holiday + $total_present_holiday,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_holiday) == 1) {
                            //echo "Total Holiday Successfully Inserted.";
                        } else {
                            //echo "Total Holiday Insertion Failed";
                        }
                    }
                }

                /*
                 * Calculate absent deduction here
                 * Find employee join date
                 * Check if the joining date is before salary month start date
                 * if joining date is befor salary month's start date, then calculate 
                 * deduction on basic salary amoung
                 * If joining date is after salary month start, then a different formula
                 * Find calendar days between two dates
                 * Both of the formulas are -
                 * Formula one (Existing employee):  (basic/calendar_days) * absent days
                 * Formula two for news joined employee : (gross/calendar_days) * date_diff + (basic/calendar_days) * absent days
                 */

                //Find total absent data
                $absent_days = '';
                $total_absents = array();
                $total_absents = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_year='$year' AND payroll_additional_salary_month='$month'");

                /*
                 * find employee basic salary
                 * from table 'payroll_employee_salary'
                 */
                $basic_salary = '';
                $basic_salary_details = array();
                $basic_salary_details = $con->SelectAllByCondition("payroll_employee_salary", "PES_employee_code='$emp_code' AND PES_PSH_id='1'");
                if (isset($basic_salary_details{0}->PES_amount)) {
                    $basic_salary = $basic_salary_details{0}->PES_amount;
                }


                $gross_salary = '';
                $gross_salary_details = array();
                $gross_salary_details = $con->SelectAllByCondition("payroll_employee_salary", "PES_employee_code='$emp_code' AND PES_PSH_id='5'");
                if (count($gross_salary_details) > 0) {
                    $gross_salary = $gross_salary_details{0}->PES_amount;
                }

                if (count($total_absents) > 0) {
                    $absent_days = $total_absents{0}->pa_total_absent_finalized;
                    $deductible_leaves = $total_absents{0}->pa_deductible_leave_total_finalized;
                }

                if ($joining_date <= $formatted_start_date) {
                    //Absent cut will be applied on basic salary
                    $absent_deduction = ($basic_salary / $calendar_days) * $absent_days;
                } else {

                    //Find date diff between join date and salary month start date 
                    $diff_datetime1 = date_create($start_date);
                    $diff_datetime2 = date_create($joining_date);
                    $diff_interval = date_diff($datetime1, $datetime2);

                    //$total_days_working = $interval->format('%R%a days');
                    $diff_days_g = $interval->format('%a');
                    $diff_days = $diff_days_g + 1;

                    $absent_deduction = (($gross_salary / $diff_days) * $absent_days) + (($basic_salary / $calendar_days) * $absent_days);
                }

                $deductible_leaves_deduction = ($gross_salary / $calendar_days) * $deductible_leaves;

                /*
                 * Update/store absent deduction into database
                 */

                if ($absent_deduction != '') {
                    $check_exist_absent_deduction = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_absent_deduction) > 0) {
                        $payroll_additional_id = $check_exist_absent_deduction{0}->payroll_additional_id;
                        $update_array_absent_ded = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_absent_deduction_original" => $absent_deduction,
                            "pa_absent_deduction_finalized" => $absent_deduction,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_absent_ded) == 1) {
                            //echo "Total Absent Deduction Succesfully Updated. ";
                        } else {
                            // echo "Total Absent Deduction Update Failed.";
                        }
                    } else {
                        $insert_array_absent_ded = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_absent_deduction_original" => $absent_deduction,
                            "pa_absent_deduction_finalized" => $absent_deduction,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_absent_ded) == 1) {
                            //echo "Total Absent Deduction Successfully Inserted.";
                        } else {
                            //echo "Total Absent Deduction Insertion Failed";
                        }
                    }
                }

                if ($deductible_leaves_deduction != '') {

                    $check_exist_leave_deduction = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                    if (count($check_exist_leave_deduction) > 0) {
                        $payroll_additional_id = $check_exist_leave_deduction{0}->payroll_additional_id;
                        $update_array_leave_cut = array(
                            "payroll_additional_id" => $payroll_additional_id,
                            "pa_leave_deduction_original" => $absent_deduction,
                            "pa_leave_deduction_finalized" => $absent_deduction,
                            "company_id" => $company_id
                        );
                        if ($con->update("payroll_additional", $update_array_leave_cut) == 1) {
                            //echo "Total Absent Deduction Succesfully Updated. ";
                        } else {
                            //echo "Total Absent Deduction Update Failed.";
                        }
                    } else {

                        $insert_array_leave_cut = array(
                            "payroll_additional_emp_code" => $emp_code,
                            "payroll_additional_salary_year" => $year,
                            "payroll_additional_salary_month" => $month,
                            "pa_leave_deduction_original" => $absent_deduction,
                            "pa_leave_deduction_finalized" => $absent_deduction,
                            "company_id" => $company_id
                        );
                        if ($con->insert("payroll_additional", $insert_array_leave_cut) == 1) {
                            //echo "Total Absent Deduction Successfully Inserted.";
                        } else {
                            //echo "Total Absent Deduction Insertion Failed";
                        }
                    }
                }

                /*
                 * Calculate net salary from gross salary
                 * Dedecuted amount will be-
                 * tax, advance, pf, absent cut, leave cut
                 */

                //Collect gross salary
                $gross_salary_info = array();
                $profident_fund = '';
                $advance = '';
                $tax = '';
                $absent_cut = '';
                $leave_cut = '';
                $net_salary = '';

                $gross_salary_info = $con->SelectAllByCondition("payroll", "payroll_emp_code='$emp_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month' AND payroll_is_gross='yes'");

                if (count($gross_salary_info) > 0) {
                    $gross_salary = $gross_salary_info{0}->payroll_salary_finalized;
                    if ($gross_salary != '') {

                        /*
                         * Find net salary here
                         * Deduct all deduction above mentioned
                         */
                        $additional_info = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_year='$year' AND payroll_additional_salary_month='$month'");
                        if (count($additional_info) > 0) {
                            $profident_fund = $additional_info{0}->payroll_additional_pf_finalized;
                            $advance = $additional_info{0}->payroll_additional_advance_finalized;
                            $tax = $additional_info{0}->payroll_additional_tax_finalized;
                            $absent_cut = $additional_info{0}->pa_absent_deduction_finalized;
                            $leave_cut = $additional_info{0}->pa_leave_deduction_finalized;
                            $over_time = $additional_info{0}->payroll_additional_ot_finalized;
                        }

                        //Calculate net salary
                        $net_salary = ($gross_salary + $over_time) - ($profident_fund + $advance + $tax + $absent_cut + $leave_cut);

                        //Update table with net salary
                        if ($net_salary != '') {

                            if (count($additional_info) > 0) {
                                $payroll_additional_id = $additional_info{0}->payroll_additional_id;
                                $update_net_salary = array(
                                    "payroll_additional_id" => $payroll_additional_id,
                                    "pa_net_salary_original" => $net_salary,
                                    "pa_net_salary_finalized" => $net_salary,
                                    "company_id" => $company_id
                                );
                                if ($con->update("payroll_additional", $update_net_salary) == 1) {
                                    //echo "Net salary succesfully updated";
                                } else {
                                    //echo "Net Salary Updated Failed";
                                }
                            }
                        }
                    }
                }

                /*
                 * Update table with salary start date
                 * Update table with salary end date
                 * Update table with calendar days
                 */
                $check_exist_detail = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$emp_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' LIMIT 0,1");
                if (count($check_exist_detail) > 0) {
                    $payroll_additional_id = $check_exist_detail{0}->payroll_additional_id;
                    $update_array_dates = array(
                        "payroll_additional_id" => $payroll_additional_id,
                        "pa_calender_days" => $calendar_days,
                        "pa_start_date" => $formatted_start_date,
                        "pa_end_date" => $formatted_end_date
                    );

                    if ($con->update("payroll_additional", $update_array_dates) == 1) {
                        //Success message    
                    } else {
                        //error message
                    }
                } else {
                    $insert_array_dates = array(
                        "payroll_additional_emp_code" => $emp_code,
                        "payroll_additional_salary_year" => $year,
                        "payroll_additional_salary_month" => $month,
                        "pa_calender_days" => $calendar_days,
                        "pa_start_date" => $formatted_start_date,
                        "pa_end_date" => $formatted_end_date,
                        "company_id" => $company_id
                    );
                    if ($con->insert("payroll_additional", $insert_array_dates) == 1) {
                        //Success message    
                    } else {
                        //error message
                    }
                }
            }
        } else {
            $err = "No employees found from salary definition for selected criteria.";
        }
    }
    $msg = "Salary succesfully processed";
}
?>

<?php include '../view_layout/header_view.php'; ?>
<style type="text/css">   
    .k-edit,.k-delete,.k-add {
        margin-top: -2px !important;
    }
</style>

<script type="text/javascript">
    $(document).ready(function () {
        $("#company").kendoDropDownList();
    });
</script>
<?php include("../../layout/msg.php"); ?>
<form method="post"> 
    <!--Select a company-->
    <div class="col-md-4" style="padding-left: 0px;"> 
        <label for="Full name">Company Name:</label><br/> 
        <select id="company" style="width: 80%" name="company_id">
            <option value="0">Select Company</option>
            <?php if (count($companies) >= 1): ?>
                <?php foreach ($companies as $com): ?>
                    <option value="<?php echo $com->company_id; ?>" 
                    <?php
                    if ($com->company_id == $company_id) {
                        echo "selected='selected'";
                    }
                    ?>><?php echo $com->company_title; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
        </select>
    </div>

    <div class="col-md-4" style="padding-left:0px;"> 
        <label for="Full name" style="padding-left:0px;">Department:</label><br/>
        <input type="text" style="width:80%;" id="department_id" name="department_id" value="<?php echo $department_id; ?>" placeholder=""/>
    </div>

    <div class="col-md-4">
        <label for="Full name">Designation:</label><br/>
        <input type="text" style="width:80%;" id="designation_id" name="designation_id" value="<?php echo $designation_id; ?>" placeholder=""/>
    </div>
    <div class="clearfix"></div>
    <br/>

    <div class="col-md-4" style="padding-left:0px;">
        <label for="Full name">Year:</label><br/> 
        <input type="text" id="year1" style="width:80%; padding-left: 0px;" name="year" style="width: 80%;" value="<?php echo $year; ?>" />
    </div>

    <div class="col-md-4" style="padding-left: 0px;">
        <label for="Start Date">Start Date:</label><br/>
        <input type="text" id="start_date" class="emp_datepicker" value="<?php echo $start_date; ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
    </div>

    <div class="col-md-4">
        <label for="Start Date">End Date:</label><br/>
        <input id="end_date" type="text" class="emp_datepicker" value="<?php echo $end_date; ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
    </div>

    <script>
        $(document).ready(function () {
            $("#department_id").kendoComboBox({
                placeholder: "Select department...",
                dataTextField: "department_title",
                dataValueField: "department_id",
                dataSource: {
                    transport: {
                        read: {
                            url: "../../controller/department.php",
                            type: "GET"
                        }
                    },
                    schema: {
                        data: "data"
                    }
                }
            }).data("kendoComboBox");
            $("#designation_id").kendoComboBox({
                placeholder: "Select Designation...",
                dataTextField: "designation_title",
                dataValueField: "designation_id",
                dataSource: {
                    transport: {
                        read: {
                            url: "../../controller/designation.php",
                            type: "GET"
                        }
                    },
                    schema: {
                        data: "data"
                    }
                }
            }).data("kendoComboBox");
            $("#start_date").kendoDatePicker();
            $("#end_date").kendoDatePicker();
        });
    </script>
    <div class="clearfix"></div>
    <br />

    <div class="col-md-4" style="padding-left:0px;">
        <label for="Full name">Month:</label> <br />
        <input id="month1" name="month" style="width: 80%;" value="<?php echo $month; ?>" />
    </div>

    <div class="clearfix"></div>
    <br/>
    <input type="submit" name="salary_process" value="Process Salary" class="k-textbox">

</form>
<!--Script to trigger this page again with this ID -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#month1').change(function () {
            //window.location = "salary_list.php?month=" + $(this).val() + "&com_id=" + $("#company").val() + "year=" + $("#month1").val();
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#year1").kendoComboBox({
            placeholder: "Select Year...",
            dataTextField: "year_name",
            dataValueField: "year_name",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/year.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");

        $("#month1").kendoComboBox({
            autoBind: false,
            cascadeFrom: "year1",
            placeholder: "Select Month..",
            dataTextField: "month",
            dataValueField: "month_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/month.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");

    });
</script>
<?php include '../view_layout/footer_view.php'; ?>