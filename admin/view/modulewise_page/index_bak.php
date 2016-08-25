<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();
//error_reporting(0);
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
//Declaring local variable
$module_page_id = '';
$module_page_title = '';
$status = '';
$rules_id = '';
$module = '';
$emp_id = '';
$emp_firstname = '';
$company_id = '';
$company_title = '';
$id_temp_key = '';
$view_temp_key = '';
$create_temp_key = '';
$delete_temp_key = '';
$update_temp_key = '';
$approve_temp_key = '';
$cancel_temp_key = '';
$module_page_id_temp_key = '';
$rules_id_temp_key = '';
$module_page_title_temp_key = '';
$module_headline_temp_key = '';
$temp_view = '';
$temp_create = '';
$temp_delete = '';
$temp_update = '';
$temp_approve = '';
$temp_cancel = '';
$temp_module_page_id = '';
$temp_rules_id = '';
$temp_module_page_title = '';
$url = '';
$module_objects = array();
$employee_role = $con->SelectAll("employee_role");

$query_mod = "SELECT mo.*,em.module FROM module_page mo, employee_module as em WHERE mo.rules_id = '$rules_id' ORDER BY module_page_id DESC";
$result11 = mysqli_query($open, $query_mod);
while ($rows11 = mysqli_fetch_object($result11)) {
    $all_module[] = $rows11;
}

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Declaring local variables
$resul = '';
$err = "";
$msg = '';

//Fetch assigned permission for selected employee
if (isset($_GET["set_id"])) {
    $emp_id = $_GET["set_id"];

    $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
    $emp_code = $employees{0}->emp_code;

    $modulequery = array();
//$modulequery = $con->SelectAll("module_page");
    $modulequery = "SELECT
    module_permission.permission_id,
    module,
    emp_code,
    module_page_title,
    rules_id,
    module_headline,
    module_page_id,
    perview,
    percreate,
    perupdate,
    percancel,
    perapprove,
    perexport,
    perdelete
FROM
    module_permission
WHERE
    emp_code = '$emp_code'
UNION
    SELECT
        0 AS permission_id,
        '$emp_code' AS emp_code,
        module_page_title,
        mp.rules_id,
        module_headline,
        module,
        mp.module_page_id,
        'no' AS perview,
        'no' AS percreate,
        'no' AS perdelete,
        'no' AS percancel,
        'no' AS perapprove,
        'no' AS perexport,
        'no' AS perdelete
    FROM
        module_page mp,
        employee_module AS em
    WHERE
        mp.rules_id = em.rules_id
    AND mp.module_page_id NOT IN (
        SELECT
            module_page_id
        FROM
            module_permission
        WHERE
            emp_code = '$emp_code'
    )
    ORDER BY
        module_page_id";
    $module_result = mysqli_query($open, $modulequery);
    while ($module_rows = mysqli_fetch_assoc($module_result)) { // mysqli_fetch_objects
        $module_objects[] = $module_rows;
    }

    $_SESSION["emp_id"] = $emp_id;
    $_SESSION["com_id"] = $_GET["com_id"];
    if ($emp_id != '') {
        //Check employee in permission table
        $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
        $emp_code = $employees{0}->emp_code;
        $query_existing = "SELECT * FROM module_permission WHERE emp_code='$emp_code'";
        // if the user have direct module permission then
        if (count($query_existing) > 0) {
            $result_existing = mysqli_query($open, $query_existing);
            while ($rows_existing = mysqli_fetch_object($result_existing)) {
                $arr_existing[] = $rows_existing;
            }

//            $con->debug($arr_existing);
//            exit();
        } else {
            // if the user do not have direct module permission
            //Check employee in role assign table
            $employees = $con->SelectAllByCondition("tmp_emloyee", "emp_id='$emp_id'");
            $emp_code = $employees{0}->emp_code;
            $query_existing_two = "SELECT
                        ra.em_role_id,
                        mp.*
                FROM
                        role_assign AS ra
                LEFT JOIN module_permission AS mp ON mp.em_role_id = ra.em_role_id
                WHERE
                        ra.em_role_id = '$emp_code'
                GROUP BY
                        mp.module";
            $result_existing_two = mysqli_query($open, $query_existing_two);
            while ($rows_existing_two = mysqli_fetch_object($result_existing_two)) {
                $arr_existing_two[] = $rows_existing_two;
            }
//             $con->debug($arr_existing_two);
//            exit();
        }
    }
}

//Fetch assigned permission for selected role. 
if (isset($_GET["em_role_id"])) {
    $em_role_id = $_GET["em_role_id"];
    $_SESSION["em_role_id"] = $em_role_id;
    if ($em_role_id != '') {
        $query_for_role = "SELECT * FROM module_permission WHERE em_role_id='$em_role_id' AND rules_id='$rules_id'";
        $result_for_role = mysqli_query($open, $query_existing_two);
        while ($rows_for_role = mysqli_fetch_object($result_for_role)) {
            $arr_for_role[] = $rows_for_role;
        }
    }
}
// If new permission is given or edited and saved...
if (isset($_POST["add_field"])) {
    extract($_POST);
//    $emp_code = $_POST['emp_code'];
//    unset($_POST['emp_code']);
    unset($_POST['add_field']);
//    $con->debug($_POST);//    exit();
    if ($emp_id > 0 && $em_role_id > 0) {
        $err = "You can only select either a user or a company.";
    } else {
        if ($em_role_id != '' && $em_role_id != 0) {
            $queryEmRole = $con->SelectAllByCondition("employee_role", "em_role_id= '$em_role_id'");
            $employeeRole = $queryEmRole{0}->role_type;
        }

        $module_request_arr = array();
        array_shift($_POST);
        array_shift($_POST);
        array_shift($_POST);
        array_shift($_POST);

        $i = 0;
        $main_array = array();
        $temp_array = array();
        foreach ($module_objects as $mo) {
            foreach ($_POST as $keyP => $valP) {
                $key_arr = explode("_", $keyP);
                if ($mo['permission_id'] == $key_arr[0]) {  //                   $temp_array["$keyP"] = $valP;
                    $keyys = explode("_", $keyP);
                    array_shift($keyys);
                    $keys = join("_", $keyys);
                    $temp_array["$keys"] = $valP;
                }
            }
            $main_array[] = $temp_array;
        }
        $an_array = array();
        $an_array = $_SESSION['permisions'];

        $con->debug($main_array);
        $con->debug($an_array);

        array_diff($main_array, $an_array);
        $con->debug($main_array);
        exit();
////////////////////////////////////////////////////////////////////////////////////////////////
        if (count($module_request_arr) >= 1) {
            $GPArray = array();
            foreach ($module_request_arr as $key => $val) {

                $id_temp_key = "specialmodule_" . $key;

                $view_temp_key = "specialmodule_" . $key . "_" . "perview";
                $create_temp_key = "specialmodule_" . $key . "_" . "percreate";
                $delete_temp_key = "specialmodule_" . $key . "_" . "perdelete";
                $update_temp_key = "specialmodule_" . $key . "_" . "perupdate";
                $approve_temp_key = "specialmodule_" . $key . "_" . "perapprove";
                $cancel_temp_key = "specialmodule_" . $key . "_" . "percancel";
                $export_temp_key = "specialmodule_" . $key . "_" . "perexport";
                $module_page_id_temp_key = "specialmodule_" . $key . "_" . "module_page_id";

                $rules_id_temp_key = "specialmodule_" . $key . "_role";

                $module_page_title_temp_key = "specialmodule_" . $key . "_" . "module_page_title";
                $module_headline_temp_key = "specialmodule_" . $key . "_" . "module_headline";


                $temp_view = $module_request_arr[$key]["$view_temp_key"];
                $temp_create = $module_request_arr[$key]["$create_temp_key"];
                $temp_delete = $module_request_arr[$key]["$delete_temp_key"];
                $temp_update = $module_request_arr[$key]["$update_temp_key"];
                $temp_approve = $module_request_arr[$key]["$approve_temp_key"];
                $temp_cancel = $module_request_arr[$key]["$cancel_temp_key"];
                $temp_export = $module_request_arr[$key]["$export_temp_key"];

                $temp_rules_id = $module_request_arr[$key]["$rules_id_temp_key"];
                $temp_module_page_title = $module_request_arr[$key]["$module_page_title_temp_key"];
                $temp_module_page_id = $module_request_arr[$key]["$id_temp_key"];
//$con->debug();
                //Fetch url for module page id
                $base_url = $con->SelectAllByCondition("module_page", "module_page_id='$temp_module_page_id'");
                $url = $base_url{0}->module_page_title;
                $page_title = $base_url{0}->module_headline;

                //unset specific session variable
                if ($_SESSION['em_role_id'] > 0) {
                    $em_role_id_from_session = $_SESSION['em_role_id'];
                    unset($_SESSION["emp_id"]);
                } else {
                    if ($_SESSION['emp_id'] > 0) {
                        $emp_id_from_session = $_SESSION["emp_id"];
                        unset($_SESSION["emp_role_id"]);
                    }
                }

                $employeeNameF = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id_from_session'");
                $employeeFirstName = $employeeNameF{0}->emp_firstname;
                $emp_code = $employeeNameF{0}->emp_code;
                //Check if some definition exists for the same selection
                if ($emp_code != '' || $emp_code != 0) {
                    $existing_rules = $con->SelectAllByCondition("module_permission", " emp_code='$emp_code' AND module_page_id='$temp_module_page_id' AND rules_id='$temp_rules_id'");
                } else {
                    $existing_rules = $con->SelectAllByCondition("module_permission", " em_role_id='$em_role_id_from_session' AND module_page_id='$temp_module_page_id' AND rules_id='$temp_rules_id'");
                }

                //fetch module title
                $module_name = $con->SelectAllByCondition("employee_module", " rules_id='$temp_rules_id'");
                $module = $module_name{0}->module;

                if (count($existing_rules) >= 1) {
                    $permission_id = $existing_rules{0}->permission_id;
                    $update_array = array(
                        "permission_id" => $permission_id,
                        "perview" => $temp_view,
                        "percreate" => $temp_create,
                        "perdelete" => $temp_delete,
                        "perupdate" => $temp_update,
                        "perapprove" => $temp_approve,
                        "percancel" => $temp_cancel,
                        "perexport" => $temp_export
                    );
                    if ($con->update("module_permission", $update_array) == 1) {
                        $msg = " $employeeFirstName $employeeRole - Permission is Updated.";
                    }
                } else {
                    $module_array = array(
                        "module_page_id" => $temp_module_page_id,
                        "company_id" => $company_id,
                        "emp_code" => $emp_code,
                        "module_page_title" => $page_title,
                        "rules_id" => $temp_rules_id,
                        "em_role_id" => $em_role_id_from_session,
                        "module_headline" => $temp_module_headline,
                        "perview" => $temp_view,
                        "percreate" => $temp_create,
                        "perdelete" => $temp_delete,
                        "perupdate" => $temp_update,
                        "perapprove" => $temp_approve,
                        "percancel" => $temp_cancel,
                        "perexport" => $temp_export,
                        "base_url" => $url,
                        "module" => $module
                    );
                    if ($con->insert("module_permission", $module_array) == 1) {
                        $msg = "A new authentication procedure is succesfully created.";
                    }
                }
            }
        } else {
            $err = "Please select a page!";
        }
    }
}
//Delete a permission :: To change on post method
if (isset($_GET['delete_permission_id'])) {
    $permission_id = $_GET['delete_permission_id'];
    $delete_array = array("permission_id" => $permission_id);
    $con->delete("module_permission", $delete_array);
}

$emp_code = $_SESSION["emp_code"];
if (isset($_POST["btn_delete"])) {
    extract($_POST);
    $delete_array = array("permission_id" => $del_permission_id);
    $con->delete("module_permission", $delete_array);
}
?>
<?php include '../view_layout/header_view.php'; ?>
<form method="post" enctype="multipart/form-data">
    <div class="widget" style="background-color: white;">
        <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Manage User Access Information</h6></div>
        <div class="widget-body">
            <?php include("../../layout/msg.php"); ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    $('#employeesEm').on('change', function () {
                        if ($("#employeesEm").val() > "0") {
                            $("#employeeRoleName").prop('disabled', true);
                        }
                    });
                });
            </script>
            <div id="User" class="colorsDrop">
                <div class="col-md-4" style="padding-left:0px;">    
                    <label  for="companieEm"> Select a Company:</label><br>
                    <div class="col-md-12" style="padding-left:0px;">
                        <?php
                        $com_id_now = $_SESSION["com_id"];
                        $companies_now = $con->SelectAllByCondition("company", "company_id='$com_id_now'");
                        $company_title_now = $companies_now{0}->company_title;
                        ?>
                        <input id="companieEm" name="company_id" style="width: 100%" value="<?php echo $company_title_now; ?>" />
                    </div>  
                </div>
                <!--Script to populate the grid with defined -->
                <script type="text/javascript">
                    $(document).ready(function () {
                        $('#employeesEm').change(function () {
                            window.location = "index.php?set_id=" + $(this).val() + "&com_id=" + $("#companieEm").val();
                        });
                    });
                    $(document).ready(function () {
                        $('#employeeRoleName').change(function () {
                            window.location = "index.php?em_role_id=" + $(this).val();
                        });
                    });

                </script>
                <div class="col-md-4" style="padding-left:0px;">
                    <label for="employeesEm">Select an Employee:</label><br />
                    <div class="col-md-12" style="padding-left:0px;">  
                        <?php
                        if ($_SESSION["emp_role_id"] > 0) {
                            unset($_SESSION["emp_id"]);
                        } else {
                            if ($_SESSION["emp_id"] > 0) {
                                $emp_id_now = $_SESSION["emp_id"];
                                unset($_SESSION["emp_role_id"]);
                            }
                        }
                        $employees_now = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id_now'");
                        $emp_code_now = $employees_now{0}->emp_code;
                        $emp_firstname_now = $employees_now{0}->emp_firstname;
                        ?>
                        <input id="employeesEm" name="emp_id" style="width: 100%;" value="<?php
                        echo $emp_code_now;
                        echo "-";
                        echo $emp_firstname_now;
                        ?>"/>
                    </div>
                </div>   
                <div id="Role" class="colorsDrop col-md-4" style="padding-left: 0px;">
                    <label for="Full name">Select a Role</label><br />
                    <div class="col-md-12" style="padding-left:0px;">
                        <select id="employeeRoleName" style="width: 100%;" name="em_role_id">
                            <option value="0">Select Role</option>
                            <?php if (count($employee_role) >= 1): ?>
                                <?php foreach ($employee_role as $er): ?>
                                    <option value="<?php echo $er->em_role_id; ?>" 
                                    <?php
                                    if ($er->em_role_id == $_SESSION["em_role_id"]) {
                                        echo "selected='selected'";
                                    }
                                    ?>><?php echo $er->role_type; ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                        </select>
                    </div> 
                </div>
                <div class="clearfix"></div>
                <br/>
                <script type="text/javascript">
                    $(document).ready(function () {
                        var companieEm = $("#companieEm").kendoComboBox({
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

                        var employeesEm = $("#employeesEm").kendoComboBox({
                            cascadeFrom: "companieEm",
                            placeholder: "Select Employee..",
                            autoBind: true,
                            dataTextField: "emp_name",
                            dataValueField: "emp_id",
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
                </script>
            </div>
            <div class="clearfix"></div>
            <br/>
            <!--End of Java Script Snippet kendo-->
            <div class="col-md-12" style="padding-left:0px;">
                <!--Select all -->
                <script language="JavaScript">
                    $(document).ready(function () {
                        // Listen for click on toggle checkbox
                        $('#select-all').click(function (event) {
                            if (this.checked) {
                                // Iterate each checkbox
                                $(':checkbox').each(function () {
                                    this.checked = true;
                                });
                            } else {
                                // Iterate each checkbox
                                $(':checkbox').each(function () {
                                    this.checked = false;
                                });
                            }
                        });
                    });
                </script>
                <div class="clearfix"></div>
                <br />
                <script type="text/javascript">
                    $(document).ready(function () {
                        oTable = $('#example1').dataTable({
                            "bPaginate": false
                                    //        "ajax": "data/arrays.txt"
                        });
                    });
                </script>
                <input type="checkbox" id="select-all" /> Select All<br/>
                <br />
                <div style="padding-left:0px;">
                    <table id="example1" class="display" width="100%" cellspacing="0" align="left">
                        <thead>
                            <tr>
                                <th> Module</th>
                                <th>Sub Module</th>
                                <th>View</th>
                                <th>Create</th>
                                <th>Update</th>
                                <th>Cancel</th>
                                <th>Approve</th>
                                <th>Export</th>
                                <th>Delete</th>
                                <th>Select All</th>
                            </tr>
                        </thead>
                        <tbody>          
                            <?php
                            if (count($module_objects) >= 1):
                                $_SESSION['permisions'] = '';
                                $_SESSION['permisions'] = $module_objects;
                                ?>
                                <?php foreach ($module_objects as $nw): ?>
                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        // add multiple select / deselect functionality
                                        $("#checkAll_<?php echo $nw['module_page_id']; ?>").click(function () {
                                            $('.classall_<?php echo $nw['module_page_id']; ?>').attr('checked', this.checked);
                                        });

                                        // if all checkbox are selected, check the selectall checkbox
                                        $(".classall_").click(function () {
                                            if ($(".classall_<?php echo $nw['module_page_id']; ?>").length == $(".classall_<?php echo $nw['module_page_id']; ?>:checked").length) {
                                                $("#checkAll_<?php echo $nw['module_page_id']; ?>").attr("checked", "checked");
                                            } else {
                                                $("#checkAll_<?php echo $nw['module_page_id']; ?>").removeAttr("checked");
                                            }
                                        });
                                    });
                                </script>
        <!--                    <script type="text/javascript">
                                    $(document).ready(function() {
        //                            oTable = $('#example1').dataTable({
        //                                //        "ajax": "data/arrays.txt"
        //                            });
                                        $('#add_field').submit(function() {
                                            var sData = oTable.$('input').serialize();
                                            $('#textbox1').val(sData);
                                            // alert( "The following data would have been submitted to the server: \n\n"+sData );
                                            console.log(sData);
                                            //        return false;
                                        });
                                    });
                                </script>-->
                                <tr>
                                    <td>
                                        <?php
                                        $p_id = $nw['permission_id'];
                                        $pag_titl = explode(" ", $nw['module_page_title']);
//                                      $mod_part = substr($pag_titl[0], 0, 3);
                                        $name = $p_id;
                                        ?>
                                        <input type="hidden"  name="<?php echo $p_id; ?>_permission_id" value="<?php echo $p_id; ?>" />
                                        <input type="hidden"  name="<?php echo $p_id; ?>_module" value="<?php echo $nw['module']; ?>" />
                                        <input type="hidden"  name="<?php echo $p_id; ?>_emp_code" value="<?php echo $nw['emp_code']; ?>" />
                                        <?php echo $nw['module']; ?>
                                    </td>
                                    <td>
                                        <input type="hidden"  name="<?php echo $p_id; ?>_module_page_title" value="<?php echo $nw['module_page_title']; ?>" />
                                        <input type="hidden"  name="<?php echo $p_id; ?>_rules_id" value="<?php echo $nw['rules_id']; ?>" />
                                        <input type="hidden"  name="<?php echo $p_id; ?>_module_headline" value="<?php echo $nw['module_headline']; ?>" />

                                        <?php if ($nw['permission_id'] == 0) { ?>
                                            <input type="checkbox" class="classchk_<?php echo $nw['module_page_id'] ?>" id="checkme_<?php echo $nw['module_page_id'] ?>" name="<?php echo $p_id; ?>_module_page_id" value="<?php echo $nw['module_page_id']; ?>"> &nbsp;<?php echo $nw['module_page_title']; ?>
                                        <?php } else { ?>
                                            <input type="checkbox" class="classchk_<?php echo $nw['module_page_id'] ?>" id="checkme_<?php echo $nw['module_page_id'] ?>" name="<?php echo $p_id; ?>_module_page_id" checked="checked" value="<?php echo $nw['module_page_id']; ?>"> &nbsp;<?php echo $nw['module_page_title']; ?>
                                        <?php } ?>
                                    </td>
                                    <td>

                                        <?php
                                        $view = $nw['perview'];
                                        if ($view == "yes") {
                                            echo '<input type="checkbox" id="checkme_' . $nw['module_page_id'] . '" name="' . $name . '_perview" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkme_' . $nw['module_page_id'] . '" name="' . $name . '_perview" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $create = $nw['percreate'];
                                        if ($create == "yes") {
                                            echo '<input type="checkbox" id="checkCre_' . $nw['module_page_id'] . '" name="' . $name . '_percreate" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkCre_' . $nw['module_page_id'] . '" name="' . $name . '_percreate" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $update = $nw['perupdate'];
                                        if ($update == "yes") {
                                            echo '<input type="checkbox" id="checkDate_' . $nw['module_page_id'] . '" name="' . $name . '_perupdate" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkDate_' . $nw['module_page_id'] . '" name="' . $name . '_perupdate" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cancel = $nw['percancel'];
                                        if ($cancel == "yes") {
                                            echo '<input type="checkbox" id="checkCan_' . $nw['module_page_id'] . '" name="' . $name . '_percancel" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkCan_' . $nw['module_page_id'] . '" name="' . $name . '_percancel" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $approve = $nw['perapprove'];
                                        if ($approve == "yes") {
                                            echo '<input type="checkbox" id="checkApp_' . $nw['module_page_id'] . '" name="' . $name . '_perapprove" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkApp_' . $nw['module_page_id'] . '" name="' . $name . '_perapprove" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?> 
                                    </td>
                                    <td>
                                        <?php
                                        $export = $nw['perexport'];
                                        if ($export == "yes") {
                                            echo '<input type="checkbox" id="checkExp_' . $nw['module_page_id'] . '" name="' . $name . '_perexport" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkExp_' . $nw['module_page_id'] . '" name="' . $name . '_perexport" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $delete = $nw['perdelete'];
                                        if ($delete == "yes") {
                                            echo '<input type="checkbox" id="checkDel_' . $nw['module_page_id'] . '" name="' . $name . '_perdelete" class="classall_' . $nw['module_page_id'] . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkDel_' . $nw['module_page_id'] . '" name="' . $name . '_perdelete" class="classall_' . $nw['module_page_id'] . '" value="no">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="classall_<?php echo $nw['module_page_id'] ?>" id="checkAll_<?php echo $nw['module_page_id']; ?>" value="">
                                    </td>
                                </tr>
                                <!--End of Java Script Snippet-->
                            <?php endforeach; ?>
                        <?php endif; ?> 
                        </tbody>
                    </table>
                    <!--<input type="hidden" name="textbox1" id="textbox1">-->
                </div>
                <!-- Grid -->
                <div class="clearfix"></div>
            </div>
        </div> 
        <div class="clearfix"></div>
        <br/>
        <div class="col-md-6" style="padding-right:40px; padding-left: 25px;">
            <!--<input class="k-button" type="submit" value="View Permission" name="btnSearchPermission">-->
        </div>
        <div class="col-md-6" style="padding-right:40px;">
            <input class="k-button pull-right" type="submit" value="Submit Permisions" name="add_field">
        </div>
        <div class="clearfix"></div>
        <br />
        <?php if (isset($_POST["btnSearchPermission"])): ?>
            <div class="col-md-12">
                <!-- Grid -->
                <div class="clearfix"></div>
                <br /><br />
                <!------------------------ another grid -------------------------->
                <br/>
            </div>
        <?php endif; ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#colorselector").kendoDropDownList();
                $("#companyName").kendoDropDownList();
                $("#employeeName").kendoDropDownList();
                $("#employeeRoleName").kendoDropDownList();
                $("#new").kendoDropDownList();
            });
            $(document).ready(function () {
                $("#files").kendoUpload();
            });
        </script>
        <div class="clearfix"></div>
        <br />
    </div>
</div>
</form>
<?php include '../view_layout/footer_view.php'; ?>
