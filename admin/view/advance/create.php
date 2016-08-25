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
$company_id = '';

if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
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
        
        //Find company information for the selected employee
        $today = date("Y/m/d");
        $sys_date = date_create($today);
        $formatted_today = date_format($sys_date, 'Y-m-d');
        $zero = "0000-00-00";
        $query = "SELECT
			ec_company_id
		FROM
			emp_company
		WHERE
			ec_emp_code = '$PEA_employee_code'
		AND (
			(
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date >= '$formatted_today'
			)
			OR (
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date = '$zero'
			)) LIMIT 0,1 ";

        $data = $con->QueryResult($query);
        if (count($data) > 0) {
            $company_id = $data{0}->ec_company_id;
        }
        if ($company_id == '') {
            $err = "Company information was not found for this employee.";
        } else {
            //Check for existing adavnce information for this employee
            $PEA_paid_amount = 0;
            $PEA_remain_amount = $total_amount;
            $PEA_start_from = date("Y-m-d", strtotime($PEA_start_from));

            $currentTime = date("Y-m-d H:i:s");
            $addAdvance = '';
            $addAdvance .=' PEA_employee_code = "' . mysqli_real_escape_string($open, $PEA_employee_code) . '"';
            $addAdvance .=', PEA_advance_amount = "' . floatval($PEA_advance_amount) . '"';
            $addAdvance .=', PEA_install_amount = "' . floatval($PEA_install_amount) . '"';
            $addAdvance .=', PEA_amount_per_installment = "' . intval($PEA_amount_per_installment) . '"';
            $addAdvance .=', PEA_paid_amount = "' . floatval($PEA_paid_amount) . '"';
            $addAdvance .=', PEA_remain_amount = "' . floatval($PEA_advance_amount) . '"';
            $addAdvance .=', PEA_status = "' . mysqli_real_escape_string($open, "pending") . '"';
            $addAdvance .=', PEA_created_on = "' . mysqli_real_escape_string($open, $currentTime) . '"';
            $addAdvance .=', PEA_created_by = "' . mysqli_real_escape_string($open, $emp_code) . '"';
            $addAdvance .=', company_id = "' . mysqli_real_escape_string($open, $company_id) . '"';
            $addAdvance .=', year = "' . mysqli_real_escape_string($open, $year) . '"';
            $addAdvance .=', month = "' . mysqli_real_escape_string($open, $month) . '"';
            $sqlAddAdvance = "INSERT INTO payroll_employee_advance SET $addAdvance";
            $resultAddAdvance = mysqli_query($open, $sqlAddAdvance);
            $last_id = mysqli_insert_id($open);

            if ($resultAddAdvance) {
                $msg = "Advance amount added successfully.";
                /*
                 * On success of advanced assignment
                 * Based on the number of installment, that number of rows will be inserted into 
                 * advance_details table
                 * Over all procedure will be as the installment plan view
                 */
                $master_info = array();
                $master_info = $con->SelectAllByCondition("payroll_employee_advance", "PEA_id='$last_id'");
                $year = $master_info{0}->year;
                $month = $master_info{0}->month;

                $generate_date = $year;
                $generate_date .= "-";
                $generate_date .= $month;
                $generate_date .= "-";
                $generate_date .= "01";

                $frmt_date = date("Y-m-d", strtotime($generate_date));
                $final_date = date("Y-m-d", strtotime("$frmt_date -1 month"));

                for ($i = 1; $i <= $PEA_install_amount; $i++) {

                    $view_month = date("Y-m-d", strtotime("$final_date +$i month"));
                    $rec_month = date("m", strtotime($view_month));
                    $rec_year = date("Y", strtotime($view_month));

                    $matrix = array(
                        "ad_install_no" => $i,
                        "ad_emp_code" => $PEA_employee_code,
                        "PEA_id" => $last_id,
                        "ad_year" => $rec_year,
                        "ad_month" => $rec_month,
                        "advance_total" => $PEA_advance_amount,
                        "advance_due" => $PEA_advance_amount,
                        "amount_per_installment" => $PEA_amount_per_installment
                    );

                    if ($con->insert("advance_details", $matrix) == 1) {
                        //success message
                    } else {
                        //failure message
                    }
                }
            } else {
                $err = "resultAddAdvance query failed." . mysqli_error($open);
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Advance</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-6">
                <label for="emp_code"> Employee Code: </label><br />
                <input type="text" onchange="getEmpAdvInfo()"  name="PEA_employee_code" id="PEA_employee_code" value="<?php echo $PEA_employee_code; ?>" style="width: 80%;">
            </div>

            <div class="col-md-12">
                <hr/>
                <span id="showInfo">Please select an employee first to view info.</span>
                <hr/>
            </div>
            <div class="col-md-6">
                <label for="Full name">New Advance Amount:</label><br/>
                <input type="number" id="new_advance" onkeyup="cnew_advance(this.value);" class="k-textbox"  name="PEA_advance_amount" value="<?php echo $PEA_advance_amount; ?>" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="col-md-6">
                <label for="Full name">Total Installment:</label><br/>
                <input type="number" id="total_installment" onkeyup="ctotal_installment(this.value);" class="k-textbox"  name="PEA_install_amount" value="<?php echo $PEA_install_amount; ?>" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <label for="Full name">Amount Per Installment:</label><br/>
                <input disabled="disabled" type="number" id="amount_per_installment" class="k-textbox"  name="PEA_install_number" value="<?php echo $PEA_install_number; ?>" placeholder=""  style="width: 80%;"/>
                <input type="hidden" id="amount_per_installment_hidden" name="PEA_amount_per_installment"/>
            </div>
            <div class="col-md-6">
                <label for="Full name">Start from - Year:</label><br/> 
                <input id="year1" name="year" style="width: 80%;" value="<?php echo $year; ?>" />
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-6">
                <label for="Full name">Start from - Month:</label> <br />
                <input id="month1" name="month" style="width: 80%;" value="<?php echo $month; ?>" />
            </div>
            <div class="clearfix"></div>
            <div class="col-md-4">
                <br />
                <input type="submit" class="k-button"  name="btnSave" value="Save Advance">
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
    //Calculate installment per amount
    function ctotal_installment(installAmount) {
        var total_advance_amount = $('#new_advance').val();
        var amount_per_installment = Math.ceil(total_advance_amount / installAmount);
        $("#amount_per_installment").val(amount_per_installment);
        $("#amount_per_installment_hidden").val(amount_per_installment);
    }
    function cnew_advance(amount) { 
        var total_advance_amount = $('#total_installment').val();
        var amount_per_installment = Math.ceil(amount / total_advance_amount);
        $("#amount_per_installment").val(amount_per_installment);
        $("#amount_per_installment_hidden").val(amount_per_installment);
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

    function getEmpAdvInfo() {
        var empCode = $("#PEA_employee_code").val();
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
                            html += '<h4>This Employee Has Previous Advance. <a class="k-button" href="installment_plan?emp_code="' + empCode + '>View Installment Plan</a></h4>';
                            
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
    }
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