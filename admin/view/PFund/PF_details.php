<?php
/* Author : Asma
 * Date: 6 April 15
 */
session_start();
//Importing class library
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");

//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
$err = "";
$msg = '';

if (isset($_GET['empl_code'])) {
    $empl_code = base64_decode($_GET['empl_code']);
}
$present_year = date('Y');
//$query_string = "SELECT e.*,d.designation_title FROM tmp_employee e left join designation d on e.emp_designation=d.designation_id";
//$emp_list = $con->QueryResult($query_string);
$pf_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname,e.company_id,c.company_title FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code LEFT JOIN company c on c.company_id=e.company_id WHERE p.pfd_emp_code='$empl_code'";
$pfd_detail = $con->QueryResult($pf_query);
//$company = $pfd_detail[0]->pfd_company_id;

$emp_year_pf = $con->QueryResult("select * from provident_fund_details_yearly where PFDY_emp_code='$empl_code'");

$arr = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$month = array_combine(range(1, count($arr)), array_values($arr));

//====================================================
if (isset($_POST["generate_excel"])) {
    extract($_POST);
    $today = date("Y/m/d");
    $sys_date = date_create($today);
    $formatted_today = date_format($sys_date, 'Y-m-d');
    $zero = "0000-00-00";
    $Arr = array();

    $pf_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname,e.company_id,c.company_title FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code LEFT JOIN company c on c.company_id=e.company_id WHERE p.pfd_emp_code='$empl_code' order by p.pfd_year DESC";
    $pfd_detail = $con->QueryResult($pf_query);
    $name = $pfd_detail[0]->emp_firstname;
    $company_id = $pfd_detail[0]->company_id;
    $company_title = $pfd_detail[0]->company_title;

    $Prof_detail = $con->QueryResult("select * from provident_fund_details_yearly where PFDY_emp_code='$empl_code' order by PFDY_year DESC");



    $header_array = array();
    $header_array_two = array();

    array_push($header_array, "Year", "January", " ", "February", " ", "March", " ", "April", " ", "May", " ", "June", " ", "July", " ", "August", " ", "September", " ", "October", " ", "November", " ", "December", " ", "Others", "Provident Fund Total", "Eligible Provident Fund");
    array_push($header_array_two, " ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", " ", " ", " ");

    array_unshift($Arr, $header_array, $header_array_two);

    $arr = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $month = array_combine(range(1, count($arr)), array_values($arr));

    $count = count($Prof_detail);
    $countCol = count($Arr[0]);

    $createPHPExcel = new PHPExcel();
    $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
    $rowCount = 0;
    $pfd_total = 0;

    for ($i = 0; $i < $count; $i++) {
        $j = 1;
        //Set values for the columns to be merged
        $cWorkSheet->setCellValue('A5', $Arr["0"]["0"]);
        $cWorkSheet->setCellValue('B5', $Arr["0"]["1"]);
        $cWorkSheet->setCellValue('D5', $Arr["0"]["3"]);
        $cWorkSheet->setCellValue('F5', $Arr["0"]["5"]);
        $cWorkSheet->setCellValue('H5', $Arr["0"]["7"]);
        $cWorkSheet->setCellValue('J5', $Arr["0"]["9"]);
        $cWorkSheet->setCellValue('L5', $Arr["0"]["11"]);
        $cWorkSheet->setCellValue('N5', $Arr["0"]["13"]);
        $cWorkSheet->setCellValue('P5', $Arr["0"]["15"]);
        $cWorkSheet->setCellValue('R5', $Arr["0"]["17"]);
        $cWorkSheet->setCellValue('T5', $Arr["0"]["19"]);
        $cWorkSheet->setCellValue('V5', $Arr["0"]["21"]);
        $cWorkSheet->setCellValue('X5', $Arr["0"]["23"]);
        $cWorkSheet->setCellValue('Z5', $Arr["0"]["25"]);
        $cWorkSheet->setCellValue('AA5', $Arr["0"]["26"]);
        $cWorkSheet->setCellValue('AC5', $Arr["0"]["27"]);
        //Merge cells
        $cWorkSheet->mergeCells('B5:C5');
        $cWorkSheet->mergeCells('D5:E5');
        $cWorkSheet->mergeCells('F5:G5');
        $cWorkSheet->mergeCells('H5:I5');
        $cWorkSheet->mergeCells('J5:K5');
        $cWorkSheet->mergeCells('L5:M5');
        $cWorkSheet->mergeCells('N5:O5');
        $cWorkSheet->mergeCells('P5:Q5');
        $cWorkSheet->mergeCells('R5:S5');
        $cWorkSheet->mergeCells('T5:U5');
        $cWorkSheet->mergeCells('V5:W5');
        $cWorkSheet->mergeCells('X5:Y5');
        $cWorkSheet->mergeCells('AA5:AB5');
        $cWorkSheet->mergeCells('AC5:AD5');

        $cWorkSheet->setCellValue('A6', $Arr["1"]["0"]);
        $cWorkSheet->setCellValue('B6', $Arr["1"]["1"]);
        $cWorkSheet->setCellValue('C6', $Arr["1"]["2"]);
        $cWorkSheet->setCellValue('D6', $Arr["1"]["3"]);
        $cWorkSheet->setCellValue('E6', $Arr["1"]["4"]);
        $cWorkSheet->setCellValue('F6', $Arr["1"]["5"]);
        $cWorkSheet->setCellValue('G6', $Arr["1"]["6"]);
        $cWorkSheet->setCellValue('H6', $Arr["1"]["7"]);
        $cWorkSheet->setCellValue('I6', $Arr["1"]["8"]);
        $cWorkSheet->setCellValue('J6', $Arr["1"]["9"]);
        $cWorkSheet->setCellValue('K6', $Arr["1"]["10"]);
        $cWorkSheet->setCellValue('L6', $Arr["1"]["11"]);
        $cWorkSheet->setCellValue('M6', $Arr["1"]["12"]);
        $cWorkSheet->setCellValue('N6', $Arr["1"]["13"]);
        $cWorkSheet->setCellValue('O6', $Arr["1"]["14"]);
        $cWorkSheet->setCellValue('P6', $Arr["1"]["15"]);
        $cWorkSheet->setCellValue('Q6', $Arr["1"]["16"]);
        $cWorkSheet->setCellValue('R6', $Arr["1"]["17"]);
        $cWorkSheet->setCellValue('S6', $Arr["1"]["18"]);
        $cWorkSheet->setCellValue('T6', $Arr["1"]["19"]);
        $cWorkSheet->setCellValue('U6', $Arr["1"]["20"]);
        $cWorkSheet->setCellValue('V6', $Arr["1"]["21"]);
        $cWorkSheet->setCellValue('W6', $Arr["1"]["22"]);
        $cWorkSheet->setCellValue('X6', $Arr["1"]["23"]);
        $cWorkSheet->setCellValue('Y6', $Arr["1"]["24"]);
        $cWorkSheet->setCellValue('Z6', $Arr["1"]["25"]);
        $cWorkSheet->setCellValue('AA6', $Arr["1"]["26"]);
        $cWorkSheet->setCellValue('AC6', $Arr["1"]["27"]);

        //Merged cell style
        $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
        $style_two = array('alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
        $styleArray = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => '000000'),
                'size' => 14,
                'name' => 'Verdana'));

        $styleArray1 = array(
            'font' => array(
                'bold' => true,
                'color' => array('rgb' => '000000'),
                'size' => 10,
                'name' => 'Verdana'));

        $cWorkSheet->getStyle("G1:G3")->applyFromArray($styleArray);
        $cWorkSheet->getStyle("A5:AD5")->applyFromArray($styleArray1);
        $cWorkSheet->getStyle("B6:Y6")->applyFromArray($styleArray1);
        $cWorkSheet->getStyle("B5:C5")->applyFromArray($style);
        $cWorkSheet->getStyle("D5:E5")->applyFromArray($style);
        $cWorkSheet->getStyle('F5:G5')->applyFromArray($style);
        $cWorkSheet->getStyle('H5:I5')->applyFromArray($style);
        $cWorkSheet->getStyle('J5:K5')->applyFromArray($style);
        $cWorkSheet->getStyle('L5:M5')->applyFromArray($style);
        $cWorkSheet->getStyle('N5:O5')->applyFromArray($style);
        $cWorkSheet->getStyle('P5:Q5')->applyFromArray($style);
        $cWorkSheet->getStyle('R5:S5')->applyFromArray($style);
        $cWorkSheet->getStyle('T5:U5')->applyFromArray($style);
        $cWorkSheet->getStyle('V5:W5')->applyFromArray($style);
        $cWorkSheet->getStyle('X5:Y5')->applyFromArray($style);

        //Write data to excel
        $cWorkSheet->setCellValueByColumnAndRow(6, 1, "$company_title");
        $cWorkSheet->setCellValueByColumnAndRow(6, 2, "Employee Name: $name");
        $cWorkSheet->setCellValueByColumnAndRow(6, 3, "Date: $formatted_today");
        $cWorkSheet->setCellValueByColumnAndRow(6, 4, "NB: EC = Employee Contribution, CC = Company Contribution");

        $cWorkSheet->setCellValueByColumnAndRow(0, $i + 7, $Prof_detail["$i"]->PFDY_year);
        //$Prof_detail
        foreach ($Prof_detail as $pd) {
            $year = $pd->PFDY_year;
            $empl_code = $pd->PFDY_emp_code;
//          $j++;
//            if ($pd->pfd_year == $Prof_detail[$i]->PFDY_year) {
//                $pf_yr_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname,e.company_id,c.company_title FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code LEFT JOIN company c on c.company_id=e.company_id WHERE p.pfd_emp_code='$empl_code' AND p.pfd_year='$pd->pfd_year'";
//                $pfd_year_detail = $con->QueryResult($pf_yr_query);
            foreach ($month as $key => $val) {
                $pf_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code WHERE p.pfd_year='$year' AND p.pfd_emp_code='$empl_code' AND p.pfd_month='$key' order by p.pfd_month ASC"; // AND p.pfd_company_id='$company'
                $pfd_detail = $con->QueryResult($pf_query);
                if (count($pfd_detail) >= 1) {
                    foreach ($pfd_detail as $rb) {
//                        if ($key == $pd->pfd_month) {
                        $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, $rb->pfd_emp_amount);
                        $j++;
                        $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, $rb->pfd_com_amount);
                        $j++;
                    }
                } else {
                    $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, ' ');
                    $j++;
                    $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, ' ');
                    $j++;
                }
            }
        }
        $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, $Prof_detail["$i"]->PFDY_pfd_others);
        $j++;
        $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, $Prof_detail["$i"]->PFDY_pfd_total);
        $j++;
        $cWorkSheet->setCellValueByColumnAndRow($j, $i + 7, $Prof_detail["$i"]->PFDY_eligible_total);
        $pfd_total = $pfd_total + $Prof_detail["$i"]->PFDY_eligible_total;
//        $j++;
//     } 
//     $rowCount++;
    }

    $cWorkSheet->setCellValueByColumnAndRow(0, $i + 8, "Grand Total");
    $cWorkSheet->setCellValueByColumnAndRow($j, $i + 8, $pfd_total);
    $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
    $filename = $company_id . rand(0, 9999999) . "Emp_Prov_Fund.xlsx";
    $objWriter->save("$filename");
    header("location:$filename");
//    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<style type="text/css">
    .k-edit,.k-delete,.k-add {
        margin-top: -2px !important;
    }
</style>
<form method="post">
    <div class="pull-right">
        <input type="submit" class="k-button" name="generate_excel" value="Generate Report">
    </div>
</form>
<div class="clearfix"></div>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head">
        <h6 class="heading" style="color:whitesmoke;">Employee Provident Fund Details</h6>
    </div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <div style="font-weight:bold;font-size: 16px;font-family: calibri;">
            Employee Code :&nbsp;&nbsp;<?php echo $pfd_detail[0]->emp_code; ?><br />
            Employee Name : &nbsp;&nbsp;<?php echo $pfd_detail[0]->emp_firstname; ?><br /><br />
        </div>
        <div id="example" class="k-content" style="">
            <table id="grid">
                <!--style="table-layout: fixed; "-->
                <colgroup>
                    <col style="width:110px" />
                    <?php for ($l = 1; $l <= 24; $l++) { ?>
                        <col style="width:70px;" />
                    <?php } ?>
                    <col style="width:120px" />
                    <col style="width:120px" />
                    <col style="width:160px" />
                    <!--style="pointer-events: none;width:90px; word-wrap: break-word; white-space: pre-wrap;"-->
                </colgroup>
                <thead>
                    <tr>
                        <td style="border-bottom:1px solid gray;width: 110px;" >Year </td>
                        <?php foreach ($month as $m) { ?>
                            <td style="border-bottom: 1px solid gray;text-align:center; width:140px;" colspan="2"><?php echo $m; ?></td>
                            <!--<th style="border-bottom: 1px solid gray;word-wrap: break-word; white-space: pre-wrap;text-align:center;" colspan="2"></th>-->
                        <?php } ?>
                        <td style="border-bottom:1px solid gray;width: 120px;" >Others </td>
                        <td style="border-bottom:1px solid gray;width: 160px;" >PF_Total </td>
                        <td style="border-bottom:1px solid gray;width: 160px;" >Eligible PF Amount</td>
                    </tr>
                    <tr>
                        <th data-field='PFDY_year' style="border-bottom: 1px solid gray;width: 110px;"></th>
                        <?php foreach ($month as $m) { ?>
                            <th data-field="EE_<?php echo $m; ?>" style="border-bottom: 1px solid gray;text-align:center;width: 70px;">EE</th>
                            <th data-field="EC_<?php echo $m; ?>" style="border-bottom: 1px solid gray;text-align:center;width: 70px;">EC</th>
    <!--                    <th data-field="ee" style="border-bottom: 1px solid gray;word-wrap: break-word; white-space: pre-wrap;text-align:center;">EE</th>
                            <th data-field="ec" style="border-bottom: 1px solid gray;word-wrap: break-word; white-space: pre-wrap;text-align:center;">EC</th>-->
                        <?php } ?>
                        <th data-field="PFDY_pfd_others" style="border-bottom: 1px solid gray;width: 120px;"></th>
                        <th data-field="PFDY_pfd_total" style="border-bottom: 1px solid gray;width: 160px;"></th>
                        <th data-field="PFDY_eligible_total" style="border-bottom: 1px solid gray;width: 160px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (count($emp_year_pf) >= 1) {
                        foreach ($emp_year_pf as $ey) {
                            ?>
                            <tr>
                                <td style="width: 110px;"><?php echo $ey->PFDY_year; ?></td>
                                <?php
                                $year = $ey->PFDY_year;
                                foreach ($month as $key => $val) {
                                    $pf_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code WHERE p.pfd_year='$year' AND p.pfd_emp_code='$empl_code' AND p.pfd_month='$key' order by p.pfd_month ASC"; // AND p.pfd_company_id='$company'
                                    $pfd_detail = $con->QueryResult($pf_query);
                                    if (count($pfd_detail) >= 1) {
                                        foreach ($pfd_detail as $rb) {
                                            echo '<td style="width: 70px;">' . $rb->pfd_emp_amount . '</td>';
                                            echo '<td style="width: 70px;">' . $rb->pfd_com_amount . '</td>';
                                        }
                                    } else {
                                        echo '<td style="width: 70px;"> &nbsp;</td>';
                                        echo '<td style="width: 70px;"> &nbsp;</td>';
                                    }
                                }
                                //========================================================================                              
//                                foreach ($pfd_detail as $rb) {
//                                    if ($rb->pfd_year == $ey->PFDY_year) {
//                                        foreach ($month as $key => $val) {
//                                            if ($key == $rb->pfd_month) {
//                                                echo '<td style="width: 70px;">' . $rb->pfd_emp_amount . '</td>';
//                                                echo '<td style="width: 70px;">' . $rb->pfd_com_amount . '</td>';
//                                                } else {
//                                                echo '<td style="width: 70px;"> &nbsp;&nbsp;&nbsp;</td>';
//                                                echo '<td style="width: 70px;"> &nbsp;&nbsp;&nbsp;</td>';
//                                            }
//                                        }
//                                    }
//                                }
                                ?>
                                <td style="width: 120px;"><?php echo $ey->PFDY_pfd_others; ?></td>
                                <td style="width: 160px;"><?php echo $ey->PFDY_pfd_total; ?></td>
                                <td style="width: 160px;"><?php echo $ey->PFDY_eligible_total; ?></td>
                            </tr>
                            <?php
                            $grand_total = $grand_total + $ey->PFDY_eligible_total;
                        }
                    }
                    ?> 
                </tbody>
            </table>
            <script>
                $(document).ready(function() {
                    $("#grid").kendoGrid({
                        pageable: {
                            refresh: true,
                            input: true,
                            numeric: false,
                            pageSize: 20,
                            pageSizes: true,
                            pageSizes: [20, 40, 60, 80, 120],
                        },
                        sortable: true,
                        groupable: true,
                        filterable: true,
                        resizable: true,
                        scrollable: true
                    });
                });
            </script>
        </div>
        <div style="height: 30px; background-color: rgb(220,240,240); font-size: 14px;font-weight: bold;font-family: verdana;">
            Grand Total : &nbsp;&nbsp;&nbsp;<?php echo number_format($grand_total, 2, '.', ','); ?>
        </div>
    </div>
</div>
<?php include '../view_layout/footer_view.php'; ?>