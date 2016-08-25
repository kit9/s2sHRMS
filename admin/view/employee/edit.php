<?php
session_start();
error_reporting(1);
//Importing class library
include ('../../config/class.config.php');

//Configuration classes
$con = new Config();

//Connection string
$open = $con->open();

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//initializing variables
$filename = '';
$emp_photo = '';
$emp_firstname = '';
$emp_lastname = '';
$emp_email = '';
$emp_designation = '';
$emp_department = '';
$emp_subsection = '';
$emp_dateofjoin = '';
$emp_staff_grade = '';
$emp_gross_salary = '';
$emp_location = '';
$emp_gender = '';
$emp_prop_confirmation_date = '';
$emp_dateofbirth = '';
$emp_bloodgroup = '';
$emp_address = '';
$emp_contact_number = '';
$emp_resignation_date = '';
$emp_replacement_of = '';
$emp_notes_salary_hub = '';
$emp_account_number = '';
$emp_bank_title = '';
$emp_remarks = '';
$emp_photo = '';
$emp_blood_group = '';
$emp_marital_status = '';
$emp_city = '';
$emp_contact_number_2 = '';
$emp_basic_salary = '';
$emp_hra = '';
$emp_transport = '';
$filename = '';
$emp_code = '';
$first_name_new = '';
$staffgrade_id = '';
$city_id = '';
$emp_medical = '';
$conveyance = '';
$special = '';
$lunch = '';
$others = '';
$emp_type = '';
$emp_password = '';
$user_type = '';
$supervisor_id = '';
$reporting_id = '';
$attendance_policy_id = '';
$shift_id = '';
$alternate_attn_policy_id = '';
$alternate_attn_company = '';
$implement_from_date = '';
$job_location = '';
$user_type_value = '';
$password = '';
$is_HOD = '';
$dept_effective_date = '';
$sub_effective_date = '';
$desig_from_date = '';
$grade_from_date = '';
$is_pf_eligible = '';
$pf_effective_from = '';
$pf_effective_from_date = '';
$pf_effective_from_date_value = '';

//Log out
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Declaring local variables
$resul = '';
$err = "";
$msg = '';

$uploadPath2 = '';
$uploadPath = '';

//S2S Specific Additional Records
$father_name = '';
$mother_name = '';
$national_id = '';
$emp_brother_name = '';
$emp_sister_name = '';
$emp_sister_occupation = '';
$emp_brother_occupation = '';
$emp_address_present_2 = '';
$emp_address_present_3 = '';
$emp_phone_personal_2 = '';
$emp_phone_personal_3 = '';
$emp_landphone_2 = '';
$emp_landphone_3 = '';
$indirect_supervisor_id = '';
$emp_nid_photo = '';
$id_type = '';

//Storing the employee ID fromm get
if (isset($_GET["emp_id"])) {
    $emp_id = $_GET["emp_id"];
}

$employee_cod = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");

$empl_code = $employee_cod[0]->emp_code;
$today = date("Y/m/d");
$sys_date = date_create($today);
$format_today = date_format($sys_date, 'Y-m-d');
$zero = "0000-00-00";

//Fetching employee info
$employees_query = "SELECT e.*,eg.*,ed.*,edp.*,esub.*,ec.*
FROM tmp_employee e
left join emp_company ec on ec.ec_emp_code=e.emp_code
left join emp_staff_grade eg on eg.es_emp_code=e.emp_code
left join emp_subsection esub on esub.esub_emp_code=e.emp_code
left join emp_designation ed on ed.edes_emp_code=e.emp_code
left join emp_department edp on edp.edept_emp_code=e.emp_code
WHERE e.emp_id='$emp_id'"
        . " AND ec.emp_company_id IN(SELECT max(emp_company_id) FROM emp_company where ec_emp_code='$empl_code')"
        . " OR eg.emp_staff_grade_id IN(SELECT max(emp_staff_grade_id) FROM emp_staff_grade where es_emp_code='$empl_code')"
        . " OR esub.emp_subsection_id IN(SELECT max(emp_subsection_id) FROM emp_subsection where esub_emp_code='$empl_code')"
        . " OR ed.emp_designation_id IN(SELECT max(emp_designation_id) FROM emp_designation where edes_emp_code='$empl_code')"
        . " OR edp.emp_department_id IN(SELECT max(emp_department_id) FROM emp_department where edept_emp_code='$empl_code')";

$employees = $con->QueryResult($employees_query);

foreach ($employees as $employee) {
    $emp_firstname = $employee->emp_firstname;
    $nominee_name = $employee->nominee_name;
    $emp_marital_status = $employee->emp_marital_status;
    $emp_code = $employee->emp_code;
    $company_id = $employee->ec_company_id;
    $emp_email_office = $employee->emp_email_office;
    $emp_email_personal = $employee->emp_email_personal;
    $emergency_contact_name = $employee->emergency_contact_name;
    $emergency_contact_phone = $employee->emergency_contact_phone;



    $emp_designation = $employee->edes_designation_id;
    $emp_department = $employee->edept_dept_id;
    $esub_subsec_id = $employee->esub_subsec_id;  // $emp_subsection
    $emp_dateofjoin = $employee->emp_dateofjoin;
    $emp_staff_grade = $employee->es_staff_grade_id;
    $staffgrade_id = $employee->emp_staff_grade;
    $supervisor_id = $employee->supervisor_id;
    $reporting_id = $employee->reporting_id;

    //Prepare all the effective dates :; department
    if ($employee->edept_effective_start_date > 0) {
        $dept_date_raw = date_create($employee->edept_effective_start_date);
        $dept_effective_date = date_format($dept_date_raw, 'm/d/Y');
    } else {
        $dept_effective_date = '';
    }

    //Subsection
    if ($employee->esub_effective_start_date > 0) {
        $sub_date_raw = date_create($employee->esub_effective_start_date);
        $sub_effective_date = date_format($sub_date_raw, 'm/d/Y');
    } else {
        $sub_effective_date = '';
    }

    //Designationn
    if ($employee->edes_effective_start_date > 0) {
        $desg_date_raw = date_create($employee->edes_effective_start_date);
        $desig_from_date = date_format($desg_date_raw, 'm/d/Y');
    } else {
        $desig_from_date = '';
    }

    //staff grade
    if ($employee->es_effective_start_date > 0) {
        $grad_date_raw = date_create($employee->es_effective_start_date);
        $grade_from_date = date_format($grad_date_raw, 'm/d/Y');
    } else {
        $grade_from_date = '';
    }

    $attendance_policy_id = $employee->attendance_policy_id;
    $emp_gender = $employee->emp_gender;
    $emp_prop_confirmation_date = $employee->emp_prop_confirmation_date;
    $emp_dateofbirth = $employee->emp_dateofbirth;
    $emp_blood_group = $employee->emp_blood_group;

    //Contact details
    $emp_address_present = $employee->emp_address_present;
    $emp_address_parmanent = $employee->emp_address_permanent;
    $emp_phone_personal = $employee->emp_phone_personal;
    $emp_phone_company = $employee->emp_phone_company;
    $emp_landphone = $employee->emp_landphone;


    $emp_resignation_date = $employee->emp_resignation_date;
    $emp_replacement_of = $employee->emp_replacement_of;
    $emp_notes_salary_hub = $employee->emp_notes_salary_hub;
    $emp_bank_title = $employee->emp_bank_title;
    $emp_remarks = $employee->emp_remarks;
    $emp_photo = $employee->emp_photo;

    $emp_city = $employee->emp_city;
    $emp_account_number = $employee->emp_account_number;
    $country_id = $employee->country;
    $city_id = $employee->city;
    $job_location = $employee->job_location;
    $user_type_value = $employee->user_type;
    $password = $employee->password;
    $is_HOD = $employee->is_HOD;
    $is_pf_eligible = $employee->is_pf_eligible;
    $is_ot_eligible = $employee->is_ot_eligible;
    $pf_effective_from_date_value = date("m/d/Y", strtotime($employee->pf_effective_from));

    if ($employee->wedding_date > 0) {
        $wedding_date_value = date("m/d/Y", strtotime($employee->wedding_date));
    } else {
        $wedding_date_value = '';
    }

    $family_member = $employee->family_member;
    $no_of_children = $employee->no_of_children;
    $spouse_name = $employee->spouse_name;

    //Fetch role information
    $roles = $con->SelectAllByCondition("role_assign", "emp_code='$emp_code'");
    if (count($roles) > 0) {
        $role_assign_id = $roles{0}->role_assign_id;
        $em_role_id = $roles{0}->em_role_id;
    } else {
        $role_assign_id = '';
    }

    $tiffin_allowance_eligible = $employee->tiffin_allowance_eligible;
    $night_allowance_eligible = $employee->night_allowance_eligible;

    //S2S Specific Additional Records
    $father_name = $employee->father_name;
    $mother_name = $employee->mother_name;
    $id_no = $employee->national_id;
    $emp_brother_name = $employee->emp_brother_name;
    $emp_sister_name = $employee->emp_sister_name;
    $emp_sister_occupation = $employee->emp_sister_occupation;
    $emp_brother_occupation = $employee->emp_brother_occupation;
    $emp_address_present_2 = $employee->emp_address_present_2;
    $emp_address_present_3 = $employee->emp_address_present_3;
    $emp_phone_personal_2 = $employee->emp_phone_personal_2;
    $emp_phone_personal_3 = $employee->emp_phone_personal_3;
    $emp_landphone_2 = $employee->emp_landphone_2;
    $emp_landphone_3 = $employee->emp_landphone_3;
    $indirect_supervisor_id = $employee->indirect_supervisor_id;
    $emp_nid_photo = $employee->emp_nid_photo;
    $id_type = $employee->id_type;
    
}

/*
 * Collect logged in employee
 * Look at staff grade permissions
 * Hide salaries of staff grades out of range
 */
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

/** End of priority definition
 */
//Find this employee's priority
$priority = '';
$emp_staff_grade_info = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
if (count($emp_staff_grade_info) > 0) {
    $priority = $emp_staff_grade_info{0}->priority;
}


//Find the gross salary information
$existing_gross = array();
$existing_gross = $con->SelectAllByCondition("payroll_employee_salary", "PES_employee_code='$emp_code' AND PES_is_gross='yes'");

if (count($existing_gross) > 0) {
    $PES_id = $existing_gross{0}->PES_id;
    $emp_gross_salary = $existing_gross{0}->PES_gross_salary;
}

if ($emp_dateofbirth > 0) {
    $cdob1 = date_create($emp_dateofbirth);
    $bdate1 = date_format($cdob1, 'm/d/Y');
} else {
    $bdate1 = "";
}

if ($emp_dateofjoin > 0) {
    $doj1 = date_create($emp_dateofjoin);
    $jdate1 = date_format($doj1, 'm/d/Y');
} else {
    $jdate1 = '';
}

if ($emp_prop_confirmation_date > 0) {
    $pod1 = date_create($emp_prop_confirmation_date);
    $pdate = date_format($pod1, 'm/d/Y');
} else {
    $pdate = '';
}

//Format today
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');

$staffgrades = $con->SelectAll("staffgrad");
$reportings = $con->SelectAll("reporting_method");
$attendances = $con->SelectAll("attendance_policy");
$shifts = $con->SelectAll("shift_policy");

//Look if alternate attn data exists.
$alt_attn = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$formatted_today' ORDER BY implement_from_date DESC LIMIT 0,1");
if (count($alt_attn) > 0) {

    $alternate_attn_policy_id = $alt_attn{0}->alternate_attn_policy_id;
    $alternate_attn_company = $alt_attn{0}->alt_company_id;
    $implement_from_date = $alt_attn{0}->implement_from_date;

    if ($implement_from_date != '') {
        $raw_implement_from_date_as_value = date_create($alt_attn{0}->implement_from_date);
        $implement_from_date_as_value = date_format($raw_implement_from_date_as_value, 'm/d/Y');
    }
} else {
    //If doesnt exists, then set flag to 0
    $alt_exist_flag = "no";
}

//Look if alternate attn data exists.
$up_company = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date <= '$formatted_today'  ORDER BY ec_effective_start_date DESC LIMIT 0,1");

$ec_effective_start_date_raw = date_create($up_company{0}->ec_effective_start_date);
$ec_effective_start_date_as_value = date_format($ec_effective_start_date_raw, 'm/d/Y');

//Submitting the form //$emp_dateofbirth *******************************************************
// =============================================================================================

if (isset($_POST["emp_update"])) {
    extract($_POST);
    $arrSalHeadFix = array();
    $arrSalHeadOpt = array();
    $arrSalHeadOptID = array();
    $currentTime = date("Y-m-d H-i-s");

    //Populate HOD Checkbox value
    if ($is_HOD == 'on') {
        $is_HOD = 'yes';
    } else {
        $is_HOD = '';
    }

    //Populate pf Checkbox value
    if ($is_pf_eligible == 'on') {
        $is_pf_eligible = 'yes';
    } else {
        $is_pf_eligible = '';
    }

    //Populate ot checkbox value
    if ($is_ot_eligible == 'on') {
        $is_ot_eligible = 1;
    } else {
        $is_ot_eligible = '';
    }

    //Populate night allowance check box value
    if ($night_allowance_eligible == 'on') {
        $night_allowance_eligible = 1;
    } else {
        $night_allowance_eligible = 0;
    }

    //Populate night allowance check box value
    if ($tiffin_allowance_eligible == 'on') {
        $tiffin_allowance_eligible = 1;
    } else {
        $tiffin_allowance_eligible = 0;
    }

    //creating an custom array of fixed salary header and their value
    foreach ($_POST AS $key => $val) {
        $key_brkdwn = explode('_', $key);
        if ($key_brkdwn[0] == "headerfix") {
            $arrSalHeadFix[]['id'] = $key_brkdwn[1];
            $arrSalHeadFix[(count($arrSalHeadFix) - 1)]['value'] = $val;
        }
    }

    //creating an custom array of optional salary header and their value
    foreach ($_POST AS $key => $val) {
        $key_brkdwn = explode('_', $key);
        if ($key_brkdwn[0] == "headerchk" AND $val == "yes") {
            $arrSalHeadOpt[]['id'] = $key_brkdwn[1];
            $arrSalHeadOptID[] = $key_brkdwn[1];
            $arrSalHeadOpt[(count($arrSalHeadOpt) - 1)]['value'] = $_POST['headeropt_' . $key_brkdwn[1]];
        }
    }

    //Format pf effective date from
    if ($pf_effective_from != '') {
        $pf_effective_from_date = date("Y-m-d", strtotime($pf_effective_from));
    }

    if ($wedding_date != '') {
        $wedding_date = date("Y-m-d", strtotime($wedding_date));
        $wedding_date_value = date("m/d/Y", strtotime($wedding_date));
    } else {
        $wedding_date = '';
    }

    //Date of birth
    if ($emp_dateofbirth != '') {
        $cdob = date_create($emp_dateofbirth);
        $bdate = date_format($cdob, 'Y-m-d');
        $bdate1 = date_format($cdob, 'm/d/Y');
    } else {
        $bdate = '';
    }

    //Date of join
    if ($emp_dateofjoin != '') {
        $doj = date_create($emp_dateofjoin);
        $jdate = date_format($doj, 'Y-m-d');
    } else {
        $jdate = '';
    }

    //Proposed confirmation date
    if ($emp_prop_confirmation_date != '') {
        $pod2 = date_create($emp_prop_confirmation_date);
        $prop_confirmation_date = date_format($pod2, 'Y-m-d');
    } else {
        $prop_confirmation_date = '';
    }

    //Staff grade effective date
    if ($grade_from_date != '') {
        $pgrad_dat = date_create($grade_from_date);
        $Pgrade_from_date = date_format($pgrad_dat, 'Y-m-d');
    } else {
        $Pgrade_from_date = '';
    }

    //Designation date
    if ($desig_from_date != '') {
        $pdesig_dat = date_create($desig_from_date);
        $Pdesig_from_date = date_format($pdesig_dat, 'Y-m-d');
    } else {
        $Pdesig_from_date = '';
    }

    //subsection date
    if ($sub_effective_date != '') {
        $psub_dat = date_create($sub_effective_date);
        $Psub_from_date = date_format($psub_dat, 'Y-m-d');
    } else {
        $Psub_from_date = '';
    }

    //Department
    if ($dept_effective_date != '') {
        $pdept_dat = date_create($dept_effective_date);
        $Pdept_from_date = date_format($pdept_dat, 'Y-m-d');
    } else {
        $Pdept_from_date = '';
    }


    //Creating image uplaod path
    $targetfolder = '../../uploads/emp_photo/';
    $mainName = basename($_FILES['emp_photo']['name']);
    $filename = "";

    if ($mainName != '') {
        $filename = $emp_code . '_' . basename($_FILES['emp_photo']['name']);
        $targetfolder = $targetfolder . $filename;
        $uploadPath = substr($targetfolder, 6);
    } else {
        $uploadPath = $emp_photo;
    }



    //Nid Scanned
    $targetfolder2 = '../../uploads/emp_id_photo/';
    $mainFile2 = basename($_FILES['emp_nid_photo']['name']);
    $filename2 = "";
    if ($mainFile2 != '') {
        $filename2 = $emp_code . '_' . $mainFile2;
        $targetfolder2 = $targetfolder2 . $filename2;
        $uploadPath2 = substr($targetfolder2, 6);
    } else {
        $uploadPath2 = $emp_nid_photo;
    }



    //Parsing html entity from the rich editor
    $temp_emp_remarks = htmlentities($emp_remarks);
    if ($emp_firstname == '') {
        $err = "Employee name can not be empty.";
    } else if ($emp_code == '') {
        $err = "Employee code can not be empty.";
    } else if ($emp_gender == '') {
        $err = "Employee gender must be selected.";
    } else if ($company_id == 0) {
        $err = "Please select a company.";
    } else {

        $emp_array = array(
            //Basic information
            "emp_id" => $emp_id,
            "emp_code" => $emp_code,
            "emp_firstname" => $emp_firstname,
            "emp_marital_status" => $emp_marital_status,
            "nominee_name" => $nominee_name,
            "emp_blood_group" => $emp_blood_group,
            "emp_gender" => $emp_gender,
            "emp_dateofbirth" => $bdate,
            "spouse_name" => $spouse_name,
            "no_of_children" => $no_of_children,
            "family_member" => $family_member,
            "wedding_date" => $wedding_date,
            //Contact Details
            "country" => $country_id,
            "city" => $city_id,
            "emp_address_present" => $emp_address_present,
            "emp_address_permanent" => $emp_address_parmanent,
            "emp_phone_personal" => $emp_phone_personal,
            "emp_phone_company" => $emp_phone_company,
            "emp_landphone" => $emp_landphone,
            "emp_email_office" => $emp_email_office,
            "emp_email_personal" => $emp_email_personal,
            //Emergency contact phone
            "emergency_contact_name" => $emergency_contact_name,
            "emergency_contact_phone" => $emergency_contact_phone,
            //Other information
            "emp_resignation_date" => $emp_resignation_date,
            "emp_replacement_of" => $emp_replacement_of,
            "emp_notes_salary_hub" => $emp_notes_salary_hub,
            "emp_account_number" => $emp_account_number,
            "emp_bank_title" => $emp_bank_title,
            "emp_remarks" => $temp_emp_remarks,
            "emp_photo" => $uploadPath,
            "emp_blood_group" => $emp_blood_group,
            "emp_marital_status" => $emp_marital_status,
            "emp_city" => $city_id,
            "emp_contact_number_2" => $emp_contact_number_2,
            "emp_type" => $emp_type,
            //"password" => $emp_password,
            //Temprarily disable
            //"user_type" => $user_type_value,
            "supervisor_id" => $supervisor_id,
            "reporting_id" => $reporting_id,
            "attendance_policy_id" => $attendance_policy_id,
            "job_location" => $job_location,
            "is_HOD" => $is_HOD,
            "is_pf_eligible" => $is_pf_eligible,
            "is_ot_eligible" => $is_ot_eligible,
            "pf_effective_from" => $pf_effective_from_date,
            "company_id" => $company_id,
            //job_details
            "emp_designation" => $emp_designation,
            "emp_department" => $emp_department,
            "emp_prop_confirmation_date" => $prop_confirmation_date,
            "emp_dateofjoin" => $jdate,
            //Newly added
            "tiffin_allowance_eligible" => $tiffin_allowance_eligible,
            "night_allowance_eligible" => $night_allowance_eligible,
            //S2S Specific Additional Records
            "father_name" => $father_name,
            "mother_name" => $mother_name,
            "national_id" => $national_id,
            "emp_brother_name" => $emp_brother_name,
            "emp_sister_name" => $emp_sister_name,
            "emp_sister_occupation" => $emp_sister_occupation,
            "emp_brother_occupation" => $emp_brother_occupation,
            "emp_address_present_2" => $emp_address_present_2,
            "emp_address_present_3" => $emp_address_present_3,
            "emp_phone_personal_2" => $emp_phone_personal_2,
            "emp_phone_personal_3" => $emp_phone_personal_3,
            "emp_landphone_2" => $emp_landphone_2,
            "emp_landphone_3" => $emp_landphone_3,
            "indirect_supervisor_id" => $indirect_supervisor_id,
            "emp_nid_photo" => $uploadPath2,
            "id_type" => $id_type
        );


        //Inserting the array
        if ($con->update("tmp_employee", $emp_array) == 1) {
            /*
             * Update company ID in the leave status meta
             * Find existing company ID and match with the
             * selected company ID. If different, insert a new row
             * in leave status meta with new company ID.
             * Also check for the pro rate base from the day of changing
             * the company 
             */

            //Look for existing company
            $existing_company_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
            $existing_company_id = $existing_company_info{0}->company_id;

            if ($existing_company_id != $company_id) {
                $leave_policy = $con->SelectAllBy("leave_policy");
                if (count($leave_policy) > 0) {
                    foreach ($leave_policy as $lp) {

                        //Check for pro-rate base
                        $is_pro_rate_base = $leave_all->is_pro_rate_base;

                        //Format today
                        $today = date("Y/m/d");
                        $sys_date = date_create($today);
                        $formatted_today = date_format($sys_date, 'Y-m-d');

                        $today_array = explode("-", $formatted_today);
                        $today_year = $today_array[0];

                        $build_first_date = $today_year . "-01-01";
                        $build_last_date = $today_year . "-12-31";
                        $first_day = date("Y-m-d", strtotime($build_first_date));
                        $last_day = date("Y-m-d", strtotime($build_last_date));

                        $datetime1 = date_create($formatted_today);
                        $datetime2 = date_create($build_last_date);
                        $interval = date_diff($datetime1, $datetime2);
                        $total_days_working = $interval->format('%R%a days');

                        if ($is_pro_rate_base == "true") {
                            //Calculate pro rate based total days
                            $pro_rate_based_total_days = ceil(($total_days / 365) * $total_days_working);
                            $leave_status_meta_array = array(
                                "emp_code" => $emp_code,
                                "total_days" => $pro_rate_based_total_days,
                                "company_id" => $company_id,
                                "year" => date("Y", strtotime($jdate))
                            );
                            $con->insert("leave_status_meta", $leave_status_meta_array);
                        } else {
                            $leave_status_meta_array = array(
                                "emp_code" => $emp_code,
                                "total_days" => $lp->total_days,
                                "company_id" => $company_id,
                                "year" => date("Y", strtotime($jdate))
                            );
                            $con->insert("leave_status_meta", $leave_status_meta_array);
                        }
                    }
                }
            }

            //Uploaded photo move to appropriate directory
            if ($filename != "") {
                move_uploaded_file($_FILES['emp_photo']['tmp_name'], $targetfolder);
            }
            
            //Uploaded Scanned ID photo to appropriate location
            if ($filename2 != "") {
                move_uploaded_file($_FILES['emp_nid_photo']['tmp_name'], $targetfolder2);
            }

            $raw_date = date_create($implement_from_date);
            $frmt_implement_from_date = date_format($raw_date, 'Y-m-d');

            /*
             * If state changes, then delete the alternate weekend plan for that employee
             */
            if ($alternate_attn == '' && $alt_exist_flag == '') {
                $attn_array = array("alternate_attn_policy_id" => $alternate_attn_policy_id);
                if ($con->delete("alternate_attn_policy", $attn_array) == 1) {
                    
                }
            } else if ($alternate_attn == "yes" && $alt_exist_flag == "no") {
                $alt_atn_policy_array = array(
                    "emp_code" => $emp_code,
                    "alt_company_id" => $alternate_attn_company,
                    "implement_from_date" => $frmt_implement_from_date,
                    "implement_end_date" => '0000-00-00'
                );
                if ($con->insert("alternate_attn_policy", $alt_atn_policy_array) == 1) {
                    
                }
            } else if ($alternate_attn == "yes" && $alt_exist_flag == '') {
                $alt_atn_policy_array = array(
                    "emp_code" => $emp_code,
                    "alt_company_id" => $alternate_attn_company,
                    "implement_from_date" => $frmt_implement_from_date,
                    "implement_end_date" => '0000-00-00'
                );

                if ($con->insert("alternate_attn_policy", $alt_atn_policy_array) == 1) {
                    $query_prev = "SELECT *
                        FROM
                            rpac_payroll.alternate_attn_policy
                        where
                            emp_code = '$emp_code'
                                AND implement_from_date < '$frmt_implement_from_date'
                        ORDER BY implement_from_date DESC LIMIT 0,1";
                    $query_result = $con->QueryResult($query_prev);

                    $previous_date = strtotime("$frmt_implement_from_date -1 day");
                    $pre_date = date("Y-m-d", $previous_date);

                    if (count($query_result) > 0) {
                        $alternate_attn_policy_id_pre = $query_result{0}->alternate_attn_policy_id;
                    }
                    $alt_atn_policy_array_update = array(
                        "alternate_attn_policy_id" => $alternate_attn_policy_id_pre,
                        "implement_end_date" => $pre_date
                    );
                    if ($con->update(" ", $alt_atn_policy_array_update) == 1) {
                        
                    }
                }
            }
            /*
             * Company Update Starts From here
             */

            //Effective date format to save and to display
            if ($ec_effective_start_date != '') {
                $raw_ec_effective_start_date = date_create($ec_effective_start_date);
                $ec_effective_start_date = date_format($raw_ec_effective_start_date, 'Y-m-d');
            } else {
                $ec_effective_start_date = '';
            }

            $ec_previous_date = strtotime("$ec_effective_start_date -1 day");
            $ec_pre_date = date("Y-m-d", $ec_previous_date);

            //Today time from machine
            $today = date("Y/m/d H:i:s");
            $sys_date = date_create($today);
            $formatted_today = date_format($sys_date, 'Y-m-d H:i:s');
            $emp_company_array = array(
                "ec_company_id" => $company_id,
                "ec_emp_code" => $emp_code,
                "ec_effective_start_date" => $ec_effective_start_date,
                "ec_effective_end_date" => '0000-00-00',
                "created_at" => $formatted_today,
                "created_by" => $_SESSION["emp_code"]
            );
            $check_exist_query = $con->SelectAllByCondition("emp_company", "ec_emp_code='$emp_code' AND ec_effective_start_date='$ec_effective_start_date'");

            if (count($check_exist_query) > 0) {
                //$err = 'A company is already assigned for this employee in this date. Change in company info is not succesfully saved!';
            } else {
                if ($con->insert("emp_company", $emp_company_array) == 1) {
                    /** At update a new row is inserted
                     * Existing effective end date for immediate previous definition is empty.
                     * Edited effective date - 1 day is last effective definition;s end day.
                     * Last definition will be updated with this substracted date. 
                     */
                    $query_prev_com = "SELECT * FROM emp_company
                        where
                            ec_emp_code = '$emp_code'
                                AND ec_effective_start_date < '$ec_effective_start_date'
                        ORDER BY ec_effective_start_date DESC LIMIT 0,1";
                    $query_result_com = $con->QueryResult($query_prev_com);

                    if (count($query_result_com) > 0) {
                        $emp_company_id = $query_result_com{0}->emp_company_id;
                        $ec_update_array = array(
                            "emp_company_id" => $emp_company_id,
                            "ec_effective_end_date" => $ec_pre_date,
                            "last_updated_at" => $formatted_today,
                            "last_updated_by" => $_SESSION["emp_code"]
                        );
                        if ($con->update("emp_company", $ec_update_array) == 1) {
                            $msg = "All changes are successfully saved. This page will be reloaded after 5 seconds. Then you can see your edited results in the form.";
                        } else {
                            $err = "Change in company info is not succesfully saved.";
                        }
                    }
                }
            }

            // Today time from machine
            $today = date("Y/m/d H:i:s");
            $sys_date = date_create($today);
            $formatted_today = date_format($sys_date, 'Y-m-d H:i:s');

            /*
             * Department Modification Starts Here
             */
            $dept_success = 0;
            //Generate Previous Day
            $dept_effective_start_date = date_format($pdept_dat, 'Y-m-d');
            $dep_previous_date = strtotime("$dept_effective_start_date -1 day");
            $dep_pre_date = date("Y-m-d", $dep_previous_date);

            $emp_dept_array = array(
                "edept_emp_code" => $emp_code,
                "edept_dept_id" => $emp_department,
                "edept_effective_start_date" => $Pdept_from_date,
                "edept_effective_end_date" => '0000-00-00',
                "created_at" => $formatted_today,
                "created_by" => $_SESSION["emp_code"]
            );
            $check_exist_dep = $con->SelectAllByCondition("emp_department", "edept_emp_code='$emp_code' AND edept_effective_start_date='$Pdept_from_date'");

            if (count($check_exist_dep) > 0) {
                //$err = 'The Employee is already assigned to the Department in this date. Change in Employee department is not saved!';
            } else {
                if ($con->insert("emp_department", $emp_dept_array) == 1) {

                    echo $query_prev_dep = "SELECT * FROM emp_department
                        where
                           edept_emp_code = '$emp_code'
                           AND edept_effective_start_date < '$Pdept_from_date'
                        ORDER BY edept_effective_start_date DESC LIMIT 0,1";
                    $query_result_dep = $con->QueryResult($query_prev_dep);
                    if (count($query_result_dep) > 0) {
                        $edept_dept_id = $query_result_dep{0}->emp_department_id;
                        $dep_update_array = array(
                            "emp_department_id" => $edept_dept_id,
                            "edept_effective_end_date" => $dep_pre_date,
                            "last_updated_at" => $formatted_today,
                            "last_updated_by" => $_SESSION["emp_code"]
                        );
                        if ($con->update("emp_department", $dep_update_array) == 1) {
                            $dept_success = 1;
                        }
                    }
                }
            }

            /*
             * Employee designation update starts
             */
            $desig_success = 0;
            $desig_effective_start_date = date_format($pdesig_dat, 'Y-m-d');
            $desig_previous_date = strtotime("$desig_effective_start_date -1 day");
            $desig_pre_date = date("Y-m-d", $desig_previous_date);

            $emp_desig_array = array(
                "edes_emp_code" => $emp_code,
                "edes_designation_id" => $emp_designation,
                "edes_effective_start_date" => $Pdesig_from_date,
                "edes_effective_end_date" => '0000-00-00',
                "created_at" => $formatted_today,
                "created_by" => $_SESSION["emp_code"]
            );
            $check_exist_desig = $con->SelectAllByCondition("emp_designation", "edes_emp_code='$emp_code' AND edes_effective_start_date='$Pdesig_from_date'");

            if (count($check_exist_desig) > 0) {
                //$err = 'The Employee is already assigned to the Designation in this date. Change in Employee designation is not saved!';
            } else {
                if ($con->insert("emp_designation", $emp_desig_array) == 1) {

                    $query_prev_desig = "SELECT * FROM emp_designation
                        where
                             edes_emp_code= '$emp_code'
                                AND edes_effective_start_date < '$Pdesig_from_date'
                        ORDER BY edes_effective_start_date DESC LIMIT 0,1";
                    $query_result_desig = $con->QueryResult($query_prev_desig);

                    if (count($query_result_desig) > 0) {
                        $edesig_desig_id = $query_result_desig{0}->emp_designation_id;
                        $desig_update_array = array(
                            "emp_designation_id" => $edesig_desig_id,
                            "edes_effective_end_date" => $desig_pre_date,
                            "last_updated_at" => $formatted_today,
                            "last_updated_by" => $_SESSION["emp_code"]
                        );
                        if ($con->update("emp_designation", $desig_update_array) == 1) {
                            $desig_success = 1;
                        }
                    }
                }
            }



            //============================  End  Employee Staff Grade  =========================================================
            //==================== end of add 11 April 15 ==================================================
            //getting exitsing salary headers' id from db table
            if ($emp_code != '') {
                $arrEmpSalaryHeadID = array();
                $sqlGetSalary = "SELECT * FROM payroll_employee_salary WHERE PES_employee_code='$emp_code' AND PES_company_id=$company_id";
                $resultGetSalary = mysqli_query($con->open(), $sqlGetSalary);

                if ($resultGetSalary) {
                    while ($sqlGetSalaryObj = mysqli_fetch_object($resultGetSalary)) {
                        $arrEmpSalaryHeadID[] = $sqlGetSalaryObj->PES_PSH_id;
                        $arrEmpSalaryOld[$sqlGetSalaryObj->PES_PSH_id] = $sqlGetSalaryObj;
                    }
                } else {
                    echo "resultGetSalary query failed.";
                }
            }

            $InsertHeadCheck = 0;
            if (count($arrSalHeadFix) > 0) {
                foreach ($arrSalHeadFix AS $FixHeader) {

                    $headerID = $FixHeader['id'];
                    $headerAmount = $FixHeader['value'];
                    $ifExist = FALSE;
                    //checking if header already exist in table
                    if (count($arrEmpSalaryHeadID) > 0) {
                        if (in_array($headerID, $arrEmpSalaryHeadID)) {
                            $ifExist = TRUE;
                        }
                    }

                    //if header already exist in table then need to update
                    if ($ifExist) {
                        //checking if previous header amount and current header amount is same or not
                        if (count($arrEmpSalaryOld) > 0) {
                            if (array_key_exists($headerID, $arrEmpSalaryOld)) {

                                if ($arrEmpSalaryOld[$headerID]->PES_amount != $headerAmount) {
                                    $oldAmount = $arrEmpSalaryOld[$headerID]->PES_amount;

                                    //Find if header id is of a gross field
                                    $salary_type = $con->SelectAllByCondition("payroll_salary_header", "PSH_id='$headerID'");
                                    if (count($salary_type) > 0) {
                                        if ($salary_type{0}->PSH_is_gross == "yes") {
                                            $is_gross = "yes";
                                        } else {
                                            $is_gross = " ";
                                        }
                                    }

                                    //if previous header amount and current header amount is not same then need to insert into log table
                                    $insertChange = '';
                                    $insertChange .=' PSL_employee_code = "' . mysqli_real_escape_string($con->open(), $emp_code) . '"';
                                    $insertChange .=', PSL_PSH_id = "' . intval($headerID) . '"';
                                    $insertChange .=', PSL_PSH_prev_amount = "' . floatval($oldAmount) . '"';
                                    $insertChange .=', PSL_PSH_curr_amount = "' . floatval($headerAmount) . '"';
                                    $insertChange .=', PSL_updated_by = "' . mysqli_real_escape_string($con->open(), $employeeCode) . '"';

                                    $sqlInsertChange = "INSERT INTO payroll_salary_log SET $insertChange";
                                    $resultInsertChange = mysqli_query($con->open(), $sqlInsertChange);

                                    if (!$resultInsertChange) {
                                        echo "resultInsertChange query failed.";
                                    }
                                }
                            }
                        }


                        //Find if header id is of a gross field
                        $salary_type = $con->SelectAllByCondition("payroll_salary_header", "PSH_id='$headerID'");
                        if (count($salary_type) > 0) {
                            if ($salary_type{0}->PSH_is_gross == "yes") {
                                $is_gross = "yes";
                            } else {
                                $is_gross = " ";
                            }
                        }

                        //updating existing header record
                        $updateFixHead = '';
                        $updateFixHead .=' PES_company_id = "' . intval($company_id) . '"';
                        $updateFixHead .=', PES_PSH_id = "' . intval($headerID) . '"';
                        $updateFixHead .=', PES_amount = "' . floatval($headerAmount) . '"';
                        $updateFixHead .=', PES_updated_by = "' . mysqli_real_escape_string($open, $employeeCode) . '"';
                        $updateFixHead .= ', PES_is_gross = "' . mysqli_real_escape_string($open, $is_gross) . '"';

                        $sqlInsertFixHead = "UPDATE `payroll_employee_salary` SET $updateFixHead WHERE PES_employee_code='$emp_code' AND PES_PSH_id=$headerID";


                        /*
                         * Priority is checked
                         * If positive, then update query will run
                         * If logged in employee and this employee is same
                         * Query will run
                         * If none, then this specific salary update query wont run
                         */

                        if ($priority >= $range_start && $priority <= $range_end) {
                            $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);
                        } else if ($logged_emp_code == $emp_code) {
                            $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);
                        } else {
                            
                        }

                        if (!$resultInsertFixHead) {
                            $InsertHeadCheck++;
                        }
                    } else {

                        //if header is not there then need to insert
                        //Find if header id is of a gross field
                        $salary_type = $con->SelectAllByCondition("payroll_salary_header", "PSH_id='$headerID'");
                        if (count($salary_type) > 0) {
                            if ($salary_type{0}->PSH_is_gross == "yes") {
                                $is_gross = "yes";
                            } else {
                                $is_gross = " ";
                            }
                        }

                        $insertFixHead = '';
                        $insertFixHead .=' PES_company_id = "' . intval($company_id) . '"';
                        $insertFixHead .=', PES_employee_code = "' . mysqli_real_escape_string($open, $emp_code) . '"';
                        $insertFixHead .=', PES_PSH_id = "' . intval($headerID) . '"';
                        $insertFixHead .=', PES_amount = "' . floatval($headerAmount) . '"';
                        $insertFixHead .=', PES_created_on = "' . mysqli_real_escape_string($open, $currentTime) . '"';
                        $insertFixHead .=', PES_created_by = "' . mysqli_real_escape_string($open, $employeeCode) . '"';
                        $insertFixHead .= ', PES_is_gross = "' . mysqli_real_escape_string($open, $is_gross) . '"';
                        $sqlInsertFixHead = "INSERT INTO payroll_employee_salary SET $insertFixHead";

                        /*
                         * Priority is checked
                         * If positive, then update query will run
                         * If logged in employee and this employee is same
                         * Query will run
                         * If none, then this specific salary update query wont run
                         */
                        if ($priority >= $range_start && $priority <= $range_end) {
                            $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);
                        } else if ($logged_emp_code == $emp_code) {
                            $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);
                        } else {
                            
                        }

                        if (!$resultInsertFixHead) {
                            $InsertHeadCheck++;
                        }
                    }
                }
            }

            if (count($arrSalHeadOpt) > 0) {
                foreach ($arrSalHeadOpt AS $SalaryHeadOpt) {
                    $optHeaderID = $SalaryHeadOpt['id'];
                    $optHeaderValue = $SalaryHeadOpt['value'];

                    //finding out which salary header(s) need to be inserted inside database for the first time 
                    $arrHeadNeedToAdd = array();
                    $arrHeadNeedToAdd = array_diff($arrSalHeadOptID, $arrEmpSalaryHeadID);

                    //using a flag to checkout weither this header id needs to be updated or inserted
                    $optStatus = '';

                    //checking if current header id needs to be inserted
                    if (count($arrHeadNeedToAdd) > 0) {
                        if (in_array($optHeaderID, $arrHeadNeedToAdd)) {
                            $optStatus = 'ADD'; //add this entry
                        }
                    }

                    //checking if current header id needs to be updated
                    if (count($arrEmpSalaryHeadID) > 0) {
                        if (in_array($optHeaderID, $arrEmpSalaryHeadID)) {
                            $optStatus = 'UPD'; //update this entry
                        }
                    }

                    if ($optStatus == "ADD") {

                        //adding values in the table
                        $insertFixHead = '';
                        $insertFixHead .=' PES_company_id = "' . intval($company_id) . '"';
                        $insertFixHead .=', PES_employee_code = "' . mysqli_real_escape_string($open, $emp_code) . '"';
                        $insertFixHead .=', PES_PSH_id = "' . intval($optHeaderID) . '"';
                        $insertFixHead .=', PES_amount = "' . floatval($optHeaderValue) . '"';
                        $insertFixHead .=', PES_created_on = "' . mysqli_real_escape_string($open, $currentTime) . '"';
                        $insertFixHead .=', PES_created_by = "' . mysqli_real_escape_string($open, $employeeCode) . '"';
                        $sqlInsertFixHead = "INSERT INTO payroll_employee_salary SET $insertFixHead";
                        $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);

                        if (!$resultInsertFixHead) {
                            $InsertHeadCheck++;
                        }
                    } elseif ($optStatus == "UPD") {

                        $oldAmount = 0;
                        if (count($arrEmpSalaryOld) > 0) {
                            if (array_key_exists($optHeaderID, $arrEmpSalaryOld)) {

                                if ($arrEmpSalaryOld[$optHeaderID]->PES_amount != $optHeaderValue) {
                                    $oldAmount = $arrEmpSalaryOld[$optHeaderID]->PES_amount;
                                    $insertChange = '';
                                    $insertChange .=' PSL_employee_code = "' . mysqli_real_escape_string($con->open(), $emp_code) . '"';
                                    $insertChange .=', PSL_PSH_id = "' . intval($optHeaderID) . '"';
                                    $insertChange .=', PSL_PSH_prev_amount = "' . floatval($oldAmount) . '"';
                                    $insertChange .=', PSL_PSH_curr_amount = "' . floatval($optHeaderValue) . '"';
                                    $insertChange .=', PSL_updated_by = "' . mysqli_real_escape_string($con->open(), $employeeCode) . '"';

                                    $sqlInsertChange = "INSERT INTO payroll_salary_log SET $insertChange";
                                    $resultInsertChange = mysqli_query($con->open(), $sqlInsertChange);

                                    if (!$resultInsertChange) {
                                        echo "resultInsertChange query failed.";
                                    }
                                }
                            }
                        }

                        //updating values in the table
                        $updateFixHead = '';
                        $updateFixHead .=' PES_company_id = "' . intval($company_id) . '"';
                        $updateFixHead .=', PES_PSH_id = "' . intval($optHeaderID) . '"';
                        $updateFixHead .=', PES_amount = "' . floatval($optHeaderValue) . '"';
                        $updateFixHead .=', PES_updated_by = "' . mysqli_real_escape_string($open, $employeeCode) . '"';
                        $sqlInsertFixHead = "UPDATE payroll_employee_salary SET $updateFixHead WHERE PES_employee_code='$emp_code' AND PES_PSH_id=$optHeaderID";

                        echo $sqlInsertFixHead;
                        exit();

                        $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);

                        if (!$resultInsertFixHead) {
                            $InsertHeadCheck++;
                        }
                    }
                }
            }

            $arrHeadNeedToDelete = array();
            $arrMergeRearr = array();

            //merging post value of both fixed and optional salary headers
            $arrMerge = array_merge($arrSalHeadFix, $arrSalHeadOpt);

            //rearranging merged array according to header id
            foreach ($arrMerge AS $arrMerged) {
                $arrMergeRearr[] = $arrMerged['id'];
            }

            $arrHeadNeedToDelete = array_diff($arrEmpSalaryHeadID, $arrMergeRearr);
            if (count($arrHeadNeedToDelete) > 0) {
                foreach ($arrHeadNeedToDelete AS $key => $val) {
                    //deleting values from the table
                    $sqlDeleteHead = "DELETE FROM payroll_employee_salary WHERE PES_employee_code='$emp_code' AND PES_PSH_id=$val";

                    if ($priority >= $range_start && $priority <= $range_end) {
                        $resultDeleteHead = mysqli_query($open, $sqlDeleteHead);
                    } else if ($logged_emp_code == $emp_code) {
                        $resultDeleteHead = mysqli_query($open, $sqlDeleteHead);
                    } else {
                        
                    }

                    if (!$resultDeleteHead) {
                        $InsertHeadCheck++;
                    }
                }
            }
            if ($InsertHeadCheck > 0) {
                //$err = "There is some problem with data insertion of salary part. Please check.";
            }
            /** Find the gross salary info for this employee
             * Find the existing value and place it in gross salary field. 
             */
            $update_array = array(
                "PES_id" => $PES_id,
                "PES_gross_slary" => $emp_gross_salary
            );

            if ($dept_success = 1 || $subsec_success = 1 || $desig_success == 1 || $grade_success == 1) {
                $msg = "All changes are successfully saved. This page will be reloaded after 3 seconds. Then you can see your edited results in the form.";
                header("refresh:3;url=edit.php?emp_id=" . $emp_id . "");
            } else {
                $err = "Error! Some of the information of Employee is not updated.";
            }
        } else {
            $err = "Something went wrong!";
        }
    }
    //$con->redirect("edit.php?emp_id=" . $emp_id);
}

//getting salary information from employee salary table
if ($emp_code != '') {
    $arrEmpSalary = array();
    $arrEmpSalaryID = array();
    $sqlGetSalary = "SELECT * FROM payroll_employee_salary WHERE PES_employee_code='$emp_code' AND PES_company_id=$company_id";
    $resultGetSalary = mysqli_query($con->open(), $sqlGetSalary);

    if ($resultGetSalary) {
        while ($sqlGetSalaryObj = mysqli_fetch_object($resultGetSalary)) {
            $arrEmpSalary[$sqlGetSalaryObj->PES_PSH_id] = $sqlGetSalaryObj;
            $arrEmpSalaryID[] = $sqlGetSalaryObj->PES_PSH_id;
        }
    } else {
        echo "resultGetSalary query failed.";
    }
}

//Getting salary headers from database
$arrHeaders = array();
$sqlGetHeader = "SELECT * FROM payroll_salary_header WHERE PSH_show_in_tmp_mod='yes'";
$resultGetHeader = mysqli_query($con->open(), $sqlGetHeader);
if ($resultGetHeader) {
    while ($resultGetHeaderObj = mysqli_fetch_object($resultGetHeader)) {
        $arrHeaders[] = $resultGetHeaderObj;
    }
} else {
    echo "resultGetHeader query failed.";
}

/*
 * Collect logged in employee
 * Look at staff grade permissions
 * Hide salaries of staff grades out of range
 */
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

//Find this employee's priority
$priority = '';
$emp_staff_grade_info = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
if (count($emp_staff_grade_info) > 0) {
    $priority = $emp_staff_grade_info{0}->priority;
}

/*
 * Company wise leave policy is not created yet, now leave polcies are independed
 * Leave status meta update
 * Check for approval process for new employee :: not for now
 * If approved then this employee can be added to leave_status_meta :: not for now
 */

$existing_company_id = $company_id;


//Find existing company
$leave_policy = $con->SelectAllByCondition("leave_policy");
if (count($leave_policy) > 0) {
    foreach ($leave_policy as $lp) {
        $leave_status_meta_array = array(
            "emp_code" => $emp_code,
            "total_days" => $lp->total_days,
            "company_id" => $company_id,
            "year" => date("Y", strtotime($jdate))
        );
        $con->insert("leave_status_meta", $leave_status_meta_array);
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Edit Employee Information</h6></div>
    <div class="widget-body" background-color: white;>
    <?php include("../../layout/msg.php"); ?>
         <form method="post" enctype="multipart/form-data">
            <div id="example" class="k-content"> 
                <div id="tabstrip" style="border-style: none;">
                    <ul>
                        <li class="k-state-active"> Basic Information </li>
                        <li> Contact Information </li>
                        <li> Job Details </li>
                        <li> Salary Information  </li>
                        <li> Other Information </li>
                    </ul>
                    <div>
                        <div class="weather">
                            <!--Full Name-->
                            <div class="col-md-6">
                                <label for="Full name">Full name: <span style="color: red;">*</span></label> <br />
                                <input type="text" value="<?php echo $emp_firstname; ?>" name="emp_firstname" placeholder="" class="k-textbox" type="text" id="emp_fullname" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Full name">Nominee Name:</label> <br />
                                <input class="k-textbox" type="text" value="<?php echo $nominee_name; ?>" id="nominee_name" placeholder="" name="nominee_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <div class="clearfix"></div> <br />
                            <!--Email-->
                            <div class="col-md-6">
                                <label for="Full name">Marital Status:</label> <br />
                                <select id="size" name="emp_marital_status" style="width: 80%">
                                    <option value="0">Select Marital Status</option>
                                    <?php if ($emp_marital_status == "single"): ?>
                                        <option value="single" selected="true">Single</option>
                                    <?php else: ?>
                                        <option value="single">Single</option>
                                    <?php endif; ?>
                                    <?php if ($emp_marital_status == "married"): ?>
                                        <option value="married" selected="true">Married</option>
                                    <?php else: ?>
                                        <option value="married">Married</option>
                                    <?php endif; ?>
                                </select>
                            </div> 
                            <!--Designation-->
                            <div class="col-md-6">
                                <label for="Full name">Blood Group:</label> <br />
                                <div id="options">
                                    <select id="size2" style="width: 80%" name="emp_blood_group">
                                        <option value="0">Blood Group</option>

                                        <?php if ($emp_blood_group == "A+"): ?>
                                            <option value="A+" selected="true">A+</option>
                                        <?php else: ?>
                                            <option value="A+">A+</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "B+"): ?>
                                            <option value="B+" selected="true">B+</option>
                                        <?php else: ?>
                                            <option value="B+">B+</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "AB+"): ?>
                                            <option value="AB+" selected="true">AB+</option>
                                        <?php else: ?>
                                            <option value="AB+">AB+</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "O+"): ?>
                                            <option value="O+" selected="true">O+</option>
                                        <?php else: ?>
                                            <option value="O+">O+</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "A-"): ?>
                                            <option value="A-" selected="true">A-</option>
                                        <?php else: ?>
                                            <option value="A-">A-</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "B-"): ?>
                                            <option value="B-" selected="true">B-</option>
                                        <?php else: ?>
                                            <option value="B-">B-</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "AB-"): ?>
                                            <option value="AB-" selected="true">AB-</option>
                                        <?php else: ?>
                                            <option value="AB-">AB-</option>
                                        <?php endif; ?>

                                        <?php if ($emp_blood_group == "O-"): ?>
                                            <option value="O-" selected="true">O-</option>
                                        <?php else: ?>
                                            <option value="O-">O-</option>
                                        <?php endif; ?>

                                    </select>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <!--Email-->
                            <div class="col-md-6">
                                <label for="Full name">Gender: <span style="color: red;">*</span></label> <br />
                                <div id="options">
                                    <select id="size3" style="width: 80%" name="emp_gender">
                                        <option value="0">Gender</option>
                                        <?php if ($emp_gender == "M"): ?>
                                            <option value="M" selected="true">Male</option>
                                        <?php else: ?>
                                            <option value="M">Male</option>
                                        <?php endif; ?>
                                        <?php if ($emp_gender == "F"): ?>
                                            <option value="F" selected="true">Female</option>
                                        <?php else: ?>
                                            <option value="F">Female</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <!--Designation-->
                            <div class="col-md-6">
                                <label for="Full name">Date of Birth:</label> <br />
                                <input type="text" value="<?php echo $bdate1; ?>" id="emp_dateofbirth" placeholder="" name="emp_dateofbirth" type="text" style="width: 80%;"/>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $("#emp_dateofbirth").kendoDatePicker();
                                });</script>
                            <div class="clearfix"></div>
                            <br />
                            <div class="col-md-6">
                                <label for="Full name">Spouse Name:</label> <br />
                                <input type="text" class="k-textbox" value="<?php echo $spouse_name; ?>" id="spouse_name" placeholder="" name="spouse_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">No of Children:</label> <br />
                                <input type="text" class="k-textbox" value="<?php echo $no_of_children; ?>" id="no_of_children" placeholder="" name="no_of_children" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />
                            <div class="col-md-6">
                                <label for="Full name">Family Member:</label> <br />
                                <input type="text" class="k-textbox" value="<?php echo $family_member; ?>" id="family_member" value="<?php echo $family_member; ?>" name="family_member" type="text" style="width: 80%;"/>
                            </div> 
                            <div class="col-md-6">
                                <label for="Full name">Wedding Date:</label> <br />
                                <input type="text" value="<?php echo $wedding_date_value; ?>" id="wedding_date" placeholder="" name="wedding_date" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Father Name">Father's Name:</label> <br />
                                <input class="k-textbox" type="text" id="father_name" placeholder="" value="<?php echo $father_name; ?>" name="father_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Mother Name">Mother's Name:</label> <br />
                                <input class="k-textbox" type="text" value="<?php echo $mother_name; ?>" id="mother_name" placeholder="" name="mother_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Brother Name">Brother's Name:</label> <br />
                                <input class="k-textbox" value="<?php echo $emp_brother_name; ?>" type="text" value="" id="emp_brother_name" placeholder="" name="emp_brother_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Brother Name">Brother's Occupation:</label> <br />
                                <input class="k-textbox" value="<?php echo $emp_brother_occupation; ?>" type="text" value="" id="emp_brother_occupation" placeholder="" name="emp_brother_occupation" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Brother Name">Sister's Name:</label> <br />
                                <input class="k-textbox" type="text" value="<?php echo $emp_sister_name; ?>" id="emp_sister_name" placeholder="" name="emp_sister_name" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Sister Occu">Sister's Occupation:</label> <br/>
                                <input class="k-textbox" type="text" value="<?php echo $emp_sister_occupation; ?>" id="emp_sister_occupation" placeholder="" name="emp_sister_occupation" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />

                        </div>
                    </div>

                    <div>
                        <div class="weather">
                            <!--Contact Information-->
                            <div class="col-md-6">                       
                                <label for="Country">Country:</label><br/>
                                <input id="countrys" name="country_id" style="width: 79%; height: 26px;" value="<?php echo $country_id; ?>" />                                                     
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">City:</label> <br />
                                <label for="citys"></label>
                                <input id="citys" name="city_id" style="width: 79%;" value="<?php echo $city_id; ?>" />                                   
                            </div>

                            <script type="text/javascript">
                                $(document).ready(function () {
                                    var countrys = $("#countrys").kendoComboBox({
                                        placeholder: "Select Country...",
                                        dataTextField: "country_name",
                                        dataValueField: "country_id",
                                        dataSource: {
                                            //                            type: "json",
                                            //                            data: categoriesData
                                            transport: {
                                                read: {
                                                    url: "../../controller/country.php",
                                                    type: "GET"
                                                }
                                            },
                                            schema: {
                                                data: "data"
                                            }
                                        }
                                    }).data("kendoComboBox");

                                    var citys = $("#citys").kendoComboBox({
                                        autoBind: false,
                                        cascadeFrom: "countrys",
                                        placeholder: "Select City..",
                                        dataTextField: "city_name",
                                        dataValueField: "city_id",
                                        dataSource: {
                                            //                        type: "json",
                                            //                        data: productsData
                                            transport: {
                                                read: {
                                                    url: "../../controller/citys.php",
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
                            <div class="clearfix"></div>
                            <br />


                            <div class="col-md-6">
                                <label for="Full name">Present Address : <span style="color: red;">*</span></label> <br />
                                <input type="text" value="<?php echo $emp_address_present; ?>" placeholder="" class="k-textbox" name="emp_address_present" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Full name">Permanent Address : <span style="color: red;">*</span></label> <br />
                                <input type="text" value="<?php echo $emp_address_parmanent; ?>" placeholder="" class="k-textbox" name="emp_address_parmanent" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Full name">Email (Office):</label> <br />
                                <input type="text" value="<?php echo $emp_email_office; ?>" placeholder="" class="k-textbox" name="emp_email_office" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Full name">Email (Personal):</label> <br />
                                <input type="text" value="<?php echo $emp_email_personal; ?>" placeholder="" class="k-textbox" name="emp_email_personal" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Full name">Phone (Company):</label> <br />
                                <input type="text" value="<?php echo $emp_phone_company; ?>" placeholder="" class="k-textbox" name="emp_phone_company" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Full Name">Land Phone:</label> <br />
                                <input type="text" value="<?php echo $emp_landphone; ?>" placeholder="" class="k-textbox" name="emp_landphone" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />
                            <div class="col-md-6">
                                <label for="Full name">Phone (Personal):</label> <br />
                                <input type="text" value="<?php echo $emp_phone_personal; ?>" placeholder="" class="k-textbox" name="emp_phone_personal" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Full name">Emergency Contact Person's Name:</label> <br />
                                <input type="text" value="<?php echo $emergency_contact_name; ?>" placeholder="" class="k-textbox" name="emergency_contact_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Full name">Emergency Contact Person's Phone:</label> <br />
                                <input type="text" value="<?php echo $emergency_contact_phone; ?>" placeholder="" class="k-textbox" name="emergency_contact_phone" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <h4>Present Address (Previous)</h4>
                            <hr>
                            <div class="col-md-6">
                                <label for="Full name">Present Address 2:</label> <br />
                                <input type="text" value="<?php echo $emp_address_present_2; ?>" placeholder="" class="k-textbox" name="emp_address_present_2" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">Present Address 3:</label> <br />
                                <input type="text" value="<?php echo $emp_address_present_3; ?>" placeholder="" class="k-textbox" name="emp_address_present_3" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Full name">Mobile (Personal) 2:</label> <br />
                                <input type="text" value="<?php echo $emp_phone_personal_2; ?>" placeholder="" class="k-textbox" name="emp_phone_personal_2" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Full name">Mobile (Personal) 3:</label> <br />
                                <input type="text" value="<?php echo $emp_phone_personal_3; ?>" placeholder="" class="k-textbox" name="emp_phone_personal_3" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br /> 

                            <div class="col-md-6">
                                <label for="Full name">Land Phone (Personal) 2:</label> <br />
                                <input type="text" value="<?php echo $emp_landphone_2; ?>" placeholder="" class="k-textbox" name="emp_landphone_2" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">Land Phone (Personal) 3:</label> <br />
                                <input type="text" value="<?php echo $emp_landphone_3; ?>" placeholder="" class="k-textbox" name="emp_landphone_3" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />  

                        </div>
                    </div>

                    <div>
                        <div class="weather">

                            <div class="col-md-6">
                                <label for="Full name">Date of Join:</label> <br />
                                <input type="text" value="<?php echo $jdate1; ?>" name="emp_dateofjoin" id="emp_dateofjoin" style="width: 80%;"/>
                            </div>

                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $(".emp_datepicker").kendoDatePicker();
                                });
                            </script>

                            <div class="col-md-6">
                                <label for="Full name">Proposed Confirmation Date:</label> <br />
                                <input id="proposed_confirmation_date" type="text" class="emp_proposed" value="<?php echo $pdate; ?>" placeholder="" class="k-textbox" name="emp_prop_confirmation_date" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <hr />

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Full name">Company:</label><br/>
                                    <?php $companies = $con->SelectAll("company"); ?>
                                    <div id="options">

                                        <select id="size9" style="width: 80%" name="company_id" onchange="showDiv(this)">
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
                                </div>
                            </div>

                            <!--Effective date-->
                            <div class="col-md-6">
                                <label for="Full name">Effective from:</label> <br />
                                <input type="text" value="<?php echo $ec_effective_start_date_as_value; ?>" placeholder="" class="jd_effective_start_date" name="ec_effective_start_date" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $(".jd_effective_start_date").kendoDatePicker();
                                });
                            </script>
                            <div class="col-md-6">
                                <label for="Full name">Department:</label><br/> 
                                <input id="departments" name="emp_department" style="width: 80%;" value="<?php echo $emp_department; ?>" />
                            </div>
                            <!--Effective date-->
                            <div class="col-md-6">
                                <label for="Full name">Effective from:</label> <br />
                                <input type="text" value="<?php echo $dept_effective_date; ?>" placeholder="" class="dept_effective_date" name="dept_effective_date" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $(".dept_effective_date").kendoDatePicker();
                                });
                            </script>
                            <div class="clearfix"></div><br />


                            <div class="col-md-6">
                                <label for="Full name">Designation:</label> <br />
                                <input id="designations" name="emp_designation" style="width: 80%;" value="<?php echo $emp_designation; ?>" />
                                <!-- auto complete start-->
                            </div>
                            <div class="col-md-6" id="desig_from_date">
                                <label for="Full name">Effective From:</label> <br />
                                <input type="text" class="desig_from_date" value="<?php echo $desig_from_date; ?>" placeholder="" class="k-textbox" name="desig_from_date" type="text" style="width: 80%;"/>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $(".desig_from_date").kendoDatePicker();
                                });
                            </script>
                            <div class="clearfix"></div><br />
                            <!-------------------Group---------------------------------------------->

                            <!----------------------Group End---------------------------------> 
                            <div class="clearfix"></div><br />
                            <script type="text/javascript">
                                jQuery(document).ready(function () {
                                    //Replacement employee
                                    var sup_employees = jQuery("#sup_employees").kendoComboBox({
                                        placeholder: "Select employee...",
                                        dataTextField: "emp_name",
                                        dataValueField: "emp_id",
                                        dataSource: {
                                            transport: {
                                                read: {
                                                    url: "../../controller/employee_list_supervisor.php",
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
                            <div class="col-md-6">
                                <div id="example">
                                    <div class="demo-section k-header">
                                        <label for="Full name">Supervisor:</label> <br />
                                        <div id="options">
                                            <input type="text" value="<?php echo $supervisor_id; ?>" id="sup_employees" name="supervisor_id" style="width:80%">
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            <!-- auto complete end-->

                            <div class="col-md-6">
                                <label for="Full name">Reporting Method:</label> <br />
                                <?php //echo $reporting_id;                           ?>
                                <div id="options">
                                    <select id="size11" style="width: 80%" name="reporting_id">
                                        <option value="0">Select Reporting Method</option>
                                        <?php
                                        if (count($reportings) >= 1):
                                            foreach ($reportings as $rs):
                                                ?>
                                                <option value="<?php echo $rs->reporting_id; ?>" 
                                                <?php
                                                if ($rs->reporting_id == $reporting_id) {
                                                    echo "selected='selected'";
                                                }
                                                ?>><?php echo $rs->reporting_title; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                    </select>
                                </div> 
                            </div> 
                            <div class="clearfix"></div><br />

                            <div class="col-md-6">
                                <label for="Full name">Employee Code: <span style="color: red;">*</span></label> <br />
                                <input name="emp_code" value="<?php echo $emp_code; ?>" type="text" class="k-textbox" style="width: 80%;"/>
                            </div>
                            <!--Only if logged in user is a super user-->
                            <?php if ($_SESSION["is_super"] == 'yes'): ?>
                                <div class="col-md-6">
                                    <label for="Full name">System Login Password:</label> <br />
                                    <input type="text" value="<?php echo $password; ?>" placeholder="" class="k-textbox" name="emp_password" style="width: 80%;"/>
                                </div>
                                <div class="clearfix"></div>
                                <br />
                            <?php else: ?>
                                <div class="clearfix"></div>
                                <br />
                            <?php endif; ?>

                            <?php $location_array = $con->SelectAll("job_location"); ?>
                            <div class="col-md-6">
                                <label for="Full name">Job Location:</label> <br />
                                <div id="options">
                                    <select id="job_location" style="width: 80%" name="job_location">
                                        <option value="">Select Job Location</option>
                                        <?php if (count($location_array) >= 1): ?>
                                            <?php foreach ($location_array as $la): ?>
                                                <option value="<?php echo $la->job_location_title; ?>" 
                                                <?php
                                                if ($la->job_location_title == $job_location) {
                                                    echo "selected='selected'";
                                                }
                                                ?>><?php echo $la->job_location_title; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                    </select>
                                </div> 
                            </div>

                            <div class="col-md-6">
                                <div id="example">
                                    <div class="demo-section k-header">
                                        <label for="Full name">Supervisor (indirect):</label><br/>
                                        <div id="options">
                                            <input type="text" id="indirect_supervisor_id" name="indirect_supervisor_id" style="width:80%" value="<?php echo $supervisor_id ?>">
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <br />
                        </div>
                    </div>
                    <!-- employee section end -->
                    <div>
                        <div class="weather">

                            <input type="checkbox" id="form_one"> &nbsp; &nbsp; Formula One
                            <button type="button" id="formula_one_details">View Details</button>

                            <br /><br />
                            <input type="checkbox" id="form_two"> &nbsp; &nbsp; Formula Two
                            <button type="button" id="formula_two_details">View Details</button>

                            <hr>

                            <div id="formula_one_container" style="display:none;">
                                <pre>
- House Rent = (Gross – Medical – Conveyance) / 3
- Basic = House Rent * 2
- Medical = 10 % of Gross OR Tk. 1,20,000 maximum in a year (Tk. 10,000 per month)—whichever is lower
- Conveyance = Tk. 2,500 per month; Tk. 30,000 maximum in a year
                                </pre>
                            </div>

                            <div class="clearfix"></div>
                            <br />


                            <div id="formula_two_container" style="display:none;">
                                <pre>
- House Rent = (30 % of Gross)
- Basic = (50 % of Gross)
- Medical = 10 % of Gross
- Conveyance = 10 % of Gross
                                </pre>
                            </div>

                            <div id="formula_one_area">
                                <?php
                                if ($priority >= $range_start && $priority <= $range_end) {
                                    $gross_salary = round($salary->payroll_salary_finalized);
                                    $net_salary = round($salary->pa_net_salary_finalized);
                                } else if ($logged_emp_code == $emp_code) {
                                    /*
                                     * if logged-in employee is viewing his own page
                                     * He/she might not have a definition for staff grade permission
                                     * He is allowed to view his own salary
                                     */
                                    $gross_salary = round($salary->payroll_salary_finalized);
                                    $net_salary = round($salary->pa_net_salary_finalized);
                                } else {
                                    $gross_salary = '';
                                    $net_salary = '';
                                }
                                ?>
                                <?php if ($priority >= $range_start && $priority <= $range_end): ?>
                                    <?php if (count($arrHeaders) > 0): ?>
                                        <?php foreach ($arrHeaders AS $Header): ?>
                                            <?php
                                            $salaryHeaderAmount = 0;
                                            $checkbxStatus = FALSE;
                                            if (!empty($arrEmpSalary)) {
                                                foreach ($arrEmpSalary AS $Salary) {
                                                    if ($Salary->PES_PSH_id == $Header->PSH_id) {
                                                        $salaryHeaderAmount = $Salary->PES_amount;
                                                    }
                                                }
                                            }
                                            if (count($arrEmpSalary) > 0) {
                                                if (in_array($Header->PSH_id, $arrEmpSalaryID)) {
                                                    $checkbxStatus = TRUE;
                                                }
                                            }
                                            ?>
                                            <div class="col-md-6 pull-left">
                                                <br/><br/>
                                                <label for="<?php echo $Header->PSH_header_title; ?>"><?php echo $Header->PSH_header_title; ?>:</label>
                                                <?php if ($Header->PSH_is_optional == "yes"): ?>
                                                    &nbsp;&nbsp;<input type="checkbox" value="yes" name="headerchk_<?php echo $Header->PSH_id; ?>" <?php
                                                    if ($checkbxStatus) {
                                                        echo 'checked="checked"';
                                                    }
                                                    ?>/>&nbsp; Is Applicable?
                                                    <br />
                                                    <input type="text" value="<?php echo $salaryHeaderAmount; ?>" id="emp_basic_salary" placeholder="" class="k-textbox" name="headeropt_<?php echo $Header->PSH_id; ?>" type="text" />
                                                <?php else: ?>
                                                    <br />
                                                    <input style="width:80%;" type="text" onkeyup="javascript:getSalary(<?php echo $Header->PSH_id; ?>);" value="<?php echo $salaryHeaderAmount; ?>" id="emp_basic_salary" placeholder="" class="k-textbox <?php
                                                    echo $Header->flag_title;
                                                    ?>" name="headerfix_<?php echo $Header->PSH_id; ?>" type="text" />
                                                       <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?> 
                                <?php elseif ($logged_emp_code == $emp_code): ?>
                                    <?php if (count($arrHeaders) > 0): ?>
                                        <?php foreach ($arrHeaders AS $Header): ?>
                                            <?php
                                            $salaryHeaderAmount = 0;
                                            $checkbxStatus = FALSE;
                                            if (!empty($arrEmpSalary)) {
                                                foreach ($arrEmpSalary AS $Salary) {
                                                    if ($Salary->PES_PSH_id == $Header->PSH_id) {
                                                        $salaryHeaderAmount = $Salary->PES_amount;
                                                    }
                                                }
                                            }
                                            if (count($arrEmpSalary) > 0) {
                                                if (in_array($Header->PSH_id, $arrEmpSalaryID)) {
                                                    $checkbxStatus = TRUE;
                                                }
                                            }
                                            ?>
                                            <div class="col-md-6 pull-left">
                                                <br/><br/>
                                                <label for="<?php echo $Header->PSH_header_title; ?>"><?php echo $Header->PSH_header_title; ?>:</label>
                                                <?php if ($Header->PSH_is_optional == "yes"): ?>
                                                    &nbsp;&nbsp;<input type="checkbox" value="yes" name="headerchk_<?php echo $Header->PSH_id; ?>" <?php
                                                    if ($checkbxStatus) {
                                                        echo 'checked="checked"';
                                                    }
                                                    ?>/>&nbsp; Is Applicable?
                                                    <br />
                                                    <input type="text" value="<?php echo $salaryHeaderAmount; ?>" id="emp_basic_salary" placeholder="" class="k-textbox" name="headeropt_<?php echo $Header->PSH_id; ?>" type="text" />
                                                <?php else: ?>
                                                    <br />
                                                    <input style="width:80%;" type="text" onkeyup="javascript:getSalary(<?php echo $Header->PSH_id; ?>);" value="<?php echo $salaryHeaderAmount; ?>" id="emp_basic_salary" placeholder="" class="k-textbox <?php
                                                    echo $Header->flag_title;
                                                    ?>" name="headerfix_<?php echo $Header->PSH_id; ?>" type="text" />
                                                       <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?> 

                                <?php else: ?>
                                    <font style="color: darkgray; text-align: center;"><i>You are not authorized to see salary information for this employee.</i></font>
                                <?php endif; ?>

                                <div class="clearfix"></div>
                                <br/>

                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        $("#formula_one_details").click(function () {
                                            $("#formula_one_container").toggle();
                                            $("#formula_two_container").hide();
                                        });

                                        $("#formula_two_details").click(function () {
                                            $("#formula_two_container").toggle();
                                            $("#formula_one_container").hide();
                                        });

                                        var sup_employees = jQuery("#indirect_supervisor_id").kendoComboBox({
                                            placeholder: "Select employee...",
                                            autoBind: true,
                                            dataTextField: "emp_name",
                                            dataValueField: "emp_id",
                                            dataSource: {
                                                transport: {
                                                    read: {
                                                        url: "../../controller/employee_list_supervisor.php",
                                                        type: "GET"
                                                    }
                                                },
                                                schema: {
                                                    data: "data"
                                                }
                                            }
                                        }).data("kendoComboBox");


                                        $("#form_one").click(function () {
                                            if (this.checked) {
                                                $("#formula_one_area").css({"background-color": "whitesmoke"});
                                                // $("#formula_two_area").hide();
                                                $("#form_two").prop("checked", false);
                                                $(".gross").keyup(function () {
                                                    //Formula one: 
                                                    //Description: House Rent = (Gross – Medical – Conveyance) / 3
                                                    //Basic = House Rent * 2
                                                    //Medical = 10 % of Gross OR Tk. 1,20,000 maximum in a year (Tk. 10,000 per month)—whichever is lower
                                                    //Conveyance = - Conveyance = Tk. 2,500 per month; Tk. 30,000 maximum in a year

                                                    var gross_salary = 0;
                                                    var basic_salary = 0;
                                                    var medical = 0;
                                                    var yearly_medical = 0;
                                                    var medical_final = 0;
                                                    var conveyance = 2500;
                                                    var hra = 0;

                                                    //Collect gross salary
                                                    gross_salary = $(".gross").val();

                                                    //generate medical cost 
                                                    medical_final = gross_salary * 0.1;

                                                    //Check if this is more than 120,000 a year
                                                    yearly_medical = medical * 12;
                                                    if (yearly_medical > 120000) {
                                                        medical_final = 10000;
                                                    }

                                                    //Generate home rent
                                                    hra = (gross_salary - medical_final - conveyance) / 3;

                                                    //Basic salary
                                                    basic_salary = hra * 2;

                                                    var basic_salary_final = Math.round(basic_salary);
                                                    var hra_final = Math.round(hra);
                                                    var medical_final_two = Math.round(medical_final);

                                                    $(".basic").val(basic_salary_final);
                                                    $(".hra").val(hra_final);
                                                    $(".medical").val(medical_final_two);
                                                    $(".conveyance").val(conveyance);

                                                });
                                            } else {
                                                //$("#formula_one_area").hide();
                                                $("#formula_one_area").css({"background-color": "white"});
                                            }
                                        });

                                        $("#form_two").click(function () {
                                            if (this.checked) {
                                                //$("#formula_two_area").show();

                                                $("#formula_one_area").css({"background-color": "lightyellow"});
                                                $("#form_one").prop("checked", false);
                                                $(".gross").keyup(function () {
                                                    //Formula two: 
                                                    //Description: House Rent = (Gross – Medical – Conveyance) / 3
                                                    //Basic = House Rent * 2
                                                    //Medical = 10 % of Gross OR Tk. 1,20,000 maximum in a year (Tk. 10,000 per month)—whichever is lower
                                                    //Conveyance = - Conveyance = Tk. 2,500 per month; Tk. 30,000 maximum in a year

                                                    var gross_salary = 0;
                                                    var basic_salary = 0;
                                                    var medical = 0;
                                                    var yearly_medical = 0;
                                                    var medical_final = 0;
                                                    var conveyance = 0;
                                                    var hra = 0;

                                                    //Collect gross salary
                                                    gross_salary = $(".gross").val();

                                                    //generate medical cost 
                                                    medical_final = gross_salary * 0.1;


                                                    //Generate home rent :: 30% of gross
                                                    hra = gross_salary * 0.3;

                                                    //Basic salary
                                                    basic_salary = gross_salary * 0.5;

                                                    conveyance = gross_salary * 0.1;

                                                    var basic_salary_final = Math.round(basic_salary);
                                                    var hra_final = Math.round(hra);
                                                    var medical_final_two = Math.round(medical_final);
                                                    var conveyance_final = Math.round(conveyance);
                                                    $(".basic").val(basic_salary_final);
                                                    $(".hra").val(hra_final);
                                                    $(".medical").val(medical_final_two);
                                                    $(".conveyance").val(conveyance_final);
                                                });
                                            } else {
                                                //$("#formula_two_area").hide();
                                                $("#formula_one_area").css({"background-color": "white"});
                                            }
                                        });

                                    });
                                </script>
                                <div class="clearfix"></div>
                                <hr />
                                <div class="col-md-6">
                                    <label for="Full name">Salary Bank Account.:</label> <br />
                                    <input type="text" value="<?php echo $emp_account_number; ?>" placeholder="" class="k-textbox" name="emp_account_number" type="text" style="width: 80%;"/>
                                </div>
                                <div class="col-md-6">
                                    <label for="Full name">Salary Bank Title:</label><br />
                                    <input type="text" value="<?php echo $emp_bank_title; ?>" placeholder="" class="k-textbox" name="emp_bank_title" type="text" style="width: 80%;"/>
                                </div>
                                <div class="clearfix"></div>
                                <br/>
                            </div>
                            <span class="cloudy">&nbsp;</span>
                        </div>
                    </div>
                    <div>
                        <div class="weather">

                            <!--Select employees-->
                            <script type="text/javascript">
                                jQuery(document).ready(function () {
                                    var employees = jQuery("#employees").kendoComboBox({
                                        placeholder: "Select employee...",
                                        dataTextField: "emp_name",
                                        dataValueField: "emp_code",
                                        dataSource: {
                                            transport: {
                                                read: {
                                                    url: "../../controller/employee_list.php",
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
                            <div class="col-md-6">
                                <label for="Full name">In Replacement of:</label><br />
                                <div class="options">
                                    <input type="text" id="employees" value="<?php echo $emp_replacement_of; ?>" name="emp_replacement_of" style="width:80%">
                                </div>
                            </div>
                            <?php if ($_SESSION["is_super"] == 'yes'): ?>
                                <div class="col-md-6">
                                    <label for="Full name">Assign a type</label><br />
                                    <div class="options">
                                        <select id="user_type_combo" name="user_type_value" style="width:80%">
                                            <option value="">Select a Role</option>
                                            <?php $types = $con->SelectAll("user_type"); ?>
                                            <?php if (count($types) >= 1): ?>
                                                <?php foreach ($types as $type): ?>
                                                    <option value="<?php echo $type->user_type_value; ?>" 
                                                    <?php
                                                    if ($type->user_type_value == $user_type_value) {
                                                        echo "selected='selected'";
                                                    }
                                                    ?>
                                                            ><?php echo $type->user_type_title; ?></option> 
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <br />
                            <?php endif; ?>
                            <div class="clearfix"></div><br />
                            <div class="col-md-6">
                                <label for="Full name">Assign a Role</label><br />
                                <div class="options">
                                    <select id="size30" name="em_role_id" style="width:80%">
                                        <option value="0">Select a Role</option>
                                        <?php $roles = $con->SelectAll("employee_role"); ?>
                                        <?php if (count($roles) >= 1): ?>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo $role->em_role_id; ?>" 
                                                <?php
                                                if ($role->em_role_id == $em_role_id) {
                                                    echo "selected='selected'";
                                                }
                                                ?>
                                                        ><?php echo $role->role_type; ?></option> 
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                    </select>
                                </div>
                            </div>


                            <?php $id_type_array = $con->SelectAll("id_type"); ?>
                            <div class="col-md-6">
                                <label for="Full name">Id Type:</label> <br />
                                <div id="options">
                                    <select id="id_type" style="width: 80%" name="id_type">
                                        <option value="">Select Id Type</option>
                                        <?php if (count($id_type_array) >= 1): ?>
                                            <?php foreach ($id_type_array as $la): ?>
                                                <option value="<?php echo $la->id_type_title; ?>" 
                                                <?php
                                                if ($la->id_type_title == $id_type) {
                                                    echo "selected='selected'";
                                                }
                                                ?>><?php echo $la->id_type_title; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                    </select>
                                </div> 
                            </div>

                            <!--Provident fund eligibility starts from-->

                            <div class="clearfix"></div>
                            <br />


                            <div class="col-md-6" id="pf_effective_from">
                                <label for="pf_effective_from">ID No:</label> <br />
                                <input type="text" class="k-textbox" name="id_no" value="<?php echo $id_no; ?>" placeholder="" class="k-textbox"  style="width: 80%;"/>
                            </div>


                            <div class="clearfix"></div>
                            <br/>

                            <!--Provident fund starts here-->
                            <div class="col-md-6" style="margin-top: 10px;">
                                <input type="checkbox" <?php
                                if ($is_pf_eligible == 'yes') {
                                    echo "checked ";
                                }
                                ?> name="is_pf_eligible">
                                <label for="is_pf_eligible">Provident Fund Eligible?</label>
                            </div>

                            <!--Provident fund eligibility starts from-->
                            <div class="col-md-6" id="pf_effective_from">
                                <label for="pf_effective_from">PF Effective From:</label> <br />
                                <input type="text" class="pf_effective_from" value="<?php echo $pf_effective_from_date_value; ?>" placeholder="" class="k-textbox" name="pf_effective_from" style="width: 80%;"/>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $(".pf_effective_from").kendoDatePicker();
                                });
                            </script>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-4">
                                <label for="is_ot_eligible">Is OT Eligible?</label>
                                <input type="checkbox" name="is_ot_eligible" <?php
                                if ($is_ot_eligible == 1) {
                                    echo 'checked';
                                }
                                ?>> Yes
                            </div>


                            <div class="col-md-4">
                                <label for="is_ot_eligible">Night Allowance Eligible</label>
                                <input type="checkbox" name="night_allowance_eligible" <?php
                                if ($night_allowance_eligible == 1) {
                                    echo 'checked';
                                }
                                ?>> Yes
                            </div>

                            <div class="col-md-4">
                                <label for="is_ot_eligible">Tiffin Allowance Eligible</label>
                                <input type="checkbox" name="tiffin_allowance_eligible" <?php
                                if ($tiffin_allowance_eligible == 1) {
                                    echo 'checked';
                                }
                                ?>> Yes
                            </div>

                            <div class="clearfix"></div>
                            <br />

                            <!--End of provident fund-->
                            <div class="col-md-6">
                                <div style="width:80%">
                                    <label for="Full name">Upload a Photo:</label> <br />
                                    <input name="emp_photo" id="files" type="file"/>
                                </div>
                            </div>
                            <?php if ($_SESSION["is_super"] == 'yes'): ?>
                                <div class="col-md-6">
                                    <input type="checkbox" name="is_HOD"
                                    <?php
                                    if ($is_HOD == 'yes') {
                                        echo 'checked';
                                    }
                                    ?>>
                                    &nbsp;&nbsp; Is Head of Department? 
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div>

                            <hr />


                            <div class="col-md-6">
                                <div style="width:80%">
                                    <label for="Full name">Upload ID (Scanned Copy):</label>
                                    <br/>
                                    <input name="emp_nid_photo" id="files2" type="file"/>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                            <hr />

                            <!--Multiple Leave UI reveal Animation-->
                            <script language="JavaScript">
                                $(document).ready(function () {
                                    // Listen for click on toggle checkbox :: multiple_leave_types
                                    $('#alternate_attn').click(function (event) {
                                        if (this.checked) {
                                            $("#alternative_company").show(500);
                                            $("#effective_from").show(500);
                                        } else {
                                            $("#alternative_company").hide(500);
                                            $("#effective_from").hide(500);
                                        }
                                    });

                                    if ($("#alternate_attn").is(':checked')) {
                                        $("#alternative_company").show();
                                        $("#effective_from").show();
                                    }
                                });
                            </script>
                            <div class="col-md-12">
                                <p>Please select following check box only if an employee is employed in one company, but having weekend plan of another company.</p>
                            </div>
                            <div class="clearfix"></div>
                            <!--Alternate Company For Weekend Policy-->
                            <div class="col-md-6">
                                <input type="checkbox" <?php
                                if ($alternate_attn_company != '') {
                                    echo "checked ";
                                }
                                ?> value="yes" id="alternate_attn" name="alternate_attn">&nbsp Apply Weekend Policy as Different Company
                            </div>
                            <div class="clearfix"></div>
                            <br />
                            <div class="col-md-6" id="alternative_company" style="display:none;">
                                <select id="alternate_attn_company" style="width: 80%" name="alternate_attn_company">
                                    <option value="0">Select Company</option>
                                    <?php if (count($companies) >= 1): ?>
                                        <?php foreach ($companies as $com): ?>
                                            <option value="<?php echo $com->company_id; ?>" 
                                            <?php
                                            if ($com->company_id == $alternate_attn_company) {
                                                echo "selected='selected'";
                                            }
                                            ?>>
                                                <?php echo $com->company_title; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="clearfix"></div>
                            <br />
                            <div class="col-md-6" id="effective_from" style="display:none;">
                                <label for="Full name">Effective From:</label> <br />
                                <input type="text" class="implement_from_date" value="<?php echo $implement_from_date_as_value; ?>" placeholder="" class="k-textbox" name="implement_from_date" type="text" style="width: 80%;"/>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element
                                    $(".implement_from_date").kendoDatePicker();
                                });
                            </script>
                        </div>
                        <span class="rainy">&nbsp;</span>
                    </div>
                    <style scoped>
                        #forecast {
                            width: 100%;
                            height: auto;
                            margin: 30px auto;
                            padding: 80px 15px 0 15px;
                            background: url('../../content/web/tabstrip/forecast.png') transparent no-repeat 0 0;
                        }

                        .sunny, .cloudy, .rainy {
                            display: inline-block;
                            margin: 20px 0 20px 10px;
                            width: 128px;
                            height: auto;
                            background: url('../../content/web/tabstrip/weather.png') transparent no-repeat 0 0;
                        }
                        .cloudy{
                            background-position: -128px 0;
                        }
                        .rainy{
                            background-position: -256px 0;
                        }
                        .weather {
                            width: 100%;
                            padding: 40px 0 0 0;
                        }
                        #forecast h2 {
                            font-weight: lighter;
                            font-size: 5em;
                            padding: 0;
                            margin: 0;
                        }
                        #forecast h2 span {
                            background: none;
                            padding-left: 5px;
                            font-size: .5em;
                            vertical-align: top;
                        }
                        #forecast p {
                            margin: 0;
                            padding: 0;
                        }
                    </style>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            $("#tabstrip").kendoTabStrip({
                                animation: {
                                    open: {
                                        effects: "fadeIn"
                                    }
                                }
                            });
                            $("#id_type").kendoDropDownList();
                            $("#size").kendoDropDownList();
                            $("#size2").kendoDropDownList();
                            $("#size3").kendoDropDownList();
                            $("#size4").kendoDropDownList();
                            $("#size5").kendoDropDownList();
                            $("#size6").kendoDropDownList();
                            $("#size7").kendoDropDownList();
                            $("#size8").kendoDropDownList();
                            $("#size9").kendoDropDownList();
                            $("#size10").kendoDropDownList();
                            $("#size11").kendoDropDownList();
                            $("#size13").kendoDropDownList();
                            $("#size14").kendoDropDownList();
                            $("#size15").kendoDropDownList();
                            $("#size16").kendoDropDownList();
                            $("#size17").kendoDropDownList();
                            $("#size18").kendoDropDownList();
                            $("#size20").kendoDropDownList();
                            $("#size30").kendoDropDownList();
                            $("#new").kendoDropDownList();
                            $("#user_type_combo").kendoDropDownList();
                            $("#alternate_attn_company").kendoDropDownList();
                            $("#job_location").kendoDropDownList();
                            $("#proposed_confirmation_date").kendoDatePicker();

                            $("#files").kendoUpload();
                            $("#files2").kendoUpload();

                            $("#wedding_date").kendoDatePicker();
                            $("#emp_dateofjoin").kendoDatePicker({
                                onSelect: function (d, i) {
                                    if (d !== i.lastVal) {
                                        $(this).change();
                                    }
                                }
                            });
                            //Add 6 months with joining date
                            //Assign this value as proposed confirmation date
                            $("#emp_dateofjoin").change(function () {
                                var doj = $("#emp_dateofjoin").val();
                                var doj_object = new Date(doj);
                                var one_month_from_your_date = doj_object.add(6).month();
                                var after_six_months = one_month_from_your_date.toString('M/d/yyyy');

                                $("#proposed_confirmation_date").val(after_six_months);
                            });


                            var departments = $("#departments").kendoComboBox({
                                placeholder: "Select department...",
                                dataTextField: "department_title",
                                dataValueField: "department_id",
                                dataSource: {
                                    //                            type: "json",
                                    //                            data: categoriesData

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
                            var dept = $("#departments").val();
                            $("#departments").on('change', function () {
                                dept = $("#departments").val();
                                var sections = $("#sections").kendoComboBox({
                                    autoBind: true,
                                    cascadeFrom: "departments",
                                    placeholder: "Select Section..",
                                    dataTextField: "subsection_title",
                                    dataValueField: "subsection_id",
                                    dataSource: {
                                        //                        type: "json",
                                        //                        data: productsData
                                        transport: {
                                            read: {
                                                url: "../../controller/sub_section.php?dept_id=" + dept,
                                                type: "GET"
                                            }
                                        },
                                        schema: {
                                            data: "data"
                                        }
                                    }
                                }).data("kendoComboBox");
                            });

                            var sections = $("#sections").kendoComboBox({
                                autoBind: true,
                                cascadeFrom: "departments",
                                placeholder: "Select Section..",
                                dataTextField: "subsection_title",
                                dataValueField: "subsection_id",
                                dataSource: {
                                    //                        type: "json",
                                    //                        data: productsData
                                    transport: {
                                        read: {
                                            url: "../../controller/sub_section.php?dept_id=" + dept,
                                            type: "GET"
                                        }
                                    },
                                    schema: {
                                        data: "data"
                                    }
                                }
                            }).data("kendoComboBox");

                            var designations = $("#designations").kendoComboBox({
                                autoBind: true,
                                //cascadeFrom: "departments",
                                placeholder: "Select Designation..",
                                dataTextField: "designation_title",
                                dataValueField: "designation_id",
                                dataSource: {
                                    //                        type: "json",
                                    //                        data: productsData
                                    transport: {
                                        read: {
                                            url: "../../controller/all_designation.php",
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
                    <div class="clearfix"></div>
                </div>
            </div>
            <br />
            <input type="submit" class="k-button" value="Save Changes" name="emp_update" style="float: right;">
            <div class="clearfix"></div>
        </form>
    </div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>