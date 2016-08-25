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
                    url: "../../controller/holiday.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/holiday.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/holiday.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/holiday.php",
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
                    id: "holiday_id",
                    fields: {
                        holiday_id: {editable: false, nullable: true},
                        holiday_title: {validation: {required: true}},
                        holiday_type: {validation: {required: true}},
                        start_date: {type: "string"},
                        end_date: {type: "string"},
                        no_of_days: {type: "string"},
                        is_applicable_for_all: {type: "string"},
                        company_id: {type: "string"},
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
                {name: "create", text: "Add Holiday"}
            ],
            columns: [
                {field: "holiday_title", title: "Holiday Title"},
                {field: "holiday_type", title: " Holiday Type"},
                {field: "start_date", title: "Start Date", format: "{0:yyyy-MM-dd}", editor: dateTimeEditor},
                {field: "end_date", title: "End Date", format: "{0:yyyy-MM-dd}", editor: dateTimeEditor1},
                {field: "no_of_days", title: "No. of Days"},
                {field: "is_applicable_for_all", title: "Applicable for All"},
                {field: "company_id", title: "Company Name"},
                {field: "status", title: "Active?", template: "#= status ? 'yes' : 'no' #", width: "8%"},
                {command: ["edit", "destroy"], title: "Action", width: "18%"}],
            editable: "inline"
        }).data("kendoGrid");

    });

</script>
<script>
    function dateTimeEditor(container, options) {
        $('<input data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                .appendTo(container)
                .kendoDatePicker({
                    format: "yyyy-MM-dd"
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
    

<script>
    function dateTimeEditor1(container, options) {
        $('<input data-text-field="' + options.field + '" data-value-field="' + options.field + '" data-bind="value:' + options.field + '" data-format="' + options.format + '"/>')
                .appendTo(container)
                .kendoDatePicker({
                    format: "yyyy-MM-dd"
                });
    }
</script>
<?php include '../view_layout/footer_view.php'; ?>

