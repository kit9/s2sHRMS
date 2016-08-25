<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();
//Check if user has permission on this page
include '../../config/request_controller.php';
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/employee_role.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/employee_role.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/employee_role.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/employee_role.php",
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
                    id: "em_role_id",
                     fields: {
                        em_role_id: {editable: false, nullable: true},
                        role_type: {validation: {required: true}}, 
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
            toolbar: [{name: "create", text: "Add Role"}],
            columns: [
                {field: "role_type", title: "Role Type", id: "role_type"},
                {field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
                {command: ["edit", "destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });</script>				

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>

