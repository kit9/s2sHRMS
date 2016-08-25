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

$_SESSION["emp_id"] = '';
$_SESSION["com_id"] = '';
$_SESSION["em_role_id"] = '';
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
$em_role_id = '';
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
    mo.permission_id,
    mo.module,
    mo.emp_code,
    mo.module_page_title,
    mo.rules_id,
     mpp.module_headline,
    mo.module_page_id,
    mo.perview,
    mo.percreate,
    mo.perupdate,
    mo.percancel,
    mo.perapprove,
    mo.perexport,
    mo.perdelete
FROM
    module_permission mo
    left join module_page mpp on mo.module_page_id = mpp.module_page_id
WHERE
    mo.emp_code = '$emp_code'
UNION
    SELECT
        0 AS permission_id,
        em.module,
        '$emp_code' AS emp_code,
        mp.module_page_title,
        mp.rules_id,
        mp.module_headline,
        mp.module_page_id,
        '' AS perview,
        '' AS percreate,
        '' AS perdelete,
        '' AS percancel,
        '' AS perapprove,
        '' AS perexport,
        '' AS perdelete
    FROM
        module_page mp,
        employee_module AS em
    WHERE
        mp.rules_id = em.rules_id
    AND mp.module_page_id NOT IN (SELECT
            module_page_id
        FROM
            module_permission
        WHERE
            emp_code = '$emp_code')
    ORDER BY
        module_page_id";
    $module_result = mysqli_query($open, $modulequery);
    while ($module_rows = mysqli_fetch_assoc($module_result)) { // mysqli_fetch_objects
        $module_objects[] = $module_rows;
    }

    $_SESSION["emp_id"] = $emp_id;
    $_SESSION["com_id"] = $_GET["com_id"];

}

//Fetch assigned permission for selected role. 
if (isset($_GET["em_role_id"])) {
    $em_role_id = $_GET["em_role_id"];
    $_SESSION["em_role_id"] = $em_role_id;
    $emp_code = $_SESSION["emp_code"];
    if ($em_role_id != '') {
        
        $modulequery = array();
//$modulequery = $con->SelectAll("module_page");

    $modulequery = " SELECT
    mo.permission_id,
    mo.module,
    mo.emp_code,
    mo.module_page_title,
    mo.rules_id,
     mpp.module_headline,
    mo.module_page_id,
    mo.perview,
    mo.percreate,
    mo.perupdate,
    mo.percancel,
    mo.perapprove,
    mo.perexport,
    mo.perdelete
FROM module_permission mo left join module_page mpp on mo.module_page_id = mpp.module_page_id
WHERE  mo.em_role_id = '$em_role_id'
    UNION SELECT
        0 AS permission_id,
        em.module,
        '$emp_code' AS emp_code,
        mp.module_page_title,
        mp.rules_id,
        mp.module_headline,
        mp.module_page_id,
        '' AS perview,
        '' AS percreate,
        '' AS perdelete,
        '' AS percancel,
        '' AS perapprove,
        '' AS perexport,
        '' AS perdelete
    FROM
        module_page mp,
        employee_module AS em
    WHERE
        mp.rules_id = em.rules_id
    AND mp.module_page_id NOT IN (SELECT
            module_page_id
        FROM
            module_permission
        WHERE
            em_role_id = '$em_role_id')
    ORDER BY
        module_page_id";
    $module_result = mysqli_query($open, $modulequery);
    while ($module_rows = mysqli_fetch_assoc($module_result)) { // mysqli_fetch_objects
        $module_objects[] = $module_rows;
    }
    }
}
// If new permission is given or edited and saved...
if (isset($_POST["add_field"])) {
    extract($_POST);
//  $con->debug($_POST);exit();
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
        array_shift($_POST);
        
        $keys = '';
        $i = 0;
        $main_array = array();
        $temp_array = array();
        $totalr = $_POST['total'];
        unset($_POST['total']);
//    $con->debug($_POST['total']);exit();
//         $submodule = $con->SelectAll("module_page");

        for($a=0;$a<$totalr;$a++) {
//            $con->debug($mo);exit();
            foreach ($_POST as $keyP => $valP) {
                $key_arr = explode("_", $keyP);
                if ($a == $key_arr[0]) {  //                   $temp_array["$keyP"] = $valP;
                    $keyys = explode("_", $keyP);
                    array_shift($keyys);
                    $keys = join("_", $keyys);
//                  $con->debug($valP);
                 if (!isset($_POST[$a.'_perview'])) {
                    $temp_array['perview'] = NULL;
                }
                if (!isset($_POST[$a.'_perupdate'])) {
                    $temp_array['perupdate'] = NULL;
                }
                if (!isset($_POST[$a.'_percreate'])) {
                    $temp_array['percreate'] = NULL;
                }
                if (!isset($_POST[$a.'_perapprove'])) {
                    $temp_array['perapprove'] = NULL;
                }
                if (!isset($_POST[$a.'_perdelete'])) {
                    $temp_array['perdelete'] = NULL;
                }
                if (!isset($_POST[$a.'_percancel'])) {
                    $temp_array['percancel'] = NULL;
                }
                if (!isset($_POST[$a.'_perexport'])) {
                    $temp_array['perexport'] = NULL;
                }
                    $temp_array["$keys"] = $valP;
                }
//             $main_array[] = $temp_array;
            }
            $main_array[] = $temp_array;
        }
//    $con->debug($main_array);exit();
//        $new_array = array();
////        $all_permission_ids = array(); 
//        foreach ($main_array as $key => $val) {
//
////            if (isset($val['module_page_id'])) {
//
//                if (!isset($val['perview'])) {
//                    $val['perview'] = NULL;
//                }
//                if (!isset($val['perupdate'])) {
//                    $val['perupdate'] = NULL;
//                }
//                if (!isset($val['percreate'])) {
//                    $val['percreate'] = NULL;
//                }
//                if (!isset($val['perapprove'])) {
//                    $val['perapprove'] = NULL;
//                }
//                if (!isset($val['perdelete'])) {
//                    $val['perdelete'] = NULL;
//                }
//                if (!isset($val['percancel'])) {
//                    $val['percancel'] = NULL;
//                }
//                if (!isset($val['perexport'])) {
//                    $val['perexport'] = NULL;
//                }
//                $new_array[] = $val;
////            }
//        }
//            $con->debug($new_array);exit();
        $inserted = '';
        $chk_permis = $con->existsByCondition("module_permission", "emp_code='$emp_code'");
        $chk_role_permis = $con->existsByCondition("module_permission", "em_role_id='$em_role_id'");
        if($chk_permis == 1){
          $delete_prev_per = array("emp_code" => $emp_code);
        $del_result = $con->delete("module_permission", $delete_prev_per);
        }else if($chk_role_permis == 1){
          $delete_rol_per = array("em_role_id" => $em_role_id);
        $del_rol_result = $con->delete("module_permission", $delete_rol_per);  
        }
//$con->debug($new_array);exit();
//        if ($del_result == 1){
        if (isset($_GET["em_role_id"])) { // if role id is present then make emp_code null
            $emp_code = NULL;
        }
            foreach ($main_array as $up) {
//            $con->debug($new_array);
                $us_permi_array = array("emp_code" => $emp_code,
                    "module_page_title" => $up['module_headline'],
                    "rules_id" => $up['rules_id'],
                    "module_headline" => $up['module_headline'],
                    "perview" => $up['perview'],
                    "percreate" => $up['percreate'],
                    "perdelete" => $up['perdelete'],
                    "perupdate" => $up['perupdate'],
                    "perapprove" => $up['perapprove'],
                    "percancel" => $up['percancel'],
                    "perexport" => $up['perexport'],
                    "module_page_id" => $up['module_page_id'],
                    "company_id" => $_SESSION["com_id"],
                    "em_role_id" => $em_role_id,
                    "base_url" => $up['module_page_title'],
                    "module" => $up['module']
                );
  //              $con->debug($us_permi_array);
                $insert_query = $con->insert("module_permission", $us_permi_array);
                if ($insert_query == 1) {
                    $inserted = 1;
                } else {
                    $inserted = 0;
                }
            }
          
            if ($inserted == 1) {
                $msg = "A new authentication procedure is succesfully created.";
              //  echo "<meta http-equiv='refresh' content='3;url=index.php'>";
            } else {
                $err = "Error Updating some permission of Employee code: " . $emp_code;
            }
//        } else {
//            $err = "Error deleting old permission of Employee code: " . $emp_code;
//        }
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
                $(document).ready(function() {
                    $('#employeesEm').on('change', function() {
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
                    $(document).ready(function() {
                        $('#employeesEm').change(function() {
                            window.location = "index.php?set_id=" + $(this).val() + "&com_id=" + $("#companieEm").val();
                        });
                    });
                    $(document).ready(function() {
                        $('#employeeRoleName').change(function() { 
                            window.location = "index.php?em_role_id=" + $(this).val()+ "&com_id=" + $("#companieEm").val();
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
                                    } ?>><?php echo $er->role_type; ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                        </select>
                    </div> 
                </div>
                <div class="clearfix"></div>
                <br/>
                <script type="text/javascript">
                    $(document).ready(function() {
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
                    $(document).ready(function() {
                        // Listen for click on toggle checkbox
                        $('#select-all').click(function(event) {
                            if (this.checked) {
                                // Iterate each checkbox
                                $(':checkbox').each(function() {
                                    this.checked = true;
                                });
                            } else {
                                // Iterate each checkbox
                                $(':checkbox').each(function() {
                                    this.checked = false;
                                });
                            }
                        });
                    });
                </script>
                <div class="clearfix"></div>
                <br />
                <script type="text/javascript">
                    $(document).ready(function() {
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
                                <th>Select All</th>
                                <th>View</th>
                                <th>Create</th>
                                <th>Update</th>
                                <th>Cancel</th>
                                <th>Approve</th>
                                <th>Export</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>          
                            <?php $row_counter = 0;
                            if (count($module_objects) >= 1):
//                                $con->debug($module_objects); exit();
                                ?>
                                <?php foreach ($module_objects as $nw): ?>
                                <script type="text/javascript">
                                    $(document).ready(function() {
                                        // add multiple select / deselect functionality
                                        $("#checkAll_<?php echo $row_counter; ?>").click(function() {
                                            $('.classall_<?php echo $row_counter; ?>').attr('checked', this.checked);
                                        });

                                        // if all checkbox are selected, check the selectall checkbox
                                        $(".classall_").click(function() {
                                            if ($(".classall_<?php echo $row_counter; ?>").length == $(".classall_<?php echo $row_counter; ?>:checked").length) {
                                                $("#checkAll_<?php echo $row_counter; ?>").attr("checked", "checked");
                                            } else {
                                                $("#checkAll_<?php echo $row_counter; ?>").removeAttr("checked");
                                            }
                                        });

                                        // if all checkbox are selected, check the selectall checkbox
                                        $(".classchk_<?php echo $row_counter; ?>").click(function() {
                                            if ($(".classchk_<?php echo $row_counter; ?>").length == $(".classchk_<?php echo $row_counter; ?>:checked").length) {
                                                $(".classAll_<?php echo $row_counter; ?>").removeAttr("disabled", false);
                                            } else {
                                                $(".classAll_<?php echo $row_counter; ?>").attr("disabled", true);
                                                $(".classAll_<?php echo $row_counter; ?>").removeAttr("checked");
                                                
                                            }
                                        });

                                        if ($(".classchk_<?php echo $row_counter; ?>").length == $(".classchk_<?php echo $row_counter; ?>:checked").length) {
                                            $(".classAll_<?php echo $row_counter; ?>").removeAttr("disabled", false);
                                        } else {
                                            $(".classAll_<?php echo $row_counter; ?>").attr("disabled", true);
                                            $(".classAll_<?php echo $row_counter; ?>").removeAttr("checked");
                                        }

                                    });
                                </script>

                                <tr>
                                    <td>
                                        <?php
//                                        $p_id = $nw['module_page_id'];
//                                        $pag_titl = explode(" ", $nw['module_page_title']);
//                                        $mod_part = substr($pag_titl[0], 0, 3);
//                                        $name = $p_id;
                                        ?>
                                        <input type="hidden"  name="<?php echo $row_counter; ?>_permission_id" value="<?php echo $nw['permission_id']; ?>" />
                                        <input type="hidden"  name="<?php echo $row_counter; ?>_module" value="<?php echo $nw['module']; ?>" />
                                        <input type="hidden"  name="<?php echo $row_counter; ?>_emp_code" value="<?php echo $nw['emp_code']; ?>" />
                                        <?php echo $nw['module']; ?>
                                    </td>
                                    <td>

                                        <input type="hidden"  name="<?php echo $row_counter; ?>_module_page_title" value="<?php echo $nw['module_page_title']; ?>" />
                                        <input type="hidden"  name="<?php echo $row_counter; ?>_rules_id" value="<?php echo $nw['rules_id']; ?>" />
                                        <input type="hidden"  name="<?php echo $row_counter; ?>_module_headline" value="<?php echo $nw['module_headline']; ?>" />
                                        <input type="hidden"  name="<?php echo $row_counter; ?>_module_page_id" value="<?php echo $nw['module_page_id']; ?>" />
                                        
                                       <?php if ($nw['perview'] == '' && $nw['percreate'] == '' && $nw['perupdate'] == '' && $nw['percancel'] == '' && $nw['perapprove'] == '' && $nw['perexport'] == '' && $nw['perdelete'] == '') { //$nw['permission_id'] == 0?>
                                            <input type="checkbox" class="classchk_<?php echo $row_counter; ?>" id="checkme_<?php echo $row_counter; ?>" name="<?php echo $row_counter; ?>_module_page_id" value="<?php echo $nw['module_page_id']; ?>"> &nbsp;<?php echo $nw['module_headline']; ?><?php //echo $nw['module_page_title']; ?>
                                        <?php } else { ?>
                                            <input type="checkbox" class="classchk_<?php echo $row_counter; ?>" id="checkme_<?php echo $row_counter; ?>" name="<?php echo $row_counter; ?>_module_page_id" checked="checked" value="<?php echo $nw['module_page_id']; ?>"> &nbsp;<?php echo $nw['module_headline']; // echo $nw['module_headline']; ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="classall_<?php echo $row_counter; ?>" id="checkAll_<?php echo $row_counter; ?>" value="">
                                    </td>
                                    <td>
                                        <?php
                                        $view = $nw['perview'];
                                        if ($view == "yes") { //$view == '' || 
                                            echo '<input type="checkbox" id="checkvew_' . $row_counter . '" name="' . $row_counter . '_perview" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkvew_' . $row_counter . '" name="' . $row_counter . '_perview" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $create = $nw['percreate'];
                                        if ($create == "yes") {  //$create == '' || 
                                            echo '<input type="checkbox" id="checkCre_' . $row_counter . '" name="' . $row_counter . '_percreate" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkCre_' . $row_counter . '" name="' . $row_counter . '_percreate" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $update = $nw['perupdate'];
                                        if ($update == "yes") { //$update == '' || 
                                            echo '<input type="checkbox" id="checkUpdat_' . $row_counter . '" name="' . $row_counter . '_perupdate" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkUpdat_' . $row_counter . '" name="' . $row_counter . '_perupdate" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $cancel = $nw['percancel'];
                                        if ($cancel == "yes") {   //$cancel == '' || 
                                            echo '<input type="checkbox" id="checkCan_' . $row_counter . '" name="' . $row_counter . '_percancel" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkCan_' . $row_counter . '" name="' . $row_counter . '_percancel" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $approve = $nw['perapprove'];
                                        if ($approve == "yes") {   //$approve == '' || 
                                            echo '<input type="checkbox" id="checkApp_' . $row_counter . '" name="' . $row_counter . '_perapprove" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkApp_' . $row_counter . '" name="' . $row_counter . '_perapprove" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?> 
                                    </td>
                                    <td>
                                        <?php
                                        $export = $nw['perexport'];
                                        if ($export == "yes") {   //$export == '' || 
                                            echo '<input type="checkbox" id="checkExp_' . $row_counter . '" name="' . $row_counter . '_perexport" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkExp_' . $row_counter . '" name="' . $row_counter . '_perexport" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $delete = $nw['perdelete'];
                                        if ($delete == "yes"){     //$delete == '' || 
                                            echo '<input type="checkbox" id="checkDel_' . $row_counter . '" name="' . $row_counter . '_perdelete" class="classall_' . $row_counter . '" checked="checked" value="yes">';
                                        } else {
                                            echo '<input type="checkbox" id="checkDel_' . $row_counter . '" name="' . $row_counter . '_perdelete" class="classall_' . $row_counter . '" value="yes">';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php  $row_counter++; ?>
                                <!--End of Java Script Snippet-->
                            <?php endforeach; ?>
                        <?php endif; ?> 
                        </tbody>
                    </table>
                   <input type="hidden" name="total" value="<?php echo $row_counter; ?>" /><?php echo $row_counter; ?> 
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
            $(document).ready(function() {
                $("#colorselector").kendoDropDownList();
                $("#companyName").kendoDropDownList();
                $("#employeeName").kendoDropDownList();
                $("#employeeRoleName").kendoDropDownList();
                $("#new").kendoDropDownList();
            });
            $(document).ready(function() {
                $("#files").kendoUpload();
            });
        </script>
        <div class="clearfix"></div>
        <br />
    </div>
</div>
</form>
<?php include '../view_layout/footer_view.php'; ?>