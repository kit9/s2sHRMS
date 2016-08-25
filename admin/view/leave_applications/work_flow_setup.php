<?php
session_start();
/*
 * Author: Rajan Hossain
 * Page: Search Employee
 */

//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

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

//Initialize variables
$emp_code = '';

if (isset($_POST["btnSave"])) {
    extract($_POST);
    foreach ($steps as $step) {
        $code_array = array();
        $emp_code_array = explode("-", $step);
        $first_element[] = $emp_code_array[0];
    }

    $emp_codes = array_unique($first_element);
    $i = 1;
    foreach ($emp_codes as $key => $val) {
        $insert_array = array(
            "company_id" => $companies,
            "department_id" => $department,
            "emp_code" => $val,
            "step" => $i
        );
        $con->insert("leave_workflow_settings", $insert_array);
        $i++;
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!--Link to All Leave Applications Page-->
<a href="../leave_applications/index.php" class="k-button pull-right" style="text-decoration: none;">All Leave Applications</a>
<div class="clearfix"></div>
<br />

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Approval Work Flow</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-4">
                <label for="Full name">Company:</label><br/>
                <input type="text" id="company" name="companies" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="col-md-4">
                <label for="Full name">Department:</label><br/>
                <input type="text" id="departments" name="department" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="col-md-4">
                <label for="Steps">No. of Steps:</label><br/>
                <input id="steps" placeholder="Select Steps..." style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br /><br />
            <div id="supervisor">

            </div>
            <div class="clearfix"></div>
            <div class="col-md-4">
                <input type="submit" class="k-button" name="btnSave" value="Save Work Flow">
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>


    <br />
    <br />

    <div class="clearfix"></div>

</form>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>

<!--Select department-->
<script type="text/javascript">
    $(document).ready(function () {
        var departments = $("#departments").kendoComboBox({
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

    });
</script>

<!--Number of Steps Combo-->
<script type="text/javascript">
    $(document).ready(function () {
        $("#steps").kendoComboBox({
            dataTextField: "text",
            dataValueField: "value",
            dataSource: [
                {text: "1", value: "1"},
                {text: "2", value: "2"},
                {text: "3", value: "3"},
                {text: "4", value: "4"},
                {text: "5", value: "5"}
            ],
            filter: "contains",
            suggest: true
        });


    });
</script>

<!--Company Combo-->
<script type="text/javascript">
    $(document).ready(function () {
        var departments = $("#company").kendoComboBox({
            placeholder: "Select company...",
            dataTextField: "company_title",
            dataValueField: "company_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/company.php",
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

<!--Dynamic form generation-->
<script type="text/javascript">
    //when the webpage has loaded do this
    $(document).ready(function () {
        $('#steps').change(function () {
            var num = $('#steps').val();
            var i = 0;
            var html = '';
            for (i = 1; i <= num; i++) {
                html += '<div class="col-md-4">';
                html += '<label for="All Steps">Step: ' + i + ':</label><br/>';
                html += '<input style="width:80%"; type="text" id="step_' + i + '"name="steps[]"/>';
                html += '</div>';
                if (i === 3) {
                    html += '<div class="clearfix"></div><br/>';
                }
                html += '<script type="text/javascript">';
                html += '$(document).ready(function () {';
                html += '$("#step_' + i + '").kendoComboBox({';
                html += 'placeholder: "Select employee...",';
                html += 'dataTextField: "emp_name",';
                html += 'dataValueField: "emp_code",';
                html += 'dataSource: {';
                html += 'transport: {';
                html += 'read: {';
                html += 'url: "../../controller/employee_list.php",';
                html += 'type: "GET"';
                html += '}';
                html += ' },';
                html += 'schema: {';
                html += 'data: "data"';
                html += '}';
                html += '}';
                html += ' }).data("kendoComboBox");';
                html += '});';
                html += '</scr' + 'ipt>';
                //insert this html code into the div with id supervisor
            }
            html += '<div class="clearfix"></div><br/><br/>';
            $('#supervisor').html(html);

        });
    });
</script>

