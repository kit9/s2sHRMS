<?php
include("../../lib/mpdf/mpdf.php");
if (isset($_GET["emp_id"])){
    $empp_id = $_GET["emp_id"];
}
//echo $emp_id;
//exit();
//Set page mergins
$mpdf = new mPDF('c', 'A4', '', '', 15, 15, 15, 15, 16, 13);
$mpdf->SetDisplayMode('fullpage');
//$stylesheet = file_get_contents('style.css');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
$url = "http://182.48.67.18:5000/s2s_payroll/admin/view/employee/details_pdf.php?empl_id=$empp_id";
$html = file_get_contents($url);
$mpdf->WriteHTML($html, 2);
$mpdf->Output('employee.pdf', 'D');
exit;