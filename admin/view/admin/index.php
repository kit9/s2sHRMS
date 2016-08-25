<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
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
                    url: "../../controller/admin.php",
                    type: "GET"
                },
                update: {
                    url: "../../controller/admin.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                   },
                destroy: {
                    url: "../../controller/admin.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/admin.php",
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
                    id: "ad_id",
                    fields: {
                        ad_id: {editable: false, nullable: true},
                        admin_name: {validation: {required: true}},
                        admin_email: {type: "string"},
                        password: {type: "string"},
                        admin_username: {type: "string"},
                        u_image:{type: "string"},
                        user_id: {type: "number"},
                        user_typ: {type: "string"},
                        is_active: {type: "boolean"}
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
                {name: "create", text: "Add Admin"}
            ],
          columns: [
                {field: "admin_name", title: "Admin Name"},
                {field: "admin_email", title: "Email"},
                {field: "password", title: "Password", hidden:true},
                {field: "admin_username", title: "User Name"},
                {field: "user_id",
                    title: "Role type", 
                    editor: deptDropDownEditor,
                    template: "#=user_typ#",
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
               
                {field: "is_active", title: "Active?", template: "#= is_active ? 'yes' : 'no' #", width: "8%"},
                {command: { text: "Details", click: showdetails }, title: "Details", width: "9%" },
                {command: ["edit"], title: "Action", width: "8%"}],
            
                 editable: "popup"
                 }).data("kendoGrid");
        
        wnd = $("#details")
                        .kendoWindow({
                            title: "Details",
                            modal: true,
                            visible: false,
                            resizable: false,
                            width: 300
                        }).data("kendoWindow");

                    detailsTemplate = kendo.template($("#template").html());
    });
    
    function showdetails(e) {
                    e.preventDefault();

                    var dataItem = this.dataItem($(e.currentTarget).closest("tr"));
                    wnd.content(detailsTemplate(dataItem));
                    wnd.center().open();
                }
    
     function deptFilter(element) {
        element.kendoDropDownList({
            autoBind: false,
            dataTextField: "user_typ",
            dataValueField: "user_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/get_author.php",
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
        jQuery('<input required data-text-field="user_typ" data-value-field="user_id" data-bind="value:' + options.field + '"/>')
                .appendTo(container)
                .kendoDropDownList({
                    autoBind: false,
                    dataSource: {
                        transport: {
                            read: {
                                url: "../../controller/get_author.php",  
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
  
           <script type="text/x-kendo-template" id="template" >
    
                <div id="details-container">
                 Admin Name: #= admin_name # <br/> <br/>
                 Admin Email:  #= admin_email # <br/> <br/>
                 User Name:  #= admin_username # <br/> <br/>
                User Type:  #= user_typ # <br/> <br/>
                 </div>
            </script>
            


<?php include '../view_layout/footer_view.php'; ?>
