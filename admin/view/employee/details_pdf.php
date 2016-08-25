<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

error_reporting(0);

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$edit = '';
$user_fullname = '';
$emp_contact_number_2 = '';
$emp_firstname = '';
$emp_lastname = '';
$emp_email = '';
$company_id = '';
$emp_designation = '';
$emp_department = '';
$emp_subsection = '';
$emp_dateofjoin = '';
$staffgrade_title = '';
$emp_gross_salary = '';
$emp_gender = '';
$emp_prop_confirmation_date = '';
$emp_dateofbirth = '';
$emp_blood_group = '';
$emp_contact_number = '';
$emp_resignation_date = '';
$emp_replacement_of = '';
$emp_notes_salary_hub = '';
$emp_bank_title = '';
$emp_remarks = '';
$emp_photo = '';
$emp_marital_status = '';
$department_title = '';
$designation_title = '';
$emp_contact_number_2 = '';
$emp_address = '';
$emp_account_number = '';
$department_title = '';
$job_location = '';


//Storing the employee ID fromm get
if (isset($_GET["empl_id"])) {
    $empl_id = $_GET["empl_id"];
}
//Fetching employee info
//$employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");

$employees = array();
$query_mod = "SELECT * FROM tmp_employee WHERE emp_id='$empl_id'";
$result11 = mysqli_query($open, $query_mod);
while ($rows11 = mysqli_fetch_object($result11)) {
    $employees[] = $rows11;
}

foreach ($employees as $employee) {
    $emp_firstname = $employee->emp_firstname;
    $emp_lastname = $employee->emp_lastname;
    $emp_code = $employee->emp_code;
    $company_id = $employee->company_id;
    $emp_designation = $employee->emp_designation;
    $emp_department = $employee->emp_department;
    $emp_subsection = $employee->emp_subsection;
    $emp_dateofjoin = $employee->emp_dateofjoin;
    $emp_staff_grade = $employee->emp_staff_grade;
    $emp_gross_salary = $employee->emp_gross_salary;

    $emp_gender = $employee->emp_gender;
    $emp_prop_confirmation_date = $employee->emp_prop_confirmation_date;
    $emp_dateofbirth = $employee->emp_dateofbirth;
    $emp_blood_group = $employee->emp_blood_group;
    $emp_contact_number = $employee->emp_contact_number;
    $emp_resignation_date = $employee->emp_resignation_date;
    $emp_replacement_of = $employee->emp_replacement_of;
    $emp_notes_salary_hub = $employee->emp_notes_salary_hub;
    $emp_bank_title = $employee->emp_bank_title;
    $emp_remarks = $employee->emp_remarks;
    $emp_photo = $employee->emp_photo;
    $emp_marital_status = $employee->emp_marital_status;
    $emp_contact_number_2 = $employee->emp_contact_number_2;
    $emp_address = $employee->emp_address;
    $emp_account_number = $employee->emp_account_number;
    $job_location = $employee->job_location;

    //Additional fields
    $emp_email_office = $employee->emp_email_office;
    $emp_email_personal = $employee->emp_email_personal;
    //Contact details
    $emp_address_present = $employee->emp_address_present;
    $emp_address_parmanent = $employee->emp_address_permanent;
    $emp_phone_personal = $employee->emp_phone_personal;
    $emp_phone_company = $employee->emp_phone_company;
    $emp_landphone = $employee->emp_landphone;

    $is_pf_eligible = $employee->is_pf_eligible;
    $is_ot_eligible = $employee->is_ot_eligible;
    $pf_effective_from_date_value = date("m/d/Y", strtotime($employee->pf_effective_from));

    //other s2s specific fields
    $wedding_date = date("Y-m-d", strtotime($employee->wedding_date));
    $family_member = $employee->family_member;
    $no_of_children = $employee->no_of_children;
    $spouse_name = $employee->spouse_name;
}

if ($emp_department != '') {
    $departments = $con->SelectAllByCondition("department", "department_id='$emp_department'");
    foreach ($departments as $dep) {
        $emp_department = $dep->department_title;
    }
}


$priority = '';
if ($emp_staff_grade != '') {
    $sgs = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
    foreach ($sgs as $sg) {
        $emp_staff_grade = $sg->staffgrade_title;
        $priority = $sg->priority;
    }
}

//$con->debug($emp_staff_grade);

if ($emp_subsection != '') {
    $subs = $con->SelectAllByCondition("subsection", "subsection_id='$emp_subsection'");
    foreach ($subs as $sub) {
        $emp_subsection = $sub->subsection_title;
    }
}

if ($emp_designation != '') {
    $desig = $con->SelectAllByCondition("designation", "designation_id='$emp_designation'");
    foreach ($desig as $des) {
        $emp_designation = $des->designation_title;
    }
}

//$con->debug($emp_designation);

if ($emp_notes_salary_hub != '') {
    $sh = $con->SelectAllByCondition("company", "company_id='$emp_notes_salary_hub'");
    foreach ($sh as $s) {
        $company_title = $s->company_title;
    }
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

$salary_info = array();
$gross_salary = '';
$salary_info = $con->QueryResult("SELECT
	pes.*, psh.*
FROM
	payroll_employee_salary AS pes,
	payroll_salary_header AS psh
WHERE
	pes.PES_employee_code = '$emp_code'
AND pes.PES_PSH_id = psh.PSH_id");

if (count($salary_info) > 0) {
    foreach ($salary_info as $info) {
        if ($info->PES_is_gross == 'yes') {
            if ($priority >= $range_start && $priority <= $range_end) {
                $gross_salary = $info->PES_amount;
            } else if ($logged_emp_code == $emp_code) {
                /*
                 * if logged-in employee is viewing his own page
                 * He/she might not have a definition for staff grade permission
                 * He is allowed to view his own salary
                 */
                $gross_salary = $info->PES_amount;
            } else {
                $gross_salary = '';
            }
        }
    }
}
?>


<table border='0' cellpadding='20' style="width: 100%; font-size: 11px;">
    <tr>
        <td width='200'>

            <?php if ($emp_photo == '') { ?>
                <img style="width: 175px; height: 150px; " src="empty.jpg" class="img-responsive img-clean"/>
            <?php } else { ?>
                <a href="">
                    <img style="width: 175px; height:150px;" src="<?php echo $con->baseUrl($emp_photo); ?>" class="img-responsive img-clean"/>
                </a>
            <?php } ?>

            <br/>
            <p class="lead strong margin-none"><?php echo $emp_firstname; ?></p>
            <hr />
            <p class="lead"><?php echo $emp_designation; ?></p>
        </td>
        <td>
            <table style="margin-left: 8px; width: 100%; vertical-align: top;">
                <tr>
                    <td colspan="4">
                        <h4 style="margin-top: -200px;">Basic Information</h4>
                        <hr />
                    </td>
                </tr>
                <tr>
                    <td style="width:25%"><b>Employee Code:</b></td>
                    <td style="width:25%"><?php echo $emp_code; ?></td>
                    <td style="width:25%"><b>Marital Status:</b></td>
                    <td style="width:25%"><?php if ($emp_marital_status == '') { ?>
                            Marital Status is not Inserted!
                        <?php } else { ?>
                            <?php echo $emp_marital_status; ?>
                        <?php } ?> 
                    </td>
                </tr>
                <tr>
                    <td style="width: 25%"><b>Gender: </b></td>
                    <td style="width: 25%"><?php echo $emp_gender; ?></td>
                    <td style="width: 25%"><b>Blood Group:</b></td>
                    <td style="width: 25%"><?php echo $emp_blood_group; ?></td>
                </tr>
                <tr>
                    <td style="width: 25%"><b>Date of Birth:</b></td>
                    <td style="width: 25%"><?php echo $emp_dateofbirth; ?></td>
                    <td style="width: 25%">Wedding Date</td>
                    <td style="width: 25%"><?php echo $wedding_date;?></td>
                </tr>
                <tr>
                    <td style="width: 25%"><b>No of Children:</b></td>
                    <td style="width: 25%"><?php echo $no_of_children; ?></td>
                    <td style="width: 25%">Spouse Name:</td>
                    <td style="width: 25%"><?php echo $spouse_name;?></td>
                </tr>
                 <tr>
                    <td style="width: 25%"><b>Family Member:</b></td>
                    <td style="width: 25%"><?php echo $family_member; ?></td>
                    <td style="width: 25%"></td>
                    <td style="width: 25%"></td>
                </tr>
            </table>
            <table>
                <tr>
                    <td colspan="4">                             
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td width='200'> </td>
        <td style="vertical-align: top;">
            <table border='0' style="width: 100%">
                <tr>
                    <td></td>
                    <td>
                        <table style="width: 100%">
                            <tr>
                                <td colspan="4">
                                    <h4>Contact Information</h4>
                                    <hr />
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%"> <b>Phone No (Office): </b></td>
                                <td style="width: 25%"><?php echo $emp_phone_company; ?></td>
                                <td style="width: 25%"><b>Phone No (Personal): </b></td>
                                <td style="width: 25%"><?php echo $emp_phone_personal;?> </td>
                            </tr>
                             <tr>
                                <td style="width: 25%"> <b>Email (Office): </b></td>
                                <td style="width: 25%"><?php echo $emp_email_office; ?></td>
                                <td style="width: 25%"><b>Email (Personal): </b></td>
                                <td style="width: 25%"><?php echo $emp_email_personal;?> </td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Address (Present): </b></td>
                                <td style="width: 25%"><?php echo $emp_address_present; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Address (Permanent): </b></td>
                                <td style="width: 25%"><?php echo $emp_address_parmanent; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border='0' style="width: 100%">
                <tr>
                    <td></td>
                    <td>
                        <table style="width: 100%">
                            <tr>
                                <td colspan="4">
                                    <h4>Job Details</h4>
                                    <hr />
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%"> <b>Department:</b></td>
                                <td style="width: 25%"><?php echo $emp_department; ?></td>
                                <td style="width: 25%"><b>Designation: </b></td>
                                <td style="width: 25%"><?php echo $emp_designation; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Subsection:</b></td>
                                <td style="width: 25%"><?php echo $emp_subsection; ?></td>
                                <td style="width: 25%"><b>Date of Join:</b></td>
                                <td style="width: 25%"><?php echo $emp_dateofjoin; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Staff Grade:</b></td>
                                <td style="width: 25%"><?php echo $emp_staff_grade; ?></td>
                                <td style="width: 25%"><b>Job Location:</b></td>
                                <td style="width: 25%"><?php echo $job_location; ?></td>

                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border='0' style="width: 100%">
                <tr>
                    <td></td>
                    <td>
                        <table style="width: 100%">
                            <tr>
                                <td colspan="4">
                                    <h4>Salary Information</h4>
                                    <hr />
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Gross Salary (BDT):</b></td>
                                <td style="width: 25%"><?php echo $gross_salary; ?></td>
                                <td style="width: 25%"><b>Salary Hub: </b></td>
                                <td style="width: 25%"><?php echo $company_title; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Bank:</b></td>
                                <td style="width: 25%"><?php echo $emp_bank_title; ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Account No:</b></td>
                                <td style="width: 25%"><?php if ($emp_account_number == 0) { ?>
                                        Account number is not Inserted!
                                    <?php } else { ?>
                                        <?php echo $emp_account_number; ?>
                                    <?php } ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table border='0' style="width: 100%">
                <tr>
                    <td></td>
                    <td>
                        <table style="width: 100%">
                            <tr>
                                <td colspan="4">
                                    <h4>Other Information</h4>
                                    <hr />
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Confirmation Date:</b></td>
                                <td style="width: 25%"><?php echo $emp_prop_confirmation_date; ?></td>
                                <td style="width: 25%"><b>Replacement of: </b></td>
                                <td style="width: 25%"><?php echo $emp_replacement_of; ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

