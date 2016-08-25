<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

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

?>
<?php include '../view_layout/header_view.php'; ?>
<div class="k-toolbar k-grid-toolbar">
    <a class="k-button k-button-icontext k-grid-add" href="../apply_leave/index.php">
        <span class="k-icon k-add"></span>
        Apply for a Leave
    </a>
</div>
<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/leave_applications.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/leave_applications.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/leave_applications.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/leave_applications.php",
                    type: "PUT",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                }
            },
            //    code: "Ok",
            autoSync: false,
            schema: {
                errors: function(e) {
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
                        //var grid = $("#RBOGrid").data("kendoGrid");
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "application_id",
                    fields: {
                        application_id: {editable: false, nullable: true},
                        application_date: {type: "string", validation: {required: "Invalid Leave Title."}},
                        emp_firstname: {type: "string"},
                        start_date: {type: "string", validation: {required: "Invalid Number of Days."}},
                        end_date: {type: "string", validation: {required: "Invalid Number of Days."}},
                        no_of_days: {type: "string"},
                        leave_type: {type: "string"},
                        approved_date: {type: "string"},
                        approved_by_id: {type: "number"},
                        status: {type: "boolean"}
                    }
                    //  this.cancelChanges(); 
                }
            }
            //  }
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
            //toolbar: [{name: "create", text: "Add Leave Application"}],
            columns: [
                {command: ["approve", "reject"], title: "Action", width: "200px"},
                {field: "application_date", title: "Application Date", id: "application_date", width: "150px", },
                {field: "emp_firstname", title: "Employee Name", id: "emp_id", width: "150px"},
                {field: "start_date", title: "Start Date", id: "start_date", width: "200px"},
                {field: "end_date", title: "End Date", id: "end_date", width: "200px"},
                {field: "no_of_days", title: "No. of Days", id: "no_of_days", width: "200px"},
                {field: "leave_title", title: "Leave Type", id: "leave_type", width: "200px"},
                {field: "approved_date", title: "Approved Date", id: "approved_date", width: "200px"}
//                {field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
//                {command: ["edit", "destroy"], title: "Action", width: "230px"}
                ],
                editable: "inline"
        });
    });</script>				

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>