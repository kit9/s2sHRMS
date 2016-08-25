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
$err = "";
$msg = '';
?>
<?php include '../view_layout/header_view.php'; ?>

<div id="grid"></div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        var dataSource = new kendo.data.DataSource({
            pageSize: 5,
            transport: {
                read: {
                    url: "../../controller/auth_controller.php",
                    type: "GET"
                },
                update: {
        
                    url: "../../controller/auth_controller.php",
                    type: "POST",
                    complete: function(e) {
                        jQuery("#grid").data("kendoGrid").dataSource.read();
                    }
                },
                destroy: {
                    url: "../../controller/auth_controller.php",
                    type: "DELETE"
                },
                create: {
                    url: "../../controller/auth_controller.php",
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
                        var message = "There are some errors:\n";
                        message += e.message;
                        var window = jQuery("#kWindow");
                        if (!window.data("kendoWindow")) {
                            window.kendoWindow({
                                        title: "Error window",
                                        modal: true,
                                        height: 150,
                                        width: 400
                                    });
                        }

                        window.data("kendoWindow").center().open();
                        window.html('<br/><br/><P>' + message + '</p>');
                        //var grid = $("#RBOGrid").data("kendoGrid");
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "con_auth_id",
                    fields: {
                        con_auth_id: {editable: false, nullable: true},
                        con_auth_name: {type: "string",validation: {required: true}},
                        con_auth_pass: {type: "string", validation: {required: true}},
                        con_auth_user_name: {type: "string",validation: {required: true}},
                        con_auth_img:{type: "string"},
                        con_auth_email: {type: "string",validation: {required: true}}
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
            toolbar: [{name: "create", text: "Add Author"}],
            columns: [
                {field: "con_auth_name", title: "Author Name", id: "con_auth_name"},
                {field: "con_auth_pass", title: "Password"},
                {field: "con_auth_user_name", title: "User Name"},
                {field: "con_auth_email", title: "Email", id: "con_auth_email"},
                 { field: "con_auth_img", 
               title: "Image", 
               editor: fileEditor, 
               
               template: "<img src='<?php echo $con->baseUrl("uploads/consultant_author/") ?>#=con_auth_img#' height='70' width='80'/>"
           },
                {command: ["edit", "destroy"], title: "Action", width: "200px"}],
            editable: "inline"
            
        });
        
        function fileEditor(container, options) {
        $('<input type="file" id="files" name="files" data-role="upload" />')
        .appendTo(container)
        .kendoUpload({
            multiple: false,
            async:{
                saveUrl: "../../controller/savecon.php",
               autoUpload: true
            },
             upload: function (e) {
                        e.data = { con_auth_id: options.model.con_auth_id };
                    },
             success: function (e) {
                        //console.log(e.response);
                        options.model.set("con_auth_img", e.response.con_auth_img);
            }
        });
    }
        
    });</script>				

<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>