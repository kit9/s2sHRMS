<?php
session_start();
/** Author: Rajan Hossain
 * Page: Search Employee */
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
//Instantiate variables
$companies = '';
$component_title = '';
$show_in_sheet = '';
$description = '';
$type = '';
$add_deduct = '';
$sum_in_other = '';

//Form submit
if (isset($_POST["btnSave"])) {
    extract($_POST);
//    print_r($_POST);
//    exit();
        if (empty($_POST["companies"]))
        $err = "Please select Company";
        
    $insert_array = array(
        "company_id" => $companies,
        "component_title" => $component_title,
        "show_in_sheet" => $show_in_sheet,
        "description" => $description,
        "type" => $type,
        "add_deduct" => $add_deduct,
        "sum_in_other" => $sum_in_other,
        "formula" => $Formulas,
        "status" => "true");
    if ($con->insert("salary_component", $insert_array) == 1) {
        $msg = 'A New Component is Added Successfully.';
    } else {
        $err = 'Something Went Wrong. New Component Creation Failed.';
    }
}
$quer_sal = "select component_title from salary_component";
$resultt = $con->QueryResult($quer_sal);
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Salary Component</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-4">
                <label for="Full name">Company:</label><br/>
                <input type="text" id="company" name="companies" placeholder=""  style="width: 80%;"/>
            </div>

            <div class="col-md-4">
                <label for="Full name">Salary Component:</label><br/>
                <input type="text" class="k-textbox" id="component_title" name="component_title" placeholder=""  style="width: 80%;"/>
            </div>

            <div class="col-md-4">
                <label for="Full name" style="height: 20px;"></label><br/>
                <input id="show_in_sheet" value="yes" name="show_in_sheet" type="checkbox"/> Show in Salary Sheet?
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-4">
                <label for="Full name">Description:</label><br/>
                <input type="text" class="k-textbox" id="description" name="description" placeholder=""  style="width: 80%;"/>
            </div>

            <div class="col-md-4">
                <label for="Full name">Type:</label><br/>
                <input type="text" id="type" name="type" placeholder="Select Type..." style="width: 80%;"/>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    $("#type").change(function(){
                        var type = $("#type").val();
                        if (type == "calculation") {
                            var message = "Salary Component Formula<br />";
                            message += '';
                            message += '<div width="200px" height="200px;" style="background-color: white;">';
                            message += '<h5> Example of Formula</h5><input type="text" name="formula" class="k-textbox" id="formula" style="width:350px;font-weight:bold;font-size:13px;"><div class="clearfix">&nbsp;&nbsp;</div>';
                            message += '<div class="col-md-12"><div class="col-md-4" width="250px" style="float: left;background-color: white;">';
                            message += '<select id="salary_component" size="10" style="width:120px;">';
                            message += '<?php foreach ($resultt as $res) { ?>';
                            message += '<option value="<?php echo $res->salary_component_id; ?>"> <?php echo $res->component_title; ?></option>';
                            message += '<?php } ?></select></div>';
                            message += '<div id="operator_div" class="col-md-4" width="60px" height="200px;" style="float: left;background-color: white;">';
                            message += '<select id="operator" size="10" style="width:100px;font-weight:bold;">';
                            message += '<option value="0"><b>+</b></option>';
                            message += '<option value="0"><b>-</b></option>';
                            message += '<option value="0"><b>-</b></option>';
                            message += '<option value="0"><b>*</b></option>';
                            message += '<option value="0"><b>/</b></option>';
                            message += '<option value="0"><b>(</b></option>';
                            message += '<option value="0"><b>)</b></option>';
                            message += '</select></div>';
                            message += '<script type="text/javascript">$(document).ready(function(){';
                            message += '$("#operator").attr("disabled", "disabled");';
                            message += '$("#salary_component").click(function(){';
                            message += 'var selectedText = $("#salary_component option:selected").text();';
                            message += 'var tolattext = $("#formula").val();';
                            message += 'tolattext += selectedText;';
                            message += '$("#formula").val(tolattext);';
                            message += '$("#salary_component").attr("disabled", "disabled");';
                            message += '$("#operator").removeAttr("disabled", "disabled");});';
                            message += '$("#operator").click(function(){ var tolattext = $("#formula").val();';
                            message += 'var selectedText = $("#operator option:selected").text();';
                            message += 'tolattext += selectedText;$("#formula").val(tolattext);';
                            message += '$("#operator").prop("disabled", "disabled"); $("#salary_component").removeAttr("disabled", "disabled");';
                            message += '});$("#btnreset").click(function () {$("#formula").val("");';
                            message += '$("#operator").attr("disabled", "disabled");';
                            message += '$("#salary_component").removeAttr("disabled", "disabled");';
                            message += '});';
                            message += '$("#btnSaveFormula").click(function(){';
                            message += 'var data = $("#formula").val();$("div:hidden").show("fast");$("#Formulas").val(data);$(".k-i-close").click(); });});';
                            message += '</scr' + 'ipt></div><br />';
                            message += '<div align="left" width="400px" height="30px;" >';
                            message += '<input class="k-button" name="btnSaveFormula" id="btnSaveFormula" value="Save Formula">&nbsp;&nbsp;';
                            message += '<input type="reset" id="btnreset" class="k-button" name="btnReset" value="Reset">';
                            message += '</div></div>';
                            var window = jQuery("#calculateWindow");
                            if (!window.data("kendoWindow")){
                                window.kendoWindow({
                                    title: "",
                                    modal: true,
                                    height: 350,
                                    width: 400
                                });
                            }
                            //buttonJQueryObject.closest(".k-window-content").data("kendoWindow").close();
                            window.data("kendoWindow").center().open();
                            window.html('<center><P style="color:black;font-weight:bold;">' + message + '</p></center>');
                            //var grid = $("#RBOGrid").data("kendoGrid");
                            this.cancelChanges();
                        }
//            $("#type").change(function () {
                    });
                });
            </script>
            <div id="calculateWindow"></div>
            <!--Payment Type-->
            <div class="col-md-4">
                <label for="Full name">Addition/Deduction:</label><br/>
                <input type="text" id="add_deduct" name="add_deduct" placeholder="Select Type..." style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-4" id="formula_div" style="display: none;">
                <label for="Formula">Formula:</label><br/>
                <input type="text" class="k-textbox" id="Formulas" name="Formulas" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-4">
                <input id="sum_in_other" value="yes" name="sum_in_other" type="checkbox"/> Sum in other field?
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-4">
                <input type="submit" class="k-button" name="btnSave" value="Save Component">
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>
    <br />
    <br />
    <div class="col-md-12">
        <div id="grid"></div>
    </div>
    <div class="clearfix"></div>
    <br />
</div>
<div id="kWindow"></div>
<!--</div>
</div>-->
<?php include '../view_layout/footer_view.php'; ?>

<!--Kendo Grid :: All components-->
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 10,
            transport: {
                read: {
                    url: "../../controller/salary_component_controller.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/salary_component_controller.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/salary_component_controller.php",
                    type: "DELETE"
                }
            },
            //    code: "Ok",
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
                        //var grid = $("#RBOGrid").data("kendoGrid");
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "salary_component_id",
                    fields: {
                        company_id: {type: "number"},
                        company_title: {type: "string", validation: {required: true}},
                        salary_component_id: {editable: false, nullable: true},
                        component_title: {type: "string", validation: {required: true}},
                        show_in_sheet: {type: "boolean"},
                        description: {type: "string"},
                        type: {type: "string", validation: {required: true}},
                        add_deduct: {type: "string", validation: {required: true}},
                        sum_in_other: {type: "string" },
                        status: {type: "string"}
                        }
                    //  this.cancelChanges(); 
                    }
                }
            //  }
        });
        jQuery("#grid").kendoGrid({
            autoBind: true,
            dataSource: dataSource,
            filterable: true,
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSizes: [ 10, 20, 50]
            },
            sortable: true,
            groupable: true,
            columns: [
                {field: "company_id", title: "Company Title", id: "company_title", width: "180px", 
                editor: CompanyDropDownEditor, 
                template: "#=company_title#",
                    filterable: {
                        ui: CompanyFilter,
                        extra: false,
                        operators: {
                            string: {
                                eq: "Is equal to",
                                neq: "Is not equal to"
                            }
                        }
                    }
                },
                {field: "component_title", title: "Component Title", id: "component_title", width: "180px"},
                {field: "show_in_sheet", title: "Show in Sheet?", template: "#= show_in_sheet ? 'Yes' : 'No' #", width: "180px"},
                {field: "description", title: "Description", id: "description", width: "180px"},
//            {field: "type", title: "Type", id: "type", width: "180px", editor: TypeDropDownEditor, template: "#= type #"},
                {field: "type",title: "Type", id: "type", width: "180px",
                    editor: TypeDropDownEditor, filterable: false  //// template: "#= type #",
                }, 
                {field: "add_deduct", title: "Add/Deduct", id: "add_deduct", width: "180px"},
                {field: "sum_in_other", title: "Sum in Other", id: "sum_in_other", width: "180px"},
//                 {field: "is_active", title: "Active?", template: "#= is_active ? 'yes' : 'no' #", width: "8%"},
                {field: "status", title: "Active?", template: "#= yes ? 'yes' : 'no' #", width: "120px"},
                {command: ["edit", "destroy"], title: "Action", width: "200px"}
                ],
            editable: "inline"
        });
    });
    function CompanyDropDownEditor(container, options){
        jQuery('<input required data-text-field="company_title" data-value-field="company_id" data-bind="value:' + options.field + '"/>')
                .appendTo(container)
                .kendoDropDownList({
                    autoBind: true,
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
                    },
                    optionLabel: "Select Company"
                });
    }
    function CompanyFilter(element) {
        element.kendoDropDownList({
            autoBind: true,
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
            },
            optionLabel: "Select Company"
        });
    }

    function TypeDropDownEditor(container, options) {
        jQuery('<input required data-text-field="type" data-value-field="type_id" data-bind="value:' + options.field + '"/>')
                .appendTo(container)
                .kendoDropDownList({
                    autoBind: true,
                    dataSource: {
                        transport: {
                            read: {
                                url: "../../controller/type_controller.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
//                    optionLabel: "Select Type"
                });
    }
</script>	

<!--Number of Steps Combo-->
<!--<script type="text/javascript">
    $(document).ready(function(){
        $("#type").kendoComboBox({
            dataTextField: "text",
            dataValueField: "value",
            dataSource: [
                {text: "Fixed", value: "fixed"},
                {text: "Calculation", value: "calculation"}
            ],
            filter: "contains",
            suggest: true
        });
    });
</script>-->

<!--Addition or Deduction Combo -->
<script type="text/javascript">
    $(document).ready(function() {
        $("#add_deduct").kendoComboBox({
            dataTextField: "text",
            dataValueField: "value",
            dataSource: [
                {text: "Add", value: "add"},
                {text: "Deduct", value: "deduction"}
            ],
            filter: "contains",
            suggest: true
        });
    });</script>

<!--Company Combo-->
<script type="text/javascript">
    $(document).ready(function() {
        var company = $("#company").kendoComboBox({
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

<!--Dynamic form generation-->
<!--<script type="text/javascript">
//when the webpage has loaded do this
//            $(document).ready(function() {
//    $('#steps').change(function() {
//    var num = $('#steps').val();
//            var i = 0;
//            var html = '';
//            for (i = 1; i <= num; i++) {
//    html += '<div class="col-md-4">';
//            html += '<label for="All Steps">Step: ' + i + ':</label><br/>';
//            html += '<input style="width:80%"; type="text" id="step_' + i + '"name="steps[]"/>';
//            html += '</div>';
//            if (i === 3) {
//    html += '<div class="clearfix"></div><br/>';
//    }
//    html += '<script type="text/javascript">';
//            html += '$(document).ready(function () {';
//            html += '$("#step_' + i + '").kendoComboBox({';
//            html += 'placeholder: "Select employee...",';
//            html += 'dataTextField: "emp_name",';
//            html += 'dataValueField: "emp_code",';
//            html += 'dataSource: {';
//            html += 'transport: {';
//            html += 'read: {';
//            html += 'url: "../../controller/employee_list.php",';
//            html += 'type: "GET"';
//            html += '}';
//            html += ' },';
//            html += 'schema: {';
//            html += 'data: "data"';
//            html += '}';
//            html += '}';
//            html += ' }).data("kendoComboBox");';
//            html += '});';
//            html += '</scr' + 'ipt>';
//            //insert this html code into the div with id supervisor
//    }
//    html += '<div class="clearfix"></div><br/><br/>';
//            $('#supervisor').html(html);
//    });
//    });
</script>
    //
//    function TypeDropDownEditor(container, options) {
//    jQuery('<input required data-text-field="type" data-value-field="type_id" data-bind="value:' + options.field + '"/>')
//            .appendTo(container)
//            .kendoDropDownList({
//            dataTextField: "text",
//                    dataValueField: "value",
//                    dataSource: [
//                    {text: "Fixed", value: "fixed"},
//                    {text: "Calculation", value: "calculation"}
//                    ],
//                    filter: "contains",
//                    suggest: true,
//                    autoBind: true,
////                    dataSource: {
////                        transport: {
////                            read: {
////                                url: "../../controller/type.php",
////                                type: "GET"
////                            }
////                        },
//////                        data: [
//////                            {type: "Fixed"},
//////                            {type: "Calculation"}
//////                        ],
////                        schema: {
////                            data: "data"
////                        }
////                    },
//                    optionLabel: "Select Type"
//            });
//    }