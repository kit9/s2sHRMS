<?php
session_start();

/*
 * Author: Rajan Hossain
 * Page: Apply for a leave
 */

//Importing class library
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

date_default_timezone_set('UTC');
$emp_code = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

if (isset($_SESSION["company_id"])) {
    $company_id = $_SESSION['company_id'];
}

if ($_SESSION["is_super"] == 'yes') {
    $is_super = "yes";
}

$leave_types = $con->SelectAll("leave_policy");
$companies = $con->SelectAll("company");
?>


<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Leave Request</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Company Combo-->
        <script type="text/javascript">
            $(document).ready(function() {
                 var departments = $("#emp_code_hr").kendoComboBox({
                    placeholder: "Select Employee...",
                    dataTextField: "emp_name",
                    dataValueField: "emp_code",
                    dataSource: {
                        transport: {
                            read: {
                                url: "../../controller/leave_management_controllers/employee_list.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox");
                $("#emp_code_hr").change(function() {
                    //Collect variables
                    var emp_code = $("#emp_code_hr").val();
                    //Ajax call to fetch remaining days
                    $.ajax({
                        url: "../../controller/leave_management_controllers/hr_leave_management/emp_for_leave_controller.php?emp_code=" + emp_code + "",
                        type: "GET",
                        dataType: "JSON",
                        success: function(data) {
                            var objects = data.data;
                            console.log(objects);
                            var html = '';
                            $.each(objects, function() {
                                html += '<div class="col-md-6"><b>Company Name:</b></div><div class="col-md-6">' + this.company_title + ' ';
                                html += '</div><br /><div class="clearfix"></div>';
                                html += '<div class="col-md-6"><b>Full Name:</b></div><div class="col-md-6">' + this.emp_firstname + ' ';
                                html += '</div><br /><div class="clearfix"></div>';
                                html += '<div class="col-md-6"><b>Department:</b></div><div class="col-md-6">' + this.department_title + ' ';
                                html += '</div><br /><div class="clearfix"></div>';
                                html += '<div class="col-md-6"><b>Designation:</b></div><div class="col-md-6">' + this.designation_title + ' ';
                                html += '</div><br /><div class="clearfix"></div><br /><br />';
                            });
                            $("#emp_info_container").html(html);
							$("#example").css({"display": "block"});



                            var dataSource = new kendo.data.DataSource({
                                pageSize: 20,
                                transport: {
                                    read: {
                                        url: "../../controller/leave_management_controllers/hr_leave_management/hr_leave_status_meta_controller.php?emp_code=" + emp_code + "",
                                        type: "GET"
                                    }
                                },
                                autoSync: false,
                                schema: {
                                    data: "data",
                                    total: "data.length",
                                    model: {
                                        id: "leave_status_meta_id",
                                        fields: {
                                            leave_status_meta_id: {type: "number"},
                                            leave_title: {type: "string"},
                                            total_days: {type: "string"},
                                            availed_days: {type: "string"},
                                            remaining_days: {type: "string"}
                                        }
                                    }
                                }
                            });

                            jQuery("#grid_three").kendoGrid({
                                dataSource: dataSource,
                                filterable: true,
                                scrollable: true,
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSizes: [20, 40, 60, 100]
                                },
                                sortable: true,
                                groupable: true,
                                columns: [
                                    {field: "leave_title", title: "Leave Title", id: "leave_title", width: "150px", format: "{0:dd-MM-yyyy}"},
                                    {field: "total_days", title: "Total Days", id: "total_days", width: "150px", format: "{0:dd-MM-yyyy}"},
                                    {field: "availed_days", title: "Availed Days", id: "availed_days", width: "150px"},
                                    {field: "remaining_days", title: "Remaining Days", id: "remaining_days", width: "150px"},
                                ],
                                editable: "inline"
                            });
                            
                            //refreshing kendo grid in change of employee selection from drop down
                            $("#grid_three").data("kendoGrid").dataSource.read();
                            $("#grid_three").data('kendoGrid').refresh();

                            var dataSource_two = new kendo.data.DataSource({
                                pageSize: 20,
                                transport: {
                                    read: {
                                        url: "../../controller/leave_management_controllers/hr_leave_management/hr_leave_history_controller.php?emp_code=" + emp_code + "",
                                        type: "GET"
                                    }
                                },
                                autoSync: false,
                                schema: {
                                    data: "data",
                                    total: "data.length",
                                    model: {
                                        id: "leave_application_master_id",
                                        fields: {
                                            leave_application_master_id: {type: "number"},
                                            leave_title: {type: "string"},
                                            mindate: {type: "date"},
                                            maxdate: {type: "date"},
                                            details_no_of_days: {type: "string"},
                                            status: {type: "string"},
                                            review_remark: {type: "string"},
                                            is_half: {type: "string"},
                                            day_part: {type: "string"},
                                            replacement_date: {type: "date"},
					    reasons : {type: "string"}
                                        }
                                    }
                                }
                            });

                            jQuery("#grid_four").kendoGrid({
                                dataSource: dataSource_two,
                                filterable: true,
                                scrollable: true,
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSizes: [20, 40, 60, 100]
                                },
                                sortable: true,
                                groupable: true,
                                columns: [
                                    {field: "leave_title", title: "Leave Title", id: "leave_title", width: "150px", format: "{0:dd-MM-yyyy}"},
                                    {field: "mindate", title: "Start Date", id: "Start Date", width: "150px", format: "{0:dd-MM-yyyy}"},
                                    {field: "maxdate", title: "End Date", id: "End Date", width: "150px", format: "{0:dd-MM-yyyy}"},
                                    {field: "details_no_of_days", title: "Total Days", id: "details_no_of_days", width: "150px"},
                                    {field: "replacement_date", title: "Replacement Date", id: "replacement_date", width: "150px", format: "{0:dd-MM-yyyy}"},
                                    {field: "status", title: "Status", id: "status", width: "150px"},
                                    {field: "is_half", title: "Is Half?", id: "is_half", width: "150px"},
                                    {field: "day_part", title: "Day Part", id: "day_part", width: "150px"},
                                    {field: "review_remark", title: "Remarks", id: "review_remark", width: "150px"},
				    {field: "reasons", title: "Reason", id: "reasons", width: "200px"}
                                ],
                                editable: "inline"
                            });

                            //refreshing kendo grid in change of employee selection from drop down
                            $("#grid_four").data("kendoGrid").dataSource.read();
                            $('#grid_four').data('kendoGrid').refresh();

                        }
                    });


                });
            });
        </script>
        <div class="col-md-6">
            <label for="emp_code"> Employee Code : </label><br />
            <input type="text"  name="emp_code_hr" id="emp_code_hr" value="<?php echo $emp_code; ?>" style="width: 80%;">
        </div>
        <div class="col-md-6">
            <label for="Reason">Reason:</label><br/>
            <input id="reason" maxlength="200" type="text" class="k-textbox" value="<?php echo $reason; ?>" name="reason" placeholder="" style="width: 87%;"/>
        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-6" id="emp_info_container" style="padding-left: 0px;">
        </div>
        <div class="clearfix">

        </div>
        <hr />

        <?php include("../../layout/msg.php"); ?>
        <div id="test_container"></div>
        <!--Custom Error Section-->
        <form method="post" id="myform">
            <div class="col-md-4">
                <label for="Start Date">Company:</label><br/>
                <select id="size10" style="width: 80%" name="company_id" <?php
                if ($is_super != 'yes') {
                    echo 'disabled="disabled"';
                }
                ?>>
                    <option value="0">Select Company ...</option>
                    <?php if (count($companies) >= 1): ?>
                        <?php foreach ($companies as $com): ?>
                            <option value="<?php echo $com->company_id; ?>" 
                            <?php
                            if ($com->company_id == $company_id) {
                                echo "selected='selected'";
                            }
                            ?>>
                                <?php echo $com->company_title; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="Start Date">Leave Starts From:</label><br/>
                <input type="text" id="start_date" class="emp_datepicker" value="<?php echo $start_date; ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>

            <div class="col-md-4">
                <label for="Start Date">Leave Ends At:</label><br/>
                <input id="end_date" type="text" class="emp_datepicker" value="<?php echo $end_date; ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div><br />
            <div class="col-md-6" style="padding-top:18px;">
                <input type="checkbox" id="multiple_leave_types" name="multiple_leave_types">&nbsp Apply for multiple leave types
                &nbsp;<input type="checkbox" value="yes" id="is_half" name="is_half">&nbsp; <span id="label_half">Apply for half day leave</span>
            </div>
            <div class="col-md-4" id="second_total_days_div" style="display: none;">
                <label for="Start Date">Total Days:</label><br/>
                <input type="text" id="second_total_days" class="k-textbox" name="total_days" value="<?php echo $total_days; ?>" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-4" style="display: none;" id="half_day">
                <label for="Day Part">Select Part of Day:</label><br/>
                <select id="day_part" name="day_part" style="width: 80%;">
                    <option value="">Select Half</option>
                    <option value="1st Half">1st Half</option>
                    <option value="2nd Half">2nd Half</option>
                </select>
            </div>
            <div class="clearfix"></div>

            <div class="col-md-12" style="padding-right: 68px;"  id="divider"><hr/></div>
            <div class="col-md-12" style="display: none;"  id="divider_two"><hr/></div>
            <div class="clearfix"></div>
            <div id="signle_leave">
                <div class="col-md-4">
                    <label for="Start Date">Leave Type:</label><br/>
                    <select id="size9" style="width: 80%" name="leave_type_id">
                        <option value="0">Select Leave Type</option>
                        <?php if (count($leave_types) >= 1): ?>
                            <?php foreach ($leave_types as $leave): ?>
                                <option value="<?php echo $leave->leave_policy_id; ?>" 
                                <?php
                                if ($leave->leave_policy_id == $leave_type_id) {
                                    echo "selected='selected'";
                                }
                                ?>><?php echo $leave->leave_title; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4" id="remaining_day">
                    <label for="Start Date">Remaining Days:</label><br/>
                    <input type="text" id="remaining_days" class="k-textbox" value="<?php echo $remaining_days; ?>" name="remaining_days" placeholder="" class="k-textbox" style="width: 80%;"/>
                </div>

                <div class="col-md-4" id="div_in_leu">
                    <label for="Start Date">Replacement Date: </label><br/>
                    <input type="text" id="leu_date" class="leu_datepicker" value="<?php echo $leu_date; ?>" name="leu_date" placeholder="" class="k-textbox" style="width: 80%;"/>
                </div> 

                <div class="col-md-4">
                    <label for="Start Date">Total Days:</label><br/>
                    <input type="text" id="total_days" class="k-textbox" value="<?php echo $total_days; ?>" name="total_days" placeholder="" class="k-textbox" style="width: 80%;"/>
                </div>
            </div>

            <div class="clearfix"></div>

            <div id="multiple_leave" class="col-md-12" style="display:none;">
                <div id="grid"></div>
            </div>

            <script type="text/javascript">
                $(document).ready(function() {
                    var dataSource = new kendo.data.DataSource({
                        pageSize: 20,
                        transport: {
                            read: {
                                url: "../../controller/leave_management_controllers/hr_leave_management/hr_leave_application_temp_controller.php",
                                type: "GET"
                            },
                            update: {
                                url: "../../controller/leave_management_controllers/hr_leave_management/hr_leave_application_temp_controller.php",
                                type: "POST",
                                complete: function(e) {
                                    $("#grid").data("kendoGrid").dataSource.read();
                                }
                            },
                            destroy: {
                                url: "../../controller/leave_management_controllers/hr_leave_management/hr_leave_application_temp_controller.php",
                                type: "DELETE",
                                complete: function(e) {
                                    $("#grid").data("kendoGrid").dataSource.read();
                                }
                            },
                            create: {
                                url: "../../controller/leave_management_controllers/hr_leave_management/hr_leave_application_temp_controller.php",
                                type: "PUT",
                                complete: function(e) {
                                    $("#grid").data("kendoGrid").dataSource.read();
                                }
                            }
                        },
                        autoSync: false,
                        schema: {
                            errors: function(e) {
                                if (e.error === "yes")
                                {
                                    var message = "";
                                    message += e.message;
                                    var window = $("#kWindow");
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
                                id: "leave_application_temp_id",
                                fields: {
                                    leave_application_temp_id: {type: "number"},
                                    leave_type_title: {type: "string"},
                                    start_date_temp: {type: "date", nullable: true, validation: {required: "Invalid"}},
                                    end_date_temp: {type: "date", nullable: true, validation: {required: "Invalid"}},
                                    total_days_temp: {type: "string", editable: false},
                                    leave_title: {type: "string"},
                                    leave_policy_id: {type: "number"},
                                    remaining_days_temp: {type: "string", editable: false},
                                    replacement_date: {type: "date", nullable: true}
                                }
                            }
                        }
                    });
                    $("#grid").kendoGrid({
                        dataSource: dataSource,
                        filterable: true,
                        scrollable: true,
                        pageable: {
                            refresh: true,
                            input: true,
                            numeric: false,
                            pageSizes: [20, 40, 60, 100]
                        },
                        sortable: true,
                        groupable: true,
                        toolbar: [{name: "create", text: "Add a Leave Type"}],
                        columns: [
                            {field: "leave_policy_id",
                                title: "Leave Title",
                                id: "leave_policy_id", width: "200px",
                                editor: LeaveDropDownEditor,
                                template: "#=leave_title#",
                                filterable: {
                                    ui: LeaveFilter,
                                    extra: false,
                                    operators: {
                                        string: {
                                            eq: "Is equal to",
                                            neq: "Is not equal to"
                                        }
                                    }
                                }
                            },
                            {field: "start_date_temp", title: "Start Date", id: "start_date_temp", width: "150px", format: "{0:dd-MM-yyyy}"},
                            {field: "end_date_temp", title: "End Date", id: "end_date_temp", width: "150px", format: "{0:dd-MM-yyyy}"},
                            {field: "replacement_date", title: "Replacement Date", id: "replacement_date", width: "150px", format: "{0:dd-MM-yyyy}"},
                            {field: "total_days_temp", title: "Total Days", id: "total_days_temp", width: "150px"},
                            {field: "remaining_days_temp", title: "Remaining Days", id: "remaining_days_temp", width: "150px"},
                            {command: ["edit", "destroy"], title: "Action", width: "230px"}],
                        editable: "inline"
                    });
                });
            </script>

            <script type="text/javascript">
                function LeaveFilter(element) {
                    element.kendoDropDownList({
                        autoBind: false,
                        dataTextField: "leave_title",
                        dataValueField: "leave_policy_id",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/leave_management_controllers/leave_policy.php",
                                    type: "GET"
                                }
                            },
                            schema: {
                                data: "data"
                            }
                        },
                        optionLabel: "Select Leave Title"
                    });
                }
                function LeaveDropDownEditor(container, options) {
                    jQuery('<input required data-text-field="leave_title" data-value-field="leave_policy_id" data-bind="value:' + options.field + '"/>')
                            .appendTo(container)
                            .kendoDropDownList({
                                autoBind: false,
                                dataTextField: "leave_title",
                                dataValueField: "leave_policy_id",
                                dataSource: {
                                    transport: {
                                        read: {
                                            url: "../../controller/leave_management_controllers/leave_policy.php",
                                            type: "GET"
                                        }
                                    },
                                    schema: {
                                        data: "data"
                                    }
                                },
                                optionLabel: "Select Leave Title"
                            });
                }

            </script>
            <div class="clearfix"></div>

            <br />

            <div class="col-md-12">
                <input type="button" id="save_hr" class="k-button" value="Submit Request" name="save"><br/><br />
                <input type="button" id="save_multiple_hr" style="display: none;" class="k-button" value="Submit Request" name="save_multiple"><br/><br />
            </div>

            <div class="clearfix"></div>

            <div id="example" class="k-content" style="display:none;">
                <div id="tabstrip">
                    <ul>
                        <li class="k-state-active">
                            Summary
                        </li>
                        <li>
                            History
                        </li>
                    </ul>

                    <div>
                        <div class="weather">
                            <div id="example">
                                <div id="grid_three"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="weather">
                            <div id="example">
                                <div id="grid_four"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>       
        </form>

    </div>
</div>


<?php include '../view_layout/footer_view.php'; ?>
<!--Collect employee code for logged user-->
<?php $emp_code_log = $_SESSION["emp_code"]; ?>


<script language="JavaScript">
    $(document).ready(function() {
        // Listen for click on toggle checkbox :: multiple_leave_types
        $('#multiple_leave_types').click(function(event) {
            if (this.checked) {
                $("#multiple_leave").show(500);
                $("#signle_leave").hide(500);
                $("#divider_two").show(500);
                $("#divider").hide(500);
                $("#save_hr").hide();
                $("#save_multiple_hr").show();
                $("#second_total_days_div").show(500);
                $("#is_half").hide(500);
                $("#label_half").hide(500);
            } else {
                $("#multiple_leave").hide(500);
                $("#signle_leave").show(500);
                $("#divider_two").hide(500);
                $("#divider").show(500);
                $("#save_hr").show();
                $("#save_multiple_hr").hide();
                $("#second_total_days_div").hide(500);
                $("#is_half").show(500);
                $("#label_half").show(500);
            }
        });
    });
</script> 
<!--Multiple Leave UI reveal Animation End-->

<!--HR::Save leave single types-->
<script type="text/javascript">
    $(document).ready(function() {
        $("#save_hr").click(function() {
            var master_start_date = $("#start_date").val();
            var master_end_date = $("#end_date").val();
            var total_days = $("#total_days").val();
            var company_id = $("#size10").val();
            var leave_type_id = $("#size9").val();
            var emp_code = $("#emp_code_hr").val();
            var replacement_date = $("#leu_date").val();
            var reason = $("#reason").val();

            if ($("#is_half").length === $("#is_half:checked").length) {
                var is_half = $("#is_half").val();
            }
            var day_part = $("#day_part").val();


            $.ajax({
                type: "POST",
                url: "../../controller/leave_management_controllers/hr_leave_management/hr_single_leave_save_controller.php",
                data: {
                    start_date: master_start_date,
                    end_date: master_end_date,
                    emp_code: emp_code,
                    total_days: total_days,
                    company_id: company_id,
                    leave_type_id: leave_type_id,
                    is_half: is_half,
                    day_part: day_part,
                    replacement_date: replacement_date,
                    reason: reason
                },
                dataType: "json",
                success: function(data) {
                    if (data.data.success_flag === "yes") {
                        $("#test_container").html();
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                        $('#myform')[0].reset();
                    } else {
                        $("#test_container").html();
                        $("#test_container").html("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                    }
                }
            });
            //Refresh grid_one
            $('#grid_three').data('kendoGrid').dataSource.read();
            $('#grid_three').data('kendoGrid').refresh();
            //Refresh grid_two
            $('#grid_four').data('kendoGrid').dataSource.read();
            $('#grid_four').data('kendoGrid').refresh();
        });
    });
</script>

<!--HR:: Validate form data for multiple leave types-->
<script type="text/javascript">
    $(document).ready(function() {
        $("#save_multiple_hr").click(function() {
            var master_start_date = $("#start_date").val();
            var master_end_date = $("#end_date").val();
            var total_days = $("#second_total_days").val();
            var company_id = $("#size10").val();
            var emp_code = $("#emp_code_hr").val();
            var reason = $("#reason").val();

            $.ajax({
                type: "POST",
                url: "../../controller/leave_management_controllers/hr_leave_management/hr_multi_leave_details_controller.php",
                data: {
                    master_start_date: master_start_date,
                    master_end_date: master_end_date,
                    emp_code: emp_code,
                    total_days: total_days,
                    company_id: company_id,
                    reason: reason
                },
                dataType: "json",
                success: function(data) {
                    if (data.data.success_flag === "yes") {
                        $("#test_container").html();
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                        $('#myform')[0].reset();
                        $('#grid').data('kendoGrid').dataSource.read();
                        $('#grid').data('kendoGrid').refresh();
                    } else {
                        $("#test_container").html();
                        $("#test_container").html("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                    }
                }
            });
            //Refresh grid_one
            $('#grid_one').data('kendoGrid').dataSource.read();
            $('#grid_one').data('kendoGrid').refresh();
            //Refresh grid_two
            $('#grid_two').data('kendoGrid').dataSource.read();
            $('#grid_two').data('kendoGrid').refresh();
        });
    });
</script>

<script type="text/javascript">
    //Find remaining days of selected leave type
    $(document).ready(function() {
        $("#div_in_leu").hide();
        $("#size9").change(function() {

            //Collect variables
            var leave_type_id = $("#size9").val();
            if (leave_type_id == 5) {
                $("#div_in_leu").show(500);
                $("#remaining_day").hide(500);
            } else {
                $("#remaining_day").show(500);
                $("#div_in_leu").hide(500);
            }

            //Collect variables
            var leave_type_id = $("#size9").val();
            var emp_code = "<?php echo $hr_emp_code; ?>";

            //Ajax call to fetch remaining days
            $.ajax({
                url: "../../controller/leave_management_controllers/hr_leave_management/hr_emp_leave_type_controller.php?leave_type_id=" + leave_type_id + "&emp_code=" + emp_code + "",
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    var objects = data.data;
                    console.log(objects);
                    $.each(objects, function() {
                        //Collect the variable and push
                        var remaining_days = this.remaining_days;
                        $("#remaining_days").val(remaining_days);
                    });
                }
            });
        });
    });

    $(document).ready(function() {
        // create DatePicker from input HTML element
        $(".leu_datepicker").kendoDatePicker({
            onSelect: function(d, i) {
                if (d !== i.lastVal) {
                    $(this).change();
                }
            }
        });
    });

    //No of Days between selected days
    $(document).ready(function() {
        $("#end_date").change(function() {
            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();

            var date1 = new Date("" + start_date + "");
            var date2 = new Date("" + end_date + "");
            var timeDiff = date2.getTime() - date1.getTime();
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            var correctDiffDays = parseInt(diffDays + 1);

            if (correctDiffDays <= 0 && start_date !== "" && end_date !== "") {
                $("#start_date").css({"background-color": "lightyellow", "color": "red"});
                $("#end_date").css({"background-color": "lightyellow", "color": "red"});
                $("#total_days").css({"background-color": "lightyellow", "color": "red"});
            } else {
                $("#start_date").css({"background-color": "", "color": "darkgrey"});
                $("#end_date").css({"background-color": "", "color": "darkgrey"});
                $("#total_days").css({"background-color": "", "color": "darkgrey"});
            }

            if (correctDiffDays > 0 && start_date !== "" && end_date !== "") {
                $("#total_days").val(correctDiffDays);
                $("#second_total_days").val(correctDiffDays);
            } else {
                $("#total_days").val(0);
                $("#second_total_days").val(0);
            }

        });

        $("#start_date").change(function() {
            var start_date = $("#start_date").val();
            var end_date = $("#end_date").val();

            date1 = start_date.split('/');
            date2 = end_date.split('/');
            //Reorganizing array elements to make date format "y-m-d"
            date1 = new Date(date1[2], date1[0], date1[1]);
            date2 = new Date(date2[2], date2[0], date2[1]);
            date1_unixtime = parseInt(date1.getTime() / 1000);
            date2_unixtime = parseInt(date2.getTime() / 1000);
            var timeDifference = date2_unixtime - date1_unixtime;
            var timeDifferenceInHours = timeDifference / 60 / 60;
            var timeDifferenceInDays = timeDifferenceInHours / 24;
            var diff = timeDifferenceInDays + 1;

            if (diff <= 0 && start_date !== "" && end_date !== "") {
                $("#start_date").css({"background-color": "lightyellow", "color": "red"});
                $("#end_date").css({"background-color": "lightyellow", "color": "red"});
                $("#total_days").css({"background-color": "lightyellow", "color": "red"});
            } else {
                $("#start_date").css({"background-color": "", "color": "darkgrey"});
                $("#end_date").css({"background-color": "", "color": "darkgrey"});
                $("#total_days").css({"background-color": "", "color": "darkgrey"});
            }

            if (diff > 0 && start_date !== "" && end_date !== "") {
                $("#total_days").val(diff);
                $("#second_total_days").val(diff);
            } else {
                $("#total_days").val(0);
                $("#second_total_days").val(0);
            }
        });

    });
    $(document).ready(function() {
        $("#tabstrip").kendoTabStrip({
            animation: {
                open: {
                    effects: "fadeIn"
                }
            }
        });
    });
    $(document).ready(function() {
        $("#tabstrip_two").kendoTabStrip({
            animation: {
                open: {
                    effects: "fadeIn"
                }
            }
        });
    });
    $(document).ready(function() {
        $("#size").kendoDropDownList();
        $("#size2").kendoDropDownList();
        $("#size3").kendoDropDownList();
        $("#size4").kendoDropDownList();
        $("#size5").kendoDropDownList();
        $("#size6").kendoDropDownList();
        $("#size7").kendoDropDownList();
        $("#size8").kendoDropDownList();
        $("#size9").kendoDropDownList();
        $("#size10").kendoDropDownList();
        $("#size11").kendoDropDownList();
        $("#size13").kendoDropDownList();
        $("#size14").kendoDropDownList();
        $("#size15").kendoDropDownList();
        $("#size16").kendoDropDownList();
        $("#size17").kendoDropDownList();
        $("#size18").kendoDropDownList();
        $("#size22").kendoDropDownList();
        $("#size30").kendoDropDownList();
        $("#new").kendoDropDownList();
        $("#day_part").kendoDropDownList();

    });
    $(document).ready(function() {
        $("#files").kendoUpload();
    });
    $(document).ready(function() {
        // create DatePicker from input HTML element
        $(".emp_datepicker").kendoDatePicker({
            onSelect: function(d, i) {
                if (d !== i.lastVal) {
                    $(this).change();
                }
            }
        });
    });
    $(document).ready(function() {
        // create DatePicker from input HTML element
        $(".emp1_datepicker").kendoDatePicker({
            onSelect: function(d, i) {
                if (d !== i.lastVal) {
                    $(this).change();
                }
            }
        });
    });</script>

<script language="JavaScript">
    $(document).ready(function() {
        // Listen for click on toggle checkbox :: multiple_leave_types
        $('#is_half').click(function(event) {
            if (this.checked) {
                $("#half_day").show(500);
                $("#total_days").val(0.5);
            } else {
                $("#half_day").hide(500);

                //Retrive main diff
                var start_date = $("#start_date").val();
                var end_date = $("#end_date").val();

                date1 = start_date.split('/');
                date2 = end_date.split('/');

                //Reorganizing array elements to make date format "y-m-d"
                date1 = new Date(date1[2], date1[0], date1[1]);
                date2 = new Date(date2[2], date2[0], date2[1]);
                date1_unixtime = parseInt(date1.getTime() / 1000);
                date2_unixtime = parseInt(date2.getTime() / 1000);
                var timeDifference = date2_unixtime - date1_unixtime;
                var timeDifferenceInHours = timeDifference / 60 / 60;
                var timeDifferenceInDays = timeDifferenceInHours / 24;
                var diff = timeDifferenceInDays + 1;

                if (start_date !== '' && end_date !== '') {
                    $("#total_days").val(diff);
                } else {
                    $("#total_days").val(0);
                }
            }
        });
    });
</script> 

<!--Validate form data for multiple leave types-->
<script type="text/javascript">
    $(document).ready(function() {
        $("#account_change").click(function() {
            var admin_first_name = $("#admin_first_name").val();
            var admin_last_name = $("#admin_last_name").val();
            var admin_mobile_number = $("#admin_mobile_number").val();
            var admin_address = $("#admin_address").val();
            if (admin_first_name === '') {
                $(".error").html('');
                $("#admin_first_name").after('<span class="error" style="color:red;">Please Enter First Name</span>');
            } else if (admin_last_name === '') {
                $(".error").html('');
                $("#admin_last_name").after('<span class="error" style="color:red;">Please Enter Last Name</span>');
            } else if (admin_mobile_number === '') {
                $(".error").html('');
                $("#admin_mobile_number").after('<span class="error" style="color:red;">Please Enter Mobile Number</span>');
            } else {
                $.ajax({
                    type: "POST",
                    url: "../../controller/leave_management_controllers/account_settings.php",
                    data: {
                        admin_first_name: admin_first_name,
                        admin_last_name: admin_last_name,
                        admin_mobile_number: admin_mobile_number,
                        admin_address: admin_address
                    },
                    dataType: "json",
                    success: function(response) {
                        console.log(response);
                        if (response.output !== "error") {
                            alert("Your Account Information Changed Successfully");
                            window.location = "index.php";
                        } else {
                            alert(response.msg);
                        }
                    }
                });
            }

        });
    });
</script>
