<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();
//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<script type="text/javascript">
    $(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/employee_module.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/employee_module.php",
                    type: "POST",
                    complete: function (e) {
                        $("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/employee_module.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/employee_module.php",
                    type: "PUT",
                    complete: function (e) {
                        $("#grid").data("kendoGrid").dataSource.read();
                    }
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
                    id: "rules_id",
                    fields: {
                        rules_id: {editable: false, nullable: true},
                        module: {validation: {required: true}},
                        status: {type: "boolean"}
                    }
                }
            }
        });
        $("#grid").kendoGrid({
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
            toolbar: [{name: "create", text: "Add Module"}],
            columns: [
                {field: "module", title: "Module Name", id: "role_type"},
                {field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
                {command: ["edit", "destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });</script>				

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>

