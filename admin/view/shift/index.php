<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

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
?>

<?php include '../view_layout/header_view.php'; ?>
<?php if ($con->hasPermissionCreate($permission_id) == "yes"): ?>
    <div class="k-toolbar k-grid-toolbar">
        <a class="k-button k-button-icontext k-grid-add" href="add.php">
            <span class="k-icon k-add"></span>
            Add Shift
        </a>
    </div>
<?php endif; ?>

<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/shift.php",
                    type: "GET"
                },
                destroy: {
                    url: "../../controller/shift.php",
                    type: "DELETE"
                }
            },
            //    code: "Ok",
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
                        //var grid = $("#RBOGrid").data("kendoGrid");
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "shift_id",
                    fields: {
                        shift_id: {editable: false, nullable: true},
                        shift_title: {type: "string", validation: {required: "Invalid Shift Title."}},
                        shift_start_day: {type: "string"},
                        shift_end_day: {type: "string"}
                    }
                    //  this.cancelChanges(); 
                }
            }
            //  }
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
            //toolbar: [{name: "create", text: "Create New Shift"}],
            columns: [
                {field: "shift_title", title: "  Title", id: "shift_title"},
                {field: "shift_start_day", title: "Start Day", id: "shift_start_day"},
                {field: "shift_end_day", title: "End Day", id: "shift_end_day"},
                {field: "edit", title: "Edit", width: "", template: kendo.template($("#shift_edit").html())},
                {command: ["destroy"], title: "Action", width: ""}],
            editable: "inline"
        });
    });</script>				

<?php if ($con->hasPermissionDelete($permission_id) != "yes"): ?>
    <style type="text/css">
        .k-grid-delete {
            display:none;
        }
    </style>
<?php endif; ?>   

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>
<?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
    <script id="shift_edit" type="text/x-kendo-template">
        <a href="edit.php?shift_id=#= id #" class="k-button">Edit</a>
    </script>
<?php else: ?>
    <script id="shift_edit" type="text/x-kendo-template">

    </script>
<?php endif; ?>




