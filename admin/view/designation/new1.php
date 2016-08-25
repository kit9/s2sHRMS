<?php
//session_start();
include("../../config/class.config.php");
$con = new Config();
$designation_id ='';
$department_id ='';
$subsection_id ='';


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
                 {field: "department_id",
                    title: "Department", 
                    editor: deptDropDownEditor,
                    template: "#=department_title#",
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
                
                {field: "subsection_id",
                    title: "Subsection" ,
                    editor: cityDropDownEditor,
                    template: "#=subsection_id#",
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
                {command: ["edit"], title: "Action", width: "8%"}],
            
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
            cascadeFrom: "department_title",
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
    function deptDropDownEditor(container, options) {
        jQuery('<input required data-text-field="department_title" data-value-field="department_id" data-bind="value:' + options.field + '"/>')
                .appendTo(container)
                .kendoDropDownList({
                    autoBind: false,
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
    }
     function cityDropDownEditor(container, options) {
        jQuery('<input required data-text-field="subsection_title" data-value-field="subsection_id" data-bind="value:' + options.field + '"/>')
                .appendTo(container)
                .kendoDropDownList({
                    autoBind: false,
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
    }
         
         var categories = $("#deparment").kendoComboBox({
                    placeholder: "Select category...",
                    dataTextField: "department_title",
                    dataValueField: "department_id",
                    dataSource: {
//                            type: "json",
//                            data: categoriesData

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
                
                 var products = $("#products").kendoComboBox({
                    autoBind: false,
                    cascadeFrom: "deparment",
                    placeholder: "Select Course level..",
                    dataTextField: "subsection_title",
                    dataValueField: "subsection_id",
                    dataSource: {
//                        type: "json",
//                        data: productsData
                        transport: {
                            read: {
                                url: "../../controller/sub_section.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox"); 
         
    
    </script>
    
   
<!--<script>
                $(document).ready(function() {
                    $("#files").kendoUpload();
                });
            </script>-->
            

<?php include '../view_layout/footer_view.php'; ?>

