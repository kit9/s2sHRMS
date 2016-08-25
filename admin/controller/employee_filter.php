<?php
/*
 * author: Rajan Hossain
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
if ($verb == "GET") {
    //$allemp_array = array();
    
    $dept=$_GET['dept'];
    
    $employees =$con->FlyQuery("SELECT * FROM tmp_employee WHERE emp_department='$dept'");
    //Bind all employee data to array
    echo "{\"data\":" . json_encode($employees) . "}";
}

?>
