<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

//Permission ID from permission table
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
<script type="text/javascript">
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/super_user_controller.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/super_user_controller.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/super_user_controller.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/super_user_controller.php",
                    type: "PUT",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                }
            },
            autoSync: false,
            schema: {
                data: "data",
                total: "data.length",
                model: {
                    id: "department_id",
                    fields: {
                        emp_id: {editable: false, nullable: true},
                        emp_code: {type: "string", editable: false},
                        emp_firstname: {type: "string", editable: false},
                        password: {type: "string"}
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
            toolbar: [{name: "create", text: "Add Super User"}],
            columns: [
                {field: "emp_code", title: "Employee Code", id: "emp_code"},
                {field: "emp_firstname", title: "Name", id: "emp_firstname"},
                {field: "password", title: "Password", id: "password"},
                {command: ["edit", "destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });</script>
<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>
