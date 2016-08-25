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

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if (isset($_POST["save_settings"])) {
    extract($_POST);

    $insert_array = array(
        "svp_emp_code" => $emp_code,
        "svp_sg_position_from" => $from,
        "svp_sg_position_to" => $to
    );

    if ($con->insert("salary_view_permission", $insert_array) == 1) {
        $msg = "Salary View Permission Saved Successfully.";
    } else {
        $err = "Salary View Permission Failed to be Saved.";
    }
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


<?php include '../view_layout/footer_view.php'; ?>




