<?php
session_start();
/** Author: Rajan Hossain + Asma
 * Page: Salary View Permission
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

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

$employee_code = '';
$from = '';
$to = '';
$date = '';
$logged_in = '';

if (isset($_POST["save_settings"])) {
    extract($_POST);
    $date = date('Y-m-d H:i:s');
    $logged_in = $_SESSION["emp_code"];

    $employee_code = mysqli_real_escape_string($open, $_POST["emp_code"]);
    $prev_val = $con->QueryResult("SELECT * FROM salary_view_permission WHERE svp_emp_code='$employee_code'");
    if (count($prev_val >= 1)) {

        $query = "UPDATE salary_view_permission SET svp_emp_code='$employee_code',"
                . " svp_sg_position_from='$from',"
                . " svp_sg_position_to='$to',"
                . "last_updated_at='$date',"
                . "last_updated_by='$logged_in' "
                . "WHERE svp_emp_code='$employee_code'";
        $rs = mysqli_query($open, $query);
        if ($rs) {
            $employee_code = '';
            $from = '';
            $to = '';
            $msg = "Salary View Permission Updated Successfully.";
        } else {
            $err = "Salary View Permission is not Updated.";
        }
    } else {
        $insert_array = array(
            "svp_emp_code" => $employee_code,
            "svp_sg_position_from" => $from,
            "svp_sg_position_to" => $to,
            "created_by" => $logged_in,
            "created_by" => $date);

        if ($con->insert("salary_view_permission", $insert_array) == 1) {
            $msg = "Salary View Permission Saved Successfully.";
        } else {
            $err = "Salary View Permission Failed to be Saved.";
        }
    }
}
if (isset($_GET["SVP_id"])) {
    $SVP_id = $_GET["SVP_id"];
    $result_permision = $con->QueryResult("SELECT sp.*,g.staffgrade_title as staffgrade_from,g1.staffgrade_title as staffgrade_to FROM salary_view_permission sp left join staffgrad g on g.staffgrade_id=sp.svp_sg_position_from  left join staffgrad g1 on g1.staffgrade_id=sp.svp_sg_position_to WHERE sp.svp_id='$SVP_id'");

    $emp_code = $result_permision[0]->svp_emp_code;
    $from = $result_permision[0]->svp_sg_position_from;
    $to = $result_permision[0]->svp_sg_position_to;
} else {
    $result_permision = $con->QueryResult("SELECT sp.*,g.staffgrade_title as staffgrade_from,g1.staffgrade_title as staffgrade_to FROM salary_view_permission sp left join staffgrad g on g.staffgrade_id=sp.svp_sg_position_from  left join staffgrad g1 on g1.staffgrade_id=sp.svp_sg_position_to");
}
?>
<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Leave Request</h6></div>
    <div class="widget-body" style="background-color: white;">
        <form method="post">
            <!--Company Combo-->
            <script type="text/javascript">
                jQuery(document).ready(function() {
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
                });

                jQuery(document).ready(function() {
                    var to = $("#to").kendoComboBox({
                        placeholder: "Select Staff Grade...",
                        dataTextField: "staffgrade_title",
                        dataValueField: "priority",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/salary_view_permission_controller.php",
                                    type: "GET"
                                }
                            },
                            schema: {
                                data: "data"
                            }
                        }
                    }).data("kendoComboBox");
                });

                $(document).ready(function() {
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
                                    html += '<div class="col-md-3"><b>Company Name:</b></div><div class="col-md-6">' + this.company_title + ' ';
                                    html += '</div><br /><div class="clearfix"></div>';
                                    html += '<div class="col-md-3"><b>Full Name:</b></div><div class="col-md-6">' + this.emp_firstname + ' ';
                                    html += '</div><br /><div class="clearfix"></div>';
                                    html += '<div class="col-md-3"><b>Department:</b></div><div class="col-md-6">' + this.department_title + ' ';
                                    html += '</div><br /><div class="clearfix"></div>';
                                    html += '<div class="col-md-3"><b>Designation:</b></div><div class="col-md-6">' + this.designation_title + ' ';
                                    html += '</div><br /><div class="clearfix"></div><br /><br />';
                                });
                                $("#test_container").html(html);
                                $("#example").css({"display": "block"});
                            }
                        });
                    });
                });
            </script>
            <script type="text/javascript">
                jQuery(document).ready(function() {
                    var from = $("#from").kendoComboBox({
                        placeholder: "Select Staff Grade...",
                        dataTextField: "staffgrade_title",
                        dataValueField: "priority",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/salary_view_permission_controller.php",
                                    type: "GET"
                                }
                            },
                            schema: {
                                data: "data"
                            }
                        }
                    }).data("kendoComboBox");
                });
            </script>
            <?php include("../../layout/msg.php"); ?>
            <form>
                <div class="col-md-6">
                    <label for="emp_code"> Employee Code : </label><br />
                    <input type="text"  name="emp_code"c id="emp_code_hr" value="<?php echo $emp_code; ?>" style="width: 80%;">
                </div>
                <div class="clearfix"></div>
                <br />
                <div id="test_container">
                </div>
                <div class="clearfix"></div>
                <br>
                <div class="col-md-6">
                    <label for="emp_code">Staff Grade From: </label><br />
                    <input type="text"  name="from" id="from" value="<?php echo $from; ?>" style="width: 80%;">
                </div>
                <div class="col-md-6">
                    <label for="emp_code">To: </label><br />
                    <input type="text"  name="to" id="to" value="<?php echo $to; ?>" style="width: 80%;">
                </div>
                <div class="clearfix"></div>
                <br/>
                <div class="col-md-6">
                    <input type="submit" name="save_settings" value="Save Settings" class="k-button">
                </div>
                <div class="clearfix"></div>
            </form>
    </div>
</div>
<!--//====================== GRID loads here ==================================-->
<div id="example" class="k-content">
    <div id="grid"></div>
</div>
<script type="text/javascript">
    var wnd,
            detailsTemplate;
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 10,
            transport: {
                read: {
                    url: "../../controller/salary_view_permission_controller.php?v=view",
                    type: "GET"
                },
//                update: {
//                    url: "../../controller/salary_view_permission_controller.php",
//                    type: "POST",
//                    complete: function(e) {
//                        jQuery("#grid").data("kendoGrid").dataSource.read();
//                    }
//                },
                destroy: {
                    url: "../../controller/salary_view_permission_controller.php",
                    type: "DELETE"
                },
            },
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
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "svp_id",
                    fields: {
                        svp_id: {type: "number", editable: false, nullable: true},
                        svp_emp_code: {type: "string", validation: {required: true}},
                        svp_sg_position_from: {type: "string"},
                        staffgrade_from: {type: "string"},
                        svp_sg_position_to: {type: "string"},
                        staffgrade_to: {type: "string"}
//                        status: {type: "boolean"}
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
//          toolbar: [ {name: "create", text: "Add City"}],
            columns: [
                {field: "svp_emp_code", title: "Employee Code"},
                {field: "staffgrade_from",
                    title: "Staff Grade From",
                    editor: function(container) {
                        var staffgrade_from = $('<input required id="svp_sg_position_from" name="svp_sg_position_from" />')
                        staffgrade_from.appendTo(container)
                        staffgrade_from.kendoDropDownList({
                            dataTextField: "staffgrade_from",
                            dataValueField: "svp_sg_position_from",
                            autoBind: false,
                            type: "json",
                            dataSource: {
                                transport: {
                                    read: {
                                        url: "../../controller/staffgrade_permission.php?v=from",
                                        type: "GET"
                                    }
                                },
                                schema: {
                                    data: "data"
                                }
                            },
                            optionLabel: "staff grade"
                        });
                    },
                    filterable: {
                        ui: gradeFilter,
                        extra: false,
                        operators: {
                            string: {
                                eq: "Is equal to",
                                neq: "Is not equal to"
                            }
                        }
                    }
                },
                {field: "staffgrade_to",
                    title: "Staff Grade To",
                    editor: function(container) {
                        var staffgrade_to = $('<input required id="svp_sg_position_to" name="svp_sg_position_to" />')
                        staffgrade_to.appendTo(container)
                        staffgrade_to.kendoDropDownList({
                            dataTextField: "staffgrade_to",
                            dataValueField: "svp_sg_position_to",
                            autoBind: false,
                            type: "json",
                            dataSource: {
                                transport: {
                                    read: {
                                        url: "../../controller/staffgrade_permission.php",
                                        type: "GET"
                                    }
                                },
                                schema: {
                                    data: "data"
                                }
                            },
                            optionLabel: "staff grade"
                        });
                    },
                    filterable: {
                        ui: grade1Filter,
                        extra: false,
                        operators: {
                            string: {
                                eq: "Is equal to",
                                neq: "Is not equal to"
                            }
                        }
                    }
                },
                {
                    template: kendo.template($("#edit-template").html()), width: "100px", title: "Action"
                },
                {command: ["destroy"], title: "Action", width: "100px"}],
            editable: "inline"
        }).data("kendoGrid");
    });

    function gradeFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "staffgrade_from",
            dataValueField: "svp_sg_position_from",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/staffgrade_permission.php",
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
    function grade1Filter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "staffgrade_to",
            dataValueField: "svp_sg_position_to",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/staffgrade_permission.php",
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
<script id="edit-template" type="text/x-kendo-template">
    <a class="k-button" href="index.php?SVP_id=#= svp_id #" ><i class="fa fa-edit"></i> Edit</a>
</script>
<div id="kWindow"></div>

</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>