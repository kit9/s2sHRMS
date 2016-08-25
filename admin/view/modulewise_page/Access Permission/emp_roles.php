<?php
/** author: Asma
 * Date: 23 Feb, 2015
 * RPAC- Payroll
 * controller file :: Manage Employee role 
 */
include '../config/class.config.php';
$con = new Config();
header("Content-type: application/json");

$employee_role = $con->SelectAll("employee_role");
// while ($obj = mysqli_fetch_object($rs)) {
//        $arr[] = $obj;
//    }

    echo "{\"data\":" . json_encode($employee_role) . "}";