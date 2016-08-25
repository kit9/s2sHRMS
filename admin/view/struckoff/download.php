<?php
include("../../lib/mpdf/mpdf.php");
if (isset($_GET["emp_id"])){
    $emp_id = $_GET["emp_id"];
}
$mpdf = new mPDF('c', 'A4', '', '', 15, 15, 15, 15, 16, 13);
$mpdf->SetDisplayMode('fullpage');
$mpdf->WriteHTML($stylesheet, 1);
$mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
$url = "http://localhost/rpac_payroll/admin/view/struckoff/struck_off_details_pdf.php?emp_id=$emp_id";
$html = file_get_contents($url);
$mpdf->WriteHTML($html, 2);
$mpdf->Output('stuckoff.pdf', 'D');
exit;