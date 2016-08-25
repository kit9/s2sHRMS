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
$emp_code = '';


/**
 * Fetch configuration data
 */
$configuration_info = array();
$configuration_info = $con->SelectAll("configuration_meta");

if (count($configuration_info) > 0) {
    //Find approval rule
    $approval_type = $configuration_info{0}->leave_approval_type;
    $ends_at_first_rejection = $configuration_info{0}->la_ends_at_rejection; 
}

if (isset($_POST["btnSave"])) {
    extract($_POST);

    $sqlSearch = "SELECT * FROM approval_workflow_settings WHERE department_id=$department";
    $resultSearch = mysqli_query($con->open(), $sqlSearch);
    if ($resultSearch) {
        $countRow = mysqli_num_rows($resultSearch);
    } else {
        $err = "resultSearch query failed.";
    }

    if ($countRow > 0) {
        $err = "Workflow settings already exist for submitted department. Please delete or update existing settings.";
    } else {

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
            if ($con->insert("approval_workflow_settings", $insert_array) == 1) {
                $msg = "Workflow settings information is successfully saved!";
            } else {
                $err = "Something went wrong.";
            }
            $i++;
        }
    }
}

?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div id="test_container"></div>
<div class="clearfix"></div>
<br />
<div class="widget" style="background-color: white;">
    <div class="widget-head"> 
        <h6 class="heading" style="color:whitesmoke;">Approval Work Flow</h6> 
        <span class="pull-right" style="color: white;">
            <input type="radio" <?php
            if ($approval_type == 'individual') {
                echo 'checked="true"';
            }
            ?> class="test" value="individual" id="workflow_approval_type_ind" name="workflow_approval_type" >Individual Approval &nbsp; &nbsp;  
            <input type="radio" <?php
            if ($approval_type == 'group') {
                echo 'checked="true"';
            }
            ?> value="group" id="workflow_approval_type_gro" name="workflow_approval_type" ><span> Group Approval &nbsp; &nbsp;</span>
            <input type="checkbox" <?php if ($ends_at_first_rejection == 1){echo 'checked="true"';} ?>  id="ends_at_first_rejection" name="ends_at_first_rejection" ><span id="rej_label"> Ends at First Rejection &nbsp; &nbsp;</span> 
        </span>
    </div>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#workflow_approval_type_ind").click(function() {
                var workflow_approval_type = $("#workflow_approval_type_ind").val();
                $.ajax({
                    type: "POST",
                    url: "../../controller/configuration_meta_controller.php",
                    data: {
                        workflow_approval_type: workflow_approval_type
                    },
                    dataType: "json",
                    success: function(data) {
                        $("#test_container").html();
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.msg + "</div>");
                    }
                });
            });
            
            $("#workflow_approval_type_gro").click(function() {
                var workflow_approval_type = $("#workflow_approval_type_gro").val();
                $.ajax({
                    type: "POST",
                    url: "../../controller/configuration_meta_controller.php",
                    data: {
                        workflow_approval_type: workflow_approval_type
                    },
                    dataType: "json",
                    success: function(data) {
                        $("#test_container").html();
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.msg + "</div>");
                    }
                });
            });
            
            $("#ends_at_first_rejection").click(function() {
                var ends_at_first_rejection = '';
                if ($('#ends_at_first_rejection').is(':checked')) {
                     ends_at_first_rejection = 1;
                } else {
                     ends_at_first_rejection = 0;
                }
                $.ajax({
                    type: "GET",
                    url: "../../controller/configuration_meta_controller.php?rejection_rule=1&ends_at_first_rejection=" + ends_at_first_rejection,
                    dataType: "json",
                    success: function(data) {
                        $("#test_container").html();
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.msg + "</div>");
                    }
                });
            });
        });</script>

    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->

        <!--Declare error message-->
        <?php include("../../layout/msg.php"); ?>

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
<div id="grid"></div>

<script id="edit_workflow" type="text/x-kendo-template">
    <a href="edit_work_flow_setup.php?departid=#= department_id #" class="k-button">Edit</a>
</script>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/leave_management_controllers/work_flow_setup_controler.php",
                    type: "GET"
                },
                //                update: {
                //                    url: "../../controller/leave_management_controllers/work_flow_setup_controler.php",
                //                    type: "POST",
                //                    complete: function (e) {
                //                        jQuery("#grid").data("kendoGrid").dataSource.read();
                //                    }
                //                },
                destroy: {
                    url: "../../controller/leave_management_controllers/work_flow_setup_controler.php",
                    type: "DELETE",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                create: {
                    url: "../../controller/leave_management_controllers/work_flow_setup_controler.php",
                    type: "PUT",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                }
            },
            autoSync: false,
            schema: {
                errors: function(e) {
                    if (e.error === "yes")
                    {
                        var message = "";
                        message += e.message;
                        var window = jQuery("#kWindow");
                        if (!window.data("kendoWindow")) {
                            window.kendoWindow({
                                title: "",
                                modal: true,
                                height: 120,
                                width: 400
                            });
                        }

                        window.data("kendoWindow").center().open();
                        window.html('<br/><br/><center><P style="color:red">' + message + '</p></center>');
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "department_id",
                    fields: {
                        department_id: {editable: false, nullable: true},
                        department_title: {type: "string", validation: {required: "Invalid "}},
                        step: {type: "string", validation: {required: "Invalid  "}},
                        emp_code: {type: "string", validation: {required: "Invalid "}},
                        emp_firstname: {type: "string", validation: {required: "Invalid"}},
                        emp_email: {type: "string", validation: {required: "Invalid "}},
                        status: {type: "boolean"}
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
            // toolbar: [{name: "create", text: "Add Department"}],
            columns: [
                {field: "department_title", title: "Department Title", id: "department_title", width: "190px"

                },
                {field: "step", title: "Steps", id: "step", width: "100px"},
                {field: "emp_code", title: "Employee Code", id: "emp_code", width: "190px"},
                {field: "emp_firstname", title: "First Name", id: "emp_firstname", width: "150px"},
                {field: "emp_email", title: "Mail", id: "emp_email", width: "150px"},
                {field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
                {
                    title: "Action", width: "100px",
                    template: kendo.template($("#edit_workflow").html())
                },
                {command: ["destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });</script>	
</div>

<?php include '../view_layout/footer_view.php'; ?>

<!--Select department-->
<script type="text/javascript">
    jQuery(document).ready(function() {
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
    });</script>

<!--Number of Steps Combo-->
<script type="text/javascript">
    jQuery(document).ready(function() {
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
    });</script>

<!--Company Combo-->
<script type="text/javascript">
    jQuery(document).ready(function() {
        var departments = jQuery("#company").kendoComboBox({
            placeholder: "Select company...",
            dataTextField: "company_title",
            dataValueField: "company_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/company_global.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");
    });</script>

<!--Dynamic form generation-->
<script type="text/javascript">
    //when the webpage has loaded do this
    jQuery(document).ready(function() {
        jQuery('#steps').change(function() {
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
                html += 'url: "../../controller/leave_management_controllers/employee_list.php",';
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
        });
    });
</script>
