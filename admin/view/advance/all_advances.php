<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();


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

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
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


//Instatiate variables
$advances = array();
$advance_status = '';
$company_id = '';

//collect company info
$companies = $con->SelectAll("company");

/*
 * Collect advances
 * Filtering options- company, advance status
 */

if (isset($_POST["advance_generate"])) {
    //Globally extract all posted elements
    extract($_POST);


    //Form validation
    if ($company_id == 0) {
        $err = "Please select a company.";
    } elseif ($advance_status == '') {
        $err = "Please select a status.";
    } else {

        //Collect advance data based on status and company
        $advances_query = "SELECT 
            e.emp_code,
            e.emp_firstname,
            d.designation_title,
            dep.department_title,
            sg.staffgrade_title,
	    pea.PEA_advance_amount,
            pea.PEA_remain_amount,
            pea.PEA_id,
            pea.PEA_paid_amount
        FROM
            payroll_employee_advance pea
                LEFT JOIN
            tmp_employee e ON e.emp_code = pea.PEA_employee_code
                LEFT JOIN
            designation d ON e.emp_designation = d.designation_id
                LEFT JOIN
            department dep ON dep.department_id = e.emp_department
            LEFT JOIN
            staffgrad sg on sg.staffgrade_id = e.emp_staff_grade
        WHERE
                pea.company_id = '$company_id' AND
	        pea.PEA_status = '$advance_status'
                ";
        $advances = $con->QueryResult($advances_query);
    }
}
?>

<!--Including header files-->
<?php include '../view_layout/header_view.php'; ?>

<!--Error/Success message display-->    
<?php include("../layout/msg.php"); ?>


<script type="text/javascript">
    $(document).ready(function() {
        $("#company").kendoDropDownList();
        $("#status").kendoDropDownList();
    });

</script>

<style>
    .k-header {font-size:12px;}
    tr { font-size:12px; text-decoration: none;}
</style>

<!--Loan information filtering form-->
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
                    ?>><?php echo $com->company_title; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
        </select>
    </div>

    <!--Select a status-->
    <div class="col-md-4" style="padding-left: 0px;"> 
        <label for="Advance Status">Status:</label><br/> 
        <select id="status" style="width: 80%" name="advance_status">
            <option value="">Select Status</option>
            <option <?php
            if ($advance_status == "pending") {
                echo 'selected="selected"';
            }
            ?> value="pending">Pending</option>
            <option <?php
            if ($advance_status == "closed") {
                echo 'selected="selected"';
            }
            ?> value="closed">Closed</option>
        </select>
    </div>

    <div class="clearfix"></div>
    <br/>
    <input type="submit" name="advance_generate" value="Show Advance" class="k-textbox">
    <hr />
</form>

<!--Advance -->
<?php if (count($advances) > 0) : ?>
    <div id="example" class="k-content">

        <table id="grid" style="table-layout: fixed; ">
            <colgroup>
                <col style="width:150px"/>
                <col style="width:150px" />
                <col style="width:150px" />
                <col style="width:150px" />
                <!--<col style="width:150px" />-->
                <col style="width:150px" />
                <col style="width:150px" />
                <col style="width:150px" />
                <col style="width:170px" />
            </colgroup>
            <thead>
                <tr>
                    <th data-field="emp_code">Employee code</th>
                    <th data-field="emp_firstname">Employee Name</th>
                    <th data-field="department_title">Department</th>
                    <th data-field="designation_title">Designation</th>
                    <!--<th data-field="staffgrade_title">Staff Grade</th>-->
                    <th data-field="Gross Salary">Advance amount</th>
                    <th data-field="Net Salary">Realized</th>
                    <th data-field="Net Salary">Pending</th>
                    <th data-field="Action">Install Plan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($advances) >= 1):
                    foreach ($advances as $advance):
                        ?>
                        <tr>
                            <?php
                            //Find this employee's staff grade
                            $priority = '';
                            $current_sgrade = array();
                            $current_sgrade = $con->SelectAllByCondition("emp_staff_grade", "es_emp_code='$advance->emp_code' ORDER BY emp_staff_grade_id DESC LIMIT 0,1");

                            if (count($current_sgrade) > 0) {
                                $emp_staff_grade = $current_sgrade{0}->es_staff_grade_id;
                                //find staff grade priority
                                $staff_meta = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
                                if (count($staff_meta) > 0) {
                                    $priority = $staff_meta{0}->priority;
                                }
                            }
                            ?>

                            <td style="font-size:11px;"><?php echo $advance->emp_code; ?></td>
                            <td style="font-size:11px;"><?php echo $advance->emp_firstname; ?> </td>
                            <td style="font-size:11px;"><?php echo $advance->designation_title; ?> </td>
                            <td style="font-size:11px;"><?php echo $advance->department_title; ?> </td>
                           <!--<td style="font-size:11px;"><?php //echo $advance->staffgrade_title; ?> </td>-->
                            <td style="font-size:11px;">
                                <?php
                                if ($priority >= $range_start && $priority <= $range_end) {
                                    echo round($advance->PEA_advance_amount);
                                } else if ($advance->emp_code == $logged_emp_code) {
                                    echo round($advance->PEA_advance_amount);
                                } else {
                                    echo "<i>unauthorized</i>";
                                }
                                ?>
                            </td>
                            <td style="font-size:11px;">
                                <?php
                                if ($priority >= $range_start && $priority <= $range_end) {
                                    echo round($advance->PEA_paid_amount);
                                } else if ($advance->emp_code == $logged_emp_code) {
                                    echo round($advance->PEA_paid_amount);
                                } else {
                                    echo "<i>unauthorized</i>";
                                }
                                ?>
                            </td>
                            <td style="font-size:11px;">
                                <?php
                                if ($priority >= $range_start && $priority <= $range_end) {
                                    echo round($advance->PEA_remain_amount);
                                } else if ($advance->emp_code == $logged_emp_code) {
                                    echo round($advance->PEA_remain_amount);
                                } else {
                                    echo "<i>unauthorized</i>";
                                }
                                ?>
                            </td>
                            <td role="gridcell">
                                <?php if ($priority >= $range_start && $priority <= $range_end): ?>
                                    <a style="text-decoration:none;" class="k-button k-button-icontext k-grid-edit" href="installment_plan.php?emp_code=<?php echo $advance->emp_code; ?>&PEA_id=<?php echo $advance->PEA_id; ?>&permission_id=<?php echo $permission_id;?>">
                                        <span class="k-edit"></span>View
                                    </a>
                                <?php elseif ($advance->emp_code == $logged_emp_code): ?>
                                    <a style="text-decoration:none;" class="k-button k-button-icontext k-grid-edit" href="installment_plan.php?emp_code=<?php echo $advance->emp_code; ?>&PEA_id=<?php echo $advance->PEA_id; ?>">
                                        <span class="k-edit"></span>View
                                    </a>
                                <?php else: ?>
                                    <a style="text-decoration:none; background-color: lightgoldenrodyellow" class="k-button k-button-icontext k-grid-edit">
                                        <span class="k-edit"></span>View
                                    </a>
                                <?php endif; ?>
                            </td>

                        </tr>
                        <?php
                    endforeach;
                endif;
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

    <div class="col-md-12" style="padding-top:18px; height:80px; border-radius: 5px; border-style: solid; border-width:1px; border-color: gray; background-color: whitesmoke;" >
        <center><h5 style="vertical-align: middle; color: darkgray;"><i>Advance information will be loaded here</i></h5></center>
    </div>

<?php endif; ?>
<?php include '../view_layout/footer_view.php'; ?>



