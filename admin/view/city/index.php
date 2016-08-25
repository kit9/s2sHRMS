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
<div id="details"></div> 
<script type="text/javascript">
    var wnd,
            detailsTemplate;
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/citys.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/citys.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/citys.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/citys.php",
                    type: "PUT",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
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
                    id: "city_id",
                    fields: {
                        city_id: {type: "number", editable: false, nullable: true},
                        city_name: {type: "string", validation: {required: true}},
                        country_id: {type: "number"},
                        country_name: {type: "string"},
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
                {name: "create", text: "Add City"}
            ],
            columns: [
                {field: "city_name", title: "City Name"},
                {field: "country_name",
                    title: "Country Name",
                    editor: function (container) {
                        var country_id = $('<input required id="country_id" name="country_id"  />')
                        country_id.appendTo(container)
                        country_id.kendoDropDownList({
                            dataTextField: "country_name",
                            dataValueField: "country_id",
                            autoBind: false,
                            type: "json",
                            dataSource: {
                                transport: {
                                    read: {
                                        url: "../../controller/citycountry.php",
                                        type: "GET"
                                    }
                                },
                                schema: {
                                    data: "data"
                                }
                            },
                            optionLabel: "Country"
                        });
                    },
                    filterable: {
                        ui: deptFilter,
                        extra: false,
                        operators: {
                            string: {
                                eq: "Is equal to",
                                neq: "Is not equal to"
                            }
                        }
                    }
                },
                {field: "status", title: "Active?", template: "#= status ? 'yes' : 'no' #", width: "8%"},
                {command: ["edit", "destroy"], title: "Action", width: "18%"}],
            editable: "inline"
        }).data("kendoGrid");

    });



    function deptFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "country_name",
            dataValueField: "country_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/citycountry.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            },
            optionLabel: "Select"
        });
    }

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
<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>
