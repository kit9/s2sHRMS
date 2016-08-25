<?php
session_start();
include '../../config/class.config.php';
$con = new Config();

$empCode = '';
$result = array();
$result['object'] = array();
$result['type'] = '';

extract($_POST);

if ($empCode != "") {
    $sqlSearch = "SELECT * FROM payroll_employee_advance WHERE PEA_status='approved' AND PEA_employee_code='$empCode'";
    $resultSearch = mysqli_query($con->open(), $sqlSearch);
    if ($resultSearch) {
        if(mysqli_num_rows($resultSearch) > 0){
            while ($resultSearchObj = mysqli_fetch_object($resultSearch)) {
                $result['object'][] = $resultSearchObj;
                $result['type'] = 'success';
            }
        } else {
            $result['type'] = 'success';
        }
    } else {
        $result['type'] = 'error';
    }
    echo json_encode($result);
}