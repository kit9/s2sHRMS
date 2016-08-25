<?php

session_start();
include("../../config/class.config.php");
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
$con = new Config();
$open = $con->open();
//Set up time configuration to UTC
date_default_timezone_set('UTC');

//Collecting date and company ID

$data = $_SESSION["excelValue"];
//$con->debug($data);


$temp_start_date = date('Y-m-d', strtotime($data["start_date"]));
$temp_end_date = date('Y-m-d', strtotime($data["end_date"])); //new DateTime();
$company_id = $data["company_id"];

$column = array();
$list = array();
$dates = "SELECT DISTINCT date FROM dates WHERE date BETWEEN '$temp_start_date' AND '$temp_end_date' ORDER BY date";
$result = mysqli_query($open, $dates);
$object = array();
while ($rows = mysqli_fetch_assoc($result)) {
    $object[] = $rows;
}
//$con->debug($object);
$Header_array = array();
//* push the static file in array **//
array_push($Header_array, "Employee Code", "Employee Name", "Sub Section", "Department");
// $con->debug($Header_array);
//* push the static file in array **//
foreach ($object as $key => $val) {
    array_push($Header_array, date("d-M", strtotime($val["date"])));
}
//Fetching all the array
$querySting = "select emp_code, emp_firstname, emp_subsection,department.department_title from tmp_employee inner join department on tmp_employee.emp_department = department.department_id where tmp_employee.company_id='$company_id'";
//    $tablArry =array("tmp_employee", "department");
//    $field_array = array("tmp_employee"=>"");
//$con->debug($querySting);
$employees = $con->QueryResult($querySting);
//$con->debug($employees);
//exit();
$dataArray = array();

foreach ($employees as $emp) {
    $tmpArray = array();
    $emp_code = $emp->emp_code;

    array_push($tmpArray, $emp_code, $emp->emp_firstname, $emp->emp_subsection, $emp->department_title);

    $queryString = "select A.date, B.ot_hours from 
                        (select dates.date from dates where dates.date>='2014-09-01' and dates.date<='2014-09-30' and company_id='1') as A LEFT OUTER JOIN 
                        (select job_card.date, job_card.ot_hours from job_card where job_card.date>='2014-09-01' and job_card.date<='2014-09-30' and job_card.emp_code='$emp_code') as B on A.date = B.date";
    $result = mysqli_query($open, $queryString);
    $object_2 = array();
    while ($rows = mysqli_fetch_assoc($result)) {
        $object_2[] = $rows;
    }


    foreach ($object_2 as $otKey => $val2) {
        array_push($tmpArray, $val2["ot_hours"]);
    }

    array_push($dataArray, $tmpArray);
}
$dataArray[0] = $Header_array;

//$con->debug($dataArray);
//    $fp = fopen('salary_sheet.xls', 'w');
//    fputcsv($fp, $dataArray, "\t", '"');
//    fclose($fp);
//    header("location: salary_sheet.xls");
$count = count($dataArray);
$countCol = count($dataArray[0]);

$createPHPExcel = new PHPExcel();
$cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);

$rowCount = 0;
for ($i = 1; $i <= $count; $i++) {
    for ($j = 0; $j <= $countCol - 1; $j++) {
        $cWorkSheet->setCellValueByColumnAndRow($j, $i, $dataArray["$rowCount"]["$j"]);
    }

    $rowCount++;
}
$objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
$filename = $company_id.rand(0, 9999999)."OtReprot.xlsx";
$objWriter->save("$filename");
header("location:$filename");
?>
