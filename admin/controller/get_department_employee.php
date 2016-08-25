<?php
/*
 * author: Shahnaz
 * Date: June 15, 2014
 * RPAC- Payroll
 * controller file :: Manage Employee records 
 */

//Connection parameters
include '../config/class.config.php';
$con = new Config();
$open = $con->open();

//Other declarations
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

/*
 * Read data from employee table
 * Bind data to JSON array
 */

$id = $_POST['dept_id'];

$allemp_array ='';
if ($verb == "POST") {
    $allemp_array = array();
    $employees = mysqli_query($open, "SELECT * FROM tmp_employee WHERE emp_department ='$id'");
    while ($obj = mysqli_fetch_object($employees)) {
        //$arr :: changed to $allemp_array
       $allemp_array[] = $obj->emp_firstname."-".$obj->emp_code ;
    }
   //Bind all employee data to array
    echo "{\"data\":" . json_encode($allemp_array) . "}";
}

?>