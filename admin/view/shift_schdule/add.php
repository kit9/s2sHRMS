<?php
session_start();
/*
 * Author: Shahnaz
 * Page: Employee Shifting
 */

//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

//Initialize variables
$emp_code = '';
$emp_name = '';

$employes = $con->SelectAll("employee");
if (isset($_GET['shift_id'])) {
    $shift_id = $_GET['shift_id'];
    $condition = "shift_id='" . $shift_id . "'order by shift_id DESC";
}

//$con->debug($shift_id);


if (isset($_POST["add"])) {
    extract($_POST);

    if (empty($_POST['employee_shift'])) {
        $err = "No employee is selected!";
    } else {
        $employees = $_POST['employee_shift'];
        while (list ($key, $val) = @each($employees)) {
            $array1 = array(
                "shift_id" => $shift_id,
                "emp_id" => $val
            );
//            $con->debug($val);

            if ($con->exists("employee_shifing_user", array("emp_id" => $val)) == 1) {
                $err = "Employee is already Added";
            } elseif ($con->insert("employee_shifing_user", $array1, $open) == 1) {
                $msg = "Employee added successfully";
                
                $con->redirect("index.php");
            } else {
                $err = "Something was wrong with storing the package employee!";
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Employee</h6></div>
    <div class="widget-body" style="background-color: white;">
       <!--Code-->
        <form method="POST" name="add_employee">

            <?php if (count($employes) >= 1): ?>
             <?php foreach ($employes as $p): ?>

                    <input type="checkbox" class="case" name="employee_shift[]" value="<?php echo $p->emp_id; ?>"> <?php echo $p->emp_firstname . " " . $p->emp_lastname . " - " . $p->emp_code; ?><br>

                <?php endforeach; ?>
               <?php endif; ?>
            <input type="checkbox" id="selectall"/> <p> Select All</p>
            <script type="text/javascript">
                $(function() {

                    // add multiple select / deselect functionality
                    $("#selectall").click(function() {
                        $('.case').attr('checked', this.checked);
                    });

                    // if all checkbox are selected, check the selectall checkbox
                    // and viceversa
                    $(".case").click(function() {

                        if ($(".case").length == $(".case:checked").length) {
                            $("#selectall").attr("checked", "checked");
                        } else {
                            $("#selectall").removeAttr("checked");
                        }

                    });
                });
            </script>

            <input style="margin-left: 200px;" class="k-button" type="submit" name="add" value="Add">
        </form>
        <!--Code-->



    </div>
    <div class="clearfix"></div>

</form>
</div>

</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
    



