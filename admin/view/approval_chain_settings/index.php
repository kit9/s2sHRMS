<?php
session_start();
/*
 * Author: Rajan Hossain
 * Page: Search Employee
 */
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
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Approval Chain Settings</h6></div>
    <div class="widget-body" style="background-color: white;">
        <form>
            <label for="process">Process</label><br />
            <select id="size">
                <option>Select Process...</option>
                <option>Leave Application</option>
                <option>Enrollment</option>
                <option>Payment</option>
            </select>
            <br /><br />
            <label for="numeric">Approval Steps</label><br /> 
            <select id="steps">
                <option>Select Step...</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
            </select>
            <br /><br />
            <input type="checkbox" style="margin-top: 5px;"> &nbsp;&nbsp; All steps applicable?
            <script type="text/javascript">
                $(document).ready(function () {
                    $("select").change(function () {
                        $("select option:selected").each(function () {
                            //employee is selected
                            if ($(this).attr("value") === "1") {
                                $(".employee").show();
                                $(".department").hide();
                            }
                            //If designation is selected
                            if ($(this).attr("value") === "2") {
                                $(".employee").hide();
                                $(".department").show();
                            }
                            
                            //If designation is selected
                            if ($(this).attr("value") === "0") {
                                $(".employee").hide();
                                $(".department").hide();
                            }
                        });

                    }).change();
                });
            </script>

            <br /><br />
            <label for="type">Assignment Type</label><br />
            <select id="type">
                <option value="0">Select Type...</option>
                <option value="1">Employee</option>
                <option value="2">Designation</option>
            </select>
            <br /><br />
            <div class="employee" style="display: none;">
                <label for="type">Employee</label><br />
                <select id="employee">
                    <option>Select Employee...</option>
                    <option>RPAC0001-Alena Rose</option>
                    <option>RPAC0002-Johny Ive</option>
                </select>
                <br /><br />
            </div>
            <div class="department" style="display: none;">
                <label for="department">Designation</label><br />
                <select id="department">
                    <option>Select Designation...</option>
                    <option>Engineer</option>
                    <option>Production Manager</option>
                </select>
                <br /><br />
            </div>
            <label for="options">Options</label><br />
            <input type="checkbox" style="margin-top: 5px;"> &nbsp; Approve
            <input type="checkbox" style="margin-top: 5px;"> &nbsp; Reject <br ><br />
            <input type="checkbox" style="margin-top: 5px;"> &nbsp; Cancel &nbsp;
            <input type="checkbox" style="margin-top: 5px;"> &nbsp; Hold
        </form>
    </div>
    <script>
        $(document).ready(function () {
            $("#size").kendoComboBox();
            $("#type").kendoComboBox();
            $("#department").kendoComboBox();
            $("#employee").kendoComboBox();
            $("#steps").kendoComboBox();
            $("#numeric").kendoNumericTextBox({
                decimal: 3
            });
        });
    </script>
</div>

</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>







