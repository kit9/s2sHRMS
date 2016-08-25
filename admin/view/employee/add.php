<?php
session_start();
error_reporting(0);
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
$emp_firstname = '';
$emp_lastname = '';
$emp_email = '';
$emp_password = '';
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
$emp_medical = '';
$conveyance = '';
$lunch = '';
$special = '';
$others = '';
$emp_code = '';
$department_id = '';
$department_title = '';
$designation_title = '';
$subsection_title = '';
$staffgrade_id = '';
$staffgrade_title = '';
$reporting_id = '';
$reporting_title = '';
$country_id = '';
$city_id = '';
$city_name = '';
$attendance_policy_id = '';
$policy_title = '';
$shift_id = '';
$emp_photo = '';
$emp_type = '';
$emp_password = '';
$user_type = '';
$company_id = '';
$country = '';
$city = '';
$alternate_attn_company = '';
$jd_effective_start_date_as_value = '';
$job_location = '';
$user_type_value = '';
$is_HOD = '';
$is_pf_eligible = '';
$pf_effective_from = '';
$is_ot_eligible = '';

$father_name = '';
$mother_name = '';

//Family details
$family_member = '';
$no_of_children = '';
$spouse_name = '';

//Number generation for emp code
$last_emp_code = '';
$generated_emp_code = '';
$last_emp_code = '';
$built_emp_code = '';

$staffgrades = $con->SelectAll("staffgrad");
$reportings = $con->SelectAll("reporting_method");
$attendances = $con->SelectAll("attendance_policy");
$shifts = $con->SelectAll("shift_policy");

/*
 * Find the last ID and fetch emp code for it.
 * Check this emp_code with existing emp_code, 
 * If unique, then build another code by just adding 1
 */

function getZeroCount($int) {
    $strInt = $int;
    $intLen = strlen($strInt);
    $countZero = 0;
    for ($i = 0; $i < $intLen; $i++) {
        $num = substr($strInt, $i, 1);
        if ($num == 0) {
            $countZero++;
        } else {
            break;
        }
    }
    return $countZero;
}

function getRealNum($int, $countZero) {
    $intLen = strlen($int);
    $realNumberCount = $intLen - $countZero;
    return $getRealNumber = substr($int, ($countZero), $realNumberCount);
}

function generateNumberArray($initialZero, $startNum, $endNum) {
    $arrNumArray = array();
    $firstNoCount = strlen($startNum);
    $totalNumCount = $firstNoCount + $initialZero;
    for ($i = $startNum; $i <= $endNum; $i++) {
        $arrNumArray[] = sprintf('%0' . $totalNumCount . 'd', $i);
    }
    return $arrNumArray;
}

$all_employees = $con->QueryResult("SELECT emp_code from tmp_employee WHERE emp_code = (select MAX(emp_code) from tmp_employee)");
$last_emp_code = $all_employees{0}->emp_code;
$built_emp_code = $last_emp_code + 1;

//Get zeros in last emp code
$zeroCountFirstNum = getZeroCount($last_emp_code);

//Build new emp_code
$firstRealNumber = getRealNum($last_emp_code, $zeroCountFirstNum);
$zeroCountSecondNum = getZeroCount($number2);
$secondRealNumber = getRealNum($built_emp_code, $zeroCountSecondNum);
$CodeArray = generateNumberArray($zeroCountFirstNum, $firstRealNumber, $secondRealNumber);

//build actual employee code
$generated_emp_code = $CodeArray{1};

//Log out 
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//logged in emp_code
if (isset($_SESSION["emp_code"])) {
    $logged_emp_code = $_SESSION["emp_code"];
}

//Declaring local variables
$resul = '';
$err = "";
$msg = '';
$emp_middlename = '';

//Submitting the form
if (isset($_POST["emp_create"])) {
    extract($_POST);

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

    /*
     * Salary information processing
     */

    $arrSalHeadFix = array();
    $arrSalHeadOpt = array();

    $currentTime = date("Y-m-d H-i-s");

    $Qury_sal_header = "select PSH_id,PSH_header_title from payroll_salary_header where PSH_is_optional = 'no'";
    $sal_req_head = $con->QueryResult($Qury_sal_header);
    $totl_req = count($sal_req_head);

    $Sal_id = array();
    $sal_field_empty = 0;
    foreach ($_POST AS $key => $val) {
        $key_salb = explode('_', $key);
        if ($key_salb[0] == "headerfix") {
            foreach ($sal_req_head as $sh) {
                if ($key_salb[1] == $sh->PSH_id) {
                    $Sal_id[] = $key_salb[1];
                }
            }
        }


        if ($key_salb[0] == "headeropt") {
            foreach ($sal_req_head as $sho) {
                if ($key_salb[1] == $sho->PSH_id) {
                    $Sal_id[] = $key_salb[1];
                }
            }
        }
    }

    if ($totl_req > count($Sal_id)) {
        $sal_field_empty = 1;
    }

    //creating an custom array of fixed salary header and their value
    foreach ($_POST AS $key => $val) {
        $key_brkdwn = explode('_', $key);

        if ($key_brkdwn[0] == "headerfix") {
            $arrSalHeadFix[]['id'] = $key_brkdwn[1];
            $arrSalHeadFix[(count($arrSalHeadFix) - 1)]['value'] = $val;
        }
    }

    foreach ($_POST AS $key => $val) {
        $key_brkdwn = explode('_', $key);
        if ($key_brkdwn[0] == "headerchk" AND $val == "yes") {
            $arrSalHeadOpt[]['id'] = $key_brkdwn[1];
            $arrSalHeadOpt[(count($arrSalHeadOpt) - 1)]['value'] = $_POST['headeropt_' . $key_brkdwn[1]];
        }
    }

    /*
     *  Emd of Salary information processing
     */
    
    $first_name_new = $emp_firstname . " $emp_middlename " . $emp_lastname;
    

    $cdob = date_create($emp_dateofbirth);
    $bdate = date_format($cdob, 'Y-m-d');

    $doj = date_create($emp_dateofjoin);
    $jdate = date_format($doj, 'Y-m-d');

    $pod = date_create($emp_prop_confirmation_date);
    $pdate = date_format($pod, 'Y-m-d');

    $raw_date = date_create($implement_from_date);
    $frmt_implement_from_date = date_format($raw_date, 'Y-m-d');

    $raw_implement_from_date_as_value = date_create($implement_from_date);
    $implement_from_date_as_value = date_format($raw_implement_from_date_as_value, 'm/d/Y');

    //PF effective date
    if ($pf_effective_from != '') {
        $pf_effective_from_date = date("Y-m-d", strtotime($pf_effective_from));
        $pf_effective_from_date_value = date("m/d/Y", strtotime($pf_effective_from));
    }
    /*
     * All necessary date parse and value to display
     */

    //Job details effective date 
    $job_details_effective_date = date("Y-m-d", strtotime($effective_from_date));
    $job_details_effective_date_as_value = date("m/d/Y", strtotime($effective_from_date));

    //PF Effective from date
    $pf_effective_from_date = date("Y-m-d", strtotime($pf_effective_from));
    $pf_effective_from_date_value = date("m/d/Y", strtotime($pf_effective_from));

    //Creating image uplaod path
    $targetfolder = '../../uploads/emp_photo/';
    $mainFile = basename($_FILES['emp_photo']['name']);
    $filename = "";
    if ($mainFile != '') {
        $filename = $emp_code . '_' . $mainFile;
    }
    
    $targetfolder = $targetfolder . $filename;
    $uploadPath = substr($targetfolder, 6);

    
    //Nid Scanned
    $targetfolder2 = '../../uploads/emp_id_photo/';
    $mainFile2 = basename($_FILES['emp_nid_photo']['name']);
    $filename2 = "";
    if ($mainFile2 != '') {
        $filename2 = $emp_code . '_' . $mainFile2;
    }
    
    $targetfolder2 = $targetfolder2 . $filename2;
    $uploadPath2 = substr($targetfolder2, 6);
    


    //Parsing html entity from the rich editor
    $temp_emp_remarks = htmlentities($emp_remarks);

    // check gross salary 
    $gross_error = 0;
    $check_gross = $con->QueryResult("SELECT PES_is_gross FROM payroll_employee_salary WHERE PES_employee_code='$emp_code'");
    if (!empty($check_gross)) {
        foreach ($check_gross as $gs) {
            $gross_ck = $gs->PES_is_gross;
            if ($gross_ck == 'yes') {
                $gross_am = $gs->PES_amount;
                if (empty($gross_am)) {
                    $gross_error = 1;
                    $del_array = array('PES_employee_code' => $emp_code);
                    $del_salary_data = $con->delete("payroll_employee_salary", $del_array);
                }
            }
        }
    }
    
    if (empty($emp_firstname)) {
        $err = "Please provide a name.";
    } else if (empty($emp_code)) {
        $err = "Employee code was not entered correctly.";
    } else if (empty($emp_gender)) {
        $err = "Gender is not selected.";
    } else if (empty($emp_address_present)) {
        $err = "Present address is empty.";
    } else if (empty($emp_address_parmanent)) {
        $err = "Parmanent address is empty.";
    } else if (empty($company_id)) {
        $err = "No company is selected.";
    } else if (empty($effective_from_date)) {
        $err = "Please select an effective date in the job details tab.";
    } else if (empty($emp_dateofjoin)) {
        $err = "Please select a joining date in the job details tab.";
    } else if ($alternate_attn == "yes" && $alternate_attn_company == 0) {
        $err = "You checked to apply for an alternative weekend policy. A company must be selected as alternative.";
    } else if ($is_pf_eligible == "yes" && $pf_effective_from_date == 0) {
        $err = "You checked to apply for a provident Fund. A provident Fund effective date must be selected.";
    } else {
        //Checking for existing employee code :: with the auto generated code
        $exist_array = array("emp_code" => $emp_code);
        if ($con->exists("tmp_employee", $exist_array) == 1) {
            $err = "Employee Code Already Exists!";
        } else {
            $emp_array = array(
                "emp_code" => $emp_code, //Auto generated
                "emp_firstname" => $first_name_new,
                "emp_email_office" => $emp_email_office,
                "emp_email_personal" => $emp_email_personal,
                "emp_department" => $emp_department,
                "emp_designation" => $emp_designation,
                "emp_dateofjoin" => $jdate,
                "emp_staff_grade" => $staffgrade_id,
                "emp_location" => $emp_location,
                "emp_gender" => $emp_gender,
                "emp_prop_confirmation_date" => $pdate,
                "emp_dateofbirth" => $bdate,
                "nominee_name" => $nominee_name,
                //Address
                "emp_address_present" => $emp_address_present,
                "emp_address_permanent" => $emp_address_parmanent,
                //Phone numbers                
                "emp_phone_personal" => $emp_phone_personal,
                "emp_phone_company" => $emp_phone_company,
                "emp_landphone" => $emp_landphone,
                //Wedding date :: formatted to y-m-d
                "wedding_date" => date("Y-m-d", strtotime($wedding_date)),
                //Family details
                "family_member" => $family_member,
                "no_of_children" => $no_of_children,
                "spouse_name" => $spouse_name,
                //Other details
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
                "password" => $emp_password,
                //"user_type" => $user_type_value,
                "supervisor_id" => $supervisor_id,
                "reporting_id" => $reporting_id,
                "attendance_policy_id" => $attendance_policy_id,
                "country" => $country,
                "city" => $city,
                "job_location" => $job_location,
                "is_HOD" => $is_HOD,
                "is_pf_eligible" => $is_pf_eligible,
                "is_ot_eligible" => $is_ot_eligible,
                "pf_effective_from" => $pf_effective_from_date,
                //Emergency contact phone
                "emergency_contact_name" => $emergency_contact_name,
                "emergency_contact_phone" => $emergency_contact_phone,
                //Redundant entity for specific purpose
                "company_id" => $company_id,
                
                //New add
                "night_allowance_eligible" => $night_allowance_eligible,
                "tiffin_allowance_eligible" => $tiffin_allowance_eligible,
                
                //Additional Basic Information
                "father_name" => $father_name,
                "mother_name" => $mother_name,
                "emp_brother_name" => $emp_brother_name,
                "emp_brother_occupation" => $emp_brother_occupation,
                "emp_sister_name" => $emp_sister_name,
                "emp_sister_occupation" => $emp_sister_occupation, 

                //present address record
                "emp_address_present_2" => $emp_address_present_2, 
                "emp_address_present_3" => $emp_address_present_3, 
                "emp_phone_personal_2" => $emp_phone_personal_2, 
                "emp_phone_personal_3" => $emp_phone_personal_3, 
                "emp_landphone_2" => $emp_landphone_2,
                "emp_landphone_3" => $emp_landphone_3, 

                "indirect_supervisor_id" =>  $indirect_supervisor_id,

                "id_type" => $id_type,
                "national_id" => $id_no,
                "emp_nid_photo" => $uploadPath2
            );
            
            if ($con->insert("tmp_employee", $emp_array) == 1) {
                
                //Move uploaded photo to upload folder
                if ($filename != "") {
                    move_uploaded_file($_FILES['emp_photo']['tmp_name'], $targetfolder);
                }

                /*
                 * Insert company data
                 */
                $today = date("Y/m/d H:i:s");
                $sys_date = date_create($today);
                $formatted_today = date_format($sys_date, 'Y-m-d H:i:s');
                $emp_company_array = array(
                    "ec_company_id" => $company_id,
                    "ec_emp_code" => $emp_code,
                    "ec_effective_start_date" => $job_details_effective_date,
                    "ec_effective_end_date" => '0000-00-00',
                    "created_at" => $formatted_today,
                    "created_by" => $logged_emp_code
                );

                if ($con->insert("emp_company", $emp_company_array) == 1) {
                    $msg = "A new employee is created successfully.";
                } else {
                    $err = "Employee job details information is saved but company data is not saved!";
                }

                /*
                 * Insert department data
                 */
                $dept_success = 0;
                $emp_dept_array = array(
                    "edept_emp_code" => $emp_code,
                    "edept_dept_id" => $emp_department,
                    "edept_effective_start_date" => $job_details_effective_date,
                    "edept_effective_end_date" => '0000-00-00',
                    "created_at" => $formatted_today,
                    "created_by" => $logged_emp_code
                );
                $dept_success = $con->insert("emp_department", $emp_dept_array);

                /*
                 * Insert designation data
                 */
                $desig_success = 0;
                $emp_desig_array = array(
                    "edes_emp_code" => $emp_code,
                    "edes_designation_id" => $emp_designation,
                    "edes_effective_start_date" => $job_details_effective_date,
                    "edes_effective_end_date" => '0000-00-00',
                    "created_at" => $formatted_today,
                    "created_by" => $logged_emp_code);
                $desig_success = $con->insert("emp_designation", $emp_desig_array);

                /*
                 * Staff grade data insert
                 */
                if ($staffgrade_id > 0) {
                    $grade_success = 0;
                    $emp_grad_array = array(
                        "es_emp_code" => $emp_code,
                        "es_staff_grade_id" => $staffgrade_id,
                        "es_effective_start_date" => $job_details_effective_date,
                        "es_effective_end_date" => '0000-00-00',
                        "created_at" => $formatted_today,
                        "created_by" => $logged_emp_code
                    );
                    $grade_success = $con->insert("emp_staff_grade", $emp_grad_array);
                }


                 /*
		         * Salary data entry start
		         */
		        $gross_flag = '';
		        $gross_finder = array();
		        $currentTime = date("Y-m-d H-i-s");
		        $InsertHeadCheck = TRUE;
		        if (count($arrSalHeadFix) > 0) {
		            foreach ($arrSalHeadFix AS $FixHeader) {

		                $headerID = $FixHeader['id'];
		                $headerAmount = $FixHeader['value'];

		                //Find gross flag and store it in PES table
		                $gross_finder = $con->SelectAllByCondition("payroll_salary_header", "PSH_id = '$headerID'");
		                $gross_flag = $gross_finder{0}->PSH_is_gross;

		                $insertFixHead = '';
		                $insertFixHead .=' PES_company_id = "' . intval($company_id) . '"';
		                $insertFixHead .=', PES_employee_code = "' . mysqli_real_escape_string($open, $emp_code) . '"';
		                $insertFixHead .=', PES_PSH_id = "' . intval($headerID) . '"';
		                $insertFixHead .=', PES_amount = "' . floatval($headerAmount) . '"';
		                $insertFixHead .=', PES_is_gross = "' . $gross_flag . '"';
		                $insertFixHead .=', PES_created_on = "' . mysqli_real_escape_string($open, $currentTime) . '"';
		                $insertFixHead .=', PES_created_by = "' . mysqli_real_escape_string($open, $logged_emp_code) . '"';

		                $sqlInsertFixHead = "INSERT INTO payroll_employee_salary SET $insertFixHead";
		                $resultInsertFixHead = mysqli_query($open, $sqlInsertFixHead);

		                if (!$resultInsertFixHead) {
		                    $InsertHeadCheck = FALSE;
		                    echo "resultInsertFixHead1 error: " . mysqli_error($open);
		                }
		            }
		        }


		        /*
                 * Company wise leave policy is not created yet, now leave polcies are independed
                 * Leave status meta update
                 * Check for approval process for new employee :: not for now
                 * If approved then this employee can be added to leave_status_meta :: not for now
                 */
                $leave_policy = $con->SelectAll("leave_policy");
                $carry_forward_flag = '';
                if (count($leave_policy) > 0) {
                    foreach ($leave_policy as $lp) {
                        if ($lp->is_carried_forward == 'true') {
                            $carry_forward_flag = 1;
                        } else {
                            $carry_forward_flag = '';
                        }
                        /*
                         * Find pro-rate base leave
                         * Calculate prop-rate base balance
                         * Using formula :: pro_rate_based_total_days = ceil((total_days / 365) * total_days_working);
                         */
                        $is_pro_rate_base = $lp->is_pro_rate_base;
                        if ($is_pro_rate_base == "true") {
                            $today_array = explode("-", $jdate);
                            $today_year = $today_array[0];

                            $build_first_date = $today_year . "-01-01";
                            $build_last_date = $today_year . "-12-31";

                            $first_day = date("Y-m-d", strtotime($build_first_date));
                            $last_day = date("Y-m-d", strtotime($build_last_date));

                            if ($jdate > $first_day) {
                                $datetime1 = date_create($jdate);
                                $datetime2 = date_create($build_last_date);
                                $interval = date_diff($datetime1, $datetime2);

                                //From joining date to end of regarding year
                                $total_days_working = $interval->format('%R%a days');
                                $pro_rate_based_total_days = ceil(($lp->total_days / 365) * $total_days_working);
                                $leave_status_meta_array = array(
                                    "emp_code" => $emp_code,
                                    "total_days" => $pro_rate_based_total_days,
                                    "remaining_days" => $pro_rate_based_total_days,
                                    "company_id" => $company_id,
                                    "year" => date("Y", strtotime($jdate)),
                                    "leave_type_id" => $lp->leave_policy_id,
                                    "carry_forward_flag" => $carry_forward_flag,
                                    "created_by" => $logged_emp_code,
                                    "created_at" => $currentTime
                                );
                            } else {
                                $leave_status_meta_array = array(
                                    "emp_code" => $emp_code,
                                    "total_days" => $lp->total_days,
                                    "remaining_days" => $lp->total_days,
                                    "company_id" => $company_id,
                                    "year" => date("Y", strtotime($jdate)),
                                    "leave_type_id" => $lp->leave_policy_id,
                                    "carry_forward_flag" => $carry_forward_flag,
                                    "created_by" => $logged_emp_code,
                                    "created_at" => $currentTime
                                );
                            }
                        } else {
                            $leave_status_meta_array = array(
                                "emp_code" => $emp_code,
                                "total_days" => $lp->total_days,
                                "remaining_days" => $lp->total_days,
                                "company_id" => $company_id,
                                "year" => date("Y", strtotime($jdate)),
                                "leave_type_id" => $lp->leave_policy_id,
                                "carry_forward_flag" => $carry_forward_flag,
                                "created_by" => $logged_emp_code,
                                "created_at" => $currentTime
                            );
                        }
                        $con->insert("leave_status_meta", $leave_status_meta_array);
                    }
                }


            } else {
                $err = "Something went wrong!";
            }
        }

    }
}

//getting salary headers from database
$arrHeaders = array();
$sqlGetHeader = "SELECT * FROM payroll_salary_header WHERE PSH_show_in_tmp_mod = 'yes'";
$resultGetHeader = mysqli_query($con->open(), $sqlGetHeader);
if ($resultGetHeader) {
    while ($resultGetHeaderObj = mysqli_fetch_object($resultGetHeader)) {
        $arrHeaders[] = $resultGetHeaderObj;
    }
} else {
    echo "resultGetHeader query failed.";
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Employee Information</h6></div>
    <div class="widget-body" background-color: white;>
    <?php include("../../layout/msg.php"); ?>

         <div id="example" class="k-content">
            <form method="post" enctype="multipart/form-data" autocomplete="off">
                <div id="tabstrip" style="border-style: none;">
                    <ul>
                        <li class="k-state-active"> Basic Information</li>
                        <li> Contact Information </li>
                        <li> Job Details </li>
                        <li> Salary Information </li>
                        <li> Other Information </li>
                    </ul>
                    <div>
                        <div class="weather">
                            <!--Full Name-->
                            <div class="col-md-4">
                                <label for="Full name">First name: <span style="color: red;">*</span></label> <br />
                                <input type="text" value="<?php echo $emp_firstname; ?>" name="emp_firstname" placeholder="" class="k-textbox" type="text" id="emp_fullname" style="width: 80%;"/>
                            </div>

                            <div class="col-md-4">
                                <label for="Full name">Middle name:</label> <br />
                                <input type="text" value="<?php echo $emp_middlename; ?>" name="emp_middlename" placeholder="" class="k-textbox" type="text" id="emp_middlename" style="width: 80%;"/>
                            </div>

                            <div class="col-md-4">
                                <label for="Full name">Last Name:</label><br />
                                <input type="text" value="<?php echo $emp_lastname ?>" name="emp_lastname" placeholder="" class="k-textbox" type="text" id="emp_fullname"  style="width: 80%;" />
                            </div>
                            <div class="clearfix"></div>
                            <hr />

                            <!--Email-->
                            <div class="col-md-6">
                                <label for="Full name">Marital Status:</label> <br />
                                <div id="options">
                                    <select id="size" name="emp_marital_status" style="width: 80%">
                                        <option value="0">Marital Status</option>
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
                                <input type="text" value="<?php echo $emp_dateofbirth; ?>" id="emp_dateofbirth" placeholder="" name="emp_dateofbirth" type="text" style="width: 80%;"/>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    // create DatePicker from input HTML element

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
                                <input type="text" class="k-textbox" id="family_member" placeholder="" name="spouse_name" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">Wedding Date:</label> <br />
                                <input type="text" value="" id="wedding_date" placeholder="" name="wedding_date" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Full name">Nominee Name:</label> <br />
                                <input class="k-textbox" type="text" value="" id="nominee_name" placeholder="" name="nominee_name" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Father Name">Father's Name:</label> <br />
                                <input class="k-textbox" type="text" id="father_name" placeholder="" value="<?php echo $father_name; ?>" name="father_name" type="text" style="width: 80%;"/>
                            </div>


                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Mother Name">Mother's Name:</label> <br />
                                <input class="k-textbox" type="text" value="<?php echo $mother_name; ?>" id="mother_name" placeholder="" name="mother_name" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Brother Name">Brother's Name:</label> <br />
                                <input class="k-textbox" value="<?php echo $emp_brother_name;  ?>" type="text" value="" id="emp_brother_name" placeholder="" name="emp_brother_name" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Brother Name">Brother's Occupation:</label> <br />
                                <input class="k-textbox" value="<?php echo $emp_brother_occupation; ?>" type="text" value="" id="emp_brother_occupation" placeholder="" name="emp_brother_occupation" type="text" style="width: 80%;"/>
                            </div>

                            <div class="col-md-6">
                                <label for="Brother Name">Sister's Name:</label> <br />
                                <input class="k-textbox" type="text" value="" id="emp_sister_name" placeholder="" name="emp_sister_name" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Sister Occu">Sister's Occupation:</label> <br/>
                                <input class="k-textbox" type="text" value="" id="emp_sister_occupation" placeholder="" name="emp_sister_occupation" type="text" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br />
                        </div>
                    </div>

                    <div>
                        <div class="weather">
                            <!--Contact Information-->
                            <div class="col-md-6">                       
                                <label for="Full name">Country:</label><br/>
                                <input id="countrys" name="country" style="width: 79%; height: 26px;" value="<?php echo $country; ?>" />                                                     
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">City:</label> <br />
                                <label for="citys"></label>
                                <input id="citys" name="city" style="width: 79%;" value="<?php echo $city; ?>" />                                   
                            </div>

                            <script type="text/javascript">
                                $(document).ready(function () {

                                });</script>
                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-6">
                                <label for="Full name">Present Address (Current) : <span style="color: red;">*</span></label> <br />
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
                                <label for="Date of Join">Date of Join:</label> <br />
                                <input type="text" id="emp_dateofjoin" value="<?php echo $emp_dateofjoin; ?>" placeholder="Select Joining Date" class="emp_datepicker" name="emp_dateofjoin" type="text" style="width: 80%;"/>
                            </div>
                            <div class="col-md-6">
                                <label for="Full name">Proposed Confirmation Date:</label> <br />
                                <input id="proposed_confirmation_date" type="text" class="emp_proposed" value="<?php echo $emp_prop_confirmation_date; ?>" placeholder="" class="k-textbox" name="emp_prop_confirmation_date" type="text" style="width: 80%;"/>
                            </div>
                            <div class="clearfix"></div>
                            <hr />


                            <div class="col-md-6">
                                <div class="form-group">



                                    <label for="Company">Company:</label><span style="color: red;"> *</span></label> <br />
                                    <?php $companies = $con->SelectAll("company"); ?>
                                    <div id="options">
                                        <select id="size9" style="width: 80%" name="company_id" >
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

                            <!--Employee code transfer in department tab-->
                            <div class="col-md-6">
                                <label for="Full name">Department:</label><br/> 
                                <input id="departments" name="emp_department" style="width: 80%;" value="<?php echo $emp_department; ?>" />
                            </div>

                            <div class="clearfix"></div><br />
                            <div class="col-md-6">
                                <label for="Full name">Designation:</label> <br />
                                <input id="designations" name="emp_designation" style="width: 80%;" />
                                <!-- auto complete start-->
                            </div>

                            <div class="col-md-6">
                                <label for="Full name">Effective From Date:</label> <br />
                                <input id="effective_from_date" name="effective_from_date" style="width: 80%;" />
                            </div>

                            <!----------------------Group End---------------------------------> 
                            <div class="clearfix"></div><hr />

                            <div class="col-md-6">
                                <label for="Full name">Employee ID: <span style="color: red;">*</span></label> <br />
                                <input autocomplete="off" name="emp_code" value="<?php echo $emp_code; ?>" type="text" class="k-textbox" style="width: 80%;"/>
                            </div>
                            <?php if ($_SESSION["is_super"] == 'yes'): ?>
                                <div class="col-md-6">
                                    <label for="Full name">System Login Password:</label> <br />
                                    <input autocomplete="off" type="password" value="<?php echo $emp_password; ?>" placeholder="" class="k-textbox" name="emp_password" style="width: 80%;"/>
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div> <br />
                            <div class="col-md-6">
                                <label for="Full name">Reporting Method:</label> <br />
                                <div id="options">
                                    <select id="size11" style="width: 80%" name="reporting_id">
                                        <option value="0">Select Reporting Method</option>
                                        <?php if (count($reportings) >= 1): ?>
                                            <?php foreach ($reportings as $rs): ?>
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
                            <div class="col-md-6">
                                <div id="example">
                                    <div class="demo-section k-header">
                                        <label for="Full name">Supervisor (direct):</label> <br />
                                        <div id="options">
                                            <input type="text" id="sup_employees" name="supervisor_id" style="width:80%" value="<?php echo $supervisor_id ?>">
                                        </div> 
                                    </div>
                                </div>
                                <script type="text/javascript">
                                    jQuery(document).ready(function () {
                                        //Replacement employee
                                    });</script>
                            </div>

                            <div class="clearfix"></div><br />

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
                            <!--Effective date-->


                            <div class="clearfix"></div>
                            <br />





                        </div>
                    </div>
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

                            <div id="formula_one" style="margin-top:-30px;">
                                <!--Adding up dynamic salary headers :: Gross salary is hard coded-->

                                <div id="formula_one_area" style="">
                                    <?php if (count($arrHeaders) > 0): ?>
                                        <?php foreach ($arrHeaders AS $Header): ?>
                                            <div class="col-md-6 pull-left">
                                                <br/>
                                                <label for="<?php echo $Header->PSH_header_title; ?>"><?php echo $Header->PSH_header_title; ?>:</label> 
                                                <?php if ($Header->PSH_is_optional == "yes"): ?>
                                                    <input type="checkbox" value="yes" name="headerchk_<?php echo $Header->PSH_id; ?>" id="is_applicabl_<?php echo $Header->PSH_id; ?>" />&nbsp;&nbsp;Is Applicable?
                                                    <br><input type="text" value="" id="emp_basic_salary_<?php echo $Header->PSH_id; ?>" placeholder="" class="k-textbox" name="headeropt_<?php echo $Header->PSH_id; ?>" type="text" style="width: 80%;"  disabled="disabled" />
                                                <?php else: ?>
                                                    <br><input style="width:80%;" onkeyup="javascript:getSalary(<?php echo $Header->PSH_id; ?>);" type="text" value="" id="emp_basic_salary_<?php echo $Header->PSH_id; ?>" placeholder="" class="k-textbox <?php
                                                    echo $Header->flag_title;
                                                    ?>" name="headerfix_<?php echo $Header->PSH_id; ?>" type="text" style="width: 80%;" />
                                                           <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="clearfix"></div>
                                        <br /><br />
                                    <?php endif; ?>
                                </div>

                                <!--Java Script to handle summing up gross salary-->
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


                                                    //Generate home rent
                                                    hra = gross_salary * 0.5;

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
                        </div>
                    </div>
                    <div>
                        <div class="weather">

                            <div class="col-md-6">
                                <label for="Full name">In Replacement of:</label><br />
                                <div class="options">
                                    <input type="text" id="employees" name="emp_replacement_of" style="width:80%">
                                </div>
                            </div>

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

                            <div class="clearfix"></div><br />



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
                            <?php endif; ?>

                            <div class="col-md-6" id="pf_effective_from">
                                <label for="pf_effective_from">ID No:</label> <br />
                                <input type="text" class="k-textbox" name="id_no" value="<?php echo $id_no; ?>" placeholder="" class="k-textbox" name="pf_effective_from" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <hr />

                            <!--Provident fund starts here-->
                            <div class="col-md-6" style="margin-top: 10px;">
                                <input type="checkbox" name="is_pf_eligible" 
                                <?php
                                if ($is_pf_eligible == 'yes') {
                                    echo 'checked';
                                }
                                ?>>
                                <label for="is_pf_eligible">Provident Fund Eligible?</label>
                            </div>

                            <!--Provident fund eligibility starts from-->
                            <div class="col-md-6" id="pf_effective_from">
                                <label for="pf_effective_from">PF Effective From:</label> <br />
                                <input type="text" class="pf_effective_from" value="<?php echo $pf_effective_from_date_value; ?>" placeholder="" class="k-textbox" name="pf_effective_from" style="width: 80%;"/>
                            </div>

                            <div class="clearfix"></div>
                            <br /><br />

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
                            <br /><br />

                            <!--End of provident fund-->

                            <div class="col-md-6">
                                <div style="width:80%">
                                    <label for="Full name">Upload a Photo:</label> <br />
                                    <input name="emp_photo" id="files" type="file"/>
                                </div>
                            </div>

                            <?php if ($_SESSION["is_super"] == 'yes'): ?>
                                <div class="col-md-6">
                                    Head of Department?  &nbsp;&nbsp; 
                                    <input type="checkbox" name="is_HOD" <?php
                                    if ($is_HOD == 'yes') {
                                        echo 'checked';
                                    }
                                    ?>>
                                    Yes
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div>
                            <hr />

                            <!--Provident fund eligibility starts from-->




                            <div class="col-md-6">
                                <div style="width:80%">
                                    <label for="Full name">Upload ID (Scanned Copy):</label>
                                    <br/>
                                    <input name="emp_nid_photo" id="files2" type="file"/>
                                </div>
                            </div>

                            <div class="clearfix"></div>
                            <hr />

                            <div class="col-md-12">
                                <p>Please select following check box only if an employee is employed in one company, but having weekend plan of another company.</p>
                            </div>

                            <div class="clearfix"></div>
                            <!--Alternate Company For Weekend Policy-->
                            <div class="col-md-6">
                                <input type="checkbox" value="yes" id="alternate_attn" name="alternate_attn" <?php
                                if (isset($alternate_attn)) {
                                    echo " checked";
                                }
                                ?>>&nbsp Apply Weekend Policy as Different Company
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
                                            if ($com->company_id == $company_id) {
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
                                <input type="text" class="implement_from_date" value="<?php echo $implement_from_date_as_value; ?>" placeholder="" class="k-textbox" name="implement_from_date" style="width: 80%;"/>
                            </div>
                            <script>
                                $(document).ready(function () {

                                });
                            </script>
                            <script>
                                $(document).ready(function () {
                                    // create Editor from textarea HTML element with default set of tools

                                });</script>
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
                        });
                        $(document).ready(function () {
                            $("#size").kendoDropDownList();
                            $("#job_location").kendoDropDownList();
                            $("#id_type").kendoDropDownList();
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
                            $("#size22").kendoDropDownList();
                            $("#size30").kendoDropDownList();
                            $("#new").kendoDropDownList();
                            $("#user_type_combo").kendoDropDownList();
                            $("#alternate_attn_company").kendoDropDownList();
                            $("#editor").kendoEditor();
                            $(".pf_effective_from").kendoDatePicker();
                            $(".emp_proposed").kendoDatePicker();
                            $(".jd_effective_start_date").kendoDatePicker();
                            $("#effective_from_date").kendoDatePicker();
                            $("#emp_dateofbirth").kendoDatePicker();

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

                            function getSalary() {
                                var sum = 0;
                                $(".salary_head").each(function () {
                                    sum += +$(this).val();
                                });
                                jQuery(".salary_head_gross").val(sum);
                            }

                            // Listen for click on toggle checkbox :: alternate attendance policy
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

                            //Image upload
                            $("#files").kendoUpload();
                            $("#files2").kendoUpload();
                            $("#files3").kendoUpload();
                            $("#files4").kendoUpload();

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

                            var products = $("#sections").kendoComboBox({
                                autoBind: false,
                                cascadeFrom: "departments",
                                placeholder: "Selecsubsection_titlet Section..",
                                dataTextField: "subsection_title",
                                dataValueField: "subsection_id",
                                dataSource: {
                                    //                        type: "json",
                                    //                        data: productsData
                                    transport: {
                                        read: {
                                            url: "../../controller/sub_section.php",
                                            type: "GET"
                                        }
                                    },
                                    schema: {
                                        data: "data"
                                    }
                                }
                            }).data("kendoComboBox");
                            var products = $("#designations").kendoComboBox({
                                autoBind: true,
                                placeholder: "Select Designation..",
                                dataTextField: "designation_title",
                                dataValueField: "designation_id",
                                dataSource: {
                                    //                        type: "json",
                                    //                        data: productsData
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

                            // create DatePicker from input HTML element
                            $("#emp_dateofjoin").kendoDatePicker({
                                onSelect: function (d, i) {
                                    if (d !== i.lastVal) {
                                        $(this).change();
                                    }
                                }
                            }
                            );

                            //Add 6 months with joining date
                            //Assign this value as proposed confirmation date
                            $("#emp_dateofjoin").change(function () {
                                var doj = $("#emp_dateofjoin").val();
                                var doj_object = new Date(doj);
                                var one_month_from_your_date = doj_object.add(6).month();
                                var after_six_months = one_month_from_your_date.toString('M/d/yyyy');

                                $("#proposed_confirmation_date").val(after_six_months);
                            });

                            // create DatePicker from input HTML element
                            $(".implement_from_date").kendoDatePicker();
                            $("#wedding_date").kendoDatePicker();

                            //Replacement employee
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

                            var countrys = $("#countrys").kendoComboBox({
                                placeholder: "Select Country...",
                                dataTextField: "country_name",
                                dataValueField: "country_id",
                                dataSource: {
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
                            var products = $("#citys").kendoComboBox({
                                autoBind: false,
                                cascadeFrom: "countrys",
                                placeholder: "Select City..",
                                dataTextField: "city_name",
                                dataValueField: "city_id",
                                dataSource: {
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


                </div>
        </div>
        <br />
        <input type="submit" class="k-button" value="Create Employee" name="emp_create" style="float: right;">
        <div class="clearfix"></div>
        </form>
    </div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>

