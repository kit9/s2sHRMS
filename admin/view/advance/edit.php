<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
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

$PEA_employee_code = '';
$PEA_advance_amount = '';
$PEA_install_amount = '';
$PEA_start_from = '';
$PEA_install_number = '';
$PEA_paid_amount = '';
$PEA_remain_amount = '';
$PEA_status = '';
$emp_code = '';
$total_amount = 0;
$PEA_id = '';

if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
}

if (isset($_GET['add_id'])) {
    $advance_id = $_GET['add_id'];
    $get_advance_data = $con->QueryResult("SELECT * FROM payroll_employee_advance WHERE PEA_id='$advance_id'");
    $PEA_employee_code = $get_advance_data[0]->PEA_employee_code;
    $PEA_advance_amount = $get_advance_data[0]->PEA_advance_amount;
    $PET_employee_tax_amount = $get_advance_data[0]->PEA_remain_amount;
    $total_amount = $get_advance_data[0]->PEA_advance_amount + $get_advance_data[0]->PEA_remain_amount;
    $PEA_install_amount = $get_advance_data[0]->PEA_install_amount;
    $PEA_amount_per_installment = $get_advance_data[0]->PEA_amount_per_installment;
    $PEA_start_date = $get_advance_data[0]->PEA_start_from;
    $PEA_date = date_create("$PEA_start_date");
    $PEA_start_from = date_format($PEA_date, "m/d/Y");
    $PEA_year = $get_advance_data[0]->year;
    $PEA_month = $get_advance_data[0]->month;
    $advance_status = $get_advance_data[0]->PEA_status;
}

if (isset($_POST['btnSave'])) {
    extract($_POST);

    if ($PEA_employee_code == "") {
        $err = "Employee Code is required!";
    } elseif ($PEA_advance_amount == "" OR $PEA_advance_amount <= 0) {
        $err = "Advance Amount is required!";
    } elseif ($PEA_install_amount == "" OR $PEA_install_amount <= 0) {
        $err = "Installment Amount is required!";
    } else {
        $PEA_updated_on = date('Y-m-d');
        $PEA_updated_by = $emp_code;
        $query = "UPDATE payroll_employee_advance SET PEA_advance_amount='$PEA_advance_amount',"
                . "PEA_install_amount='$PEA_install_amount',"
                . "PEA_amount_per_installment='$PEA_amount_per_installment',"
                . "PEA_paid_amount='$PEA_paid_amount',"
                . "PEA_remain_amount='$PEA_remain_amount',"
                . "PEA_status='$PEA_status',"
                . "last_updated_at='$PEA_updated_on',"
                . "last_updated_by='$PEA_updated_by',"
                . "year='$year',"
                . "month='$month',"
                . "PEA_status='$advance_status'"
                . " WHERE PEA_id='$advance_id'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            $msg = "Successfully Updated Advance recored for employee code: " . $emp_code;
        } else {
            $err = "Error in updating Advance record of Employee code : " . $emp_code;
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Edit Advance</h6></div>
    <div class="widget-body" style="background-color: white;">

        <a href="index.php?permission_id=<?php echo $permission_id; ?>" class="k-button pull-right" style="text-decoration: none;">
            All Advances
        </a>
        <div class="clearfix"></div>
        <br />

        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-6">
                <label for="emp_code"> Employee Code : </label><br />
                <input type="text"  name="PEA_employee_code" id="PEA_employee_code" value="<?php echo $PEA_employee_code; ?>" style="width: 80%;">
            </div>

            <div class="col-md-12">
                <hr/>
                <span id="showInfo">Please select an employee first to view info.</span>
                <hr/>
            </div>

            <div class="col-md-6">
                <label for="Full name">Advance Amount:</label><br/>
                <input disabled="disabled" id="totalAdvance" type="number" class="k-textbox"  value="<?php echo $total_amount; ?>" placeholder=""  style="width: 80%;"/>
                <input id="totalAdvanceHidden" type="hidden" name="total_amount" value="<?php echo $total_amount; ?>"/>
            </div>
            <div class="col-md-6">
                <label for="Full name">Total Installment:</label><br/>
                <input type="number" id="installAmount" onkeyup="calculateInstallNumber(this.value);" class="k-textbox"  name="PEA_install_amount" value="<?php echo $PEA_install_amount; ?>" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-6">
                <label for="Full name">Amount Per Installment:</label><br/>
                <input disabled="disabled" type="number" id="installNumber" onkeyup="calculateInstallAmount(this.value);" class="k-textbox"  name="PEA_install_number" value="<?php echo $PEA_amount_per_installment; ?>" placeholder=""  style="width: 80%;"/>
                <input type="hidden" id="installNumberHidden" name="PEA_install_number"/>
            </div>

            <!--            <div class="col-md-12">
                            <br/><br/>
                            <label for="Full name">Installment Start Date:</label><br/>
            <?php // echo $con->DateTimePicker("PEA_start_from", "PEA_start_from", $PEA_start_from, "", ""); ?>
                        </div>-->

            <div class="col-md-6">
                <label for="Full name">Start from Year:</label><br/> 
                <input id="year1" name="year" style="width: 80%;" value="<?php echo $PEA_year; ?>" />
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-6">
                <label for="Full name">Start from Month:</label> <br />
                <input id="month1" name="month" style="width: 80%;" value="<?php echo $PEA_month; ?>" />
            </div>

            <!--Select a status-->
            <div class="col-md-6" style="padding-left: 0px;"> 
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
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#status").kendoDropDownList();
                });
            </script>

            <div class="clearfix"></div>
            <br/>

            <div class="col-md-4">
                <input type="submit" class="k-button" name="btnSave" value="Save Advance">
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>

<script>
    function calTotalAdv(newAdvance) {
        var newAdv = parseFloat(newAdvance);
        var dueAmount = parseFloat($('#dueAdvance').val());
        var totalAmount = dueAmount + newAdv;
        $('#totalAdvance').val(totalAmount);
        $('#totalAdvanceHidden').val(totalAmount);
//        alert(newAdvance + "," + dueAmount + "," + totalAmount);
    }

    function calculateInstallNumber(installAmount) {
//        alert(installAmount);
        var totalAdvncAmount = $('#totalAdvanceHidden').val();
        var InstallNumber = Math.ceil(totalAdvncAmount / installAmount);
        $("#installNumber").val(InstallNumber);
        $("#installNumberHidden").val(InstallNumber);
    }

    function calculateInstallAmount(installNumber) {
//        alert(installNumber);
        var totalAdvncAmount = $('#totalAdvanceHidden').val();
        var InstallAmount = totalAdvncAmount / installNumber;
        $("#installAmount").val(InstallAmount);
    }
    $(document).ready(function() {
        jQuery(document).ready(function() {
            var departments = $("#PEA_employee_code").kendoComboBox({
                placeholder: "Select Employee...",
                dataTextField: "emp_name",
                dataValueField: "emp_code",
                dataSource: {
                    transport: {
                        read: {
                            url: "../../controller/leave_management_controllers/employee_list.php",
                            type: "GET"
                        }
                    },
                    schema: {
                        data: "data"
                    }
                }
            }).data("kendoComboBox");
        });
    });

    var empCode = $("#PEA_employee_code").val();
//        alert(empCode);
    $.ajax({
        type: "POST",
        url: "../../controller/advance/get_emp_advance.php",
        dataType: "json",
        data: {
            empCode: empCode,
        },
        success: function(response) {
            var obj = response;
            console.log(response.msg);
            if (obj.type === "success") {
                var html = '';

                if (obj.object.length > 0) {
                    for (var i = 0; i < obj.object.length; i++) {
                        html += '<h4>Employee Previous Advance Details</h4>';
                        html += '<h6>Employee Code: <strong>' + empCode + '<strong></h6>';
                        html += '<h6>Total Advance Amount: <strong>' + obj.object[i].PEA_advance_amount + '</strong></h6>';
                        html += '<h6>Paid Advance Amount: <strong>' + obj.object[i].PEA_paid_amount + '</strong></h6>';
                        html += '<h6>Remain Advance Amount: <strong>' + obj.object[i].PEA_remain_amount + '</strong></h6>';
                        html += '<h6>Installment Started From: <strong>' + obj.object[i].PEA_start_from + '</strong></h6>';

                        $('#dueAdvance').val(obj.object[i].PEA_remain_amount);
                    }
                } else {
                    html += '<h4>Employee Previous Advance Details</h4>';
                    html += '<h6>No due advance amount found against employee code: <strong>' + empCode + '</strong></h6>';

                    $('#dueAdvance').val(0);
                }

                $("#showInfo").html(html);
            } else {
                alert("Query failed.");
            }
        }
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#year1").kendoComboBox({
            placeholder: "Select Year...",
            dataTextField: "year_name",
            dataValueField: "year_name",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/year.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");

        $("#month1").kendoComboBox({
            autoBind: false,
            cascadeFrom: "year1",
            placeholder: "Select Month..",
            dataTextField: "month",
            dataValueField: "month_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/month.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");

    });
</script>