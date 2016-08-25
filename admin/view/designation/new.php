<?php
//session_start();
include("../../config/class.config.php");
$con = new Config();
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
                    url: "../../controller/designation.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/designation.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                   },
                destroy: {
                    url: "../../controller/designation.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/designation.php",
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
                    id: "designation_id",
                    fields: {
                        designation_id: {editable: false, nullable: true},
                        designation_title: {validation: {required: true}}, 
                        department_id: {type: "number"},
                        department_title: {type: "string"},
                        subsection_id: {type: "number"},
                        subsection_name: {type: "string"},
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
                {name: "create", text: "Add Designation"}
            ],
          columns: [
                {field: "designation_title", title: "Title"},
                 {field: "department_title",
                    title: "Department", 
                    editor: function(container){
                        var department_id = $('<input required id="department_id" name="department_id"  />')
                       department_id.appendTo(container)
                      department_id.kendoDropDownList({
                    dataTextField:"department_title",
                    dataValueField:"department_id",
                    autoBind: false,
                    type:"json",
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
                    },
                    optionLabel: "Department"
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
                
                {field: "subsection_title",
                    title: "Subsection" ,
                    editor: function(container){
                      var subsection_id=$('<input required name="subsection_id" id="subsection_id" />');
                  subsection_id.appendTo(container)
                  subsection_id.kendoDropDownList({
                    dataTextField:"subsection_title",
                    dataValueField:"subsection_id",
                    autoBind: false,
                    type:"json",
                    cascadeFrom: "department_id",
                    dataSource: {
                        transport: {
                            read: {
                                url: "../../controller/sub_section.php",
                                 type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    },
                    optionLabel: "Subsection"
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
                 {field: "status", title: "Active?", template: "#= status ? 'yes' : 'no' #", width: "8%"},
                {command: ["edit", "destroy"], title: "Action", width: "18%"}],
            
                 editable: "inline"
                 }).data("kendoGrid");
        
});
    

    
     function deptFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
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
            },
            optionLabel: "Select"
        });
    }


    function cityFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "subsection_title",
            dataValueField: "subsection_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/sub_section.php",
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
    
   
<!--<script>
                $(document).ready(function() {
                    $("#files").kendoUpload();
                });
            </script>-->
            

<?php include '../view_layout/footer_view.php'; ?>
