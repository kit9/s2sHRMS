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
            pageSize: 10,
            transport: {
                read: {
                    url: "../../controller/leave_policy.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/leave_policy.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/leave_policy.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/leave_policy.php",
                    type: "PUT",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                }
            },
            autoSync: false,
            schema: {
                errors: function (e) {
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
                    id: "leave_policy_id",
                    fields: {
                        leave_policy_id: {editable: false, nullable: true},
                        leave_title: {type: "string", validation: {required: "Invalid Leave Title."}},
                        total_days: {type: "string", validation: {required: "Invalid Number of Days."}},
                        is_applicable_for_all: {type: "boolean"},
                        available_after_months: {type: "string"},
                        is_leave_cut_applicable: {type: "boolean"},
                        is_pro_rate_base: {type: "boolean"},
                        is_carried_forward: {type: "boolean"},
                        is_wh_included: {type: "boolean"},
                        status: {type: "boolean"},
                        max_carry_forward: {type: "number"},
                        short_code: {type: "string"}
                    }
                }
            }
        });
        jQuery("#grid").kendoGrid({
            dataSource: dataSource,
            filterable: true,
            scrollable: true,
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSizes: [10, 20, 50]
            },
            sortable: true,
            groupable: true,
            toolbar: [{name: "create", text: "Add Leave Type"}],
            columns: [
                {field: "leave_title", title: "Leave Title", id: "leave_title", width: "150px"},
                {field: "short_code", title: "Short Code", id: "short_code", width: "150px"},
                {field: "total_days", title: "Total Days", id: "total_days", width: "150px"},
                {field: "is_applicable_for_all", title: "Applicable to All?", template: "#= is_applicable_for_all ? 'Yes' : 'No' #", width: "200px"},
                {field: "available_after_months", title: "Available After(month)", id: "available_after_months", width: "200px"},
                {field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
                {field: "is_leave_cut_applicable", title: "Leave Cut Applicable?", template: "#= is_leave_cut_applicable ? 'Yes' : 'No' #", width: "200px"},
                {field: "is_pro_rate_base", title: "Pro_Rate Basis?", template: "#= is_pro_rate_base ? 'Yes' : 'No' #", width: "200px"},
                {field: "is_carried_forward", title: "Carry Forward?", template: "#= is_carried_forward ? 'Yes' : 'No' #", width: "200px"},
                {field: "max_carry_forward", title: "Max Carry Forward", id: "max_carry_forward", width: "200px"},
                {field: "is_wh_included", title: "Weekend/Holiday Count?", template: "#= is_wh_included ? 'Yes' : 'No' #", width: "200px"},
                {command: ["edit", "destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });
</script>				
<?php include '../view_layout/footer_view.php'; ?>