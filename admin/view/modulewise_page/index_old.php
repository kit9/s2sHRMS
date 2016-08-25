<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
//error_reporting(0);

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Logging out user
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

$recent_query = $con->SelectAll("employee_module");
$employee_role = $con->SelectAll("employee_role");
$all_company = $con->SelectAll("company");
$query_module = "SELECT mp.*,em.module FROM module_page mp, employee_module as em WHERE mp.rules_id=em.rules_id order by module_page_id DESC";
$result12 = mysqli_query($open, $query_module);

//Main query to build the grid
while ($rows12 = mysqli_fetch_object($result12)) {
    $module_page[] = $rows12;
}

$query_emp = "SELECT emp_firstname, emp_code FROM tmp_employee";
$result_emp = mysqli_query($open, $query_emp);
while ($rows_emp = mysqli_fetch_object($result_emp)) {
    $all_employee[] = $rows_emp;
}

$modulequery = array();
$modulequery = "SELECT * FROM module_page mp LEFT JOIN
                employee_module em on em.rules_id = mp.rules_id";
$module_result = mysqli_query($open, $modulequery);
while ($module_rows = mysqli_fetch_object($module_result)) {
    $module_objects[] = $module_rows;
}


if (count($modulequery) >= 1) {
    foreach ($modulequery as $n) {
        $rules_id = $n->rules_id;
    }
}

$rulesArray = array("rules_id" => $rules_id);
$queryrulesName = $con->SelectAllByField("employee_module", $rulesArray);
foreach ($queryrulesName as $n) {
    $module_name = $n->module;
}

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
    $_SESSION["emp_id"] = $emp_id;
    $_SESSION["com_id"] = $_GET["com_id"];
    if ($emp_id != '') {
        //Check employee in permission table
        $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
        $emp_code = $employees{0}->emp_code;
        $query_existing = "SELECT * FROM module_permission WHERE emp_code='$emp_code'";
        if (count($query_existing) > 0) {
            $result_existing = mysqli_query($open, $query_existing);
            while ($rows_existing = mysqli_fetch_object($result_existing)) {
                $arr_existing[] = $rows_existing;
            }
        } else {
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



if (isset($_POST["add_field"])) {
    extract($_POST);
    if ($emp_id > 0 AND $em_role_id > 0) {
        $err = "You can only select either a user or a company.";
    } else {
        if ($em_role_id != '') {
            $queryEmRole = $con->SelectAllByCondition("employee_role", "em_role_id= '$em_role_id'");
        }
        $employeeRole = $queryEmRole{0}->role_type;
        $module_request_arr = array();
        foreach ($_POST as $key => $val) {
            $temp_module_array = explode('_', $key);
            if (in_array("specialmodule", $temp_module_array)) {
                $id_temp = $temp_module_array[1];
                if (isset($_POST["specialmodule_$id_temp"])) {
                    $module_request_arr[$temp_module_array[1]]["$key"] = $val;
                }
            }
        }
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

                <!--Kendo Grid-->
                <script type="text/javascript">
                    $(document).ready(function () {
                        $("#grid").kendoGrid({
                            filterable: true,
                            sortable: true,
                            scrollable: true,
                            groupable: true
                        });
                    });

                </script>
                <!-- Grid -->
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

                <script type="text/javascript">
                    $(document).ready(function () {
                        $("#grid_two").kendoGrid({
                            pageable: {
                                refresh: true,
                                input: true,
                                numeric: false,
                                pageSize: 10,
                                pageSizes: true,
                                pageSizes: [10, 20, 30, 50]
                            },
                            filterable: true,
                            sortable: true,
                            scrollable: true,
                            groupable: true
                        });
                    });
                </script>
                
                <div id="example" class="k-content col-md-12" style="padding-left: 0px;">
                    <table id="grid_two">
                        <colgroup>
                            <col style="width:250px"/>
                            <col style="width:250px"/>
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th data-field="module">Module</th>
                                <th data-field="sub_module">Sub Module</th>
                                <th data-field="view">View</th>
                                <th data-field="create">Create</th>
                                <th data-field="delete">Delete</th>
                                <th data-field="update">Update</th>
                                <th data-field="approve">Approve</th>
                                <th data-field="cancel">Cancel</th>
                                <th data-field="export">Export</th>
                                <th data-field="Action">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (count($arr_existing) > 0): ?>
                                <?php foreach ($arr_existing as $data): ?>
                                    <tr>
                                        <td><?php echo $data->module; ?></td>
                                        <td><?php echo $data->module_page_title; ?></td>
                                        <td><?php echo $data->perview; ?></td>
                                        <td><?php echo $data->percreate; ?></td>
                                        <td><?php echo $data->perdelete; ?></td>
                                        <td><?php echo $data->perupdate; ?></td>
                                        <td><?php echo $data->perapprove; ?></td>
                                        <td><?php echo $data->percancel; ?></td>
                                        <td><?php echo $data->perexport; ?></td>
                                        <td>
                                            <a href="index.php?delete_permission_id=<?php echo $data->permission_id; ?>&rules_id=<?php $data->rules_id; ?>" class="k-button">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php elseif (count($arr_existing_two) > 0): ?>
                                <?php foreach ($arr_existing_two as $data2): ?>
                                    <tr>
                                        <td><?php echo $data->module; ?></td>
                                        <td><?php echo $data2->module_page_title; ?></td>
                                        <td><?php echo $data2->perview; ?></td>
                                        <td><?php echo $data2->percreate; ?></td>
                                        <td><?php echo $data2->perdelete; ?></td>
                                        <td><?php echo $data2->perupdate; ?></td>
                                        <td><?php echo $data2->perapprove; ?></td>
                                        <td><?php echo $data2->percancel; ?></td>
                                        <td><?php echo $data2->perexport; ?></td>
                                        <td>
                                            <a href="index.php?delete_permission_id=<?php echo $data2->permission_id; ?>&rules_id=<?php $data->rules_id; ?>" class="k-button">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>   
                                <?php if (count($arr_for_role) > 0): ?>
                                    <?php foreach ($arr_for_role as $data3): ?>
                                        <tr>
                                            <td><?php echo $data->module; ?></td>
                                            <td><?php echo $data3->module_page_title; ?></td>
                                            <td><?php echo $data3->perview; ?></td>
                                            <td><?php echo $data3->percreate; ?></td>
                                            <td><?php echo $data3->perdelete; ?></td>
                                            <td><?php echo $data3->perupdate; ?></td>
                                            <td><?php echo $data3->perapprove; ?></td>
                                            <td><?php echo $data3->percancel; ?></td>
                                            <td><?php echo $data3->perexport; ?></td>
                                            <td>
                                                <input type="hidden" name="del_permission_id" value="<?php echo $data2->permission_id; ?>">
                                                <input type="submit" value="Delete" name="btn_delete">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>
                <br />


                <input type="checkbox" id="select-all" /> Select All<br/>
                <br />




                <div id="example" class="k-content col-md-12" style="padding-left:0px;">
                    <table id="grid">
                        <colgroup>
                            <col style="width:250px"/>
                            <col style="width:250px"/>
                            <col style="width:100px"/>
                            <col style="width:100px"/>
                            <col style="width:100px"/>
                            <col style="width:100px"/>
                            <col style="width:100px"/>
                            <col style="width:100px"/>
                            <col style="width:100px"/>
                            <col style="width:150px"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th data-field="module"> Module</th>
                                <th data-field="sub_module">Sub Module</th>
                                <th data-field="view">View</th>
                                <th data-field="create">Create</th>
                                <th data-field="delete">Delete</th>
                                <th data-field="update"> Update</th>
                                <th data-field="approve">Approve</th>
                                <th data-field="cancel">Cancel</th>
                                <th data-field="export">Export</th>
                                <th data-field="selectall">Select All</th>

                            </tr>
                        </thead>
                        <tbody>          
                            <?php if (count($module_objects) >= 1): ?>
                                <?php foreach ($module_objects as $nw): ?>

                                <script type="text/javascript">
                                    $(document).ready(function () {
                                        // add multiple select / deselect functionality
                                        $("#checkAll_<?php echo $nw->module_page_id; ?>").click(function () {
                                            $('.classall_<?php echo $nw->module_page_id ?>').attr('checked', this.checked);

                                        });
                                        // if all checkbox are selected, check the selectall checkbox
                                        $(".classall_").click(function () {
                                            if ($(".classall_<?php echo $nw->module_page_id ?>").length == $(".classall_<?php echo $nw->module_page_id ?>:checked").length) {
                                                $("#checkAll_<?php echo $nw->module_page_id; ?>").attr("checked", "checked");
                                            } else {
                                                $("#checkAll_<?php echo $nw->module_page_id; ?>").removeAttr("checked");
                                            }

                                        });
                                        
                                       
                                    });
                                </script>

                                <tr>
                                    <td>
                                        <input type="hidden"  name="specialmodule_<?php echo $nw->module_page_id ?>_role" value="<?php echo $nw->rules_id; ?>" />
                                        <?php echo $nw->module; ?>
                                    </td>
                                    <td><input type="checkbox" class="classchk_<?php echo $nw->module_page_id ?>" id="checkme_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>" value="<?php echo $nw->module_page_id; ?>"> &nbsp;<?php echo $nw->module_headline; ?></td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkView_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_perview" value="yes"></td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkCre_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_percreate" value="yes"> </td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkDel_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_perdelete" value="yes"> </td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkDate_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_perupdate" value="yes"> </td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkApp_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_perapprove" value="yes"> </td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkCan_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_percancel" value="yes"> </td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkExp_<?php echo $nw->module_page_id ?>" name="specialmodule_<?php echo $nw->module_page_id ?>_perexport" value="yes"> </td>
                                    <td><input type="checkbox" class="classall_<?php echo $nw->module_page_id ?>" id="checkAll_<?php echo $nw->module_page_id; ?>" value=""> </td>
                                </tr>


                                <!--End of Java Script Snippet-->
                            <?php endforeach; ?>
                        <?php endif; ?> 
                        </tbody>
                    </table>
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
                <!--Kendo Grid-->
                <script type="text/javascript">
                    $(document).ready(function () {
                        $("#grid_twoth").kendoGrid({
                            pageable: {
                                refresh: true,
                                input: true,
                                numeric: false,
                                pageSize: 10,
                                pageSizes: true,
                                pageSizes: [10, 20, 30, 50],
                            },
                            filterable: true,
                            sortable: true,
                            scrollable: true,
                            groupable: true
                        });
                    });
                </script>
                <div id="example" class="k-content col-md-12">
                    <table id="grid_twoth">
                        <colgroup>
                            <col style="width:250px"/>
                            <col style="width:250px"/>
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                            <col style="width:100px" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th data-field="module">Module</th>
                                <th data-field="sub_module">Sub Module</th>
                                <th data-field="view">View</th>
                                <th data-field="create">Create</th>
                                <th data-field="delete">Delete</th>
                                <th data-field="update">Update</th>
                                <th data-field="approve">Approve</th>
                                <th data-field="cancel">Cancel</th>
                                <th data-field="export">Export</th>
                                <th data-field="Action">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (count($arr_existing) > 0): ?>
                                <?php foreach ($arr_existing as $data): ?>
                                    <tr>
                                        <td><?php echo $data->module; ?></td>
                                        <td><?php echo $data->module_page_title; ?></td>
                                        <td><?php echo $data->perview; ?></td>
                                        <td><?php echo $data->percreate; ?></td>
                                        <td><?php echo $data->perdelete; ?></td>
                                        <td><?php echo $data->perupdate; ?></td>
                                        <td><?php echo $data->perapprove; ?></td>
                                        <td><?php echo $data->percancel; ?></td>
                                        <td><?php echo $data->perexport; ?></td>
                                        <td>
                                            <a href="index.php?delete_permission_id=<?php echo $data->permission_id; ?>&rules_id=<?php $data->rules_id; ?>" class="k-button">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php elseif (count($arr_existing_two) > 0): ?>
                                <?php foreach ($arr_existing_two as $data2): ?>
                                    <tr>
                                        <td><?php echo $data->module; ?></td>
                                        <td><?php echo $data2->module_page_title; ?></td>
                                        <td><?php echo $data2->perview; ?></td>
                                        <td><?php echo $data2->percreate; ?></td>
                                        <td><?php echo $data2->perdelete; ?></td>
                                        <td><?php echo $data2->perupdate; ?></td>
                                        <td><?php echo $data2->perapprove; ?></td>
                                        <td><?php echo $data2->percancel; ?></td>
                                        <td><?php echo $data2->perexport; ?></td>
                                        <td>
                                            <a href="index.php?delete_permission_id=<?php echo $data2->permission_id; ?>&rules_id=<?php $data->rules_id; ?>" class="k-button">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>   
                                <?php if (count($arr_for_role) > 0): ?>
                                    <?php foreach ($arr_for_role as $data3): ?>
                                        <tr>
                                            <td><?php echo $data->module; ?></td>
                                            <td><?php echo $data3->module_page_title; ?></td>
                                            <td><?php echo $data3->perview; ?></td>
                                            <td><?php echo $data3->percreate; ?></td>
                                            <td><?php echo $data3->perdelete; ?></td>
                                            <td><?php echo $data3->perupdate; ?></td>
                                            <td><?php echo $data3->perapprove; ?></td>
                                            <td><?php echo $data3->percancel; ?></td>
                                            <td><?php echo $data3->perexport; ?></td>
                                            <td>
                                                <a href="index.php?delete_permission_id=<?php echo $data2->permission_id; ?>&rules_id=<?php $data->rules_id; ?>" class="k-button">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

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



