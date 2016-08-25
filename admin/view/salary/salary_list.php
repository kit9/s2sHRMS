<?php
/* Author : Asma
 * Date: 16 March 15
 */
session_start();
//Importing class library
include ('../../config/class.config.php');
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if ($con->hasPermissionView($permission_id) != "yes") {
    $con->redirect("../dashboard/index.php");
}

$errors = 0;
$err = "";
$msg = '';
$year = '';
$month = '';
$company_id = '';
$logged_emp_code = '';
$is_lockedres = '';
$emp_advance_amount = '';
$PEA_paid_amount = '';
$PEA_remain_amount = '';
$last_inst_no_paid = '';
$current_installment = '';
$total_paid = '';
$total_due = '';

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

//Loggen in employee
if (isset($_SESSION["emp_code"])) {
    $logged_emp_code = $_SESSION["emp_code"];
}

$companies = array();
$companies = $con->SelectAll("company");

$logged_emp_code = '';
$range_start = '';
$range_end = '';

$staff_grade_permission = array();

if (isset($_SESSION['emp_code'])) {
    $logged_emp_code = $_SESSION['emp_code'];
}
//Find staff grade permission
$staff_grade_permission = $con->SelectAllByCondition("salary_view_permission", "svp_emp_code='$logged_emp_code'");

if (count($staff_grade_permission) > 0) {
    $range_start = $staff_grade_permission{0}->svp_sg_position_from;
    $range_end = $staff_grade_permission{0}->svp_sg_position_to;
}
if (isset($_POST["salary_generate"])) {
    extract($_POST);

    $quer_lock = "select slm_is_locked from salary_lock_meta where slm_year='$year' AND slm_month='$month' AND slm_company_id='$company_id'";
    $is_lockedres = $con->QueryResult($quer_lock);
    $quer_salary = "SELECT payroll_additional_id FROM payroll_additional WHERE payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' AND company_id='$company_id'";
    $is_processed = $con->QueryResult($quer_salary);

    $present_yr = date('Y');
    $present_month = date('m');

    if ($company_id == '') {
        $err = 'Please select a company.';
        $errors = 1;
    } else if ($year == '') {
        $err = 'Please select a year.';
        $errors = 1;
    } else if ($month == '') {
        $err = "Please select a month.";
        $errors = 1;
    } else if ($year > $present_yr) {
        $err = "Please select present or previous year.";
        $errors = 1;
    } else if ($year == $present_yr && $month > $present_month) {
        $err = "Please select date till today not future date.";
        $errors = 1;
    } else {
        $salary_query = "SELECT 
        e.emp_code,
        e.emp_firstname,
        d.designation_title,
        dep.department_title,
        sg.staffgrade_title,
        payroll.payroll_salary_finalized,
        payroll.payroll_salary_month,
        payroll.payroll_salary_year,
        payroll.company_id,
        pa.pa_net_salary_finalized 
        FROM
        payroll
        LEFT JOIN
        payroll_additional AS pa ON pa.payroll_additional_emp_code = payroll.payroll_emp_code
        LEFT JOIN
        tmp_employee e ON e.emp_code = payroll.payroll_emp_code
        LEFT JOIN
        designation d ON e.emp_designation = d.designation_id
        LEFT JOIN
        department dep ON dep.department_id = e.emp_department
        LEFT JOIN
        staffgrad sg on sg.staffgrade_id = e.emp_staff_grade
        WHERE
        payroll.company_id = '$company_id'
        AND payroll.payroll_salary_year = $year
        AND payroll.payroll_salary_month = $month
        AND payroll.payroll_is_gross = 'yes'
        AND pa.payroll_additional_salary_year = '$year'
        AND pa.payroll_additional_salary_month = '$month'
        AND pa.company_id = '$company_id'";
        $salary_info = $con->QueryResult($salary_query);
    }
}
/** Salary lock process starts here
 */
/** Salary lock process starts here.
 * Must be update things: lock flag to be set to true
 * PF company contribution based on the business logic
 * Loan or advance, installment to be sanctioned accordingly 
 */
$now_raw = date("Y/m/d H:i:s");
$now_create = date_create($today);
$now = date_format($sys_date, 'Y-m-d H:i:s');

if (isset($_POST["salary_lock"])) {
    //Extract post array
    extract($_POST);
    //set form validations
    if ($company_id == '') {
        $err = 'Please select a company.';
    } else if ($year == '') {
        $err = 'Please select a year.';
    } else if ($month == '') {
        $err = "Please select a Month.";
    } else {
        //Check existing lock status
        $existing_lock_status = $con->SelectAllByCondition("salary_lock_meta", "slm_year='$year' AND slm_month='$month' AND slm_company_id='$company_id'");
        if (count($existing_lock_status) <= 0) {

            /** Update 'salary_lock_meta' table
             * data: year, month, company_id, status
             */
            //Update,insert array
            $insert_array = array(
                "slm_year" => $year,
                "slm_month" => $month,
                "slm_company_id" => $company_id,
                "slm_is_locked" => "yes",
                "created_at" => $now,
                "created_by" => $logged_emp_code
                );
            //Insert the array
            if ($con->insert("salary_lock_meta", $insert_array) == 1) {
                /** Collect provident fund settings data.
                 * Generate company contribution for all pf information available in payroll additional table. 
                 */
                //collect pf settings data based on company
                $pf_meta = array();
                $pf_main = '';
                $pf_after_one_year = '';
                $pf_after_two_year = '';
                $pf_after_three_year = '';
                $pf_emp_code = '';
                $joining_date = '';
                $cc = '';

                //Look the database based on the company
                $pf_meta = $con->SelectAllByCondition("provident_fund", "company_id = '$company_id'");

                //Generate variables
                if (count($pf_meta) > 0) {
                    $pf_main = $pf_meta{0}->pf_main;
                    $pf_after_one_year = $pf_meta{0}->PF_after_1y;
                    $pf_after_two_year = $pf_meta{0}->PF_after_2y;
                    $pf_after_three_year = $pf_meta{0}->PF_after_3y;
                }

                /** Look payroll_additional table
                 * Based on- company, year, month
                 */
                $payroll_info = array();
                $payroll_info = $con->SelectAllByCondition("payroll_additional", "company_id='$company_id' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year'");
                //Run loop to array
                if (count($payroll_info) > 0) {
                    foreach ($payroll_info as $pf) {

                        //collect emp_code and provident fund finalized
                        $emp_code = $pf->payroll_additional_emp_code;
                        $emp_pf_amount = $pf->payroll_additional_pf_finalized;

                        //Add '0' to month less than 10
                        if ($month < 10) {
                            $zero_added = "0";
                            $zero_added .= $month;
                            $month = $zero_added;
                        }
                        $existing_advance_master = array();
                        $existing_advance = $con->SelectAllByCondition("advance_details", "ad_emp_code='$emp_code' AND ad_year='$year' AND ad_month='$month'");


                        if (!empty($existing_advance)) {

                            //Declare local variables 
                            $payable = '';
                            $paid_amount = '';
                            $ad_id = '';
                            $current_due = '';
                            $finalized_due = '';
                            $ad_master_id = '';
                            $due_amount = '';
                            $already_paid_amount = '';
                            $total_amount = '';

                            //Find amount to be paid
                            if (isset($existing_advance{0}->amount_per_installment)) {
                                $payable = $existing_advance{0}->amount_per_installment;
                            }

                            //Find amount finalized in payroll
                            if (isset($pf->payroll_additional_advance_finalized)) {
                                $paid_amount = $pf->payroll_additional_advance_finalized;
                            }

                            //Check if both amounts are equal or not
                            if ($payable == $paid_amount) {
                                /*
                                 * Update realized
                                 * Update due amount
                                 */

                                //Find primary id for rwo to update
                                $ad_id = $existing_advance{0}->ad_id;
                                if (count($existing_advance_master) > 0) {
                                    $ad_master_id = $existing_advance_master{0}->PEA_id;
                                }

                                //Find current due
                                if (isset($existing_advance{0}->advance_due)) {
                                    $current_due = $existing_advance{0}->advance_due;
                                }

                                //Calculate finalized due with realized
                                $finalized_due = $current_due - $paid_amount;
                                if (isset($existing_advance{0}->PEA_id)) {
                                    $PEA_id = $existing_advance{0}->PEA_id;
                                }
                                
                                // Check if finalized due is 0
                                if ($finalized_due <= 0) {
                                    //Update array
                                    $advance_settings = array(
                                        "PEA_id" => $PEA_id,
                                        "PEA_status" => "closed"
                                        );
                                    if ($con->update("payroll_employee_advance", $advance_settings) == 1) {
                                        //Success message
                                    }
                                }

                                //Find currently paid amount
                                $advance_master = array();
                                $advance_master_info = $con->SelectAllByCondition("payroll_employee_advance", "PEA_id='$PEA_id'");
                                if (count($advance_master_info) > 0) {
                                    $already_paid_amount = $advance_master_info{0}->PEA_paid_amount;
                                    $total_amount = $advance_master_info{0}->PEA_advance_amount;
                                }

                                $paid_amount = $already_paid_amount + $paid_amount;
                                $due_amount = $total_amount - $paid_amount;

                                //Update master array
                                $ad_master_up_array = array(
                                    "PEA_id" => $PEA_id,
                                    "PEA_paid_amount" => $paid_amount,
                                    "PEA_remain_amount" => $due_amount
                                    );
                                $con->update("payroll_employee_advance", $ad_master_up_array);

                                //Update array
                                $ad_up_array = array(
                                    "ad_id" => $ad_id,
                                    "advance_realized" => $paid_amount,
                                    "advance_due" => $finalized_due
                                    );

                                /*
                                 * On success of update,
                                 * update all due amount for next months
                                 */
                                if ($con->update("advance_details", $ad_up_array) == 1) {

                                    //Run update query manually
                                    $next_elems_update = "UPDATE advance_details
                                    SET `advance_due` = '$finalized_due'
                                    WHERE

                                    (
                                        (ad_month > '$month' AND ad_year = '$year') 
                                        OR (ad_year > '$year')
                                        )

AND ad_emp_code = '$emp_code'";

                                    //Run query
$exec_sql = mysqli_query($open, $next_elems_update);
if (!$exec_sql) {
                                        //Err message 
}
}
} else if ($paid_amount < $payable && $paid_amount > 0) {

                                //Declare local variables
    $current_due = '';
    $total_resting = '';

                                //Find primary id for rwo to update
    $ad_id = $existing_advance{0}->ad_id;

                                //Find current due
    if (isset($existing_advance{0}->advance_due)) {
        $current_due = $existing_advance{0}->advance_due;
    }

                                //Calculate finalized due with realized
    $finalized_due = $current_due - $paid_amount;

                                // Check if finalized due is 0
    if ($finalized_due <= 0) {

        if (isset($existing_advance{0}->PEA_id)) {
            $PEA_id = $existing_advance{0}->PEA_id;
        }

                                    //Update array
        $advance_settings = array(
            "PEA_id" => $PEA_id,
            "PEA_status" => "closed"
            );

        if ($con->update("payroll_employee_advance", $advance_settings) == 1) {
                                        //Success message
        }
    }

                                //Find resting number of installl
    $rest_inst_query = "SELECT * FROM advance_details
    WHERE

    (
        (ad_month > '$month' AND ad_year = '$year') 
        OR (ad_year > '$year')
        )

AND ad_emp_code = '$emp_code'";

$rest_inst = $con->QueryResult($rest_inst_query);
if (count($rest_inst) > 0) {
    $total_resting = count($rest_inst);
}

                                //Calculate new amount per installment
$new_amount_per_inst = round($finalized_due / $total_resting);

                                //Update array
$ad_up_array = array(
    "ad_id" => $ad_id,
    "advance_realized" => $paid_amount,
    "advance_due" => $finalized_due
    );
                                /*
                                 * On success of update,
                                 * update all due amount for next months
                                 */
                                if ($con->update("advance_details", $ad_up_array) == 1) {

                                    //Run update query manually
                                    $next_elems_update = "UPDATE advance_details
                                    SET `advance_due` = '$finalized_due', amount_per_installment = '$new_amount_per_inst'
                                    WHERE

                                    (
                                        (ad_month > '$month' AND ad_year = '$year') 
                                        OR (ad_year > '$year')
                                        )

AND ad_emp_code = '$emp_code'";

                                    //Run query
$exec_sql = mysqli_query($open, $next_elems_update);
if (!$exec_sql) {
                                        //Err message 
}
}
} else if ($paid_amount > $payable) {

                                //Declare local variables
    $current_due = '';
    $total_resting = '';

                                //Find primary id for rwo to update
    $ad_id = $existing_advance{0}->ad_id;

                                //Find current due
    if (isset($existing_advance{0}->advance_due)) {
        $current_due = $existing_advance{0}->advance_due;
    }

                                //Calculate finalized due with realized
    $finalized_due = $current_due - $paid_amount;

                                // Check if finalized due is 0
    if ($finalized_due <= 0) {

        if (isset($existing_advance{0}->PEA_id)) {
            $PEA_id = $existing_advance{0}->PEA_id;
        }

                                    //Update array
        $advance_settings = array(
            "PEA_id" => $PEA_id,
            "PEA_status" => "closed"
            );

        if ($con->update("payroll_employee_advance", $advance_settings) == 1) {
                                        //Success message
        }
    }
}

                            //Find resting number of installl
$rest_inst_query = "SELECT * FROM advance_details
WHERE         
(
    (ad_month > '$month' AND ad_year = '$year') 
    OR (ad_year > '$year')
    )

AND ad_emp_code = '$emp_code'";

$rest_inst = $con->QueryResult($rest_inst_query);
if (count($rest_inst) > 0) {
    $total_resting = count($rest_inst);
}

                            //Calculate new amount per installment
$new_amount_per_inst = round($finalized_due / $total_resting);

                            //Update array
$ad_up_array = array(
    "ad_id" => $ad_id,
    "advance_realized" => $paid_amount,
    "advance_due" => $finalized_due
    );

                            /*
                             * On success of update,
                             * update all due amount for next months
                             */
                            if ($con->update("advance_details", $ad_up_array) == 1) {
                                //Run update query manually
                                $next_elems_update = "UPDATE advance_details
                                SET `advance_due` = '$finalized_due', amount_per_installment = '$new_amount_per_inst'
                                WHERE

                                (
                                    (ad_month > '$month' AND ad_year = '$year') 
                                    OR (ad_year > '$year')
                                    )

AND ad_emp_code = '$emp_code'";

                                //Run query
$exec_sql = mysqli_query($open, $next_elems_update);
if (!$exec_sql) {
                                    //Err message 
}
}
}
                        /*
                         * End of advance settlement
                         */

                        /*
                         * PF Loan will follow exact same logic as advance
                         */

                        /*
                         * End of PF Loan Settlement
                         */

                        //Check if pf is not empty
                        //Check if pf is not empty
                        if ($emp_pf_amount != '') {

                            //fecth employee's date of join
                            $emp_info = array();
                            $emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$pf_emp_code'");

                            //Check if array is not empty
                            if (count($emp_info) > 0) {
                                $joining_date = $emp_info{0}->emp_dateofjoin;
                            }
                            //Find diff in year between joining date and today
                            $date1 = date_create($formatted_today);
                            $date2 = date_create($joining_date);
                            $diff = date_diff($date1, $date2);
                            $job_duration = $diff->y;

                            if ($job_duration >= 3 && $company_id == 1) {
                                //Calculate cc
                                $cc = ($emp_pf_amount * $pf_after_three_year) / 100;
                            } else if ($company_id == 2 && $job_duration >= 2 && $job_duration <= 3) {
                                //if job duration is more than or equal 2 years but less than 3 
                                $cc = ($emp_pf_amount * $pf_after_two_year) / 100;
                            } else if ($company_id == 2 && $job_duration >= 3) {
                                //if job duration is more than or equal 3 years
                                $cc = ($emp_pf_amount * $pf_after_two_year) / 100;
                            } else {
                                //If above conditions fail cc will be empty 
                                $cc = '';
                            }

                            //Generate pf array
                            $pf_insert_array = array(
                                "pfd_emp_code" => $pf_emp_code,
                                "pfd_year" => $year,
                                "pfd_month" => $month,
                                "pfd_emp_amount" => $emp_pf_amount,
                                "pfd_com_amount" => $emp_pf_amount,
                                "eligible_amount" => $cc,
                                "pfd_company_id" => $company_id,
                                "created_by" => $now,
                                "created_at" => $logged_emp_code);

                            //insert the data
                            if ($con->insert("provident_fund_details", $pf_insert_array) == 1) {
                                //update provident_fund_yearly_table
                                //Find existing data
                                $yearly_total = '';
                                $pf_yearly = array();
                                $pf_yearly = $con->SelectAllByCondition("provident_fund_details_yearly", "PFDY_emp_code='$pf_emp_code' AND PFDY_year='$year'");
                                if (count($pf_yearly) > 0) {
                                    //Find id
                                    $PFDY_id = $pf_yearly{0}->PFDY_id;
                                    //Find yearly total
                                    $yearly_total = $pf_yearly{0}->PFDY_pfd_total;
                                    $yearly_total_eligible = $pf_yearly{0}->PFDY_eligible_total;

                                    //Calculate current total
                                    $total_now = $yearly_total + $cc + $emp_pf_amount;
                                    $eligible_total_now = $yearly_total_eligible + $cc + $emp_pf_amount;

                                    $update_total_pf = array(
                                        "PFDY_id" => $PFDY_id,
                                        "PFDY_pfd_total" => $total_now,
                                        "PFDY_eligible_total" => $eligible_total_now,
                                        "last_updated_at" => $now,
                                        "last_updated_by" => $logged_emp_code
                                        );

                                    //Update database
                                    if ($con->update("provident_fund_details_yearly", $update_total_pf) == 1) {
                                        //Success message
                                    } else {
                                        //Error message
                                    }
                                } else {
                                    //Create insert array
                                    $insert_total_pf = array(
                                        "PFDY_emp_code" => $pf_emp_code,
                                        "PFDY_year" => $year,
                                        "PFDY_pfd_total" => $emp_pf_amount,
                                        "PFDY_eligible_total" => $eligible_total_now,
                                        "created_at" => $now,
                                        "created_by" => $logged_emp_code
                                        );
                                    //insert into database
                                    if ($con->insert("provident_fund_details_yearly", $insert_total_pf) == 1) {
                                        //Success message
                                    } else {
                                        //Error message
                                    }
                                }
                            }
                        }
                    }
                }

                /*
                 * Advance process finalize starts here
                 * Collect advance for key-data: 
                 * emp_code, month, year
                 * Update following fields: 
                 * PEA_paid_amount, PEA_remain_amount, ad_install_no, ad_paid_amount, ad_month, ad_year, ad_emp_code,
                 */

                // Echo success message
                $msg = "Salary lock is completed successfully for selected month.";
            } else {
                $err = "Salary Lock Status Update Failed.";
            }
        } else {
            $err = 'Salary is already locked for this month.';
        }
    }
}

/*
 * Excel generate code
 */

if (isset($_POST["generate_excel"])) {

    //Extract post array
    extract($_POST);

    //Set form validations
    if ($company_id == '') {
        $err = 'Please select a company.';
    } else if ($year == '') {
        $err = 'Please select a year.';
    } else if ($month == '') {
        $err = "Please select a year.";
    } else {

        /*
         * Generate sub-header array
         */
        $sub_header_array = array("Emp.", "AC NO", "Name", "Designation", "Department", "Section", "DOJ", "Grade", "Cal", "Attn", "Weekend", "Festival/holiday", "Leave", "Absent", "OT");
        $sub_header_array_two = array();

        //Collect salary headers displayed in employee module.
        $salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_show_in_tmp_mod='yes'");
        if (count($salary_headers) > 0) {
            foreach ($salary_headers as $header) {
                //Add emp module salary headers to sub header array
                array_push($sub_header_array, $header->PSH_header_title);
            }
        }

        //Collect all addition field to main salary headers
        //Add OT earning
        array_push($sub_header_array, "OT Earning");

        //Add dynamic headers based on 'add category'
        $add_salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_display_on='add'");
        if (count($add_salary_headers) > 0) {
            foreach ($add_salary_headers as $add_headers) {
                array_push($sub_header_array, $add_headers->PSH_header_title);
            }
        }

        array_push($sub_header_array, "Total Earning");

        //Add dynamic headers based on 'add category'
        $deduct_salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_display_on='deduct'");
        if (count($deduct_salary_headers) > 0) {
            foreach ($deduct_salary_headers as $deduct_headers) {
                array_push($sub_header_array, $deduct_headers->PSH_header_title);
            }
        }

        //Add fixed deductibles
        array_push($sub_header_array, "Provident Fund", "Advance", "PF Loan", "Tax", "Absent Cut", "Leave Cut", "Total Deduction");

        //Add net payable and remarks
        array_push($sub_header_array, "Net Payable", "Remarks");


        //Generate main array
        $detailed_query = "SELECT 
        e.emp_code,
        e.emp_dateofjoin,
        e.emp_account_number,
        e.emp_firstname,
        d.designation_title,
        dep.department_title,
        sg.staffgrade_title,
        sub.subsection_title,
        pa.*
        FROM
        payroll_additional AS pa
        LEFT JOIN
        tmp_employee e ON e.emp_code = pa.payroll_additional_emp_code
        LEFT JOIN
        designation d ON e.emp_designation = d.designation_id
        LEFT JOIN
        department dep ON dep.department_id = e.emp_department
        LEFT JOIN
        staffgrad sg ON sg.staffgrade_id = e.emp_staff_grade
        LEFT JOIN
        subsection AS sub ON sub.subsection_id = e.emp_subsection
        WHERE
        pa.payroll_additional_salary_year = '$year'
        AND pa.payroll_additional_salary_month = '$month'
        AND pa.company_id = '$company_id' ORDER BY dep.department_id";

        $query_result = $con->QueryResult($detailed_query);
        $master_array = array();

        foreach ($query_result as $data) {
            $total_earning = 0;
            $total_deduction = 0;
            $other_addition = 0;

            $emp_code = $data->payroll_additional_emp_code;

            //Bank Account Number
            if (isset($data->emp_account_number)) {
                $emp_account_number = $data->emp_account_number;
            } else {
                $emp_account_number = " ";
            }

            //Full Name
            if (isset($data->emp_firstname)) {
                $full_name = $data->emp_firstname;
            } else {
                $full_name = " ";
            }

            //Designation
            if (isset($data->designation_title)) {
                $designation = $data->designation_title;
            } else {
                $designation = " ";
            }

            //Department
            if (isset($data->department_title)) {
                $department_title = $data->department_title;
            } else {
                $department_title = " ";
            }

            //Subsection
            if (isset($data->subsection_title)) {
                $subsection_title = $data->subsection_title;
            } else {
                $subsection_title = " ";
            }

            //Date of join
            if (isset($data->emp_dateofjoin)) {
                $date_of_join = $data->emp_dateofjoin;
            } else {
                $date_of_join = " ";
            }

            //Staff grade
            if (isset($data->staffgrade_title)) {
                $staff_grade = $data->staffgrade_title;
            } else {
                $staff_grade = " ";
            }

            //Calendar days
            if (isset($data->pa_calender_days)) {
                $calendar_days = $data->pa_calender_days;
            } else {
                $calendar_days = " ";
            }

            //Present
            if (isset($data->pa_total_present_finalized)) {
                $present = $data->pa_total_present_finalized;
            } else {
                $present = " ";
            }

            //Weekend
            if (isset($data->pa_total_weekend_finalized)) {
                $weekend = $data->pa_total_weekend_finalized;
            } else {
                $weekend = " ";
            }

            //Holiday
            if (isset($data->pa_total_holiday_finalized)) {
                $holiday = $data->pa_total_holiday_finalized;
            } else {
                $holiday = " ";
            }

            //Leave
            if (isset($data->pa_total_leave_finalized)) {
                $leave = $data->pa_total_leave_finalized;
            } else {
                $leave = " ";
            }

            //Absent
            if (isset($data->pa_total_absent_finalized)) {
                $absent = $data->pa_total_absent_finalized;
            } else {
                $absent = " ";
            }

            //Over time hours
            if (isset($data->payroll_additional_oth_finalized)) {
                $oth = $data->payroll_additional_oth_finalized;
            } else {
                $oth = " ";
            }

            $primary_elements_array = array();
            array_push($primary_elements_array, $emp_code, $emp_account_number, $full_name, $designation, $department_title, $subsection_title, $date_of_join, $staff_grade, $calendar_days, $present, $weekend, $holiday, $leave, $absent, $oth);

            $sec_dynamic_query = "SELECT 
            p.*, psh.PSH_show_in_tmp_mod, psh.PSH_is_gross
            FROM
            payroll p,
            payroll_salary_header psh
            WHERE
            payroll_emp_code = '$emp_code'
            AND payroll_salary_month = '$month'
            AND payroll_salary_year = '$year'
            AND company_id = '$company_id' AND p.PES_PSH_id = psh.PSH_id";
            $sec_dynamic_query_exec = $con->QueryResult($sec_dynamic_query);

            foreach ($sec_dynamic_query_exec as $second_data) {
                if ($second_data->PSH_show_in_tmp_mod == 'yes') {

                    if (isset($second_data->payroll_salary_finalized)) {
                        $salary_amount = $second_data->payroll_salary_finalized;
                    } else {
                        $salary_amount = " ";
                    }

                    //Dynamic salary basic component  
                    array_push($primary_elements_array, $salary_amount);
                }

                if ($second_data->PSH_is_gross == 'yes') {
                    $gross_salary_amount = $second_data->payroll_salary_finalized;
                }
            }

            $total_earning += $gross_salary_amount;

            //Over time payment
            if (isset($data->payroll_additional_ot_finalized)) {
                $over_time_payment = round($data->payroll_additional_ot_finalized);
            } else {
                $over_time_payment = " ";
            }

            $total_earning += $over_time_payment;

            array_push($primary_elements_array, $over_time_payment);


            //Basic add category
            $addidtion_s_component = array();
            $addidtion_s_component = $con->SelectAll("payroll_salary_header");
            if (count($addidtion_s_component) > 0) {
                foreach ($addidtion_s_component as $add_data) {
                    //Build array component for add category
                    if ($add_data->PSH_display_on == 'add') {
                        $header_id = $add_data->PSH_id;
                        $condition = "payroll_salary_year='$year' AND payroll_salary_month='$month' AND payroll_emp_code='$emp_code' AND PES_PSH_id='$header_id'";
                        $additional_salaries = $con->SelectAllByCondition("payroll", $condition);
                        if (count($additional_salaries) > 0) {
                            if (isset($additional_salaries{0}->payroll_salary_finalized)) {
                                $additional_salary = $additional_salaries{0}->payroll_salary_finalized;
                            } else {
                                $additional_salary = " ";
                            }
                        } else {
                            $additional_salary = " ";
                        }
                        $total_earning += $additional_salary;
                        array_push($primary_elements_array, $additional_salary);
                    }
                }
            }

            array_push($primary_elements_array, $total_earning);

            //Deduct category payment
            $addidtion_s_component = array();
            $addidtion_s_component = $con->SelectAll("payroll_salary_header");
            if (count($addidtion_s_component) > 0) {
                foreach ($addidtion_s_component as $add_data) {

                    //Build array component for deduct category
                    if ($add_data->PSH_display_on == 'deduct') {
                        $header_id = $add_data->PSH_id;
                        $condition = "payroll_salary_year='$year' AND payroll_salary_month='$month' AND payroll_emp_code='$emp_code' AND PES_PSH_id='$header_id'";
                        $deductive_salaries = $con->SelectAllByCondition("payroll", $condition);
                        if (count($deductive_salaries) > 0) {
                            if (isset($deductive_salaries{0}->payroll_salary_finalized)) {
                                //payment exist
                                $deductive_salary = $deductive_salaries{0}->payroll_salary_finalized;
                            } else {
                                //Payment doesn't exist
                                $deductive_salary = " ";
                            }
                        } else {
                            //Payment is not defined in the payroll table for
                            $deductive_salary = " ";
                        }
                        $total_deduction += $deductive_salary;
                        array_push($primary_elements_array, round($deductive_salary));
                    }
                }
            }

            //Deductible payment fixed component :: provident fund
            if (isset($data->payroll_additional_pf_finalized)) {
                $provident_fund = round($data->payroll_additional_pf_finalized);
            } else {
                $provident_fund = " ";
            }

            //Advance
            if (isset($data->payroll_additional_advance_finalized)) {
                $advance = round($data->payroll_additional_advance_finalized);
            } else {
                $advance = " ";
            }

            //PF Loan
            if (isset($data->pa_pf_loan_finalized)) {
                $pf_loan = round($data->pa_pf_loan_finalized);
            } else {
                $pf_loan = " ";
            }


            //Tax
            if (isset($data->payroll_additional_tax_finalized)) {
                $tax = round($data->payroll_additional_tax_finalized);
            } else {
                $tax = " ";
            }

            //Absent cut
            if (isset($data->pa_absent_deduction_finalized)) {
                $absent_cut = round($data->pa_absent_deduction_finalized);
            } else {
                $absent_cut = " ";
            }

            //Leave cut
            if (isset($data->pa_leave_deduction_finalized)) {
                $leave_cut = round($data->pa_leave_deduction_finalized);
            } else {
                $leave_cut = " ";
            }

            //Total deduction
            $total_deduction = $provident_fund + $advance + $pf_loan + $tax + $absent_cut + $leave_cut;
            array_push($primary_elements_array, $provident_fund, $advance, $pf_loan, $tax, round($absent_cut), $leave_cut, $total_deduction);


            //Net salary
            if (isset($data->pa_net_salary_finalized)) {
                $net_salary = round($data->pa_net_salary_finalized);
            } else {
                $net_salary = " ";
            }

            $remarks = " ";

            array_push($primary_elements_array, $net_salary, $remarks);
            array_push($master_array, $primary_elements_array);
        }

        //Merge header array with the master array
        array_unshift($master_array, $sub_header_array);

        $count = count($master_array);
        $countCol = count($master_array[0]);

        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
        $rowCount = 0;

        //Collect company info
        $companies = $con->SelectAllByCondition("company", "company_id='$company_id'");
        $company_title = $companies{0}->company_title;

        //Collect salary period
        $salary_periods = $con->SelectAllByCondition("payroll_additional", "payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' AND company_id='$company_id' LIMIT 0,1");
        $pa_start_date = $salary_periods{0}->pa_start_date;
        $pa_end_date = $salary_periods{0}->pa_end_date;

        //Find month name
        $salary_month = date("F", strtotime($pa_end_date));


        for ($i = 1; $i <= $count; $i++) {
            for ($j = 0; $j <= $countCol - 1; $j++) {

                $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
                $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Salary Year: $year");
                $cWorkSheet->setCellValueByColumnAndRow(0, 3, "Salary Month: $salary_month");
                $cWorkSheet->setCellValueByColumnAndRow(0, 4, "Salary Period: $pa_start_date  TO $pa_end_date");

                $cWorkSheet->setCellValueByColumnAndRow($j, $i + 6, $master_array["$rowCount"]["$j"]);
            }
            $rowCount++;
        }


        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
        $filename = $company_id . rand(0, 9999999) . "Salary_Sheet.xlsx";
        $objWriter->save("$filename");
        header("location:$filename");
    }
}

/*
 * Following excel file is a different version
 * Additional salary component will be added to 
 * other addition as a single category in the excel file
 * Same rule will be applicable to the deduction category.
 */

if (isset($_POST["summerized_excel"])) {

//Extract post array
    extract($_POST);

    //set form validations
    if ($company_id == '') {
        $err = 'Please select a company.';
    } else if ($year == '') {
        $err = 'Please select a year.';
    } else if ($month == '') {
        $err = "Please select a year.";
    } else {

        /*
         * Generate sub-header array
         */
        $sub_header_array = array("Emp.", "AC NO", "Name", "Designation", "Department", "Section", "DOJ", "Grade", "Cal", "Attn", "Weekend", "Festival/holiday", "Leave", "Absent", "OT");
        $sub_header_array_two = array();

//Collect salary headers displayed in employee module.
        $salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_show_in_tmp_mod='yes'");
        if (count($salary_headers) > 0) {
            foreach ($salary_headers as $header) {
                //Add emp module salary headers to sub header array
                array_push($sub_header_array, $header->PSH_header_title);
            }
        }

//Add OT earning
        array_push($sub_header_array, "OT Earning");

//Add other additional header
        array_push($sub_header_array, "Other Addition");

//Total additions
        array_push($sub_header_array, "Total Earning");

//Add other deductions as a sum
        array_push($sub_header_array, "Other Deduction");

//Add fixed deductibles
        array_push($sub_header_array, "Provident Fund", "Advance", "PF Loan", "Tax", "Absent Cut", "Leave Cut", "Total Deduction");

//Add net payable and remarks
        array_push($sub_header_array, "Net Payable", "Remarks");


//Generate main array
        $detailed_query = "SELECT 
        e.emp_code,
        e.emp_dateofjoin,
        e.emp_account_number,
        e.emp_firstname,
        d.designation_title,
        dep.department_title,
        sg.staffgrade_title,
        sub.subsection_title,
        pa.*
        FROM
        payroll_additional AS pa
        LEFT JOIN
        tmp_employee e ON e.emp_code = pa.payroll_additional_emp_code
        LEFT JOIN
        designation d ON e.emp_designation = d.designation_id
        LEFT JOIN
        department dep ON dep.department_id = e.emp_department
        LEFT JOIN
        staffgrad sg ON sg.staffgrade_id = e.emp_staff_grade
        LEFT JOIN
        subsection AS sub ON sub.subsection_id = e.emp_subsection
        WHERE
        pa.payroll_additional_salary_year = '$year'
        AND pa.payroll_additional_salary_month = '$month'
        AND pa.company_id = '$company_id'";

        $query_result = $con->QueryResult($detailed_query);
        $master_array = array();



        foreach ($query_result as $data) {

            $total_earning = 0;
            $total_deduction = 0;
            $other_addition = 0;
            $other_deduction = 0;

            $emp_code = $data->payroll_additional_emp_code;

            //Bank Account Number
            if (isset($data->emp_account_number)) {
                $emp_account_number = $data->emp_account_number;
            } else {
                $emp_account_number = " ";
            }

            //Full Name
            if (isset($data->emp_firstname)) {
                $full_name = $data->emp_firstname;
            } else {
                $full_name = " ";
            }

            //Designation
            if (isset($data->designation_title)) {
                $designation = $data->designation_title;
            } else {
                $designation = " ";
            }

            //Department
            if (isset($data->department_title)) {
                $department_title = $data->department_title;
            } else {
                $department_title = " ";
            }

            //Subsection
            if (isset($data->subsection_title)) {
                $subsection_title = $data->subsection_title;
            } else {
                $subsection_title = " ";
            }

            //Date of join
            if (isset($data->emp_dateofjoin)) {
                $date_of_join = $data->emp_dateofjoin;
            } else {
                $date_of_join = " ";
            }

            //Staff grade
            if (isset($data->staffgrade_title)) {
                $staff_grade = $data->staffgrade_title;
            } else {
                $staff_grade = " ";
            }

            //Calendar days
            if (isset($data->pa_calender_days)) {
                $calendar_days = $data->pa_calender_days;
            } else {
                $calendar_days = " ";
            }

            //Present
            if (isset($data->pa_total_present_finalized)) {
                $present = $data->pa_total_present_finalized;
            } else {
                $present = " ";
            }

            //Weekend
            if (isset($data->pa_total_weekend_finalized)) {
                $weekend = $data->pa_total_weekend_finalized;
            } else {
                $weekend = " ";
            }

            //Holiday
            if (isset($data->pa_total_holiday_finalized)) {
                $holiday = $data->pa_total_holiday_finalized;
            } else {
                $holiday = " ";
            }

            //Leave
            if (isset($data->pa_total_leave_finalized)) {
                $leave = $data->pa_total_leave_finalized;
            } else {
                $leave = " ";
            }

            //Absent
            if (isset($data->pa_total_absent_finalized)) {
                $absent = $data->pa_total_absent_finalized;
            } else {
                $absent = " ";
            }

            //Over time hours
            if (isset($data->payroll_additional_oth_finalized)) {
                $oth = $data->payroll_additional_oth_finalized;
            } else {
                $oth = " ";
            }

            /*
             * Staff grade priority range in permission for logged in employee
             * Staff grade priority for employee in the loop.
             */

            $priority = '';
            $staff_grade_result = array();
            $generated_date = $year;
            $generated_date .= "-";
            $generated_date .= $month;
            $generated_date .= "-";
            $generated_date .= "01";
            $first_day = date("y-m-d", strtotime($generated_date));
            $gross_salary = '';
            $net_salary = '';

            $staff_grade_query = "
            SELECT
            esg.es_staff_grade_id, sg.staffgrade_title, sg.priority
            FROM
            emp_staff_grade esg
            LEFT JOIN staffgrad sg ON sg.staffgrade_id = esg.es_staff_grade_id
            WHERE
            es_emp_code = '$emp_code'
            AND (
                (
                    es_effective_start_date <= '$first_day'
                    AND es_effective_end_date >= '$first_day'
                    )
OR (
    es_effective_start_date <= '$first_day'
    AND es_effective_end_date = '0000-00-00'
    )
) LIMIT 0,1
";
$staff_grade_result = $con->QueryResult($staff_grade_query);

if (count($staff_grade_result) > 0) {
    $priority = $staff_grade_result{0}->priority;
}

$primary_elements_array = array();
array_push($primary_elements_array, $emp_code, $emp_account_number, $full_name, $designation, $department_title, $subsection_title, $date_of_join, $staff_grade, $calendar_days, $present, $weekend, $holiday, $leave, $absent, $oth);

$sec_dynamic_query = "SELECT 
p.*, psh.PSH_show_in_tmp_mod, psh.PSH_is_gross
FROM
payroll p,
payroll_salary_header psh
WHERE
payroll_emp_code = '$emp_code'
AND payroll_salary_month = '$month'
AND payroll_salary_year = '$year'
AND company_id = '$company_id' AND p.PES_PSH_id = psh.PSH_id";
$sec_dynamic_query_exec = $con->QueryResult($sec_dynamic_query);

if (count($sec_dynamic_query_exec) > 0) {
    foreach ($sec_dynamic_query_exec as $second_data) {
        if ($second_data->PSH_show_in_tmp_mod == 'yes') {

            if (isset($second_data->payroll_salary_finalized)) {
                $salary_amount = $second_data->payroll_salary_finalized;
            } else {
                $salary_amount = " ";
            }

                        //Check for priority staff grade of logged in employee
            if ($priority >= $range_start && $priority <= $range_end) {
                            //Dynamic salary basic component  
                array_push($primary_elements_array, $salary_amount);
            } else if ($logged_emp_code == $emp_code) {
                            //Dynamic salary basic component  
                array_push($primary_elements_array, $salary_amount);
            } else {
                array_push($primary_elements_array, " ");
            }
        }

        if ($second_data->PSH_is_gross == 'yes') {
            $gross_salary_amount = $second_data->payroll_salary_finalized;
        }
    }
}


$total_earning += $gross_salary_amount;

            //Over time payment
if (isset($data->payroll_additional_ot_finalized)) {
    $over_time_payment = round($data->payroll_additional_ot_finalized);
} else {
    $over_time_payment = " ";
}

$total_earning += $over_time_payment;

            //Check for priority staff grade of logged in employee
if ($priority >= $range_start && $priority <= $range_end) {
                //Dynamic salary basic component  
    array_push($primary_elements_array, $over_time_payment);
} else if ($logged_emp_code == $emp_code) {
                //Dynamic salary basic component  
    array_push($primary_elements_array, $over_time_payment);
} else {
    array_push($primary_elements_array, " ");
}

            //Basic add category
$addidtion_s_component = array();
$addidtion_s_component = $con->SelectAll("payroll_salary_header");
if (count($addidtion_s_component) > 0) {
    foreach ($addidtion_s_component as $add_data) {
                    //Build array component for add category
        if ($add_data->PSH_display_on == 'add') {
            $header_id = $add_data->PSH_id;
            $condition = "payroll_salary_year='$year' AND payroll_salary_month='$month' AND payroll_emp_code='$emp_code' AND PES_PSH_id='$header_id'";
            $additional_salaries = $con->SelectAllByCondition("payroll", $condition);

            if (count($additional_salaries) > 0) {
                if (isset($additional_salaries{0}->payroll_salary_finalized)) {
                    $additional_salary = $additional_salaries{0}->payroll_salary_finalized;
                } else {
                    $additional_salary = " ";
                }
            } else {
                $additional_salary = " ";
            }


                        //Generate all additional salary as other additional salary
            $other_addition += $additional_salary;
            $total_earning += $additional_salary;
        }
    }
}

            //Check for priority staff grade of logged in employee
if ($priority >= $range_start && $priority <= $range_end) {
    array_push($primary_elements_array, $other_addition);
} else if ($logged_emp_code == $emp_code) {
    array_push($primary_elements_array, $other_addition);
} else {
    array_push($primary_elements_array, " ");
}

            //array_push($primary_elements_array, $other_addition);
            //
             //Check for priority staff grade of logged in employee
if ($priority >= $range_start && $priority <= $range_end) {
    array_push($primary_elements_array, $total_earning);
} else if ($logged_emp_code == $emp_code) {
    array_push($primary_elements_array, $total_earning);
} else {
    array_push($primary_elements_array, " ");
}
            //array_push($primary_elements_array, $total_earning);
            //Deduct category payment
$addidtion_s_component = array();
$addidtion_s_component = $con->SelectAll("payroll_salary_header");
if (count($addidtion_s_component) > 0) {
    foreach ($addidtion_s_component as $add_data) {

                    //Build array component for deduct category
        if ($add_data->PSH_display_on == 'deduct') {
            $header_id = $add_data->PSH_id;
            $condition = "payroll_salary_year='$year' AND payroll_salary_month='$month' AND payroll_emp_code='$emp_code' AND PES_PSH_id='$header_id'";
            $deductive_salaries = $con->SelectAllByCondition("payroll", $condition);
            if (count($deductive_salaries) > 0) {
                if (isset($deductive_salaries{0}->payroll_salary_finalized)) {
                                //payment exist
                    $deductive_salary = $deductive_salaries{0}->payroll_salary_finalized;
                } else {
                                //Payment doesn't exist
                    $deductive_salary = " ";
                }
            } else {
                            //Payment is not defined in the payroll table for
                $deductive_salary = " ";
            }

            $other_deduction += $deductive_salary;
            $total_deduction += $deductive_salary;
                        //array_push($primary_elements_array, $deductive_salary);
        }
    }
}

if ($priority >= $range_start && $priority <= $range_end) {
    array_push($primary_elements_array, $other_deduction);
} else if ($logged_emp_code == $emp_code) {
    array_push($primary_elements_array, $other_deduction);
} else {
    array_push($primary_elements_array, " ");
}

            //array_push($primary_elements_array, $other_deduction);
            //Deductible payment fixed component :: provident fund
if (isset($data->payroll_additional_pf_finalized)) {
    $provident_fund = round($data->payroll_additional_pf_finalized);
} else {
    $provident_fund = " ";
}

            //Advance
if (isset($data->payroll_additional_advance_finalized)) {
    $advance = round($data->payroll_additional_advance_finalized);
} else {
    $advance = " ";
}

            //PF Loan
if (isset($data->pa_pf_loan_finalized)) {
    $pf_loan = $data->pa_pf_loan_finalized;
} else {
    $pf_loan = " ";
}

            //Tax
if (isset($data->payroll_additional_tax_finalized)) {
    $tax = round($data->payroll_additional_tax_finalized);
} else {
    $tax = " ";
}

            //Absent cut
if (isset($data->pa_absent_deduction_finalized)) {
    $absent_cut = round($data->pa_absent_deduction_finalized);
} else {
    $absent_cut = " ";
}

            //Leave cut
if (isset($data->pa_leave_deduction_finalized)) {
    $leave_cut = round($data->pa_leave_deduction_finalized);
} else {
    $leave_cut = " ";
}

            //Total deduction
$total_deduction = $provident_fund + $advance + $pf_loan + $tax + $absent_cut + $leave_cut;
if ($priority >= $range_start && $priority <= $range_end) {
    array_push($primary_elements_array, $provident_fund, $advance, $pf_loan, $tax, $absent_cut, $leave_cut, $total_deduction);
} else if ($logged_emp_code == $emp_code) {
    array_push($primary_elements_array, $provident_fund, $advance, $pf_loan, $tax, $absent_cut, $leave_cut, $total_deduction);
} else {
    array_push($primary_elements_array, " ", " ", " ", " ", " ", " ", " ");
}

            //array_push($primary_elements_array, $provident_fund, $advance, $tax, $absent_cut, $leave_cut, $total_deduction);
            //Net salary
if (isset($data->pa_net_salary_finalized)) {
    $net_salary = round($data->pa_net_salary_finalized);
} else {
    $net_salary = " ";
}

$remarks = " ";
            //Total deduction
if ($priority >= $range_start && $priority <= $range_end) {
    array_push($primary_elements_array, $net_salary, $remarks);
} else if ($logged_emp_code == $emp_code) {
    array_push($primary_elements_array, $net_salary, $remarks);
} else {
    array_push($primary_elements_array, " ", " ");
}

            //array_push($primary_elements_array, $net_salary, $remarks);
array_push($master_array, $primary_elements_array);
}




        //Merge header array with the master array
array_unshift($master_array, $sub_header_array);
$count = count($master_array);
$countCol = count($master_array[0]);

$createPHPExcel = new PHPExcel();
$cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
$rowCount = 0;

        //Collect company info
$companies = $con->SelectAllByCondition("company", "company_id='$company_id'");
$company_title = $companies{0}->company_title;

        //Collect salary period
$salary_periods = $con->SelectAllByCondition("payroll_additional", "payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year' AND company_id='$company_id' LIMIT 0,1");
$pa_start_date = $salary_periods{0}->pa_start_date;
$pa_end_date = $salary_periods{0}->pa_end_date;

        //Find month name
$salary_month = date("F", strtotime($pa_end_date));


for ($i = 1; $i <= $count; $i++) {
    for ($j = 0; $j <= $countCol - 1; $j++) {

        $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
        $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Salary Year: $year");
        $cWorkSheet->setCellValueByColumnAndRow(0, 3, "Salary Month: $salary_month");
        $cWorkSheet->setCellValueByColumnAndRow(0, 4, "Salary Period: $pa_start_date  TO $pa_end_date");

        $cWorkSheet->setCellValueByColumnAndRow($j, $i + 6, $master_array["$rowCount"]["$j"]);
    }
    $rowCount++;
}


$objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
$filename = $company_id . rand(0, 9999999) . "Salary_Sheet.xlsx";
$objWriter->save("$filename");
header("location:$filename");
}
}
?>

<?php include '../view_layout/header_view.php'; ?>

<style type="text/css">   
    .k-edit,.k-delete,.k-add {
        margin-top: -2px !important;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {
        $("#company").kendoDropDownList();
    });
</script>

<!--Error/success message-->
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

        <div class="col-md-4">
            <label for="Full name">Year:</label><br/> 
            <input id="year1" name="year" style="width: 80%;" value="<?php echo $year; ?>" />
        </div>

    <!--<div class="col-md-4" style="padding-left: 0px;">
        <label for="Start Date">Start Date:</label><br/>
        <input type="text" id="start_date" class="emp_datepicker" value="<?php // echo $start_date;                                                                                                                                                                                                                                                                                                              ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
    </div>

    <div class="col-md-4">
        <label for="Start Date">End Date:</label><br/>
        <input id="end_date" type="text" class="emp_datepicker" value="<?php // echo $end_date;                                                                                                                                                                                                                                                                                                              ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
    </div>-->

    <script>
        $(document).ready(function() {
            $("#start_date").kendoDatePicker();
            $("#end_date").kendoDatePicker();
        });
    </script>

    <div class="col-md-4">
        <label for="Full name">Month:</label> <br />
        <input id="month1" name="month" style="width: 80%;" value="<?php echo $month; ?>" />
    </div>
    <div class="clearfix"></div>
    <br/>
    <hr />

    <input type="submit" name="salary_generate" value="Generate Salary" class="k-textbox">
    <?php if ($con->hasPermissionExport($permission_id) == "yes"): ?>
        <!--<input type="submit" name="generate_excel" value="Report (Detailed)" class="k-textbox" style="">-->
        <input type="submit" name="summerized_excel" value="Report (Summary)" class="k-textbox">
    <?php endif; ?>

    <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
        <?php if (isset($_POST["salary_generate"]) && $errors == 0 && $is_processed[0]->payroll_additional_id != '' && $is_lockedres[0]->slm_is_locked != 'yes' && !empty($is_processed)) { ?>
        <input type="submit" class="k-button pull-right" name="salary_lock" value="Lock Salary Process" ><br/><br />
        <?php } ?>
    <?php endif; ?>

</form>
<!--Script to trigger this page again with this ID -->
<script type="text/javascript">
    $(document).ready(function() {
        $('#month1').change(function() {
            //window.location = "salary_list.php?month=" + $(this).val() + "&com_id=" + $("#company").val() + "year=" + $("#month1").val();
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
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

<?php if (isset($_POST["salary_generate"])) { ?>
<?php
extract($_POST);
$priority = '';
$staff_grade_result = array();
$generated_date .= $year;
$generated_date .= "-";
$generated_date .= $month;
$generated_date .= "-";
$generated_date .= "01";
$first_day = date("Y-m-d", strtotime($generated_date));
$gross_salary = '';
$net_salary = '';
?>
<div id="example" class="k-content">

    <table id="grid" style="table-layout: fixed; ">
        <colgroup>
        <col style="width:100px"/>
        <col style="width:150px" />
        <col style="width:150px" />
        <col style="width:150px" />
        <col style="width:150px" />
        <col style="width:150px" />
        <col style="width:150px" />
        <col style="width:170px" />
    </colgroup>
    <thead>
        <tr>
            <th data-field="  ">Employee code</th>
            <th data-field="emp_firstname">Employee Name</th>
            <th data-field="department_title">Department</th>
            <th data-field="designation_title">Designation</th>
            <th data-field="staffgrade_title">Staff Grade</th>
            <th data-field="Gross Salary">Gross Salary</th>
            <th data-field="Net Salary">Net Salary</th>
            <th data-field="Action">Action </th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (count($salary_info) >= 1):
            foreach ($salary_info as $salary):
                ?>
            <tr>
                <?php
                            /*
                             * Find this employee's priority
                             * Based on the selected month, 
                             * a date will be generated, against which
                             * employee's priority will be fetched
                             * Then it will be compared to logged in employee's
                             * priority range
                             */
                            $staff_grade_query = "
                            SELECT
                            esg.es_staff_grade_id, sg.staffgrade_title, sg.priority
                            FROM
                            emp_staff_grade esg
                            LEFT JOIN staffgrad sg ON sg.staffgrade_id = esg.es_staff_grade_id
                            WHERE
                            es_emp_code = '$salary->emp_code'
                            AND (
                                (
                                    es_effective_start_date <= '$first_day'
                                    AND es_effective_end_date >= '$first_day'
                                    )
OR (
    es_effective_start_date <= '$first_day'
    AND es_effective_end_date = '0000-00-00'
    )
) LIMIT 0,1
";

$staff_grade_result = $con->QueryResult($staff_grade_query);
$priority = $staff_grade_result{0}->priority;

if (($priority >= $range_start && $priority <= $range_end) || ($logged_emp_code == $emp_code)) {
    $gross_salary = round($salary->payroll_salary_finalized);
    $net_salary = round($salary->pa_net_salary_finalized);
} else {
    $gross_salary = '';
    $net_salary = '';
}
?>

<td><?php echo $salary->emp_code; ?></td>
<td><?php echo $salary->emp_firstname; ?> </td>
<td><?php echo $salary->designation_title; ?> </td>
<td><?php echo $salary->department_title; ?> </td>
<td><?php echo $salary->staffgrade_title; ?> </td>

<td><?php echo $gross_salary; ?></td>
<td><?php echo $net_salary; ?></td>

<td role="gridcell">
    <?php if ($con->hasPermissionView($permission_id) == "yes"): ?>
        <?php if ($priority >= $range_start && $priority <= $range_end): ?>
            <a style="text-decoration:none;" target="_blank" class="k-button k-button-icontext k-grid-edit" href="salary_detail.php?empl_code=<?php echo base64_encode($salary->emp_code); ?>&year=<?php echo $salary->payroll_salary_year; ?>&month=<?php echo $salary->payroll_salary_month; ?>&company_id=<?php echo $salary->company_id; ?>&permission_id=<?php echo $permission_id; ?>">
                <span class="k-edit"></span>Details
            </a>
        <?php elseif ($logged_emp_code == $salary->emp_code): ?>
            <a style="text-decoration:none;" style="text-decoration:none;" target="_blank" class="k-button k-button-icontext k-grid-edit" href="salary_detail.php?empl_code=<?php echo base64_encode($salary->emp_code); ?>&year=<?php echo $salary->payroll_salary_year; ?>&month=<?php echo $salary->payroll_salary_month; ?>&company_id=<?php echo $salary->company_id; ?>&permission_id=<?php echo $permission_id; ?>">
                <span class="k-edit"></span>Details
            </a>
        <?php else: ?>
            <a style="text-decoration:none;" class="k-button k-button-icontext k-grid-edit" style="background-color:silver; text-decoration: none;">
                <span class="k-edit"></span>Details
            </a>
        <?php endif; ?>
    <?php endif; ?>
</td>
</tr>
<?php
endforeach;
endif;
?> 
</tbody>
</table>
<script>
    $(document).ready(function() {
        $("#grid").kendoGrid({
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSize: 10,
                pageSizes: true,
                pageSizes: [10, 20, 50]
            },
            filterable: true,
            sortable: true,
            groupable: true
        });
    });
</script>
</div>
<?php } ?>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>