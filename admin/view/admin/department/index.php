<?php
include("../../config/class.config.php");
$con = new Config();
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<div id="details"></div> 

<script type="text/javascript">
    var wnd,
            detailsTemplate;
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/wing.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/wing.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/wing.php",
                    type: "DELETE"
                },
                details: {
                    url: "../../controller/wing.php",
                    type: "DETAILS"
                },
                create: {
                    url: "../../controller/wing.php",
                    type: "PUT",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
            },
            autoSync: false,
            schema: {
                data: "data",
                total: "data.length",
                model: {
                    id: "wing_id",
                    fields: {
                        wing_id: {editable: false, nullable: true},
                        wing_name: {validation: {required: true}},
                        dept_id: {type: "number"},
                        dept_name: {type: "string"},
                        is_active: {type: "boolean"}
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
                {name: "create", text: "Add Wing"}
            ],
            columns: [
                {field: "wing_name", title: "Wing Name"},
                {field: "dept_id",
                    title: "Department",
                    editor: deptDropDownEditor,
                    template: "#=dept_name#",
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
//                {field: "view_de", title: "View Details", template: "#= view_de#", width: "8%"},
                {field: "is_active", title: "Active?", template: "#= is_active ? 'yes' : 'no' #", width: "8%"},
                {command: {text: "Details", click: showdetails}, title: "Details", width: "120px"},
                {command: ["edit"], title: "Action", width: "100px"}],
            editable: "popup"
        }).data("kendoGrid");

        wnd = $("#details")
                .kendoWindow({
                    title: "Details",
                    modal: true,
                    visible: false,
                    resizable: false,
                    width: 300
                }).data("kendoWindow");

        detailsTemplate = kendo.template($("#template").html());
    });

    function showdetails(e) {
        e.preventDefault();

        var dataItem = this.dataItem($(e.currentTarget).closest("tr"));
        wnd.content(detailsTemplate(dataItem));
        wnd.center().open();
    }

    function deptFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "dept_name",
            dataValueField: "dept_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/getdepartments.php"
                    }
                },
                schema: {
                    data: "data"
                }
            },
            optionLabel: "Select"
        });
    }
    function deptDropDownEditor(container, options) {
        jQuery('<input required data-text-field="dept_name" data-value-field="dept_id" data-bind="value:' + options.field + '"/>')
                .appendTo(container)
                .kendoDropDownList({
                    autoBind: false,
                    dataSource: {
                        transport: {
                            read: {
                                url: "../../controller/getdepartments.php"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    },
                    optionLabel: "Select Department"
                });
    }


</script>	
<script type="text/x-kendo-template" id="template">
    <div id="details-container">
    Wing Name: #= wing_name # <br/>
    Dept Name:  #= dept_name #          
    </div>
</script>



<?php include '../view_layout/footer_view.php'; ?>
