<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

error_reporting(1);

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
if (isset($_GET["emp_id"])) {
    $empl_id = $_GET["emp_id"];
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

if ($emp_notes_salary_hub != '') {
    $sh = $con->SelectAllByCondition("company", "company_id='$emp_notes_salary_hub'");
    foreach ($sh as $s) {
        $company_title = $s->company_title;
    }
}

//Find company info and assign it as salary hub
$companies = $con->SelectAllByCondition("emp_company", "emp_company_id = (SELECT max(emp_company_id) FROM emp_company where ec_emp_code='$emp_code')");
if (count($companies) > 0) {
    $emp_company_id = $companies{0}->ec_company_id;
}

$company_info = $con->SelectAllByCondition("company", "company_id='$emp_company_id'");
if (count($company_info) > 0) {
    $company_title = $company_info{0}->company_title;
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

/** Find salary information
 * Source table: payroll_employee_salary 
 */
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
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<a href="download.php?emp_id=<?php echo $empl_id; ?>" class="k-button pull-right" style="text-decoration: none;">Export To PDF</a>
<div class="clearfix"></div>
<br/>
<!-- Widget -->
<div class="widget finances_summary widget-inverse">
    <div class="row row-merge">
        <!-- col -->
        <div class="col-sm-12 col-md-3">
            <!-- Profile Photo -->
            <div class="border-bottom">

                <?php if ($emp_photo == '') { ?>
                    <img style="width: 100%; height: 35%; padding: 4%" src="empty.jpg" class="img-responsive img-clean"/>
                <?php } else { ?>
                    <a href="">
                        <img style="width: 100%; height: 35%; padding: 4%" src="<?php echo $con->baseUrl($emp_photo); ?>" class="img-responsive img-clean"/>
                    </a>
                <?php } ?>
            </div>
            <div class="innerAll inner-4x text-center">
                <p class="lead strong margin-none"><?php echo $emp_firstname; ?></p>
                <hr />
                <p class="lead"><?php echo $emp_designation; ?></p>
            </div>
        </div> 
        <div class="col-lg-9 col-md-9 col-sm-12">

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Basic Information</h4>		
                <div class="clearfix"></div>
            </div>
            <div class="innerAll ">
                <div class="col-md-3"><b>Employee Code:</b></div>
                <div class="col-md-3"><?php echo $emp_code; ?></div>

                <div class="col-md-3"><b>Marital Status:</b></div>
                <?php if ($emp_marital_status == '') { ?>
                    Marital Status is not Inserted!
                <?php } else { ?>
                    <div class="col-md-3"><?php echo $emp_marital_status; ?></div>
                <?php } ?>

                <div class="clearfix"></div>
                <br />
                <div class="col-md-3"><b>Gender: </b></div>
                <div class="col-md-3"><?php echo $emp_gender; ?></div>
                <div class="col-md-3"><b>Blood Group:</b> </div>
                <div class="col-md-3"><?php echo $emp_blood_group; ?></div>
                <div class="clearfix"></div>
                <br />
                <div class="col-md-3"><b>Date of Birth:</b></div>
                <div class="col-md-3"><?php echo $emp_dateofbirth; ?></div>
                
                <div class="col-md-3"><b>Wedding Date</b></div>
                <div class="col-md-3"><?php echo $wedding_date; ?></div>
                <div class="clearfix"></div>
                <br/>
                <div class="col-md-3"><b>Spouse Name:</b></div>
                <div class="col-md-3"><?php echo $spouse_name; ?></div>
                
                <div class="col-md-3"><b>No of Children</b></div>
                <div class="col-md-3"><?php echo $no_of_children; ?></div>
                <div class="clearfix"></div>
                <br/>
                
                <div class="col-md-3"><b>Family Member:</b></div>
                <div class="col-md-3"><?php echo $family_member; ?></div>
                
                
                <div class="clearfix"></div>
                
                <br/>
            </div>
            <div class="clearfix"></div>

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Contact Information</h4>		
                <div class="clearfix"></div>
            </div>
            <div class="innerAll ">
                
                <!--Phone number-->
                <div class="col-md-3"><b>Mobile No (Personal): </b></div>
                <div class="col-md-3"><?php echo $emp_phone_personal; ?></div>
                <div class="col-md-3"><b>Mobile No (Office): </b></div>
                <div class="col-md-3"><?php echo $emp_phone_company; ?></div>
                <div class="clearfix"></div>
                <br />
                
                <!--Email number-->
                <div class="col-md-3"><b>Land Phone (Home): </b></div>
                <div class="col-md-3"><?php echo $emp_landphone; ?></div>
                <div class="col-md-3"><b>Email (Office): </b></div>
                <div class="col-md-3"><?php echo $emp_email_office; ?></div>
                <div class="clearfix"></div>
                <br />
                
                <div class="col-md-3"><b>Email (Personal): </b></div>
                <div class="col-md-3"><?php echo $emp_email_personal; ?></div>
                 <div class="clearfix"></div>
                <br />
                
                <div class="col-md-3"><b>Address (Present): </b></div>
                <div class="col-md-6"><?php echo $emp_address_present; ?></div>
                <div class="clearfix"></div>
                <br />
                <div class="col-md-3"><b>Address (Permanent): </b></div>
                <div class="col-md-6"><?php echo $emp_address_parmanent; ?></div>
                <div class="clearfix"></div>
                
            </div>
            <div class="clearfix"></div>

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Job Details</h4>		
                <div class="clearfix"></div>

            </div>
            <div class="innerAll ">
                <div class="col-md-3"><b>Department:</b></div>
                <div class="col-md-3"><?php echo $emp_department; ?></div>
                <div class="col-md-3"><b>Designation: </b></div>
                <div class="col-md-3"><?php echo $emp_designation; ?></div>

                <div class="clearfix"></div>
                <br />

                <div class="col-md-3"><b>Subsection:</b></div>
                <div class="col-md-3"><?php echo $emp_subsection; ?></div>
                <div class="col-md-3"><b>Date of Join:</b></div>
                <div class="col-md-3"><?php echo $emp_dateofjoin; ?></div>
                <div class="clearfix"></div>
                <br />

                <div class="col-md-3"><b>Staff Grade:</b></div>
                <div class="col-md-3"><?php echo $emp_staff_grade; ?></div>
                <div class="col-md-3"><b>Job Location:</b></div>
                <div class="col-md-3"><?php echo $job_location; ?></div>
                <div class="clearfix"></div>
                <br />
            </div>
            <div class="clearfix"></div>

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Salary Information</h4>		
                <div class="clearfix"></div>

            </div>
            <div class="innerAll ">
                <div class="col-md-3"><b>Gross Salary (BDT):</b></div>
                <div class="col-md-3"><?php echo $gross_salary; ?></div>
                <div class="col-md-3"><b>Salary Hub: </b></div>
                <div class="col-md-3"><?php echo $company_title; ?></div>

                <div class="clearfix"></div>
                <br />
                <div class="col-md-3"><b>Bank:</b></div>
                <div class="col-md-3"><?php echo $emp_bank_title; ?></div>

                <div class="col-md-3"><b>Account No:</b></div>
                <?php if ($emp_account_number == 0) { ?>
                    Account number is not Inserted!
                <?php } else { ?>
                    <div class="col-md-3"><?php echo $emp_account_number; ?></div>
                <?php } ?>

                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Other Information</h4>		
                <div class="clearfix"></div>
            </div>
            <div class="innerAll ">
                <div class="col-md-3"><b>Confirmation Date:</b></div>
                <div class="col-md-2"><?php echo $emp_prop_confirmation_date; ?></div>
                <div class="col-md-3"><b>Replacement of: </b></div>
                <div class="col-md-3"><?php echo $emp_replacement_of; ?></div>
                <!--                <div class="clearfix"></div>
                
                                <br />-->
                <!--                <div class="col-md-2"><b>Remarks:</b></div>
                                <div class="clearfix"></div>
                                <div class="col-md-9">
                <?php // echo html_entity_decode($emp_remarks);            ?>
                                </div>-->
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
        </div> 
        <!-- // END col -->
    </div>		
</div>
</div>
<!-- // Content END -->
<?php include '../view_layout/footer_view.php'; ?>