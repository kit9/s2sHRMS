<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
?>
<?php include '../view_layout/header_view.php'; ?>
<div id="grid"></div>
<script id="add_header" type="text/x-kendo-template">
    <a class="k-button k-button-icontext k-grid-add" href="create.php"><span class="k-icon k-add"></span>Add Header</a>
</script>
<script id="edit_header" type="text/x-kendo-template">
    <a class="k-button k-button-icontext k-grid-edit" href="edit.php?hid=#= PSH_id #"><span class="k-icon k-edit"></span>Edit</a>
    <a class="k-button k-button-icontext k-grid-delete" onclick="javascript:deleteClick(#= PSH_id #);" ><span class="k-icon k-delete"></span>Delete</a>
</script>
<script type="text/javascript">
    function deleteClick(PSH_id) {
        var c = confirm("Do you want to delete?");
        if (c === true) {
            $.ajax({
                type: "POST",
                dataType: "json",
                url: "../../controller/salary_header/salary_header_list.php",
                data: {PSH_id: PSH_id},
                success: function (result) {
                    if (result === true) {
                        $(".k-i-refresh").click();
                    }
                }
            });
        }
    }
</script>

<script type="text/javascript">
    jQuery(document).ready(function () {
        var dataSource = new kendo.data.DataSource({
            pageSize: 10,
            transport: {
                read: {
                    url: "../../controller/salary_header/salary_header_list.php",
                    type: "GET"
                },
                destroy: {
                    url: "../../controller/salary_header/salary_header_list.php",
                    type: "POST"
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
                        this.cancelChanges();
                    }
                },
                data: "data",
                total: "data.length",
                model: {
                    id: "PSH_id",
                    fields: {
                        PSH_id: {editable: false, nullable: true},
                        PSH_header_title: {type: "string"},
                        PSH_display_on: {type: "string"},
                        PSH_is_monthly: {type: "string"},
                        PSH_is_optional: {type: "string"}
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
            toolbar: kendo.template($("#add_header").html()),
            columns: [
                {field: "PSH_id", title: "Header ID", id: "PSH_id", width: "120px"},
                {field: "PSH_header_title", title: "Header Title", width: "120px"},
                {field: "PSH_display_on", title: "Header Display In", width: "120px"},
                {field: "PSH_is_monthly", title: "Is Monthly", width: "100px"},
                {field: "PSH_is_optional", title: "Is Optional", width: "100px"},
//                {field: "PSH_is_required", title: "Is Required", width: "100px"},
                {
                    title: "Action", width: "120px",
                    template: kendo.template($("#edit_header").html())
                }
            ]
        });
    });</script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.k-grid-delete').click(function () {
            $.ajax({
                type: 'POST',
                url: "../../controller/salary_header/salary_header_list.php",
                data: "",
                success: function () {

                }
            });
        });
    });
</script>
<div id="kWindow"></div>
<?php include '../view_layout/footer_view.php'; ?>