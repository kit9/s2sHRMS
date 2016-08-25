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
    $emp_id = $_GET["emp_id"];
}
//echo $emp_id;
//Fetching employee info
//$employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");

$employees = array();
$query_mod = "SELECT * FROM struck_off WHERE emp_id='$emp_id'";
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
?>

<table border='0' cellpadding='20' style="width: 100%">
    <tr>
        <td width='200'>
            <a href="">
                <?php if ($emp_photo == '') { ?>
                    <img style="width: 175px;; height: 150px; " src="empty.jpg" class="img-responsive img-clean"/>
                <?php } else { ?>
                    <a href="">
                        <img style="width: 100%; height: 35%; " src="<?php echo $con->baseUrl($emp_photo); ?>" class="img-responsive img-clean"/>
                    </a>
                <?php } ?>
            </a> 
            <br/>
            <p style="margin-left:8px;"><?php echo $emp_firstname; ?></p>
            <p style="margin-left:8px;"><?php echo $emp_designation; ?></p>
        </td>
        <td>
            <table style="margin-left: 8px; width: 100%">
                <tr>
                    <td colspan="4">
                        <h4 style="">Basic Information</h4>
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
        <td>
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
                                <td style="width: 25%"> <b>Phone 1: </b></td>
                                <td style="width: 25%"><?php echo $emp_contact_number; ?></td>
                                <td style="width: 25%"><b>Phone 2: </b></td>
                                <td style="width: 25%"><?php if ($emp_contact_number_2 == '') { ?>
                                        Phone 2 is not Inserted!
                                    <?php } else { ?>
                                        <?php echo $emp_contact_number_2; ?>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 25%"><b>Address: </b></td>
                                <td style="width: 25%"><?php echo $emp_address; ?></td>
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
                                <td style="width: 25%"><?php echo $emp_gross_salary; ?></td>
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

