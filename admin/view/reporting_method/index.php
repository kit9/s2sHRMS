<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
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
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<div id="details"></div> 

<script type="text/javascript">
    var wnd,
            detailsTemplate;
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/reportingmethod.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/reportingmethod.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/reportingmethod.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/reportingmethod.php",
                    type: "PUT",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
            },
            autoSync: false,
            schema: {
                data: "data",
                total: "data.length",
                model: {
                    id: "reporting_id",
                    fields: {
                        reporting_id: {editable: false, nullable: true},
                        reporting_title: {validation: {required: true}},
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
                pageSizes: true,
                pageSizes: [5, 10, 20, 50],
            },
            sortable: true,
            groupable: true,
            toolbar: [
                {name: "create", text: "Add Repoting Method"}
            ],
            columns: [
                {field: "reporting_title", title: "Title"},
                {field: "status", title: "Active?", template: "#= status ? 'yes' : 'no' #", width: "8%"},
                {command: ["edit", "destroy"], title: "Action", width: "18%"}],
            editable: "inline"
        }).data("kendoGrid");

    });
</script>
<?php if ($con->hasPermissionUpdate($permission_id) != "yes"): ?>
    <style type="text/css">
        .k-grid-edit {
            display:none;
        }
    </style>
<?php endif; ?>

<?php if ($con->hasPermissionDelete($permission_id) != "yes"): ?>
    <style type="text/css">
        .k-grid-delete {
            display:none;
        }
    </style>
<?php endif; ?>   
<?php if ($con->hasPermissionCreate($permission_id) != "yes"): ?>
    <style type="text/css">
        .k-grid-add {
            display:none;
        }
    </style>
<?php endif; ?>   
<?php include '../view_layout/footer_view.php'; ?>

