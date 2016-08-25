<?php
session_start();
include("../../config/class.config.php");

$con = new Config();
$open = $con->open();
$err = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if ($con->hasPermissionView($permission_id) != "yes"){
    $con->redirect("../dashboard/index.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

if (isset($_SESSION['emp_code'])) {
    $logged_emp_code = $_SESSION['emp_code'];
}

//Priority range of the logged in employee
$range_start = '';
$range_end = '';

$staff_grade_permission = array();

//Find staff grade permission
$staff_grade_permission = $con->SelectAllByCondition("salary_view_permission", "svp_emp_code='$logged_emp_code'");

if (count($staff_grade_permission) > 0) {
    $range_start = $staff_grade_permission{0}->svp_sg_position_from;
    $range_end = $staff_grade_permission{0}->svp_sg_position_to;
}

$tax_info = array();
if (isset($_POST["tax_generate"])) {
    extract($_POST);
    if ($company_id == 0) {
        $err = "Please select a company";
    } else {

        $check_exist = $con->SelectAllByCondition("payroll_employee_tax", "company_id='$company_id'");
        if (count($check_exist) <= 0) {
            $err = 'No tax data available for selected company.';
        } else {

            //Fetch data against company
            $tax_info_query = "SELECT
                    pet.PET_id,
                    pet.PET_employee_code,
                    tmp.emp_firstname,
                    pet.PET_employee_tax_amount
            FROM
                    payroll_employee_tax pet,
                    tmp_employee tmp
            WHERE
                    pet.company_id = '$company_id'
            AND tmp.emp_code = pet.PET_employee_code";
            $tax_info = $con->QueryResult($tax_info_query);
        }
    }
}

$companies = $con->SelectAll("company");
$tid = '';

if (isset($_GET["delete_tid"])) {
    $tid = $_GET["delete_tid"];
    $delete_array = array(
        "PET_id" => $tid
    );
    if ($con->delete("payroll_employee_tax", $delete_array) == 1) {
        $msg = "Tax Information Succesfully Deleted.";
    } else {
        $err = "Tax Information Could not be Deleted.";
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!--Error/Success message display-->    
<?php include("../layout/msg.php"); ?>
<!--Tax information filtering form-->
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
                    ?>>
                        <?php echo $com->company_title; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-6">
        <br />
        <input type="submit" name="tax_generate" value="View Taxes" class="k-textbox">
    </div>
    <div class="clearfix"></div>
    <hr />
</form>

<?php if (count($tax_info) > 0) : ?>
    <div id="example" class="k-content">
        <table id="grid" style="table-layout: fixed; ">
            <colgroup>
                <col style="width:150px"/>
                <col style="width:150px" />
                <col style="width:150px" />
                <col style="width:150px" />
            </colgroup>
            <thead>
                <tr>
                    <th data-field="emp_code">Employee code</th>
                    <th data-field="emp_firstname">Employee Name</th>
                    <th data-field="tax_amount">Tax Amount</th>
                    <th data-field="action">Edit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($tax_info as $tax):
                    ?>
                    <tr>
                        <?php
                        //Find this employee's staff grade
                        $priority = '';
                        $current_sgrade = array();
                        $current_sgrade = $con->SelectAllByCondition("emp_staff_grade", "es_emp_code='$tax->PET_employee_code' ORDER BY emp_staff_grade_id DESC LIMIT 0,1");

                        if (count($current_sgrade) > 0) {
                            $emp_staff_grade = $current_sgrade{0}->es_staff_grade_id;
                            //find staff grade priority
                            $staff_meta = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
                            if (count($staff_meta) > 0) {
                                $priority = $staff_meta{0}->priority;
                            }
                        }
                        ?>

                        <td style="font-size:11px;"><?php echo $tax->PET_employee_code; ?></td>
                        <td style="font-size:11px;"><?php echo $tax->emp_firstname; ?> </td>

                        <td style="font-size:11px;">
                            <?php
                            if ($priority >= $range_start && $priority <= $range_end) {
                                echo round($tax->PET_employee_tax_amount);
                            } else if ($tax->PET_employee_code == $logged_emp_code) {
                                echo round($tax->PET_employee_tax_amount);
                            } else {
                                echo "<i>unauthorized</i>";
                            }
                            ?>
                        </td>
                        <td> 
                            <?php if ($priority >= $range_start && $priority <= $range_end): ?>
                                <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
                                    <a target="_blank" style="text-decoration: none;" href="edit.php?tid=<?php echo $tax->PET_id; ?>&permission_id=<?php echo $permission_id; ?>" class="k-button">Edit</a>
                                <?php endif; ?>
                            <?php elseif ($tax->PET_employee_code == $logged_emp_code): ?>
                                <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
                                    <a target="_blank" style="text-decoration: none;" href="edit.php?tid=<?php echo $tax->PET_id; ?>&permission_id=<?php echo $permission_id; ?>" class="k-button">Edit</a>
                                <?php endif; ?>
                            <?php else : ?>
                                <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
                                    <a style="text-decoration: none; background-color: lightgoldenrodyellow" class="k-button">Edit</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                endforeach;
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
<?php else: ?>

    <div class="col-md-12" style="padding-top:12px; height:60px; border-radius: 5px; border-style: solid; border-width:1px; border-color: gray; background-color: whitesmoke;" >
        <center><h5 style="vertical-align: middle; color: darkgray;"><i>Tax information will be loaded here</i></h5></center>
    </div>

<?php endif; ?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#company").kendoDropDownList();
    });
</script>
<?php include '../view_layout/footer_view.php'; ?>