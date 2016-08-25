<?php

session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

$la_ends_at_rejection = '';

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


/**
 * Fetch configuration data
*/

$configuration_info = array();
$configuration_info = $con->SelectAll("configuration_meta");

if (count($configuration_info) > 0){
    //Find approval rule
    $approval_type = $configuration_info{0}->leave_approval_type;
    $la_ends_at_rejection = $configuration_info{0}->la_ends_at_rejection;

    if ($approval_type == 'individual'){
        $limit = 1;
    } else {
        $limit = 0;
    }
}


?>
<?php include '../view_layout/header_view.php'; ?>

<style>
    .k-header {font-size:12px;}
    tr { font-size:12px; text-decoration: none;}
</style>

<div class="col-md-6" style="padding-left: 0px;">
    <span>Leave Aplications Pending for Your Approval</span>
</div>
<hr />
<div class="clearfix"></div>

<!--Div to contain messages-->
<div id="test_container"></div>

<!--Div to contain pending applications as a first reviewer-->
<?php if ($la_ends_at_rejection == 1): ?>
    <div id="grid_first_reviewer" style="font-size: 12px;"></div>
<?php endif; ?>
<!--Div to contain main grid-->
<div id="grid" style="font-size: 12px;"></div>
<br />

<!--Div to contain secondary grid-->
<input type="checkbox" id="all_applications"> View all Leave Applications
<br />
<br />
<div id="grid_two" style="display: none; font-size: 12px;"></div>


 

<!--Trigger leave status update :: approve/reject -->   
<script id="approve-template" type="text/x-kendo-template">
    # if(aws_status == "pending") { #
    <a style="font-size:11px; text-decoration: none;" onclick="javascript:leave_approval(#= aws_id #);" class="k-button k-grid-even" >Approve</a>
    <a style="font-size:11px; text-decoration: none;" onclick="javascript:leave_reject(#= aws_id #);" class="k-button k-grid-even">Reject</a>
    <a href="application_details.php?mid=#= leave_application_master_id #&aws_id=#= aws_id #" style="font-size:11px; text-decoration: none;" class="k-button k-grid-even"  target="_blank">Details</a>
    # } #
</script>

<script id="details-two-template" type="text/x-kendo-template">
    # if(aws_status == "pending" || aws_status == "approved" || aws_status == "rejected") { #
    <a href="application_details.php?from_all=1&mid=#= leave_application_master_id #&aws_id=#= aws_id #" style="font-size:11px; text-decoration: none;" class="k-button k-grid-even" target="_blank">Details</a>
    # } #
</script>

<!--All application grid reveal Animation-->
<script language="JavaScript">
    $(document).ready(function () {
        // Listen for click on toggle checkbox :: multiple_leave_types
        $('#all_applications').click(function (event) {
            if (this.checked) {
                $("#grid_two").show(500);
                $("#divs").show(500);
            } else {
                $("#grid_two").hide(500);
                $("#divs").hide(500);
            }
        });
    });
</script>
<script>
    function leave_approval(aproval_status_id) {
        $.ajax({
            url: "../../controller/leave_management_controllers/leave_applications_approval.php",
            data: {aws_id: aproval_status_id},
            type: "POST",
            dataType: "json",
            success: function (data) {
                if (data.data.success_flag === "yes") {
                    $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");

                    $('#grid_two').data('kendoGrid').dataSource.read();
                    $('#grid_two').data('kendoGrid').refresh();

                    $('#grid').data('kendoGrid').dataSource.read();
                    $('#grid').data('kendoGrid').refresh();
                } else {
                    $("#test_container").html("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                }

            }
        });


    }
    function leave_reject(reject_status_id) {
        $.ajax({
            url: "../../controller/leave_management_controllers/leave_applications_reject.php?aws_id=#= aws_id #",
            data: {aws_id: reject_status_id},
            type: "POST",
            dataType: "json",
            success: function (data) {
                if (data.data.success_flag === "yes") {
                    $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                } else {
                    $("#test_container").html("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                }
            }
        });

        //Refresh grid
        $('#grid').data('kendoGrid').dataSource.read();
        $('#grid').data('kendoGrid').refresh();
    }
</script>

<script type="text/javascript">
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/leave_management_controllers/leave_applications_pending.php",
                    type: "GET"
                }
            },
            autoSync: false,
            schema: {
                errors: function (e) {
                    //alert(e.error);
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
                    id: "aws_id",
                    fields: {
                        aws_id: {type: "number"},
                        aws_emp_code: {type: "string"},
                        emp_firstname: {type: "string"},
                        department_title: {type: "string"},
                        emp_subsection: {type: "string"},
                        application_date: {type: "date"},
                        start_date: {type: "date"},
                        end_date: {type: "date"},
                        no_of_days: {type: "string"},
                        leave_title: {type: "string"},
                        remarks_id: {type: "string"},
                        approved_by_id: {type: "number"},
                        aws_status: {type: "string"},
                        leave_application_master_id: {type: "number"}
                    }
                }
            }
        });

        jQuery("#grid").kendoGrid({
            dataSource: dataSource,
            filterable: true,
            scrollable: true,
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSizes: [5, 10, 20, 50]
            },
            sortable: true,
            groupable: true,
            columns: [
                {
                    title: "Action", width: "250px",
                    template: kendo.template($("#approve-template").html())
                },
                {field: "aws_status", title: "Status", id: "aws_status", width: "150px", attributes: {style: "font-size:11px;"}},
                {field: "aws_emp_code", title: "Card No", id: "aws_emp_code", width: "100px", attributes: {style: "font-size:11px;"}},
                {field: "emp_firstname", title: "Employee Name", id: "emp_id", width: "200px", attributes: {style: "font-size:11px;"}},
                {field: "department_title", title: "Department Title", id: "dept_id", width: "200px", attributes: {style: "font-size:11px;"}},
                {field: "emp_subsection", title: "Section", id: "subsection_id", width: "150px", attributes: {style: "font-size:11px;"}},
                {field: "application_date", title: "Application Date", id: "application_date", width: "180px", format: "{0:dd-MM-yyyy}", attributes: {style: "font-size:11px;"}},
                {field: "start_date", title: "Start Date", id: "start_date", width: "180px", format: "{0:dd-MM-yyyy}", attributes: {style: "font-size:11px;"}},
                {field: "end_date", title: "End Date", id: "end_date", width: "180px", format: "{0:dd-MM-yyyy}", attributes: {style: "font-size:11px;"}},
                {field: "no_of_days", title: "No. of Days", id: "no_of_days", width: "140px", attributes: {style: "font-size:11px;"}}
            ],
            editable: "inline"
        });

    });
</script>

<script type="text/javascript">
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/leave_management_controllers/leave_applications_all.php",
                    type: "GET"
                }
            },
            autoSync: false,
            schema: {
                errors: function (e) {
                    //alert(e.error);
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
                    id: "aws_id",
                    fields: {
                        aws_id: {type: "number"},
                        aws_emp_code: {type: "string"},
                        emp_firstname: {type: "string"},
                        department_title: {type: "string"},
                        emp_subsection: {type: "string"},
                        application_date: {type: "date"},
                        start_date: {type: "date"},
                        end_date: {type: "date"},
                        no_of_days: {type: "string"},
                        leave_title: {type: "string"},
                        remarks_id: {type: "string"},
                        approved_by_id: {type: "number"},
                        aws_status: {type: "string"}
                    }
                }
            }
        });

        jQuery("#grid_two").kendoGrid({
            dataSource: dataSource,
            filterable: true,
            scrollable: true,
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSizes: [5, 10, 20, 50]
            },
            sortable: true,
            groupable: true,
            columns: [
                {
                    title: "Action", width: "250px",
                    template: kendo.template($("#details-two-template").html())
                },
                {field: "aws_status", title: "Status", id: "aws_status", width: "100px", attributes: {style: "font-size:11px;"}},
                {field: "aws_emp_code", title: "Card No", id: "aws_emp_code", width: "100px", attributes: {style: "font-size:11px;"}},
                {field: "emp_firstname", title: "Employee Name", id: "emp_id", width: "200px", attributes: {style: "font-size:11px;"}},
                {field: "department_title", title: "Department Title", id: "dept_id", width: "200px", attributes: {style: "font-size:11px;"}},
                {field: "emp_subsection", title: "Section", id: "subsection_id", width: "150px", attributes: {style: "font-size:11px;"}},
                {field: "application_date", title: "Application Date", id: "application_date", width: "180px", format: "{0:dd-MM-yyyy}", attributes: {style: "font-size:11px;"}},
                {field: "start_date", title: "Start Date", id: "start_date", width: "180px", format: "{0:dd-MM-yyyy}", attributes: {style: "font-size:11px;"}},
                {field: "end_date", title: "End Date", id: "end_date", width: "180px", format: "{0:dd-MM-yyyy}", attributes: {style: "font-size:11px;"}},
                {field: "no_of_days", title: "No. of Days", id: "no_of_days", width: "140px", attributes: {style: "font-size:11px;"}}
            ],
            editable: "inline"
        });

    });
</script>
<?php include '../view_layout/footer_view.php'; ?>