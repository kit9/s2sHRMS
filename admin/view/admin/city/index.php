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

        <script type="text/javascript">
                  jQuery(document).ready(function () {
                        var dataSource = new kendo.data.DataSource({
                           pageSize: 5,
                    transport: {
                        read: {
                            url: "../../controller/city.php",
                            type: "GET"
                        },
                        update: {
                            url: "../../controller/city.php",
                            type: "POST",
                            complete: function(e) {
                                jQuery("#grid").data("kendoGrid").dataSource.read();
                            }
                        },
                        destroy: {
                            url: "../../controller/city.php",
                            type: "DELETE"
                        },
                        create: {
                            url: "../../controller/city.php",
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
                            id: "city_id",
                            fields: {
                                city_id: {editable: false, nullable: true},
                                city_name: {type: "string", validation: {required: true}},
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
                    toolbar: [{name: "create", text: "Add City"}],
                    columns: [
                        {field: "city_name", title: "City Name"},
                        {command: ["edit", "destroy"], title: "Action", width: "230px"}],
                    editable: "inline"
                });
            });

        </script>				
       

<?php include '../view_layout/footer_view.php'; ?>
