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

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
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
                    url: "../../controller/all_designation.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/designation.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/designation.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/designation.php",
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
                        //var grid = $("#RBOGrid").data("kendoGrid");
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "designation_id",
                    fields: {
                        designation_id: {editable: false, nullable: true},
                        designation_title: {validation: {required: true}},
                        department_id: {type: "number"},
                        department_title: {type: "string"},
                        subsection_id: {type: "number"},
                        subsection_name: {type: "string"},
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
                {name: "create", text: "Add Designation"}
            ],
            columns: [
                {field: "designation_title", title: "Title"},
                {field: "department_title",
                    title: "Department",
                    editor: function (container) {
                        var department_id = $('<input required id="department_id" name="department_id"  />')
                        department_id.appendTo(container)
                        department_id.kendoDropDownList({
                            dataTextField: "department_title",
                            dataValueField: "department_id",
                            autoBind: false,
                            type: "json",
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
                            },
                            optionLabel: "Department"
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
                {field: "subsection_title",
                    title: "Subsection",
                    editor: function (container) {
                        var subsection_id = $('<input name="subsection_id" id="subsection_id"/>');
                        subsection_id.appendTo(container)
                        subsection_id.kendoDropDownList({
                            dataTextField: "subsection_title",
                            dataValueField: "subsection_id",
                            autoBind: false,
                            type: "json",
                            cascadeFrom: "department_id",
                            dataSource: {
                                transport: {
                                    read: {
                                        url: "../../controller/sub_section.php",
                                        type: "GET"
                                    }
                                },
                                schema: {
                                    data: "data"
                                }
                            },
                            optionLabel: "Subsection"
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
            },
            optionLabel: "Select"
        });
    }


    function cityFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "subsection_title",
            dataValueField: "subsection_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/sub_section.php",
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
