<?php
session_start();
include("../../config/class.config.php");
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
date_default_timezone_set('UTC');
$con = new Config();
$open = $con->open();
error_reporting(0);

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$companies = array();
$companies = $con->SelectAll("company");
$employees = $con->SelectAll("tmp_employee");

if (isset($_POST["SearchOT"])) {
    extract($_POST);
    if ($company_id == 0) {
        $err = 'Please Select a Company.';
    } else if ($start_date == '') {
        $err = 'You must Select Start Date and End Date Both.';
    } else if ($end_date == '') {
        $err = 'You must Select Start Date and End Date Both.';
    } else {
        //If everything is normal, generate PDF
        $link = "pdf_job_card_group.php?emp_code=$emp_code&company_id=$company_id&department_id=$department_id&start_date=$start_date&end_date=$end_date";
        $con->redirect($link);
    }
}
?>

<?php include '../view_layout/header_view.php'; ?>
<style type="text/css">
    .unique_color{
        background-color: lightblue;
        padding-top:10px;
        padding-left: 10px;
        margin-left:-10px;
        height:45px;
        margin-top: -10px;
        margin-bottom:-10px;
        margin-right:-10px;
    }
</style>
<script>
    $(document).ready(function () {
        $("#company").kendoDropDownList();
        $("#department_id").kendoComboBox({
            placeholder: "Select department...",
            dataTextField: "department_title",
            dataValueField: "department_id",
            dataSource: {
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
        $("#grid").kendoGrid({
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSize: 40,
                pageSizes: true,
                pageSizes: [40, 100, 200],
            },
            sortable: true,
            groupable: true
        });
    });
</script>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Group Job Cards</h6></div>
    <div class="widget-body" style="background-color: white;">
        <div class="col-md-12">
            <?php include("../../layout/msg.php"); ?>
            <form method="POST">
                <div class="col-md-6"> 
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
                <div class="col-md-6"> 
                    <label for="Full name">Department:</label><br/>
                    <input type="text" id="department_id" name="department_id" value="<?php echo $department_id; ?>" placeholder=""  style="width: 80%;"/>
                </div>
                <div class="clearfix"></div>
                <br/>

                <div class="col-md-6">
                    <label>Start Date</label>
                    <div  style="width: 100%;"><?php echo $con->DateTimePicker("start_date", "start_date", $start_date, "width:80%", ""); ?></div>
                </div>
                <div class="col-md-6">
                    <label>End Date</label>
                    <div  style="width: 100%;">
                        <?php echo $con->DateTimePicker("end_date", "end_date", $end_date, "width:80%", ""); ?>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br />
                <div class="col-md-2">
                    <input value="Generate PDF" type="submit" id="SearchOT" class="k-button" name="SearchOT" style="width: 120px; margin-top: 20px; height:30px;"/>
                </div>
                <div class="clearfix"></div>
            </form>
        </div>
        <div class="clearfix"></div>
        <br /><br />

    </div>
</div>

<?php include '../view_layout/footer_view.php'; ?>



