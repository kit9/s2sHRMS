<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();
error_reporting(0);
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
$queryemp = "SELECT * FROM struck_off";
$resultemp = mysqli_query($open, $queryemp);
while ($rows_emp = mysqli_fetch_object($resultemp)) {
    $tmp_employees[] = $rows_emp;
}
if (isset($_POST["report"])) {
    $column = array();
    $list = array();

    //Generate today's date
    //Collect and format date from the system.
    $today = date("Y/m/d");
    $sys_date = date_create($today);
    $formatted_today = date_format($sys_date, 'Y-m-d');
    $zero = "0000-00-00";

    //Headers Array
    $columns = array(
        'Employee Code', 'Company', 'Full Name', 'Email', 'Designation', 'Department',
        'Subsection', 'Date of Join', 'Staff Grade', 'Reporting Method', 'Supervisor', 'Gross Salary', 'Gender', 'Prop. Confirmatin Date',
        'Date of Birth', 'Address', 'Phone', 'Resignation Date',
        'Replacement', 'Account No.', 'Bank', 'Blood Group', 'Marital Status', 'City',
        'Country', 'Basic', 'HRA', 'Medical', 'Conveyance', 'Special', 'lunch', 'User_Type'
    );
    array_push($list, $columns);
    /*
     * Making rows
     * Fetching data :: mysql_fetch_array
     */
    $company_id = $_SESSION["company_id"];
    $all_salary = "
	SELECT
	tmp.emp_code,
	com.company_title,
	tmp.emp_firstname,
	tmp.emp_email,
	desg.designation_title,
	dept.department_title,
	tmp.emp_subsection,
	tmp.emp_dateofjoin,
	sg.staffgrade_title,
	rep.reporting_title,
        sup.emp_code AS supervisor,
	tmp.emp_gross_salary,
	tmp.emp_gender,
	tmp.emp_prop_confirmation_date,
	tmp.emp_dateofbirth,
	tmp.emp_address,
	tmp.emp_contact_number,
	tmp.emp_resignation_date,
	tmp.emp_replacement_of,
	tmp.emp_account_number,
	tmp.emp_bank_title,
	tmp.emp_blood_group,
	tmp.emp_marital_status,
	city.city_name,
	con.country_name,
	tmp.emp_basic,
	tmp.emp_hra,
	tmp.emp_medical,
	tmp.conveyance,
	tmp.special,
	tmp.lunch,
	tmp.user_type
FROM
    struck_off tmp
LEFT JOIN department dept ON tmp.emp_department = dept.department_id
LEFT JOIN designation desg ON tmp.emp_designation = desg.designation_id
LEFT JOIN staffgrad sg ON tmp.emp_staff_grade = sg.staffgrade_id
LEFT JOIN company com ON tmp.company_id = com.company_id
LEFT JOIN country con ON tmp.country = con.country_id
LEFT JOIN city ON tmp.city = city.city_id
LEFT JOIN company sal ON tmp.emp_notes_salary_hub = sal.company_id
LEFT JOIN reporting_method rep ON tmp.reporting_id = rep.reporting_id
LEFT JOIN struck_off sup ON tmp.emp_id = sup.supervisor_id ";
    $output = mysqli_query($open, $all_salary);
    $objects = array();
    while ($rows = mysqli_fetch_assoc($output)) {
        $objects[] = $rows;
    }

    foreach ($objects as $key => $val) {
        $arr = array();
        if ($val != '' || $val <= 0) {
            $arr = array_values($val);
        } else {
            $arr = array_values(" ");
        }
        array_push($list, $arr);
    }

    $fp = fopen('stuckoff.xls', 'w');
    foreach ($list as $fields) {
        fputcsv($fp, $fields, "\t", '"');
    }
    fclose($fp);
    header("location: stuckoff.xls");
}
?>

<!--Including headers-->
<?php include '../view_layout/header_view.php'; ?>
<?php include '../../layout/msg.php'; ?>


<div class="clearfix"></div>
<br/>
<form method="post">
    <input type="submit" class="k-button pull-right" name="report" value="Export to Excel">
</form>         
<div class="clearfix"></div>
<br />


<div id="example" class="k-content">
    <form class="form-horizontal" method= "post" enctype="multipart/form-data">
        <table id="grid">
            <colgroup>
                <col style="width:18%"/>
                <col style="width:15%" />
                <col style="width:15%" />
                <col style="width:19%" />
                <col style="width:15%" />
                <col style="width:17%" />
            </colgroup>
            <thead>
                <tr>
                    <th data-field="emp_firstname">Name</th>
                    <th data-field="emp_code">Employee Code</th>
                    <th data-field="emp_designation">Date of Birth</th>
                    <th data-field="emp_department">Phone</th>
                    <th data-field="emp_struck_date">Date</th>
                    <th data-field="action">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($tmp_employees) >= 1): ?>
                    <?php foreach ($tmp_employees as $ap): ?>
                        <tr>
                            <td style="font-size: 14; font-family: calibri;"><?php echo $ap->emp_firstname; ?></td>
                            <td><?php echo $ap->emp_code; ?></td>
                            <td><?php echo $ap->emp_dateofbirth; ?></td>
                            <td><?php echo $ap->emp_contact_number; ?></td>
                            <td><?php echo $ap->struck_off_date; ?></td>
                            <td style="width:250px;" role="gridcell">
                                <a class="k-button k-button-icontext k-grid-edit" href="../struckoff/struck_off_details.php?emp_id=<?php echo $ap->emp_id; ?>"> Details </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?> 
            </tbody>
        </table>
    </form>
    <script>
        $(document).ready(function() {
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


