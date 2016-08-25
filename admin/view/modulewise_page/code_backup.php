<!--Script to populate the grid with defined -->
<script type="text/javascript">
    $(document).ready(function () {
        $('#employeesEm').change(function () {
            window.location = "index.php?set_id=" + $(this).val();
        });
    });
</script>
<?php
//Check existing permissions for selected employee
if (isset($_GET["set_id"])) {
    $set_id = $_GET["set_id"];
    $employees_here = $con->SelectAllByCondition("tmp_employee", "emp_id='$set_id'");
    $existing_emp_code = $employees_here{0}->emp_code;
    //Ftech all permissions for this employee code
    $existing_permissions = $con->SelectAllByCondition("module_permission", "emp_code='$existing_emp_code'");
}

//Display existing role for an employee
if (isset($_POST["btnSearchPermission"])) {
    extract($_POST);
    if ($emp_id != '') {
        //Check employee in permission table
        $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
        $emp_code = $employees{0}->emp_code;
        $query_existing = "SELECT * FROM module_permission WHERE emp_code='$emp_code'";
        if (count($query_existing) > 0) {
            $result_existing = mysqli_query($open, $query_existing);
            while ($rows_existing = mysqli_fetch_object($result_existing)) {
                $arr_existing[] = $rows_existing;
            }
        } else {
            //Check employee in role assign table
            $employees = $con->SelectAllByCondition("tmp_emloyee", "emp_id='$emp_id'");
            $emp_code = $employees{0}->emp_code;
            $query_existing_two = "SELECT
                        ra.em_role_id,
                        mp.*
                FROM
                        role_assign AS ra
                LEFT JOIN module_permission AS mp ON mp.em_role_id = ra.em_role_id
                WHERE
                        ra.em_role_id = '$emp_code'
                GROUP BY
                        mp.module";
            $result_existing_two = mysqli_query($open, $query_existing_two);
            while ($rows_existing_two = mysqli_fetch_object($result_existing_two)) {
                $arr_existing_two[] = $rows_existing_two;
            }
        }
    } else if ($em_role_id != '') {
        $query_for_role = "SELECT * FROM module_permission WHERE em_role_id='$em_role_id' AND rules_id='$rules_id'";
        $result_for_role = mysqli_query($open, $query_existing_two);
        while ($rows_for_role = mysqli_fetch_object($result_for_role)) {
            $arr_for_role[] = $rows_for_role;
        }
    }
}

?>