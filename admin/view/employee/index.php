<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();
error_reporting(0);

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Loging out user 
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
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
$queryemp = "SELECT * FROM tmp_employee where emp_code IN";
$resultemp = mysqli_query($open, $queryemp);
while ($rows_emp = mysqli_fetch_object($resultemp)) {
    $tmp_employees[] = $rows_emp ;
}

?>

<!--Including headers-->
<?php include '../view_layout/header_view.php'; ?>
<?php include '../../layout/msg.php'; ?>

<div id="example" class="k-content">
    <form class="form-horizontal" method= "post" enctype="multipart/form-data">
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
                            <tr>
                            <td style="font-size: 14; font-family: calibri;"><?php echo $ap->emp_firstname; ?></td>
                            <td><?php echo $ap->emp_code; ?></td>
                            <td><?php echo $ap->emp_dateofbirth; ?></td>
                            <td><?php echo $ap->emp_contact_number; ?></td>
                            <td style="width:250px;" role="gridcell">
                            <a class="k-button k-button-icontext k-grid-edit" href="details.php?emp_id=<?php echo $ap->emp_id; ?>"> Details </a>
                            <a class="k-button k-button-icontext k-grid-edit" href="edit.php?emp_id=<?php echo $ap->emp_id; ?>"> Edit </a>
                            <!--<a onclick="return confirm('Are you sure you want to delete?');" href="test.php?delid=<?php echo base64_encode($ap->emp_id);?>" class="k-button k-button-icontext k-grid-edit">Delete</a>!-->
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
