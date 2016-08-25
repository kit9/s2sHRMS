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

<div class="k-toolbar k-grid-toolbar">
    <a class="k-button k-button-icontext k-grid-add" href="add.php">
        <span class="k-icon k-add"></span>
        Add Attendance Policy
    </a>
</div>
<div id="grid"></div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({ 
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/attendance_policy.php",
                    type: "GET"
                },
                destroy: {
                    url: "../../controller/attendance_policy.php",
                    type: "DELETE"
                },
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
                    id: "attendance_policy_id",
                    fields: {
                        attendance_policy_id: {editable: false, nullable: true},
                        policy_title: {type: "string",validation: {required: "Invalid Company Title."}},
                        office_start_time: {type: "string"},
                        office_end_time: {type: "string"},
                        total_hours: {type: "string"},
                        office_start_day: {type: "string"},
                        office_end_day: {type: "string"},
                        weekend_days: {type: "string"},
                        is_ot_applicable: {type: "string"},
                        no_of_latedays: {type: "string"},
                        attn_bonus_prcnt: {type: "string"},
                        half_day_work: {type: "string"},
                        ot_percentage: {type: "string"}
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
            scrollable:true,
            groupable: true,
            columns: [
                {field: "policy_title", title: "Policy Title", id: "policy_title",width: "120px"},
                {field: "office_start_time", title: "Office Start Time", id: "office_start_time",width: "140px"},
                {field: "office_end_time", title: "Office End Time", id: "office_end_time",width: "130px"},
                {field: "total_hours", title: "Total Hours", id: "total_hours",width: "100px"},
                {field: "office_start_day", title: "Office Start Day", id: "office_start_day",width: "130px"},
                {field: "office_end_day", title: "Office End Date", id: "office_end_day",width: "130px"},
                {field: "weekend_days", title: "Total Hours", id: "weekend_days",width: "100px"},
                {field: "is_ot_applicable", title: "OT Applicable", id: "is_ot_applicable",width: "130px"},
                {field: "no_of_latedays", title: "Late Days", id: "no_of_latedays",width: "100px"},
                {field: "attn_bonus_prcnt", title: "Bonus Percent", id: "attn_bonus_prcnt",width: "120px"},
                {field: "half_day_work", title: "Half Days Working", id: "half_day_work",width: "150px"},
                {field: "ot_percentage", title: "OT percentage", id: "ot_percentage",width: "120px"},
                //{field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
                {command: ["edit", "destroy"], title: "Action", width: "200px"}],
            editable: "inline"
        });
    });</script>

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>