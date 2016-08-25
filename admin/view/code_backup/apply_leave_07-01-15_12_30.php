<?php
session_start();
/*
 * Author: Rajan Hossain
 * Page: Apply for a leave
 */

//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

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

//Initialize variables
//Employee Info
$emp_code = '';
$emp_name = '';
$emp_fullname = '';
$emp_department = '';
$emp_contact_number = '';
$emp_id = '';
$employees = array();

//Application info
$application_date = '';
$employee_id = '';
$start_date = '';
$end_date = '';
$total_days = '';
$no_of_days = '';
$leave_type_id = '';
$is_approved = '';
$status = '';
$approved_date = '';
$approved_by_id = '';
$leaves = array();
$leave_type = '';

$applied_annual_leave_id = '';
//Fetch all leave types
$leaves = $con->SelectAll("leave_policy");

//Search employee
if (isset($_POST["search_employee"])) {
    extract($_POST);
    $employees = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
    if (count($employees) > 0) {
        foreach ($employees as $employee) {
            $emp_id = $employee->emp_id;
            $_SESSION["emp_id"] = $employee->emp_id;
            $emp_fullname .= $employee->emp_firstname;
            $emp_fullname .= ' ' . $employee->emp_lastname;
            $emp_photo = $employee->emp_photo;
            $emp_email = $employee->emp_email;
            $emp_department = $employee->emp_department;
            $emp_contact_number = $employee->emp_contact_number;
        }

        $applied_leaves = $con->SelectAllByCondition("applied_annual_leave", "emp_id='$emp_id'");
        if (count($applied_leaves) > 0) {
            foreach ($applied_leaves as $aleave) {
                $_SESSION['applied_annual_leave_id'] = $aleave->applied_annual_leave_id;
            }
        }
    }
}

/*
 * Globally usable employee ID
 * Not generated until above form posted
 */

if (isset($_SESSION["emp_id"])) {
    $emp_id = $_SESSION["emp_id"];
}

if (isset($_SESSION["applied_annual_leave_id"])) {
    $applied_annual_leave_id = $_SESSION["applied_annual_leave_id"];
}


if (isset($_SESSION["emp_code"])) {
    $emp_code = $_SESSION["emp_code"];
}

if (isset($_SESSION["hr_emp_code"])) {
    if ($_SESSION["hr_emp_code"] != '') {
        $emp_code = $_SESSION["hr_emp_code"];
    }
}

if (isset($_SESSION["company_id"])) {
    $company_id = $_SESSION['company_id'];
}

if ($_SESSION["is_super"] == 'yes') {
    $is_super = "yes";
}

//Create leave schedule for a single type of leave
if (isset($_POST['save'])) {
    extract($_POST);

    if ($company_id == 0) {
        $err = "Please select a company";
    } else if ($start_date == '') {
        $err = 'Please specify start date.';
    } else if ($start_date > $end_date) {
        $err = "Invalid date range selection. Start date can not be larger than end date.";
    } else if ($end_date == '') {
        $err = 'Please specify end date.';
    } else if ($leave_type_id == 0) {
        $err = 'Please select a leave type.';
    } else {

        //Format today
        $today = date("Y/m/d");
        $sys_date = date_create($today);
        $formatted_today = date_format($sys_date, 'Y-m-d');

        //Format start date
        $frm_start_date = date_create($start_date);
        $formatted_start_date = date_format($frm_start_date, 'Y-m-d');

        //Format end date
        $frm_end_date = date_create($end_date);
        $formatted_end_date = date_format($frm_end_date, 'Y-m-d');

        $app_array = array(
            "company_id" => $company_id,
            "emp_code" => $emp_code,
            "start_date" => $formatted_start_date,
            "end_date" => $formatted_end_date,
            "no_of_days" => $total_days,
            "status" => "pending",
            "is_approved" => "no"
        );

        $last_id = $con->insert_with_last_id("leave_application_master", $app_array);
        $dates = $con->SelectAllByCondition("dates", "company_id='$company_id' AND date between '$formatted_start_date' AND '$formatted_end_date'");

        foreach ($dates as $date) {
            $frm_start_date = date_create($date->date);
            $formatted_start_date = date_format($frm_start_date, 'Y-m-d');

            if ($last_id != 0) {
                $details_array = array(
                    "leave_application_master_id" => $last_id,
                    "leave_type_id" => $leave_type_id,
                    "details_date" => $formatted_start_date,
                    "details_no_of_days" => $total_days,
                    "status" => "pending"
                );
                if ($con->insert("leave_application_details", $details_array) == 1) {
                    $msg = 'A leave request is submitted. Once reviewed, you should recieve a confirmation email.';
                    unset($_SESSION["hr_emp_code"]);
                }
            }
        }
    }
}

//Create leave application for multiple type of leave
$leave_types = $con->SelectAll("leave_policy");
$companies = $con->SelectAll("company");

$leave_status_query = "select leave_status_meta.*, leave_policy.leave_title
                    FROM leave_status_meta
                    LEFT JOIN leave_policy on leave_policy.leave_policy_id = leave_status_meta.leave_type_id
                    WHERE leave_status_meta.emp_code='$emp_code'";
$meta_result = $con->QueryResult($leave_status_query);

$history_query = "select  A.leave_application_master_id, A.leave_type_id, A.mindate, B.maxdate, A.details_no_of_days,  A.status, A.remarks, A.leave_title FROM
(SELECT  leave_application_master_id, leave_type_id, min(details_date) as mindate , details_no_of_days,  leave_application_details.status, leave_title, remarks from leave_application_details 
LEFT JOIN leave_policy  ON leave_policy.leave_policy_id =  leave_application_details.leave_type_id
 where leave_application_master_id 
in(SELECT  leave_application_master_id from leave_application_master  WHERE emp_code='$emp_code')
GROUP BY leave_application_master_id, leave_type_id)  A,
(SELECT  leave_application_master_id, leave_type_id, max(details_date) as maxdate from leave_application_details where leave_application_master_id 
in(SELECT  leave_application_master_id from leave_application_master  WHERE emp_code='$emp_code')
GROUP BY leave_application_master_id, leave_type_id) B
WHERE A.leave_application_master_id = B.leave_application_master_id and A.leave_type_id = B.leave_type_id";
$histories = $con->QueryResult($history_query);

//Submit view summary trigger for HR type application
if (isset($_POST["frmShowInfo"])) {
    $employees_query = "select 
    tmp_employee.*,
    department.department_title,
    designation.designation_title,
    company.company_title
    from tmp_employee 
    left join department on department.department_id = tmp_employee.emp_department
    left join designation on designation.designation_id = tmp_employee.emp_designation
    left join company on company.company_id = tmp_employee.company_id
    where tmp_employee.emp_code='$emp_code'
    ";
    $employees = $con->QueryResult($employees_query);
}
?>


<?php include '../view_layout/header_view.php'; ?>

<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Leave Request</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php if (isset($_GET["type"])): ?>
            <?php if ($_GET["type"] == 'hr'): ?>
                <!--Company Combo-->
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        var departments = $("#emp_code_hr").kendoComboBox({
                            placeholder: "Select Employee...",
                            dataTextField: "emp_name",
                            dataValueField: "emp_code",
                            dataSource: {
                                transport: {
                                    read: {
                                        url: "../../controller/employee_list.php",
                                        type: "GET"
                                    }
                                },
                                schema: {
                                    data: "data"
                                }
                            }
                        }).data("kendoComboBox");
                    });
                    $(document).ready(function () {
                        $("#emp_code_hr").change(function () {
                            //Collect variables
                            var emp_code = $("#emp_code_hr").val();
                            //Ajax call to fetch remaining days
                            $.ajax({
                                url: "../../controller/emp_for_leave_controller.php?emp_code=" + emp_code + "",
                                type: "GET",
                                dataType: "JSON",
                                success: function (data) {
                                    var objects = data.data;
                                    console.log(objects);
                                    var html = '';
                                    $.each(objects, function () {
                                        html += '<div class="col-md-6"><b>Company Name:</b></div><div class="col-md-6">' + this.company_title + ' ';
                                        html += '</div><br /><div class="clearfix"></div>';
                                        html += '<div class="col-md-6"><b>Full Name:</b></div><div class="col-md-6">' + this.emp_firstname + ' ';
                                        html += '</div><br /><div class="clearfix"></div>';
                                        html += '<div class="col-md-6"><b>Department:</b></div><div class="col-md-6">' + this.department_title + ' ';
                                        html += '</div><br /><div class="clearfix"></div>';
                                        html += '<div class="col-md-6"><b>Designation:</b></div><div class="col-md-6">' + this.designation_title + ' ';
                                        html += '</div><br /><div class="clearfix"></div><br /><br />';
                                        html += '<form method="post">';
                                        html += '<input type="submit" name="frmShowInfo" id="frmShowInfo" value="View Leave Summary" class="k-button">';
                                        html += '</form>';
                                    });
                                    $("#emp_info_container").html(html);
                                }
                            });


                        });
                    });
                </script>
                <div class="col-md-6">
                    <label for="emp_code"> Employee Code : </label><br />
                    <input type="text"  name="emp_code_hr" id="emp_code_hr" value="<?php echo $emp_code; ?>" style="width: 80%;">
                </div>
                <div class="clearfix"></div>
                <br />
                <div class="col-md-6" id="emp_info_container" style="padding-left: 0px;">

                </div>
                <div class="clearfix">

                </div>
                <div class="col-md-6" style="padding-left:0px;">
                    <?php if (count($employees) > 0): ?>
                        <div class="col-md-6"><b>Company Name:</b></div>
                        <div class="col-md-6">
                            <?php echo $employees{0}->company_title; ?>
                        </div>
                        <br /><div class="clearfix"></div>
                        <div class="col-md-6"><b>Full Name:</b></div><div class="col-md-6">
                            <?php echo $employees{0}->emp_firstname; ?>
                        </div><br /><div class="clearfix"></div>
                        <div class="col-md-6"><b>Department:</b></div><div class="col-md-6">
                            <?php echo $employees{0}->department_title; ?>
                        </div><br /><div class="clearfix"></div>
                        <div class="col-md-6"><b>Designation:</b></div><div class="col-md-6">
                            <?php echo $employees{0}->designation_title; ?>
                        </div><br /><div class="clearfix"></div><br /><br />
                        <form method="post">
                            <input type="submit" name="frmShowInfo" id="frmShowInfo" value="View Leave Summary" class="k-button">
                        </form>
                    <?php endif; ?>
                </div>
                <div class="clearfix"></div>
                <hr />

            <?php endif; ?>
        <?php endif; ?>
        <?php include("../../layout/msg.php"); ?>
        <div id="test_container"></div>
        <!--Custom Error Section-->
        <form method="post">
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
            <div class="col-md-4" style="padding-top:18px;">
                <input type="checkbox" id="multiple_leave_types" name="multiple_leave_types">&nbsp Apply for multiple leave types
            </div>
            <div class="col-md-4" id="second_total_days_div" style="display: none;">
                <label for="Start Date">Total Days:</label><br/>
                <input type="text" id="second_total_days" class="k-textbox" name="total_days" placeholder="" class="k-textbox" style="width: 80%;"/>
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
                                if ($leave->leave_policy_id == $leave_policy_id) {
                                    echo "selected='selected'";
                                }
                                ?>><?php echo $leave->leave_title; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="Start Date">Remaining Days:</label><br/>
                    <input type="text" id="remaining_days" class="k-textbox" name="remaining_days" placeholder="" class="k-textbox" style="width: 80%;"/>
                </div>

                <div class="col-md-4">
                    <label for="Start Date">Total Days:</label><br/>
                    <input type="text" id="total_days" class="k-textbox" name="total_days" placeholder="" class="k-textbox" style="width: 80%;"/>
                </div>
            </div>

            <div class="clearfix"></div>

            <div id="multiple_leave" class="col-md-12" style="display:none;">
                <div id="grid"></div>
            </div>





            <script type="text/javascript">
                jQuery(document).ready(function () {
                    var dataSource = new kendo.data.DataSource({
                        pageSize: 5,
                        transport: {
                            read: {
                                url: "../../controller/leave_application_temp_controller.php",
                                type: "GET"
                            },
                            update: {
                                url: "../../controller/leave_application_temp_controller.php",
                                type: "POST",
                                complete: function (e) {
                                    jQuery("#grid").data("kendoGrid").dataSource.read();
                                }
                            },
                            destroy: {
                                url: "../../controller/leave_application_temp_controller.php",
                                type: "DELETE",
                                complete: function (e) {
                                    jQuery("#grid").data("kendoGrid").dataSource.read();
                                }
                            },
                            create: {
                                url: "../../controller/leave_application_temp_controller.php",
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
                                id: "leave_application_temp_id",
                                fields: {
                                    leave_application_temp_id: {type: "number"},
                                    leave_type_title: {type: "string"},
                                    start_date_temp: {type: "date", validation: {required: "Invalid"}},
                                    end_date_temp: {type: "date", validation: {required: "Invalid"}},
                                    total_days_temp: {type: "string", editable: false},
                                    leave_title: {type: "string"},
                                    leave_policy_id: {type: "number"},
                                    remaining_days_temp: {type: "string", editable: false}
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
                            pageSizes: [5, 10, 20, 50]
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
                                    url: "../../controller/leave_policy.php",
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
                                            url: "../../controller/leave_policy.php",
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
                <input type="submit" id="save" class="k-button" value="Submit Request" name="save"><br/><br />
                <input type="button" id="save_multiple" style="display: none;" class="k-button" value="Submit Request" name="save_multiple"><br/><br />
            </div>

            <div class="clearfix"></div>
            <br />
        </form>
        <?php if (isset($_GET["type"])): ?>
            <?php if (isset($_POST["frmShowInfo"])): ?>
                <div id="example" class="k-content">
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
                                    <table id="grid1">
                                        <colgroup>
                                            <col style="width:70px" />
                                            <col style="width:70px" />
                                            <col style="width:70px" />
                                            <col style="width:70px" />
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th data-field="make">Leave Type</th>
                                                <th data-field="model1">No of Days</th>
                                                <th data-field="model2">availed Days</th>
                                                <th data-field="model">Remaining Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($meta_result) > 0): ?>
                                                <?php foreach ($meta_result as $meta): ?>
                                                    <tr>
                                                        <td><?php echo $meta->leave_title; ?></td>
                                                        <td><?php echo $meta->total_days; ?></td>
                                                        <td><?php echo $meta->availed_days; ?></td>
                                                        <td><?php echo $meta->remaining_days; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                    <br />
                                    <script>
                                        $(document).ready(function () {
                                            $("#grid1").kendoGrid({
                                                //                        height: auto,
                                                sortable: true,
                                                filterable: true,
                                                scrollable: true,
                                                pageable: {
                                                    refresh: true,
                                                    input: true,
                                                    numeric: false,
                                                    pageSizes: [5, 10, 20, 50]
                                                },
                                                groupable: true,
                                            });
                                        });
                                    </script>                  
                                </div>           
                            </div>
                        </div>

                        <div>
                            <div class="weather">
                                <div id="example">
                                    <table id="grid2">
                                        <colgroup>
                                            <col style="width:100px" />
                                            <col style="width:100px" />
                                            <col style="width:100px" />
                                            <col style="width:100px" />
                                            <col style="width:100px" />
                                            <col style="width:100px" />
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th data-field="make">Leave Type</th>
                                                <th data-field="sdate">Start Day</th>
                                                <th data-field="edate">End Day</th>
                                                <th data-field="ndays">No of Days</th>
                                                <th data-field="status">Status</th>
                                                <th data-field="year">Remarks</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($histories) > 0): ?>
                                                <?php foreach ($histories as $history): ?>
                                                    <tr>
                                                        <td><?php echo $history->leave_title; ?></td>
                                                        <td><?php echo $history->mindate; ?></td>
                                                        <td><?php echo $history->maxdate; ?></td>
                                                        <td><?php echo $history->details_no_of_days; ?></td>
                                                        <td><?php echo $history->status; ?></td>
                                                        <td><?php echo $history->remarks; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>
                                    <script>
                                        $(document).ready(function () {
                                            $("#grid2").kendoGrid({
                                                //                        height: auto,
                                                sortable: true,
                                                filterable: true,
                                                scrollable: true,
                                                pageable: {
                                                    refresh: true,
                                                    input: true,
                                                    numeric: false,
                                                    pageSizes: [5, 10, 20, 50]
                                                },
                                                groupable: true,
                                            });
                                        });
                                    </script>                  
                                </div> 
                            </div>

                        </div>
                        <style scoped>
                            #forecast {
                                width: 100%;
                                height: auto;
                                margin: 30px auto;
                                padding: 80px 15px 0 15px;
                                background: url('../../content/web/tabstrip/forecast.png') transparent no-repeat 0 0;
                            }

                            .sunny, .cloudy, .rainy {
                                display: inline-block;
                                margin: 20px 0 20px 10px;
                                width: 128px;
                                height: auto;
                                background: url('../../content/web/tabstrip/weather.png') transparent no-repeat 0 0;
                            }

                            .cloudy{
                                background-position: -128px 0;
                            }

                            .rainy{
                                background-position: -256px 0;
                            }

                            .weather {
                                width: 100%;
                                padding: 40px 0 0 0;

                            }

                            #forecast h2 {
                                font-weight: lighter;
                                font-size: 5em;
                                padding: 0;
                                margin: 0;
                            }

                            #forecast h2 span {
                                background: none;
                                padding-left: 5px;
                                font-size: .5em;
                                vertical-align: top;
                            }

                            #forecast p {
                                margin: 0;
                                padding: 0;
                            }
                        </style>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div id="example" class="k-content">
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
                                <table id="grid1">
                                    <colgroup>
                                        <col style="width:70px" />
                                        <col style="width:70px" />
                                        <col style="width:70px" />
                                        <col style="width:70px" />
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th data-field="make">Leave Type</th>
                                            <th data-field="model1">No of Days</th>
                                            <th data-field="model2">availed Days</th>
                                            <th data-field="model">Remaining Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($meta_result) > 0): ?>
                                            <?php foreach ($meta_result as $meta): ?>
                                                <tr>
                                                    <td><?php echo $meta->leave_title; ?></td>
                                                    <td><?php echo $meta->total_days; ?></td>
                                                    <td><?php echo $meta->availed_days; ?></td>
                                                    <td><?php echo $meta->remaining_days; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <br />
                                <script>
                                    $(document).ready(function () {
                                        $("#grid1").kendoGrid({
                                            //                        height: auto,
                                            sortable: true,
                                            filterable: true,
                                            scrollable: true,
                                            pageable: {
                                                refresh: true,
                                                input: true,
                                                numeric: false,
                                                pageSizes: [5, 10, 20, 50]
                                            },
                                            groupable: true,
                                        });
                                    });
                                </script>                  
                            </div>           
                        </div>
                    </div>

                    <div>
                        <div class="weather">
                            <div id="example">
                                <table id="grid2">
                                    <colgroup>
                                        <col style="width:100px" />
                                        <col style="width:100px" />
                                        <col style="width:100px" />
                                        <col style="width:100px" />
                                        <col style="width:100px" />
                                        <col style="width:100px" />
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th data-field="make">Leave Type</th>
                                            <th data-field="sdate">Start Day</th>
                                            <th data-field="edate">End Day</th>
                                            <th data-field="ndays">No of Days</th>
                                            <th data-field="status">Status</th>
                                            <th data-field="year">Remarks</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($histories) > 0): ?>
                                            <?php foreach ($histories as $history): ?>
                                                <tr>
                                                    <td><?php echo $history->leave_title; ?></td>
                                                    <td><?php echo $history->mindate; ?></td>
                                                    <td><?php echo $history->maxdate; ?></td>
                                                    <td><?php echo $history->details_no_of_days; ?></td>
                                                    <td><?php echo $history->status; ?></td>
                                                    <td><?php echo $history->remarks; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                    </tbody>
                                </table>
                                <script>
                                    $(document).ready(function () {
                                        $("#grid2").kendoGrid({
                                            //                        height: auto,
                                            sortable: true,
                                            filterable: true,
                                            scrollable: true,
                                            pageable: {
                                                refresh: true,
                                                input: true,
                                                numeric: false,
                                                pageSizes: [5, 10, 20, 50]
                                            },
                                            groupable: true,
                                        });
                                    });
                                </script>                  
                            </div> 
                        </div>

                    </div>
                    <style scoped>
                        #forecast {
                            width: 100%;
                            height: auto;
                            margin: 30px auto;
                            padding: 80px 15px 0 15px;
                            background: url('../../content/web/tabstrip/forecast.png') transparent no-repeat 0 0;
                        }

                        .sunny, .cloudy, .rainy {
                            display: inline-block;
                            margin: 20px 0 20px 10px;
                            width: 128px;
                            height: auto;
                            background: url('../../content/web/tabstrip/weather.png') transparent no-repeat 0 0;
                        }

                        .cloudy{
                            background-position: -128px 0;
                        }

                        .rainy{
                            background-position: -256px 0;
                        }

                        .weather {
                            width: 100%;
                            padding: 40px 0 0 0;

                        }

                        #forecast h2 {
                            font-weight: lighter;
                            font-size: 5em;
                            padding: 0;
                            margin: 0;
                        }

                        #forecast h2 span {
                            background: none;
                            padding-left: 5px;
                            font-size: .5em;
                            vertical-align: top;
                        }

                        #forecast p {
                            margin: 0;
                            padding: 0;
                        }
                    </style>
                </div>
            </div>
        <?php endif; ?>
        <br />

        <div class="clearfix"></div>

        <?php include '../view_layout/footer_view.php'; ?>
        <?php
        $emp_code_log = $_SESSION["emp_code"];
        ?>

        <script language="JavaScript">
            $(document).ready(function () {
                // Listen for click on toggle checkbox :: multiple_leave_types
                $('#multiple_leave_types').click(function (event) {
                    if (this.checked) {
                        $("#multiple_leave").show(500);
                        $("#signle_leave").hide(500);
                        $("#divider_two").show(500);
                        $("#divider").hide(500);
                        $("#save").hide();
                        $("#save_multiple").show();
                        $("#second_total_days_div").show(500);
                    } else {
                        $("#multiple_leave").hide(500);
                        $("#signle_leave").show(500);
                        $("#divider_two").hide(500);
                        $("#divider").show(500);
                        $("#save").show();
                        $("#save_multiple").hide();
                        $("#second_total_days_div").hide(500);
                    }
                });
            });
        </script> 

        <!--Validate form data for multiple leave types-->
        <script type="text/javascript">
            $(document).ready(function () {
                $("#save_multiple").click(function () {
                    var master_start_date = $("#start_date").val();
                    var master_end_date = $("#end_date").val();
                    var total_days = $("#second_total_days").val();
                    var company_id = $("#size10").val();

                    $.ajax({
                        type: "POST",
                        url: "../../controller/multi_leave_details_controller.php",
                        data: {
                            master_start_date: master_start_date,
                            master_end_date: master_end_date,
                            emp_code: "<?php echo $emp_code_log; ?>",
                            total_days: total_days,
                            company_id: company_id
                        },
                        dataType: "json",
                        success: function (data) {
                            $("#test_container").append("");
                            $("#test_container").append("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");

                        }
                    });

                });
            });
        </script>

        <script type="text/javascript">
            //Find remaining days of selected leave type
            $(document).ready(function () {
                $("#size9").change(function () {
                    //Collect variables
                    var leave_type_id = $("#size9").val();
                    var emp_code = "<?php echo $emp_code_log; ?>";

                    //Ajax call to fetch remaining days
                    $.ajax({
                        url: "../../controller/emp_leave_type_controller.php?leave_type_id=" + leave_type_id + "&emp_code=" + emp_code + "",
                        type: "GET",
                        dataType: "JSON",
                        success: function (data) {
                            var objects = data.data;
                            console.log(objects);
                            $.each(objects, function () {
                                //Collect the variable and push
                                var remaining_days = this.remaining_days;
                                $("#remaining_days").val(remaining_days);
                            });
                        }
                    });


                });
            });

            //No of Days between selected days
            $(document).ready(function () {
                $("#end_date").change(function () {
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

                    $("#total_days").val(diff);
                    $("#second_total_days").val(diff);



                });
            });


            $(document).ready(function () {
                $("#tabstrip").kendoTabStrip({
                    animation: {
                        open: {
                            effects: "fadeIn"
                        }
                    }
                });
            });

            $(document).ready(function () {
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
            });

            $(document).ready(function () {
                $("#files").kendoUpload();
            });

            $(document).ready(function () {
                // create DatePicker from input HTML element
                $(".emp_datepicker").kendoDatePicker({
                    onSelect: function (d, i) {
                        if (d !== i.lastVal) {
                            $(this).change();
                        }
                    }
                });
            });
            $(document).ready(function () {
                // create DatePicker from input HTML element
                $(".emp1_datepicker").kendoDatePicker({
                    onSelect: function (d, i) {
                        if (d !== i.lastVal) {
                            $(this).change();
                        }
                    }
                });
            });
        </script>

        <!--Validate form data for multiple leave types-->
        <script type="text/javascript">
            $(document).ready(function () {
                $("#account_change").click(function () {
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
                            url: "../../controller/account_settings.php",
                            data: {
                                admin_first_name: admin_first_name,
                                admin_last_name: admin_last_name,
                                admin_mobile_number: admin_mobile_number,
                                admin_address: admin_address
                            },
                            dataType: "json",
                            success: function (response) {
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