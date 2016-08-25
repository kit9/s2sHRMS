<?php
/** author: Asma
 * Date: 23 Feb, 2015
 * RPAC- Payroll
 * controller file :: Manage Employee role 
 */
//Connection parameters
include '../config/class.config.php';
$con = new Config();
$open = $con->open();

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

$emp_rol = $_POST['emp_rol'];

$allemp_array ='';
//if ($verb == "POST") {
    $allemp_array = array();
    $employe_rol = mysqli_query($open, "SELECT e.emp_code,e.emp_firstname FROM role_assign r INNER JOIN tmp_employee e ON e.emp_code=r.emp_code WHERE em_role_id='$emp_rol'");
    while ($obj = mysqli_fetch_object($employe_rol)) {
        //$arr :: changed to $allemp_array
       $allemp_array[] = $obj->emp_firstname."-".$obj->emp_code ;
    }

   //Bind all employee data to array
    echo "{\"data\":" . json_encode($allemp_array) . "}";
//}
?>