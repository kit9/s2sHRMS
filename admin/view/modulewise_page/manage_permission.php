<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

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
$url = '';

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$recent_query = $con->SelectAll("employee_module");
$employee_role = $con->SelectAll("employee_role");
$all_company = $con->SelectAll("company");
$query_module = "SELECT mp.*,em.module FROM module_page mp, employee_module as em WHERE mp.rules_id=em.rules_id order by module_page_id DESC";
$result12 = mysqli_query($open, $query_module);
while ($rows12 = mysqli_fetch_object($result12)) {
    $module_page[] = $rows12;
}
$query_emp = "SELECT emp_firstname, emp_code FROM tmp_employee";
$result_emp = mysqli_query($open, $query_emp);
while ($rows_emp = mysqli_fetch_object($result_emp)) {
    $all_employee[] = $rows_emp;
}
$modulequery = array();
if (isset($_GET['rules_id'])) {
    $rules_id = $_GET['rules_id'];
    $_SESSION["rules_id"] = $rules_id;
    $condition = "rules_id='" . $rules_id . "'order by rules_id DESC";
    $modulequery = $con->SelectAllByCondition("module_page", $condition);
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
}

//Delete a permission
if (isset($_GET['delete_permission_id'])) {
    $permission_id = $_GET['delete_permission_id'];
    $delete_array = array("permission_id" => $permission_id);
    $con->delete("module_permission", $delete_array);
}

if (isset($_POST["btnSearchPermission"])) {
    extract($_POST);
    if ($emp_id != '') {
        //Check employee in permission table
        $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
        $emp_code = $employees{0}->emp_code;
        $query_existing = "SELECT * FROM module_permission WHERE emp_code='$emp_code' AND rules_id='$rules_id'";
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
                        AND mp.rules_id = '$rules_id'
                GROUP BY
                        mp.module";
            $result_existing_two = mysqli_query($open, $query_existing_two);
            while ($rows_existing_two = mysqli_fetch_object($result_existing_two)) {
                $arr_existing_two[] = $rows_existing_two;
            }
        }
    } else if ($em_role_id != '') {
        $query_for_role = "SELECT * FROM module_permission WHERE em_role_id='$em_role_id' AND rules_id='$rules_id'";
        $result_for_role = mysqli_query($open, $query_existing_two);
        while ($rows_for_role = mysqli_fetch_object($result_for_role)) {
            $arr_for_role[] = $rows_for_role;
        }
    }
}
$emp_code = $_SESSION["emp_code"];
?>
<?php include '../view_layout/header_view.php'; ?>
<form method="post" enctype="multipart/form-data">
    <div class="widget" style="background-color: white;">
        <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Manage User Access Information</h6></div>
        <div class="widget-body">
            <?php include("../../layout/msg.php"); ?>
            <div class="col-md-12" style="padding-left:0px;">
                <label for="Full name">Permission Type</label><br />
                <div class="col-md-6" style="padding-left:0px;">
                    <Select style="width: 85.5%;" name="userType" id="colorselector">
                        <option value="0">Select A Type</option>
                        <option value="User">Per User</option>
                        <option value="Role">Role</option>
                    </Select>
                </div>
            </div>
            <br/>
            <div id="User" class="colorsDrop" style="display:none">
                <br /><br />
                <div class="col-md-6" style="padding-left:0px;">    
                    <label  for="companieEm"> Select a Company:</label><br>
                    <div class="col-md-12" style="padding-left:0px;">
                        <input id="companieEm" name="company_id" style="width: 85.6%;" value="<?php echo $company_id; ?>" />
                    </div>  
                </div>
                <div class="col-md-6" style="padding-left:0px;">
                    <label for="employeesEm">Select an Employee:</label><br />
                    <div class="col-md-12" style="padding-left:0px; ">  
                        <input id="employeesEm" name="emp_id" style="width: 85.6%;" value="<?php echo $emp_firstname; ?>" />
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
                            autoBind: false,
                            cascadeFrom: "companieEm",
                            placeholder: "Select Employee..",
                            dataTextField: "emp_firstname",
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
            <div id="Role" class="colorsDrop col-md-6" style="display:none; padding-left: 0px;">
                <br />
                <label for="Full name">Select a Role</label><br />
                <div class="col-md-12" style="padding-left:0px;">
                    <select id="employeeRoleName" style="width: 85.6%;" name="em_role_id">
                        <option value="0">Select Role</option>
                        <?php if (count($employee_role) >= 1): ?>
                            <?php foreach ($employee_role as $er): ?>
                                <option value="<?php echo $er->em_role_id; ?>" 
                                        ><?php echo $er->role_type; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                    </select>
                </div> 
            </div>



            <!-- Script for showing  user and role wise employee Start-->
            <script type="text/javascript">
                $(document).ready(function() {
                    $(function() {
                        $('#colorselector').change(function() {
                            $('.colorsDrop').hide();
                            $('#' + $(this).val()).show();
                        });
                    });
                });
            </script>

            <script type="text/javascript">
                $(document).ready(function() {
                    $(function() {
                        $('#companyName').change(function() {
                            $('.colorsDropIn').hide();
                            console.log($('#companyName option:selected').val());
                        });
                    });
                });
            </script>

            <!-- Script for showing  user and role wise employee End-->
            <div class="clearfix"></div>
            <br/>
            <div class="col-md-6" style="padding-left:0px;">
                <input type="submit" value="Search Permissions" name="btnSearchPermission" class="k-button" style="width:150px;">
            </div>
            <div class="clearfix"></div>
            <hr />
            <!-- List of Modules -->

            <?php if (isset($_GET["rules_id"])): ?>
                <div id="example" class="k-content col-md-3" style="border: 1px solid silver; padding-left: 0px; padding-right: 0px;">
                    <div style="text-align: center; padding-top: 8px; padding-bottom: 8px; border-bottom: 1px solid silver;">
                        Select a Module
                    </div>
                    <?php if (count($recent_query) >= 1): ?>
                        <?php foreach ($recent_query as $p): ?>
                            <div style=" border-bottom: 1px solid silver; padding-top:5px; padding-bottom: 5px;" class="col-md-12">
                                <a href="manage_permission.php?rules_id=<?php echo $p->rules_id; ?>" style="text-decoration:none;">
                                    <?php
                                    if ($p->rules_id == $rules_id) {
                                        echo "<span style=\"color:black;\">" . $p->module . "</span>";
                                    } else {
                                        echo $p->module;
                                    }
                                    ?>
                                </a>
                            </div>
                            <div class="clearfix"></div>
                        <?php endforeach; ?>
                    <?php endif; ?> 
                </div>
            <?php else: ?>
                <div id="example" class="k-content col-md-5" style="border: 1px solid gray;">
                    <div style="text-align: center; padding-top: 8px;">
                        Select a Module
                    </div><?php if (count($recent_query) >= 1): ?>
                        <?php foreach ($recent_query as $p): ?>
                            <div style=" border-bottom: 1px solid silver; padding-top:5px;">
                                <a href="manage_permission.php?rules_id=<?php echo $p->rules_id; ?>" style="text-decoration:none;">
                                    <?php
                                    if ($p->rules_id == $rules_id) {
                                        echo "<span style=\"color:black;\">" . $p->module . "</span>";
                                    } else {
                                        echo $p->module;
                                    }
                                    ?>
                                </a>
                            </div>
                            <div class="clearfix"></div>
                        <?php endforeach; ?>
                        <br />
                    <?php endif; ?> 
                </div>
            <?php endif; ?>

            <!--End of Java Script Snippet kendo-->
            <?php if (isset($_GET['rules_id'])) { ?>
                <div class="col-md-9">
                    <!-- Grid -->
                    <div class="clearfix"></div>
                    <br /><br />
                    <!--Kendo Grid-->
                    <script type="text/javascript">
                        $(document).ready(function() {
                            $("#grid_two").kendoGrid({
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSize: 5,
                                    pageSizes: true,
                                    pageSizes: [5, 10, 20, 50],
                                },
                                filterable: true,
                                sortable: true,
                                scrollable: true,
                                groupable: true
                            });
                        });
                    </script>
                    <div id="example" class="k-content col-md-12">
                        <table id="grid_two">
                            <colgroup>
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
                    <?php
                } else {
                    
                }
                ?>
            </div> 
            <div class="clearfix"></div>
            <br/>
            <?php if (isset($_GET["rules_id"])): ?>
                <div class="col-md-12" style="padding-right:15px;">
                    <input class="k-button pull-right" type="submit" value="Submit Permisions" name="add_field">
                </div>
                <div class="clearfix"></div>
            <?php endif; ?>
            <br/>
        </div>
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



