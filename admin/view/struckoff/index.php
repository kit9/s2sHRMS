<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();
error_reporting(0);

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Loging out user 
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//initializing variables
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
$emp_medical = '';
$conveyance = '';
$lunch = '';
$special = '';
$others = '';




/** QUERY FOR TABLE message */
//$tmp_employees = $con->SelectAll("tmp_employee");
$tmp_employees = array();
$queryemp = "SELECT * FROM tmp_employee";
$resultemp = mysqli_query($open, $queryemp);
while ($rows_emp = mysqli_fetch_object($resultemp)) {
    $tmp_employees[] = $rows_emp;
}

if (isset($_GET["emp_id"])) {
    $emp_id = $_GET["emp_id"];

    //Fetching employee info
    $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
    foreach ($employees as $employee) {
        $emp_firstname = $employee->emp_firstname;
        $emp_code = $employee->emp_code;
        $company_id = $employee->company_id;            /// NUll in database...............
        $emp_lastname = $employee->emp_lastname;
        $emp_email = $employee->emp_email;
        $emp_designation = $employee->emp_designation;
        $emp_department = $employee->emp_department;
        $emp_subsection = $employee->emp_subsection;
        $emp_dateofjoin = $employee->emp_dateofjoin;
        $emp_staff_grade = $employee->emp_staff_grade;
        $staffgrade_id = $employee->emp_staff_grade;
        $supervisor_id = $employee->supervisor_id;
        $reporting_id = $employee->reporting_id;
        $attendance_policy_id = $employee->attendance_policy_id;
        $shift_id = $employee->shift_id;
        $emp_gross_salary = $employee->emp_gross_salary;
        $emp_basic_salary = $employee->emp_basic;
        $emp_hra = $employee->emp_hra;
        $lunch = $employee->lunch;
        $special = $employee->special;
        $conveyance = $employee->conveyance;
        $emp_medical = $employee->emp_medical;
        $emp_location = $employee->emp_location;
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
        $emp_city = $employee->emp_city;
        $emp_contact_number_2 = $employee->emp_contact_number_2;
        $emp_address = $employee->emp_address;
        $emp_account_number = $employee->emp_account_number;
        $country_id = $employee->country;
        $city_id = $employee->city;
    }
    $cdob1 = date_create($emp_dateofbirth);
    $bdate1 = date_format($cdob1, 'm/d/Y');

    $doj1 = date_create($emp_dateofjoin);
    $jdate1 = date_format($doj1, 'm/d/Y');

    $pod1 = date_create($emp_prop_confirmation_date);
    $pdate1 = date_format($pod1, 'm/d/Y');
    $emp_array = array(
        "emp_code" => $emp_code,
        "emp_firstname" => $emp_firstname,
        "emp_email" => $emp_email,
        "emp_designation" => $emp_designation,
        "emp_department" => $emp_department,
        "emp_subsection" => $emp_subsection,
        "emp_dateofjoin" => $jdate,
        "emp_staff_grade" => $staffgrade_id,
        "emp_gross_salary" => $emp_gross_salary, //1
        "emp_location" => $emp_location,
        "emp_gender" => $emp_gender,
        "emp_prop_confirmation_date" => $pdate,
        "emp_dateofbirth" => $bdate,
        "emp_address" => $emp_address,
        "emp_contact_number" => $emp_contact_number,
        "emp_resignation_date" => $emp_resignation_date,
        "emp_replacement_of" => $emp_replacement_of,
        "emp_notes_salary_hub" => $emp_notes_salary_hub, //1
        "emp_account_number" => $emp_account_number,
        "emp_bank_title" => $emp_bank_title,
        "emp_remarks" => $temp_emp_remarks,
        "emp_photo" => $uploadPath,
        "emp_blood_group" => $emp_blood_group,
        "emp_marital_status" => $emp_marital_status,
        "emp_city" => $city_id,
        "emp_contact_number_2" => $emp_contact_number_2,
        "emp_basic" => $emp_basic_salary, //2
        "emp_hra" => $emp_hra,
        "emp_medical" => $emp_medical, //2
        "conveyance" => $conveyance,
        "special" => $special,
        "lunch" => $lunch,
        "others" => $others,
        "emp_type" => $emp_type,
        "password" => $emp_password,
        "user_type" => $user_type,
        "supervisor_id" => $supervisor_id,
        "reporting_id" => $reporting_id,
        "attendance_policy_id" => $attendance_policy_id,
        "shift_id" => $shift_id,
        "company_id" => $company_id,
        "country" => $country_id,
        "city" => $city_id
    );
}
?>

<!--Including headers-->
<?php include '../view_layout/header_view.php'; ?>
<?php include '../../layout/msg.php'; ?>

<script src="../../assets/components/library/bootstrap/js/bootstrap.min.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/forms/elements/bootstrap-datepicker/assets/lib/js/bootstrap-datepicker.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/forms/elements/bootstrap-datepicker/assets/custom/js/bootstrap-datepicker.init.js?v=v1.2.3"></script>
<link rel="stylesheet" href="../../assets/css/docs/module.examples.page.bootstrap-datepicker-1.min.css" />
<script src="../../assets/components/library/jquery/jquery-migrate.min.js?v=v1.2.3"></script>
<script src="../../assets/components/library/modernizr/modernizr.js?v=v1.2.3"></script>
<script src="../../assets/components/plugins/less-js/less.min.js?v=v1.2.3"></script>
<script src="../../assets/components/modules/admin/charts/flot/assets/lib/excanvas.js?v=v1.2.3"></script>
<script src="../../assets/components/plugins/browser/ie/ie.prototype.polyfill.js?v=v1.2.3"></script>

<div id="example" class="k-content">

    <!--        <a href="all.php" style="text-decoration: none;" class="k-button pull-right">Struck-Off List</a>-->

    <table id="grid">
        <colgroup>
            <col style="width:18%"/>
            <col style="width:13%" />
            <col style="width:18%" />
            <col style="width:19%" />
            <col style="width:23%" />
        </colgroup>
        <thead>
            <tr>
                <th data-field="emp_firstname">Name</th>
                <th data-field="emp_code">Employee Code</th>
                <th data-field="emp_designation">Date of Birth</th>
                <th data-field="emp_department">Phone</th>
                <th data-field="action">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($tmp_employees) >= 1): ?>
                <?php foreach ($tmp_employees as $ap): ?>
                <form>
                    <tr>
                        <td style="font-size: 14; font-family: calibri;"><?php echo $ap->emp_firstname; ?></td>
                        <td><?php echo $ap->emp_code; ?></td>
                        <td><?php echo $ap->emp_dateofbirth; ?></td>
                        <td><?php echo $ap->emp_contact_number; ?></td>
                        <td style="width:250px;" role="gridcell">
                            <?php if ($con->hasPermissionView($permission_id) == "yes"): ?>
                                <a class="k-button k-button-icontext k-grid-edit" style="text-decoration: none;" target="_blank" href="../employee/details.php?emp_id=<?php echo $ap->emp_id; ?>">Details</a>
                            <?php endif; ?>
                            <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
                                <a class="k-button k-button-icontext k-grid-edit" style="text-decoration: none;" href="struckoff.php?emp_id=<?php echo $ap->emp_id; ?>">Struck Off</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                </form>
            <?php endforeach; ?>
        <?php endif; ?> 


        </tbody>
    </table>

    <script>
        $(document).ready(function () {
            $("#grid").kendoGrid({
                pageable: {
                    refresh: true,
                    input: true,
                    numeric: false,
                    pageSize: 10,
                    pageSizes: true,
                    pageSizes: [10, 20, 50],
                },
                filterable: true,
                sortable: true,
                groupable: true
            });
        });

    </script>


</div>
<?php include '../view_layout/footer_view.php'; ?>
