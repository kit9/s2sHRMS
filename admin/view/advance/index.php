<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

$companies = array();
$companies = $con->SelectAll("company");

if ($con->hasPermissionView($permission_id) != "yes") {
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
$advance_information = array();
if (isset($_POST["btnSearch"])) {
    $company_id = $_POST["company_id"];
    $advance_information = $con->SelectAllByCondition("payroll_employee_advance", "company_id='$company_id'");
}
?>
<?php include '../view_layout/header_view.php'; ?>



<script id="edit_advance" type="text/x-kendo-template">
    <a target="_blank" class="k-button k-button-icontext" href="edit.php?add_id=#= PEA_id #&permission_id=#= <?php echo $permission_id; ?> #"><span class="k-icon k-edit"></span>Edit</a>
</script>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">All Advances</h6></div>
    <div class="widget-body" style="background-color: white;">
        <a style="text-align: " target="_blank" class="k-button k-button-icontext pull-right" href="create.php?permission_id=<?php echo $permission_id; ?>"><span class="k-icon k-add"></span>Add Advance</a>
        <div class="clearfix"></div>
        <hr />
        <form method="post">
            <div class="col-md-6" style="padding-left: 0px;">
                <label for="Full name">Company Name:</label><br/> 
                <select id="company_id" style="width: 80%" name="company_id">
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
            <div class="clearfix"></div>
            <br />
            <div class="col-md-2" style="padding-left: 0px;">
                <input class="k-button" type="submit" value="Search" name="btnSearch"/>
            </div>
            <div class="clearfix"></div>
            <br />
        </form>
        <?php if (isset($_POST["btnSearch"])): ?>
            <?php if (count($advance_information) > 0): ?>
                <div id="grid"></div>
            <?php else: ?>
                <div class="col-md-6" style="border: 1px solid red; height: 80px; border-radius: 5px; background-color: lightgoldenrodyellow">
                    <span>No Advance Data Was Found for Selected Company</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                $("#company_id").kendoDropDownList();
                var company_id = "<?php echo $company_id; ?>";
                var dataSource = new kendo.data.DataSource({
                    pageSize: 10,
                    transport: {
                        read: {
                            url: "../../controller/advance/advance_controller.php?company_id=" + company_id,
                            type: "GET"
                        },
                        update: {
                            url: "../../controller/advance/advance_controller.php",
                            type: "POST",
                            complete: function(e) {
                                jQuery("#grid").data("kendoGrid").dataSource.read();
                            }
                        },
                        destroy: {
                            url: "../../controller/advance/advance_controller.php",
                            type: "DELETE"
                        },
                        create: {
                            url: "../../controller/advance/advance_controller.php",
                            type: "PUT",
                            complete: function(e) {
                                jQuery("#grid").data("kendoGrid").dataSource.read();
                            }
                        }
                    },
                    autoSync: false,
                    schema: {
                        data: "data",
                        total: "data.length",
                        model: {
                            id: "PEA_id",
                            fields: {
                                PEA_id: {editable: false, nullable: true},
                                PET_employee_code: {type: "string"},
                                PEA_advance_amount: {type: "string"},
                                PEA_install_amount: {type: "string"},
                                PEA_amount_per_installment: {type: "string"},
                                PEA_paid_amount: {type: "string"},
                                PEA_remain_amount: {type: "string"},
                                PEA_status: {type: "string"},
                                PEA_created_on: {type: "string"},
                                PEA_created_by: {type: "number"},
                                PEA_updated_on: {type: "string"},
                                PEA_updated_by: {type: "number"},
                                year: {type: "year"},
                                month: {type: "month"}
                            }
                        }
                    }
                });
                jQuery("#grid").kendoGrid({
                    dataSource: dataSource,
                    filterable: true,
                    pageable: {
                        refresh: true,
                        input: true,
                        numeric: false,
                        pageSizes: [5, 10, 20, 50]
                    },
                    sortable: true,
                    groupable: true,
                    columns: [
                        {field: "PEA_employee_code", title: "Employee Code", id: "PEA_employee_code", width: "150px"},
                        {field: "PEA_advance_amount", title: "Advance Amount ", width: "150px"},
                        {field: "PEA_amount_per_installment", title: "Payment per Installment", width: "200px"},
                        {field: "PEA_install_amount", title: "Total Installment", width: "150px"},
                        {field: "PEA_status", title: "Status", width: "90px"},
                        {
                            title: "Action", width: "90px",
                            template: kendo.template($("#edit_advance").html())
                        },
                        {command: ["destroy"], title: "Action", width: "100px"}],
                    editable: "inline"
                });
            });
        </script>
    </div>
</div>
<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>