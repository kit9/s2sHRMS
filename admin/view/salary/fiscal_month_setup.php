<?php
session_start();
/** Author: Rajan Hossain + Asma
 * Page: Salary View Permission
 */
//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

date_default_timezone_set('UTC');
$emp_code = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

if (isset($_SESSION["company_id"])) {
    $company_id = $_SESSION['company_id'];
}

if ($_SESSION["is_super"] == 'yes') {
    $is_super = "yes";
}

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
}

//if ($con->hasPermissionView($permission_id) != "yes") {
//    $con->redirect("../dashboard/index.php");
//}

$employee_code = '';
$from = '';
$to = '';
$date = '';
$logged_in = '';
$company_id = '';
$starts_from_previous_month = '';
$fiscal_month_setup_id = '';
$created_at = date("Y-m-d H:i:s");
$start_from = '';
$ends_to = ''; 


if (isset($_GET["fiscal_month_setup_id"])){
    $fiscal_month_setup_id = $_GET["fiscal_month_setup_id"];
    /**
     * find all the values to edit
     */
    $fiscal_month_info_alone = array();
    $fiscal_month_info_alone = $con->SelectAllByCondition("fiscal_month_setup", "fiscal_month_setup_id='$fiscal_month_setup_id'");
    if (count($fiscal_month_info_alone) > 0){
        $company_id = $fiscal_month_info_alone{0}->company_id;
        $from = $fiscal_month_info_alone{0}->start_from;
        $to = $fiscal_month_info_alone{0}->ends_to;
        $starts_from_previous_month = $fiscal_month_info_alone{0}->starts_from_previous_month;
    }

    //Turn on edit mode flag
    $edit_mode = 1;
} else {
    $edit_mode = 0;
}


if (isset($_POST["save_settings"])) {
    extract($_POST);

    /*
     * Generate boolean value for 'starts from previous month'
     */
    if ($starts_from_previous_month == "on") {
        $starts_from_previous_month = "yes";
    } else {
        $starts_from_previous_month = "no";
    }

    /*
     * From and to values can be between 1 - 31.
     */
    if ($company_id <= 0) {
        $err = "Please select a company.";
    } else if ($year <= 0) {
        $err = "Please select a year.";
    } else if ($from > 31 || $to > 31) {
        $err = "Start Day or End Day Value can not be more than 31. Because that is longer than length of a month.";
    } else if ($from <= 0 || $to <= 0) {
        $err = "Start Day or End Day Value can not be less than 0. Because that is less than length of a month.";
    } else if ($from > $to && $starts_from_previous_month == 0) {
        $err = "If start day is larger than end day, we are assuming that your salary month starts at previous month. In that case, please check 'Starts at Previous Month'";
    } else {
        /*
         * Now check for duplicate rules for same company
         */
        $duplicate_rule = array();
        $duplicate_rule = $con->SelectAllByCondition("fiscal_month_setup", "company_id='$company_id'");
        if (count($duplicate_rule) > 0) {
            $err = "A rule fiscal month definition for this company already exists.";
        } else {
            $fiscal_info_array = array(
                "company_id" => $company_id,
                "start_from" => $from,
                "ends_to" => $to,
                "starts_from_previous_month" => $starts_from_previous_month,
                "created_by" => $emp_code,
                "created_at" => $created_at
                );
            /*
             * Insert data into table
             * On success through success message
             * On failure, through error message
             */
            $output = $con->insert("fiscal_month_setup", $fiscal_info_array);
            if ($output == 1) {
                $company_id = '';
                $from = '';
                $to = '';
                $starts_from_previous_month = '';
                $msg = "Fiscal month setup information is succesfully saved.";
            } else {
                $err = "Something went wrong.";
            }
        }
    }
}

/**
 * Edit settings information
 */
if (isset($_POST["edit_settings"])) {
    extract($_POST);

    /*
     * Generate custom value for 'starts from previous month'
     */
    if ($starts_from_previous_month == "on") {
        $starts_from_previous_month = "yes";
    } else {
        $starts_from_previous_month = "no";
    }

    /*
     * From and to values can be between 1 - 31.
     */
    
    if ($company_id <= 0) {
        $err = "Please select a company.";
    } else if ($from > 31 || $to > 31) {
        $err = "Start Day or End Day Value can not be more than 31. Because that is longer than length of a month.";
    } else if ($from <= 0 || $to <= 0) {
        $err = "Start Day or End Day Value can not be less than 0. Because that is less than length of a month.";
    } else if ($from > $to && $starts_from_previous_month == 0) {
        $err = "If start day is larger than end day, we are assuming that your salary month starts at previous month. In that case, please check 'Starts at Previous Month'";
    } else {

        $fiscal_info_array = array(
            "fiscal_month_setup_id" => $fiscal_month_setup_id,
            "start_from" => $from,
            "ends_to" => $to,
            "starts_from_previous_month" => $starts_from_previous_month,
            "created_by" => $emp_code,
            "created_at" => $created_at
            );
            /*
             * Insert data into table
             * On success through success message
             * On failure, through error message
             */
            $output = $con->update("fiscal_month_setup", $fiscal_info_array);
            if ($output == 1) {
                $msg = "Fiscal month setup information is succesfully saved.";
            } else {
                $err = "Something went wrong.";
            }

        }
    }

$years = array();
$companies = array();
$years = $con->SelectAll("year");
$companies = $con->SelectAll("company");
?>

<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Fiscal Month Setup</h6></div>
    <div class="widget-body" style="background-color: white;">
        <form method="post">
            <?php include("../../layout/msg.php"); ?>

            <?php if ($edit_mode == 1): ?>
                <a href="fiscal_month_setup.php" class="k-button pull-right" style="text-decoration: none;"> Go Back to Previous Page </a>
            <?php endif; ?>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-6">
                <label for="emp_code">Select a Company:</label><br />
                <select id="company_id" style="width: 80%" name="company_id" <?php if ($edit_mode == 1){echo 'disabled="disabled"';} ?>>
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
                <?php
                function check_previous($starts_from_previous_month) {
                    if ($starts_from_previous_month == 'yes') {
                        echo "checked";
                    }
                }
                ?>

                <div class="clearfix"></div>
                <br />
                <div class="col-md-6">
                    <label for="emp_code">Start Day: &nbsp;&nbsp; <input type="checkbox" <?php check_previous($starts_from_previous_month); ?> name="starts_from_previous_month"> Starts at Previous Month</label><br />
                    <select id="from" style="width: 80%" name="from">
                        <option value="0">Select Start Day</option>
                        <?php for ($i = 1; $i <= 31; $i++): ?> 
                            <option value="<?php echo $i; ?>" 
                                <?php
                                if ($i == $from) {
                                    echo "selected='selected'";
                                }
                                ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="emp_code">End Day: </label><br />
                        <select id="to" style="width: 80%" name="to">
                            <option value="0">Select Start Day</option>
                            <?php for ($i = 1; $i <= 31; $i++): ?> 
                                <option value="<?php echo $i; ?>" 
                                    <?php
                                    if ($i == $to) {
                                        echo "selected='selected'";
                                    }
                                    ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>

                        </div>
                        <div class="clearfix"></div>
                        <br/>
                        <div class="col-md-6">
                            <!-- If in edit mode Load edit settings button -->
                            <!-- If in normal mode Load save settings button -->
                            <?php if ($edit_mode == 1 ): ?>
                                <input type="submit" name="edit_settings" value="Edit Settings" class="k-button">
                            <?php else: ?>    
                                <input type="submit" name="save_settings" value="Save Settings" class="k-button">
                            <?php endif; ?>

                        </div>
                        <div class="clearfix"></div>
                    </form>
                </div>
            </div>
            <!--List of Setups under companies-->
            <div class="widget" style="background-color: white;">
                <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Fiscal Month List</h6></div>
                <div class="widget-body" style="background-color: white;">
                    <div id="grid"></div>
                    <script type="text/javascript">
                        $(document).ready(function() {
                            var dataSource = new kendo.data.DataSource({
                                pageSize: 10,
                                transport: {
                                    read: {
                                        url: "../../controller/fiscal_month_setup_controller.php",
                                        type: "GET"
                                    },
                                    destroy: {
                                        url: "../../controller/fiscal_month_setup_controller.php",
                                        type: "DELETE"
                                    }
                                },
                                autoSync: false,
                                schema: {
                                    data: "data",
                                    total: "data.length",
                                    model: {
                                        id: "fiscal_month_setup_id",
                                        fields: {
                                            fiscal_month_setup_id: {type: "number"},
                                            company_title: {type: "string"},
                                            start_from: {type: "string"},
                                            ends_to: {type: "string"},
                                            starts_from_previous_month: {type: "string"}
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
        pageSizes: true,
        pageSizes: [5, 10, 20, 50],
    },
    sortable: true,
    groupable: true,
    columns: [
    {field: "company_title", title: "Company"},
    {field: "start_from", title: "Start Day"},
    {field: "ends_to", title: "End Day"},
    {field: "starts_from_previous_month", title: "Starts From Previous Month", width: "230px"},
    {field: "edit", title: "Edit", width: "", template: kendo.template($("#fiscal_month_edit").html())}
    ],
    editable: "inline"
}).data("kendoGrid");
});</script>
</div>
</div>

<?php include '../view_layout/footer_view.php'; ?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#company_id").kendoDropDownList();
        $("#year").kendoDropDownList();
        $("#from").kendoDropDownList();
        $("#to").kendoDropDownList();
    });
</script>

//Place edit button inside kendo grid
<script id="fiscal_month_edit" type="text/x-kendo-template">
    <a href="fiscal_month_setup.php?fiscal_month_setup_id=#= fiscal_month_setup_id #" class="k-button">Edit</a>
</script>
