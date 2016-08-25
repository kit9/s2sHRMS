<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

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
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<div id="details"></div> 

<script type="text/javascript">
    var wnd,
    detailsTemplate;
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/module_page.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/module_page.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                   },
                destroy: {
                    url: "../../controller/module_page.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/module_page.php",
                    type: "PUT",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
            },
            autoSync: false,
            schema: {
                data: "data",
                total: "data.length",
                model: {
                    id: "module_page_id",
                    fields: {
                        module_page_id: {editable: false, nullable: true},
                        module_headline: {validation: {required: true}}, 
                        module_page_title: {validation: {required: true}}, 
                        rules_id: {type: "number"},
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
                pageSizes: true,
                pageSizes: [5, 10, 20, 50],
            },
            sortable: true,
            groupable: true,
            toolbar: [
                {name: "create", text: "Add Page Module"}
            ],
          columns: [
               
                 {field: "module",
                    title: "Module", 
                    editor: function(container){
                        var rules_id = $('<input required id="rules_id" name="rules_id"  />')
                        rules_id.appendTo(container)
                        rules_id.kendoDropDownList({
                    dataTextField:"module",
                    dataValueField:"rules_id",
                    autoBind: false,
                    type:"json",
                    dataSource: {
                        transport: {
                            read: {
                                url: "../../controller/emp_rules.php",  
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    },
                    optionLabel: "Page Module"
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
                {field: "module_headline", title: "Page Name"},    
                {field: "module_page_title", title: "Page Link"},
                {field: "status", title: "Active?", template: "#= status ? 'yes' : 'no' #", width: "8%"},
                {command: ["edit", "destroy"], title: "Action", width: "18%"}],
            
                 editable: "inline"
                 }).data("kendoGrid");
        
});
    

    
     function deptFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "module",
            dataValueField: "rules_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/emp_rules.php",
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
<?php include '../view_layout/footer_view.php'; ?>
