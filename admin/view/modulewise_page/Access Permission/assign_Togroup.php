<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
error_reporting(0);
// Set timezone
date_default_timezone_set('UTC');

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

$eemp_cd = $_SESSION['emp_code'];
//$creatd_qury = $con->SelectAllByCondition("tmp_employee", "emp_code = '$eemp_cd'");
//$createdby = $creatd_qury{0}->emp_id;
$createdby = $eemp_cd;
$_SESSION['role_id'] = '';

$employee_role = $con->SelectAll("employee_role");
if (isset($_GET['role_id'])) {
    $role_id = $_GET['role_id'];
    $_SESSION['role_id'] = $role_id;
}
if (isset($_POST["add_empgroup"])) {
    extract($_POST);
//    $con->debug($_POST); //    exit();

    $insert_array = array();
    $error_array = array();
    $error_arr = array();

    foreach ($_POST['emp_code'] as $emp) {
        $first = explode("-", $emp);
        $emp_cod = $first[1];
        $chek_exist = $con->existsByCondition("role_assign", "emp_code='$emp_cod'");
        if ($chek_exist == 1) {
//            $updarrate_array = array("emp_code" => $emp_cod, "em_role_id" => $role_id, "last_updated_at" => $datenow, "last_updated_by" => $createdby);
//            $update_query = $con->update("role_assign", $update_array);
//             $error_arr[] = $emp_cod;
            array_push($error_array, $emp_cod);
        } else {
            $insert_array = array("emp_code" => $emp_cod, "em_role_id" => $_SESSION['role_id'], "created_by" => $createdby);
            $insert_query = $con->insert("role_assign", $insert_array);
        }
    }
    if ($insert_query == 1) {
        $msg = "Selected Employees have been successfully assign to the Role.";
    } else {
        $msg = "Some of the Employees were assigned to a group already, Please delete them from below and assign them to new role.<br />";
    }
}
if (isset($_POST['del_existEmp'])) {

    foreach ($_POST['emp_code_exist'] as $empE) {

        $Empx = explode("-", $empE);
        $empl_cod = $Empx[1];
        $delete_array = array("emp_code" => $empl_cod);
        $delete_query = $con->delete("role_assign", $delete_array);
    }
    if ($delete_query == 1) {
        $msg = "Selected Employees have been successfully deleted from the Group.";
    } else {
        $err = "Error Deleting employees from the group";
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;height: auto;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Assign Employee to Group</h6></div>
    <div class="widget-body">
        <div class="clearfix"></div>
        <div class="col-md-12">
            <?php if (count($error_array) > 0): ?>
                <br />
                <div class="alert alert-success fade in">
                    <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                    <?php echo $msg; ?>
                    <hr />
                    <?php
                     echo "Some of the Employees were already assigned to other role and was not updated are: <br />";
                    foreach ($error_array as $key => $data) {
                        echo $data;
                        echo ", ";
                    }
                    echo '<br />';
                    ?>
                </div>
            <?php else: ?>
                <br />
                <?php include("../../layout/msg.php"); ?>
            <?php endif; ?>
        </div>
        <div class="clearfix"></div>
        <br />
        <form method="post" enctype="multipart/form-data" name="frm1">
            <!--<div class="col-md-12">-->   
            <div style="border: 1px solid #72AF46; min-height:200px;" class="col-md-2">
                <h5 style="text-align:center;"> Employee Roles</h5>
                <hr style="margin-top: -2px;" />
                <?php if (count($employee_role) >= 1): ?>
                    <?php foreach ($employee_role as $p): ?>
                        <label for="Roles"><a href="assign_Togroup.php?role_id=<?php echo $p->em_role_id; ?>">
                                <?php
                                if ($p->em_role_id == $role_id) {
                                    echo "<span style=\"color:gray;\">" . $p->role_type . "</span>";
                                } else {
                                    echo $p->role_type;
                                }
                                ?>
                            </a></label> <br />
                    <?php endforeach; ?>
                <?php endif; ?> 
            </div>
            <div class="col-md-10">
                <div style="height: auto; width:82%;" id="example" class="k-content">
                    <!---------------------------------------->
                    <!--   <div class="widget" style="background-color: white; width:98%;">           
                              <div class="widget-body">-->

                    <?php if (isset($_GET['role_id'])) { ?>
                        <div class="col-md-12">
                            <div class="col-md-4">
                                <label for="Full name">Company:</label><br/> 
                                <input id="companies1" name="company_title" style="width: 80%;" value="<?php echo $company_title; ?>" />
                            </div>
                            <div class="col-md-4">
                                <label for="Full name">Department:</label> <br />
                                <input id="department1" name="department_title" style="width: 80%;" value="<?php echo $department_title; ?>" />
                            </div>

                            <div class="col-md-4">
                                <label for="Full name">Sub Section:</label> <br />
                                <input id="subsections1" name="emp_subsection" style="width: 80%;" value="<?php echo $emp_subsection; ?>" />
                                <!-- auto complete start-->
                            </div>

                            <div class="clearfix"></div>
                            <br/>
                        </div>
                        <script type="text/javascript">
                            $(document).ready(function() {
                                var companies1 = $("#companies1").kendoComboBox({
                                    placeholder: "Select Company...",
                                    dataTextField: "company_title",
                                    dataValueField: "company_id",
                                    dataSource: {
                                        transport: {
                                            read: {
                                                url: "../../controller/company.php",
                                                type: "GET"
                                            }
                                        },
                                        schema: {
                                            data: "data"
                                        }
                                    }
                                }).data("kendoComboBox");

                                var department1 = $("#department1").kendoComboBox({
                                    autoBind: true,
                                    placeholder: "Select Department..",
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
                                    }
                                }).data("kendoComboBox");

                                var subsections1 = $("#subsections1").kendoComboBox({
                                    autoBind: false,
                                    placeholder: "Select Subsection..",
                                    dataTextField: "subsection_title",
                                    dataValueField: "subsection_title",
                                    dataSource: {
                                        transport: {
                                            read: {
                                                url: "../../controller/subsection.php",
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

                        <script type="text/javascript">
                            $(document).on('change', '#companies1', function() {
                                //                            alert("asdas");
                                var com_id = $("#companies1").val();
                                $.ajax({
                                    type: 'POST',
                                    url: '../../controller/getCompanyEmployee.php',
                                    data: {com_id: com_id},
                                    success: function(response) {

                                        var objects = eval(response.data);
                                        //                                     alert(objects);
                                        var checkBoxHtml = "";
                                        $("#valueCheck").html('');
                                        //                                    $("#givmail").html('');

                                        $(objects).each(function(index, obj) {
                                            checkBoxHtml += '<input class="case1" style="margin-bottom: 3%" type="checkbox" name="emp_code[]" id="emp_code"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';

                                            //console.log(obj.Email_address);
                                        });
                                        // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                        $("#valueCheck").html(checkBoxHtml);
                                        //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                        //                                    $("#givmail").html(datas);
                                    }
                                });
                            });
                        </script>

                        <script type="text/javascript">
                            $(document).on('change', '#department1', function() {
                                var dept_id = $("#department1").val();
                                $.ajax({
                                    type: 'POST',
                                    url: '../../controller/get_department_employee.php',
                                    data: {dept_id: dept_id},
                                    success: function(response) {

                                        var objects = eval(response.data);
                                        //                                     alert(objects);
                                        var checkBoxHtml = "";
                                        $("#valueCheck").html('');
                                        //                                    $("#givmail").html('');

                                        $(objects).each(function(index, obj) {
                                            checkBoxHtml += '<input class="case1" style="margin-bottom: 3%" type="checkbox" name="emp_code[]" id="emp_code"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';

                                            //console.log(obj.Email_address);
                                        });
                                        // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                        $("#valueCheck").html(checkBoxHtml);
                                        //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                        //                                    $("#givmail").html(datas);
                                    }
                                });
                            });
                        </script>

                        <script type="text/javascript">
                            $(document).on('change', '#subsections1', function() {
                                //                            alert("asdas");
                                var subsection_title = $("#subsections1").val();
                                //                            console.log(department_id);
                                $.ajax({
                                    type: 'POST',
                                    url: '../../controller/get_subsection_employee.php',
                                    data: {subsection_title: subsection_title},
                                    success: function(response) {

                                        var objects = eval(response.data);
                                        console.log(objects);
                                        var checkBoxHtml = "";
                                        $("#valueCheck").html('');
                                        //                                    $("#givmail").html('');
                                        $(objects).each(function(index, obj) {
                                            checkBoxHtml += '<input class="case1" type="checkbox" name="emp_code[]" id="emp_code"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';
                                            //console.log(obj.Email_address);
                                        });
                                        // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                        $("#valueCheck").html(checkBoxHtml);
                                        //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                        //                                    $("#givmail").html(datas);
                                    }
                                });
                            });
                        </script>
                        <div class="clearfix"></div>
                        <br/>
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <div id="valueCheck"> </div>
                            </div>

                            <div class="col-md-6">
                                <input type="checkbox" id="selectallemp"/> Select All
                            </div>

                            <script type="text/javascript">
                                $(function() {
                                    $("#selectallemp").click(function() {
                                        $('.case1').attr('checked', this.checked);
                                    });
                                    $(".case1").click(function() {
                                        if ($(".case").length == $(".case:checked").length) {
                                            $("#selectallemp").attr("checked", "checked");
                                        } else {
                                            $("#selectallemp").removeAttr("checked");
                                        }
                                    });
                                });
                            </script>
                        </div>
                        <div class="clearfix"></div>
                        <br/>
                        <div class="col-md-3">
                            <input class="k-button" style="width:70px;" type="submit" value="Add" name="add_empgroup">
                        </div>
                        <div class="clearfix"></div>
                        <?php
                    } else {
                        echo "<br/><br/><h4>Please Select a Employee Role.</h4>";
                    }
                    ?>
                    <!---------------------------------------->  
                    <div class="clearfix"></div>

                </div> 
            </div>
            <div class="clearfix"></div>
            <br />
            <!--</div>--> 
            <!------------------------------------------------------------------------------>           
            <div class="widget" style="background-color: white; width:98%;">
                <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Existing Employee Group Information</h6></div>
                <div class="widget-body">

                    <div style="min-height: 167px;">
                        <div class="weather">
                            <?php if (isset($_GET["role_id"])): ?>
                                <div class="col-md-6">
                                    <label for="role">Employee Roles</label> <br />
                                    <input id="roles" name="role_id" style="width: 80%;" value="<?php // echo $department_title;  ?>" />
                                </div>
                                <script type="text/javascript">
                                    $(document).ready(function() {
                                        var roles = $("#roles").kendoComboBox({
                                            placeholder: "Select role ",
                                            dataTextField: "role_type",
                                            dataValueField: "em_role_id",
                                            dataSource: {
                                                transport: {
                                                    read: {
                                                        url: "../../controller/emp_roles.php",
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
                                <script type="text/javascript">
                                    $(document).on('change', '#roles', function() {
                                        var emp_rol = $("#roles").val();
                                        $.ajax({
                                            type: 'POST',
                                            url: '../../controller/get_employee_byGroup.php',
                                            data: {
                                                emp_rol: emp_rol
                                            },
                                            success: function(response) {
                                                var objects = eval(response.data);
                                                var checkBoxHtml = "";
                                                $("#emp_role").html('');
                                                //                                    $("#givmail").html('');
                                                $(objects).each(function(index, obj) {
                                                    checkBoxHtml += '<input class="case2" style="margin-bottom: 3%" type="checkbox" name="emp_code_exist[]" id="emp_code_exist"  value="' + obj + '">&nbsp;&nbsp;' + obj + '</br>';
                                                    //console.log(obj.Email_address);
                                                });
                                                // datas = '<label class="col-md-6 control-label">With Email:</label><div class="col-md-1" style="azimuth:left"><label for="c_email"></label><input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" /></div>';
                                                $("#emp_role").html(checkBoxHtml);
                                                //                                    datas = '<input id="c_email" type="checkbox" name="c_email" value="1" style="width: 100%" />';
                                                //                                    $("#givmail").html(datas);
                                            }
                                        });
                                    });
                                </script>
                                <div class="clearfix"></div>
                                <br />
                                <!--                                <div class="col-md-12">
                                                                    <input type="submit" class="k-button" name="view_employee" value="View Employees">
                                                                </div>-->
                                <!--<div class="clearfix"></div>-->

                                <h5>Employees in this Role</h5>
                                <hr width="50%" align="left" />
                                <div class="col-md-4" id="emp_role">

                                </div>
                                <div class="col-md-6">
                                    <input type="checkbox" id="selectall"/> Select All
                                </div>
                                <div class="clearfix"></div>
                                <!--Select/Deselect Function-->
                                <script type="text/javascript">
                                    $(function() {
                                        // add multiple select / deselect functionality
                                        $("#selectall").click(function() {
                                            $('.case2').attr('checked', this.checked);
                                        });
                                        // Reverse and Viceversa
                                        $(".case2").click(function() {
                                            if ($(".case2").length == $(".case2:checked").length) {
                                                $("#selectall").attr("checked", "checked");
                                            } else {
                                                $("#selectall").removeAttr("checked");
                                            }
                                        });
                                    });
                                </script>
                                <br/>
                                <div class="col-md-6">
                                    <input class="k-button" type="submit" value="Delete" name="del_existEmp">
                                </div>
                                <div class="clearfix"></div>        
                            <?php endif; ?>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <!------------------------------------------------------------------------------>           
    </div>
</div> 
</form>
<?php include '../view_layout/footer_view.php'; ?>
<!--<script type="text/javascript">
  $(document).ready(function() {
      $("#to_view_company").kendoDropDownList();
      $("#shift_id").kendoDropDownList();
      $("#main_company_id").kendoDropDownList();

  });
</script>-->
