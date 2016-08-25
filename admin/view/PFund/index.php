<?php
/*  Author: ASMA 
 *  Date : 15 March 2015
 */
session_start();
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
// initialize variable
$PSH_id = '';
$PF_start = '';
$PF_after1y = '';
$PF_after2y = '';
$PF_after3y = '';

$component = $con->SelectAll("payroll_salary_header");

if (isset($_POST['btnSave'])) {
    extract($_POST);
//    $con->debug($_POST);
//    $get_PFkey = $con->get_primary_key_val("provident_fund");
    /** check with company id,salary component id and PF data 
     *  if exist then update else insert
     * **/
    $company = mysqli_real_escape_string($open,$_POST["company_id"]);
    $prev_val = $con->QueryResult("SELECT * FROM provident_fund WHERE company_id='$company'");
//    $con->debug($prev_val);

    if (count($prev_val >= 1)) {
        $PF_id = $prev_val[0]->PF_id;
        $array_obj = array("PF_id" => $PF_id, "PF_after_1y" => $PF_after1y, "PF_after_2y" => $PF_after2y, "PF_after_3y" => $PF_after3y, "salary_component_id" => $Salary_component, "pf_main" => $pf_main);
        $result = $con->update("provident_fund", $array_obj);
    } else {
        $array_obj = array("company_id"=>$company_id, "PF_after_1y" => $PF_after1y, "PF_after_2y" => $PF_after2y, "PF_after_3y" => $PF_after3y, "salary_component_id" => $Salary_component, "pf_main" => $pf_main);
        $result = $con->insert("provident_fund", $array_obj);
    }

    if ($result == 1) {
        $msg = "Provident Fund Information is Saved Successfully!";
    } else {
        $err = "Error! Saving Provident Information.";
    }
}

//$get_data = $con->SelectAll("provident_fund");
////$con->debug($get_data);exit();
//foreach ($get_data as $pf) {
//    $PSH_id = $pf->PF_id;
//    $company_id = $pf->company_id;
//    $PF_start = $pf->PF_start;
//    $PF_after1y = $pf->PF_after_1y;
//    $PF_after2y = $pf->PF_after_2y;
//    $PF_after3y = $pf->PF_after_3y;
//    $pf_main = $pf->pf_main;
//}
if(isset($_GET["PF_id"])){
    $Pf_id = $_GET["PF_id"];
    $get_data1 = $con->SelectAllByCondition("provident_fund","PF_id='$Pf_id'");

foreach ($get_data1 as $pf1) {
    $PSH_id = $pf1->salary_component_id;
    $company_id = $pf1->company_id;
    $PF_start = $pf1->PF_start;
    $PF_after1y = $pf1->PF_after_1y;
    $PF_after2y = $pf1->PF_after_2y;
    $PF_after3y = $pf1->PF_after_3y;
    $pf_main = $pf1->pf_main;
}
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!--    <style type="text/css">
        .k-grid-add {
            display:none;
        }
    </style>-->
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head">
        <h6 class="heading" style="color:whitesmoke;">Provident Fund Setup</h6>
    </div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <!-- Group -->
            <!--            <div class="col-md-6">
                            <label class="control-label" for="empcode" >Available after (Months):</label><br />
                            <input style="width: 80%" id="PF_start" name="PF_start" value="<?php // echo $PF_start;       ?>" />
                        </div>-->
            <div class="col-md-6">
                <label for="Full name">Company:</label> <br />
                <input id="company_id" name="company_id" style="width: 80%;" value="<?php echo $company_id; ?>" />
                <!-- auto complete start-->
            </div>
            <script type="text/javascript">
                $(document).ready(function () {
                    var company_id = jQuery("#company_id").kendoComboBox({
                        placeholder: "Select company...",
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
                });
            </script>
            <div class="col-md-6">
                <label class="control-label" for="empcode" >PF Percentage:</label><br />
                <input type="text"  class="k-textbox" style="width: 80%" id="pf_main" name="pf_main" value="<?php echo $pf_main; ?>" />
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-6">
                <label class="control-label" for="firstname">Profit Percentage(%) after 1 Year </label><br />
                <input type="text" class="k-textbox" id="PF_after1y" style="width: 80%" name="PF_after1y" value="<?php echo $PF_after1y; ?>" />
            </div>

            <div class="col-md-6">
                <label class="control-label" for="firstname">Profit Percentage(%) after 2 Year </label><br />
                <input type="text" class="k-textbox" id="PF_after2y" style="width: 80%" name="PF_after2y" value="<?php echo $PF_after2y; ?>" />
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <label class="control-label" for="firstname">Profit Percentage(%) after 3 Year </label><br />
                <input type="text" class="k-textbox" id="PF_after3y" style="width: 80%" name="PF_after3y" value="<?php echo $PF_after3y; ?>" />
            </div>
            <div class="col-md-6">
                <label class="control-label" for="firstname">Provident Fund Apply on Field </label>
                <br />
                <div id="alternative_company">
                    <select id="Salary_component" style="width: 80%" name="Salary_component">
                        <option value="0">Select Salary Components</option>
                        <?php if (count($component) >= 1): ?>
                            <?php foreach ($component as $com): ?>
                                <option value="<?php echo $com->PSH_id; ?>" 
                                <?php
                                if ($com->PSH_id == $PSH_id) {
                                    echo "selected='selected'";
                                }
                                ?>><?php echo $com->PSH_header_title; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                    </select>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $("#Salary_component").kendoDropDownList();
                        });
                    </script>
                </div>
            </div>
            <!-- Group End -->
            <div class="clearfix"></div>
            <div class="col-md-4">
                <br/><br/>
                <input type="submit" class="k-button" name="btnSave" value="Save PF Settings">
            </div>
            <div class="clearfix"></div>
            <br />
        </form>
            <div class="k-toolbar k-grid-toolbar">
        <a class="k-button k-button-icontext k-grid-add" href="index.php">
            <span class="k-icon k-add"></span> Add Provident Fund</a>
    </div>
<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/PF_controller.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/PF_controller.php",
                    type: "POST",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/PF_controller.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/PF_controller.php",
                    type: "PUT",
                    complete: function (e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                }
            },
            //    code: "Ok",
            autoSync: false,
            schema: {
                errors: function (e) {
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
                    id: "PF_id",
                    fields: {
                        PF_id: {editable: false, nullable: true},
                        company_title:{type: "string"},
                        pf_main:{type: "string"}, 
                        PSH_header_title:{type: "string"},
                        PF_start:{type: "string"},
                        PF_after_1y:{type: "string"},
                        PF_after_2y:{type: "string"},
                        PF_after_3y:{type: "string"},
                        action:{type: "string"}
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
            groupable: true,
//            toolbar: [{name: "create", text: "Add Provident Fund"}],
            columns: [
                {field: "company_title", title: "Company", id: "company_title", width: "200px"},
                {field: "pf_main", title: "Provident Fund %", id: "pf_main", width: "150px"},   
                {field: "PSH_header_title", title: "On Salary Component", id: "PSH_header_title", width: "180px"},
                {field: "PF_start", title: "Provident Fund<br /> Start", id: "PF_start", width: "140px"},
                {field: "PF_after_1y", title: "Provident Fund<br /> After 1yr", id: "PF_after_1y", width: "140px"},
                {field: "PF_after_2y", title: "Provident Fund<br /> After 2yr", id: "PF_after_2y", width: "140px"},
                {field: "PF_after_3y", title: "Provident Fund<br /> After 3yr", width: "140px"},
                {
                   template: kendo.template($("#edit-template").html()), width: "100px", title: "Action"
                },
                {command: [ "destroy"], title: "Action", width: "180px"}
                ],
            editable: "inline"
        });
    });</script>
<script id="edit-template" type="text/x-kendo-template">
    <a class="k-button" href="index.php?PF_id=#= PF_id #" ><i class="fa fa-edit"></i> Edit</a>
</script>

<?php // if ($con->hasPermissionUpdate($permission_id) != "yes"): ?>
<!--    <style type="text/css">
        .k-grid-edit {
            display:none;
        }
    </style>-->
<?php // endif; ?>

<?php // if ($con->hasPermissionDelete($permission_id) != "yes"): ?>
<!--    <style type="text/css">
        .k-grid-delete {
            display:none;
        }
    </style>-->
<?php // endif; ?>   
<?php // if ($con->hasPermissionCreate($permission_id) != "yes"): ?>
<!--    <style type="text/css">
        .k-grid-add {
            display:none;
        }
    </style>-->
<?php // endif; ?>   


<div id="kWindow"></div>
    </div>
</div>    
<?php include '../view_layout/footer_view.php'; ?>