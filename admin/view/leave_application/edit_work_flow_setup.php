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
$countRow = 0;

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
$departmentID = 0;
$companyID = 0;

if (isset($_GET['leave_workflow_id'])) {
    $leaveWorkflowId = $_GET['leave_workflow_id'];
}

if (isset($_POST["btnUpdate"])) {
    extract($_POST);
//    print_r($_POST);
//    exit();
    if (!empty($companies) && !empty($department) && !empty($employee) && !empty($idlwi)) {
        if ($con->FlyPrepare("UPDATE approval_workflow_settings SET company_id='" . $companies . "',department_id='" . $department . "',emp_code='" . $employee . "' WHERE leave_workflow_id='" . $idlwi . "'") == 1) {
            $msg = "Workflow settings information is successfully updated!";
        } else {
            $err = "Update query failed.";
        }
    } else {
        $err = "Please Fillup The Fields.";
    }

//    $arrStep = array();
//
//
//    //sorting the array and taking only emp codes
//    foreach ($steps AS $key => $val) {
//        if ($key % 2 != 0) {
//            $arrStep[] = $val;
//        }
//    }
//
//    //deleting everything from database first against that department
//    $sqlDel = "DELETE FROM approval_workflow_settings WHERE department_id='" . mysqli_real_escape_string($con->open(), $departmentID) . "'";
//    $resultDel = mysqli_query($con->open(), $sqlDel);
//    if ($resultDel) {
//        $countVar = 1;
//        foreach ($arrStep as $key => $val) {
//            $insert_array = array(
//                "company_id" => $companies,
//                "department_id" => $department,
//                "emp_code" => $val,
//                "step" => $countVar
//            );
//
//            if ($con->insert("approval_workflow_settings", $insert_array) == 1) {
//                $msg = "Workflow settings information is successfully updated!";
//            } else {
//                $err = "Update query failed.";
//            }
//            $countVar++;
//        }
//    } else {
//        echo "resultDel query failed.";
//    }
}

if ($leaveWorkflowId > 0) {
    $arrWorkflow = array();
    $sqlGetWorkflow = "SELECT * FROM approval_workflow_settings "
            . "WHERE leave_workflow_id=$leaveWorkflowId";
    // 

    $resultGetWorkflow = $con->FlyQuery($sqlGetWorkflow);


//    print_r($resultGetWorkflow);
//    exit();
    if (!empty($resultGetWorkflow)) {
        $companyID = $resultGetWorkflow[0]->company_id;
        $departmentID = $resultGetWorkflow[0]->department_id;
        $emp_code = $resultGetWorkflow[0]->emp_code;
    }

//    print_r($resultGetWorkflow);
//    exit();
}
?>
<?php include '../view_layout/header_view.php'; ?>


<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Approval Work Flow</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->

        <!--Declare error message-->
<?php include("../../layout/msg.php"); ?>

        <form method="post">
            <input type="hidden" name="idlwi" value="<?php echo $leaveWorkflowId; ?>" />
            <div class="col-md-4">
                <label for="Full name">Company:</label><br/>
                <input type="text" id="company" name="companies" placeholder="" value="<?php echo $companyID; ?>" style="width: 80%;"/>
            </div>
            <div class="col-md-4">
                <label for="Full name">Department:</label><br/>
                <input type="text" id="departments" name="department" placeholder="" value="<?php echo $departmentID; ?>" style="width: 80%;"/>
            </div>

            <div class="col-md-4">
                <label for="Full name">Employee:</label><br/>
                <input type="text" id="employee" class="k-textbox" name="employee" placeholder="" value="<?php echo $emp_code; ?>"  style="width: 80%;"/>
            </div>

            <div class="clearfix"></div>
            <br /><br />
            <div class="col-md-4">
                <input type="submit" class="k-button" name="btnUpdate" value="Update Work Flow">
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

<?php include '../view_layout/footer_view.php'; ?>

<!--Select department-->
<script type="text/javascript">
    jQuery(document).ready(function () {
        var departments = jQuery("#departments").kendoComboBox({
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

        var employee = $("#employee").kendoDropDownList({
            optionLabel: "Select Employees...",
            dataTextField: "emp_firstname",
            dataValueField: "emp_code",
            dataSource: {
                serverFiltering: true,
                transport: {
                    read: "../../controller/employee.php"
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoDropDownList");

    });
</script>

<!--Number of Steps Combo
-->
<!--<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#steps").kendoComboBox({
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
    });</script>-->
<!--

Company Combo-->
<script type="text/javascript">
    jQuery(document).ready(function () {
        var departments = jQuery("#company").kendoComboBox({
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
    jQuery(document).ready(function () {
        jQuery('#steps').change(function () {
            var num = jQuery('#steps').val();
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
                html += 'jQuery(document).ready(function () {';
                html += 'jQuery("#step_' + i + '").kendoComboBox({';
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
            jQuery('#supervisor').html(html);

//Now push value to existing step input field
<?php foreach ($arrWorkflow as $flowdata): ?>
    <?php $step_no = $flowdata->step; ?>
                $("#step_<?php echo $step_no; ?>").val("<?php echo $flowdata->emp_code; ?>");
<?php endforeach; ?>
        });
    });
</script>
