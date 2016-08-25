<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/country.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/country.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/country.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/country.php",
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
                        var message = "There are some errors:\n";
                        message += e.message;
                        var window = jQuery("#kWindow");
                        if (!window.data("kendoWindow")) {
                            window.kendoWindow({
                                        title: "Error window",
                                        modal: true,
                                        height: 150,
                                        width: 400
                                    });
                        }

                        window.data("kendoWindow").center().open();
                        window.html('<br/><br/><P>' + message + '</p>');
                        //var grid = $("#RBOGrid").data("kendoGrid");
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "country_id",
                    fields: {
                        country_id: {editable: false, nullable: true},
                        country_name: {type: "string",validation: {required: true}},
                        c_nationality: {type: "string", validation: {required: "Nationality is required"}}
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
            toolbar: [{name: "create", text: "Add Country"}],
            columns: [
                {field: "country_name", title: "Country Name", id: "country_name"},
                {field: "c_nationality", title: "Nationality"},
                {command: ["edit", "destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });</script>				

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>