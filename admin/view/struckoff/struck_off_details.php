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
if (isset($_GET["emp_id"])) {
    $employee_id = $_GET["emp_id"];
}

//echo $emp_id;
//Fetching employee info
//$employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");

$employees = array();
$query_mod = "SELECT * FROM struck_off WHERE emp_id='$employee_id'";
$result11 = mysqli_query($open, $query_mod);
while ($rows11 = mysqli_fetch_object($result11)) {
    $employees[] = $rows11;
}
foreach ($employees as $employee) {
    $emp_firstname = $employee->emp_firstname;
    $emp_lastname = $employee->emp_lastname;
    $emp_code = $employee->emp_code;
    $emp_email = $employee->emp_email;
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
}

if ($emp_department != '') {
    $departments = $con->SelectAllByCondition("department", "department_id='$emp_department'");
    foreach ($departments as $dep) {
        $emp_department = $dep->department_title;
    }
}



if ($emp_staff_grade != '') {
    $sgs = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
    foreach ($sgs as $sg) {
        $emp_staff_grade = $sg->staffgrade_title;
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
//print_r($employee_id);
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<a href="download.php?emp_id=<?php echo $employee_id; ?>" class="k-button pull-right" style="text-decoration: none;">Export To PDF</a>
<div class="clearfix"></div>
<br/>
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
            <div class="innerAll inner-2x text-center">
                <p class="lead strong margin-none"><?php echo $emp_firstname; ?></p>
                <p class="lead"><?php echo $designation_title; ?></p>
            </div>

        </div> 

        <div class="col-lg-9 col-md-9 col-sm-12">

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Basic Information</h4>		
                <div class="clearfix"></div>

            </div>
            <div class="innerAll ">
                <div class="col-md-2"><b>Employee Code:</b></div>
                <div class="col-md-2"><?php echo $emp_code; ?></div>

                <div class="col-md-2"><b>Marital Status:</b></div>
                <?php if ($emp_marital_status == '') { ?>
                    Marital Status is not Inserted!
                <?php } else { ?>
                    <div class="col-md-3"><?php echo $emp_marital_status; ?></div>
                <?php } ?>

                <div class="clearfix"></div>
                <br />
                <div class="col-md-2"><b>Gender: </b></div>
                <div class="col-md-2"><?php echo $emp_gender; ?></div>
                <div class="col-md-2"><b>Blood Group:</b> </div>
                <div class="col-md-2"><?php echo $emp_blood_group; ?></div>
                <div class="clearfix"></div>
                <br />
                <div class="col-md-2"><b>Date of Birth:</b></div>
                <div class="col-md-3"><?php echo $emp_dateofbirth; ?></div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Contact Information</h4>		
                <div class="clearfix"></div>

            </div>
            <div class="innerAll ">
                <!--                <div class="col-md-2"><b>Country: </b></div>
                                <div class="col-md-2"><?php // echo $emp_location;    ?></div>
                                <div class="col-md-2"><b>City: </b></div>
                                <div class="col-md-2"><?php // echo $emp_city;    ?></div>
                                <div class="clearfix"></div>
                
                                <br />-->


                <div class="col-md-2"><b>Phone 1: </b></div>
                <div class="col-md-2"><?php echo $emp_contact_number; ?></div>

                <div class="col-md-2"><b>Phone 2: </b></div>
                <?php if ($emp_contact_number_2 == '') { ?>
                    Phone 2 is not Inserted!
                <?php } else { ?>
                    <div class="col-md-3"><?php echo $emp_contact_number_2; ?></div>
                <?php } ?>

                <div class="clearfix"></div>
                <br />

                <div class="col-md-2"><b>Address: </b></div>
                <div class="col-md-6"><?php echo $emp_address; ?></div>
                <div class="clearfix"></div>


            </div>
            <div class="clearfix"></div>

            <div class="innerAll half border-bottom">
                <h4 class="pull-left innerT half margin-none">Job Details</h4>		
                <div class="clearfix"></div>

            </div>
            <div class="innerAll ">
                <div class="col-md-2"><b>Department:</b></div>
                <div class="col-md-2"><?php echo $emp_department; ?></div>
                <div class="col-md-2"><b>Designation: </b></div>
                <div class="col-md-3"><?php echo $emp_designation; ?></div>

                <div class="clearfix"></div>

                <br />

                <div class="col-md-2"><b>Subsection:</b></div>
                <div class="col-md-2"><?php echo $emp_subsection; ?></div>
                <div class="col-md-2"><b>Date of Join:</b></div>
                <div class="col-md-3"><?php echo $emp_dateofjoin; ?></div>
                <div class="clearfix"></div>
                <br />

                <div class="col-md-2"><b>Staff Grade:</b></div>
                <div class="col-md-2"><?php echo $emp_staff_grade; ?></div>
                <div class="col-md-2"><b>Job Location:</b></div>
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
                <div class="col-md-2"><b>Gross Salary (BDT):</b></div>
                <div class="col-md-2"><?php echo $emp_gross_salary; ?></div>
                <div class="col-md-2"><b>Salary Hub: </b></div>
                <div class="col-md-3"><?php echo $company_title; ?></div>

                <div class="clearfix"></div>

                <br />
                <div class="col-md-2"><b>Bank:</b></div>
                <div class="col-md-2"><?php echo $emp_bank_title; ?></div>

                <div class="col-md-2"><b>Account No:</b></div>
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
                <?php // echo html_entity_decode($emp_remarks);  ?>
                                </div>-->

                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>

        </div> 
        <!-- // END col -->

    </div>		

</div>
<!-- // END row -->

<!-- // END row-app -->




</div>
<!-- // Content END -->
<?php include '../view_layout/footer_view.php'; ?>