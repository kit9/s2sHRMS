<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/shift_pattern.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/shift_pattern.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/shift_pattern.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/shift_pattern.php",
                    type: "PUT",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
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
                    id: "shift_pattern_id",
                     fields: {
                        shift_pattern_id: {editable: false, nullable: true},
                        pattern: {validation: {required: true}}, 
                        description: {validation: {required: true}}, 
                        no_of_shift: {validation: {required: true}},
                        company_id: {validation: {required: true}}, 
                        status: {type: "boolean"}
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
                pageSizes: [5, 10, 20, 50]
            },
            sortable: true,
            groupable: true,
            toolbar: [{name: "create", text: "Add Shift Pattern"}],
            columns: [
            {field: "company_title",
                    title: "Company Name", 
                    editor: function(container){
                        var company_id = $('<input required id="company_id" name="company_id"  />')
                       company_id.appendTo(container)
                      company_id.kendoDropDownList({
                    dataTextField:"company_title",
                    dataValueField:"company_id",
                    autoBind: false,
                    type:"json",
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
                    optionLabel: "Company"
                });
                    },
                    
                    filterable: {
                        ui: deptFilter,
                        extra: false,
                        operators: {
                            string: {
                                eq: "Is equal to",
                                neq: "Is not equal to"
                            }
                        }
                    }
                },
            
                {field: "pattern", title: "Pattern", id: "pattern"},
                {field: "description", title: "Description", id: "description"},
                {field: "no_of_shift", title: "No of Shift", id: "no_of_shift"},
                {field: "status", title: "Active?", template: "#= status ? 'Yes' : 'No' #", width: "90px"},
                {command: ["edit", "destroy"], title: "Action", width: "230px"}],
            editable: "inline"
        });
    });
    
    function deptFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
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
            optionLabel: "Select"
        });
    }
    
    </script>				

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>

