<?php
/* Autjor : Asma
 * Date : 21 March 15
 */
//Importing class library
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
session_start();

//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
$emp_code = '';

error_reporting(0);

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if ($con->hasPermissionView($permission_id) != "yes") {
    $con->redirect("../dashboard/index.php");
}

//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$err = "";
$msg = '';
$provident_fund = '';
$absent_deduction = '';
$leave_dedcution = '';
$tax = '';
$year = '';
$month = '';
$over_time = '';
$advance = '';
$pf_loan = '';

if (isset($_GET['empl_code'])) {
    $empl_code = base64_decode($_GET['empl_code']);
    $get_detail = $con->QueryResult("SELECT ps.*,e.emp_firstname,e.emp_lastname,h.* FROM payroll_salary_header h"
            . " left join payroll_employee_salary ps on ps.PES_PSH_id=h.PSH_id"
            . " left join tmp_employee e on ps.PES_employee_code=e.emp_code"
            . " WHERE ps.PES_employee_code='$empl_code'");
    $get_all_com = $con->SelectAll("payroll_salary_header");
}

/** Get existing deduction information * */
if (isset($_GET["year"])) {
    $year = $_GET["year"];
}
if (isset($_GET["month"])) {
    $month = $_GET["month"];
}
if (isset($_GET["company_id"])) {
    $company_id = $_GET['company_id'];
}

$quer_lock = "SELECT slm_is_locked from salary_lock_meta where slm_year='$year' AND slm_month='$month' AND slm_company_id =$company_id";
$check_lock = $con->QueryResult($quer_lock);

if (isset($_POST["btnSave"])) {
    extract($_POST);
    $quer_header = $con->QueryResult("SELECT * FROM payroll_salary_header");

    foreach ($_POST as $key => $val) {
        $sal_part = explode("_", $key);
        foreach ($quer_header as $PSH) {
            if (isset($sal_part[2]) && $sal_part[2] == $PSH->PSH_id) {

                $check_exist_headr = $con->SelectAllByCondition("payroll", "payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month' AND PES_PSH_id='$sal_part[2]'");
                if (count($check_exist_headr) > 0) {

                    $payroll_id = $check_exist_headr{0}->payroll_id;
                    $payroll_salary_original = $check_exist_headr{0}->payroll_salary_original;

                    $update_payment_array = array(
                        "payroll_id" => $payroll_id,
                        "payroll_emp_code" => $empl_code,
                        "payroll_salary_year" => $year,
                        "payroll_salary_month" => $month,
                        "payroll_salary_finalized" => $val
                    );
                    $update_result = $con->update("payroll", $update_payment_array);
                    if ($update_result == 1) {
                        $msg = 'Payment information is updated successfully.';
                    } else {
                        $err = "Payment information update failed.";
                    }
                } else {
                    $insert_salary_array = array(
                        "payroll_emp_code" => $empl_code,
                        "payroll_salary_year" => $year,
                        "payroll_salary_month" => $month,
                        "payroll_salary_original" => $val,
                        "payroll_salary_finalized" => $val,
                        "PES_PSH_id" => $PSH->PSH_id);
                    $insertion_result = $con->insert("payroll", $insert_salary_array);

                    if ($insertion_result == 1) {
                        $msg = "Payment information is successfully updated..";
                    } else {
                        $err = "Payment information update failed.";
                    }
                }
            }
        }
        /*
         * Grab deduction values from form
         * Update the payroll_additional table with the values
         * In this update only the finalized field will be updated.
         * If original field were empty, then it will remain empty
         */
        //Find payroll additional ID
        $existing_data = array();
        $existing_data = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$empl_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year'");
        if (count($existing_data) > 0) {
            $payroll_additional_id = $existing_data{0}->payroll_additional_id;
            $update_fixed_elements = array(
                "payroll_additional_id" => $payroll_additional_id,
                "payroll_additional_tax_finalized" => $tax,
                "payroll_additional_advance_finalized" => $advance,
                "payroll_additional_pf_finalized" => $provident_fund,
                "pa_absent_deduction_finalized" => $absent_deduction,
                "pa_leave_deduction_finalized" => $leave_deduction,
                "payroll_additional_ot_finalized" => $over_time,
                "pa_pf_loan_finalized" => $pf_loan
            );

            if ($con->update("payroll_additional", $update_fixed_elements) == 1) {
                $msg = "Payment information succesfully updated.";
            } else {
                $err = "Payment information update failed.";
            }
        }
    }

    /* update payroll_aditional table pa_net_salary_finalized  
     */
    $G_total = mysqli_real_escape_string($open, $_POST["grand_total"]);
    $update_payadd = $con->QueryResultForNormalEntry("UPDATE payroll_additional SET pa_net_salary_finalized = '$G_total' WHERE payroll_additional_emp_code='$empl_code' AND payroll_additional_salary_year = '$year' AND payroll_additional_salary_month='$month'", $open);

    if ($update_payadd == 1) {
        $msg = "Payroll additional data succesfully updated.";
    } else {
        $err = "Payroll additional data update failed.";
    }
}

$existing_deduction = array();
$existing_deducttion = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$empl_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year'");
if (count($existing_deducttion) > 0) {
    $provident_fund = $existing_deducttion{0}->payroll_additional_pf_finalized;
    $tax = $existing_deducttion{0}->payroll_additional_tax_finalized;
    $advance = $existing_deducttion{0}->payroll_additional_advance_finalized;
    $absent_deduction = $existing_deducttion{0}->pa_absent_deduction_finalized;
    $leave_deduction = $existing_deducttion{0}->pa_leave_deduction_finalized;
    $over_time = $existing_deducttion{0}->payroll_additional_ot_finalized;
    $pf_loan = $existing_deducttion{0}->pa_pf_loan_finalized;
}


/*
 * Generate excel report
 */

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

    $sal_headers = $con->QueryResult("select * from payroll_salary_header"); //where PFDY_emp_code='$empl_code' order by PFDY_year DESC


    $count = count($sal_headers);
    $createPHPExcel = new PHPExcel();
    $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
    $rowCount = 0;
    $pfd_total = 0;


    $cWorkSheet->setCellValue('A5', "Fixed Salary Component");
    $cWorkSheet->setCellValue('E5', "Addition Salary Component");
    $cWorkSheet->setCellValue('I5', "Deducted Salary Component");

    //Merge cells
    $cWorkSheet->mergeCells('A5:C5');
    $cWorkSheet->mergeCells('E5:G5');
    $cWorkSheet->mergeCells('I5:K5');
    $cWorkSheet->mergeCells('D1:G1');

    $cWorkSheet->mergeCells('D1:G1');

    //Merged cell style
    $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
    $style_two = array('alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
    $styleArray = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '000000'),
            'size' => 12,
            'name' => 'Verdana',
            'align' => 'center'));

    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '000000'),
            'size' => 10,
            'name' => 'Verdana',
            'align' => 'left'));

    $cWorkSheet->getStyle("D1:H1")->applyFromArray($styleArray);
    $cWorkSheet->getStyle("D2:D3")->applyFromArray($styleArray1);
    $cWorkSheet->getStyle("A5:J5")->applyFromArray($styleArray1);

    //Write data to excel
    $cWorkSheet->setCellValueByColumnAndRow(3, 1, "$company_title");
    $cWorkSheet->setCellValueByColumnAndRow(3, 2, "Employee Name: $name");
    $cWorkSheet->setCellValueByColumnAndRow(3, 3, "Date: $formatted_today");

    $j = 1;
    $i = 0;
    $f = 0;
    $a = 0;
    $s = 0;
    $row = 0;
    //Fixed salary Header
    foreach ($get_all_com as $SD) {
        if ($SD->PSH_is_optional == "no") {
            $salaries = array();
            $fix_amount = '';
            $PSH_id = $SD->PSH_id;
            $salaries = $con->SelectAllByCondition("payroll", "PES_PSH_id='$PSH_id' AND payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
            $fix_amount = $salaries{0}->payroll_salary_finalized;

            $cWorkSheet->setCellValueByColumnAndRow(0, $row + 7, $SD->PSH_header_title);
            $cWorkSheet->setCellValueByColumnAndRow(1, $row + 7, round($fix_amount));
            $row++;
            if ($SD->PSH_header_title == "Gross Salary") {
                $tot_fix_sal += $fix_amount;
            }
            $f++;
        }
    }
    $row = 0;
    //=================== Add  ===================
    foreach ($get_all_com as $SD) {
        if ($SD->PSH_is_optional == "yes" && $SD->PSH_display_on == "add") {
            $salaries1 = array();
            $add_amount = '';
            $PSH_id = $SD->PSH_id;
            $salaries1 = $con->SelectAllByCondition("payroll", "PES_PSH_id='$PSH_id' AND payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
            $add_amount = $salaries1{0}->payroll_salary_finalized;

            $cWorkSheet->setCellValueByColumnAndRow(4, $row + 7, $SD->PSH_header_title);
            $cWorkSheet->setCellValueByColumnAndRow(5, $row + 7, round($add_amount));
            $row++;

            $tot_add_sal += $add_amount;
            $a++;
        }
    }
    $tot_add_sal = $tot_add_sal + $over_time;
    $cWorkSheet->setCellValueByColumnAndRow(4, $row + 7, "Over Time");
    $cWorkSheet->setCellValueByColumnAndRow(5, $row + 7, round($over_time));
    $row++;
    $row = 0;

    //=================== Sub  ===================  
    foreach ($get_all_com as $SD) {
        if ($SD->PSH_is_optional == "yes" && $SD->PSH_display_on == "deduct") {
            $salaries2 = array();
            $sub_amount = '';
            $PSH_id = $SD->PSH_id;
            $salaries2 = $con->SelectAllByCondition("payroll", "PES_PSH_id='$PSH_id' AND payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
            $sub_amount = $salaries2{0}->payroll_salary_finalized;

            $cWorkSheet->setCellValueByColumnAndRow(8, $row + 7, $SD->PSH_header_title);
            $cWorkSheet->setCellValueByColumnAndRow(9, $row + 7, round($sub_amount));
            $row++;
            $tot_sub_sal += $sub_amount;
            $s++;
        }
    }
    $tot_sub_sal = $tot_sub_sal + $tax + $provident_fund + $absent_deduction + $leave_deduction + $advance;

    $grand_total = ($tot_fix_sal + $tot_add_sal) - $tot_sub_sal;
    $net_pay = number_format(round($grand_total), 2, '.', ',');

    $cWorkSheet->setCellValueByColumnAndRow(8, $row + 7, "Tax");
    $cWorkSheet->setCellValueByColumnAndRow(9, $row + 7, round($tax));
    $row++;
    $cWorkSheet->setCellValueByColumnAndRow(8, $row + 7, "Provident Fund");
    $cWorkSheet->setCellValueByColumnAndRow(9, $row + 7, round($provident_fund));
    $row++;
    $cWorkSheet->setCellValueByColumnAndRow(8, $row + 7, "Absent Deduction");
    $cWorkSheet->setCellValueByColumnAndRow(9, $row + 7, round($absent_deduction));
    $row++;
    $cWorkSheet->setCellValueByColumnAndRow(8, $row + 7, "Leave Deduction");
    $cWorkSheet->setCellValueByColumnAndRow(9, $row + 7, round($leave_deduction));
    $row++;
    $cWorkSheet->setCellValueByColumnAndRow(8, $row + 7, "Advance");
    $cWorkSheet->setCellValueByColumnAndRow(9, $row + 7, round($advance));
    $row++;

    $mx = max($f, $a, $s);

    $cWorkSheet->setCellValueByColumnAndRow(0, $mx + 9, "Total Fixed");
    $cWorkSheet->setCellValueByColumnAndRow(1, $mx + 9, round($tot_fix_sal));
    $cWorkSheet->setCellValueByColumnAndRow(4, $mx + 9, "Total Addition");
    $cWorkSheet->setCellValueByColumnAndRow(5, $mx + 9, round($tot_add_sal));
    $cWorkSheet->setCellValueByColumnAndRow(8, $mx + 9, "Total Deduction");
    $cWorkSheet->setCellValueByColumnAndRow(9, $mx + 9, round($tot_sub_sal));

    $cWorkSheet->setCellValueByColumnAndRow(0, $mx + 11, "Net Salary Payable");
    $cWorkSheet->setCellValueByColumnAndRow(3, $mx + 11, $net_pay);

    $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
    $filename = $company_id . rand(0, 9999999) . "Emp_Payment_Slip.xlsx";
    $objWriter->save("$filename");
    header("location:$filename");
}
?>
<?php include '../view_layout/header_view.php'; ?>
<form method="post">
    <div class="pull-right">
        <input type="submit" class="k-button" name="generate_excel" value="Generate Report">
    </div>
</form>
<div class="clearfix"></div>
<br />

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head">
        <h6 class="heading" style="color:whitesmoke;">Individual Salary Report</h6>
    </div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <form method="post">      
            <!-- Group -->
            <div class="col-md-2">
                <label class="control-label" for="empcode" >Employee Name:</label><br />
            </div>
            <div class="col-md-3" style="float: left;">
                <input style="width: 80%" id="empl_name" name="empl_name" value="<?php echo $get_detail[0]->emp_firstname . ' ' . $get_detail[0]->emp_lastname; ?>" />
            </div>
            <div class="clearfix"></div><br />
            <div class="col-md-2">
                <label class="control-label" for="empcode" >Employee Code:</label><br />
            </div>
            <div class="col-md-3" style="float: left;">
                <input style="width: 80%" id="empl_code" name="empl_code" value="<?php echo $get_detail[0]->PES_employee_code; ?>" />
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-12">
                <div class="col-md-4 bdiv" style="border: 1px solid black;">
                    <label class="control-label" for="empcode" style="font-weight:bold;font-family:candara;font-size:18px;margin:3px 0px 0px 65px;">Fixed Salary Component:</label><br />
                    <hr style="width: 100%">
                    <?php
                    $tot_fix_sal = 0;
                    foreach ($get_all_com as $SD) {
                        if ($SD->PSH_is_optional == "no") {

                            /*
                             * Salary amount to be fetched from main payroll table
                             * After process for selected month in the main salary list page.
                             * :: run a query on payroll table based on key-
                             * emp_code, year, month, salary heade id
                             * information to be fetched- salary amount 
                             */

                            $salaries = array();
                            $pay_amount = '';
                            $PSH_id = $SD->PSH_id;
                            $salaries = $con->SelectAllByCondition("payroll", "PES_PSH_id='$PSH_id' AND payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
                            $pay_amount = $salaries{0}->payroll_salary_finalized;

                            echo '<div class="col-md-6">';
                            echo $SD->PSH_header_title . " : </div><div class='col-md-6'><b>" . $pay_amount;
                            echo '</b></div><br /><br />';
                            if ($SD->PSH_header_title == "Gross Salary") {
                                $tot_fix_sal = $pay_amount;
                            }
                        }
                    }
                    ?>
                    <hr style="width: 100%">
                    <label class="control-label" for="gros" style="font-weight:bold; font-size:16px;">Total Fixed:&nbsp;&nbsp;&nbsp; <?php echo number_format(round($tot_fix_sal), 2, '.', ','); ?></label><br /> 
                </div>
                <div class="col-md-4 bdiv" style="border: 1px solid black;">
                    <label class="control-label" for="empcode" style="font-weight:bold;font-family:candara;font-size:18px;margin:3px 0px 0px 65px;">Addition Salary Component:</label><br />
                    <hr style="width: 100%">
                    <?php
                    $tot_add_sal = 0;
                    foreach ($get_all_com as $SD) {
                        if ($SD->PSH_is_optional == "yes" && $SD->PSH_display_on == "add") {
                            /*
                             * Salary amount to be fetched from main payroll table
                             * After process for selected month in the main salary list page.
                             * :: run a query on payroll table based on key-
                             * emp_code, year, month, salary heade id
                             * information to be fetched- salary amount 
                             */

                            $salaries = array();
                            $pay_amount = '';
                            $PSH_id = $SD->PSH_id;
                            $salaries = $con->SelectAllByCondition("payroll", "PES_PSH_id='$PSH_id' AND payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
                            $pay_amount = $salaries{0}->payroll_salary_finalized;

                            echo '<div class="col-md-6">';
                            echo $SD->PSH_header_title . " : </div><div class='col-md-6'>";
                            if ($check_lock[0]->slm_is_locked == 'yes') {
                                echo $pay_amount;
                            } else {
                                echo "<input style='width: 80%' name='fix_sal_" . $SD->PSH_id . "' id='fix_sal_" . $SD->PSH_id . "' value='" . $pay_amount . "' />";
                            }
                            echo '</div><br /><br />';
                            $tot_add_sal += $pay_amount;
                        }
                    }
                    $tot_add_sal = $tot_add_sal + $over_time;
                    ?>
                    <div class="col-md-6"> Over Time : </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="over_time" id="over_time" value="<?php // echo $over_time;                  ?>" />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo round($over_time);
                        } else {
                            ?> 
                            <input style="width: 80%" name="over_time" id="over_time" value="<?php echo round($over_time); ?>" />
                        <?php } ?>   
                    </div>
                    <br />
                    <hr style="width: 100%">
                    <label class="control-label" for="gros" style="font-weight:bold;font-size:16px;">Total Addition: &nbsp;&nbsp;&nbsp;<?php echo number_format(round($tot_add_sal), 2, '.', ','); ?></label><br /> 
                </div>
                <div class="col-md-4 bdiv" style="border: 1px solid black;">
                    <label class="control-label" for="empcode" style="font-weight:bold;font-family:candara;font-size:18px;margin:3px 0px 0px 65px;">Deducted Salary Component:</label><br />
                    <hr style="width: 100%">

                    <?php
                    $tot_sub_sal = 0;
                    foreach ($get_all_com as $SD) {
                        if ($SD->PSH_is_optional == "yes" && $SD->PSH_display_on == "deduct") {
                            /*
                             * Salary amount to be fetched from main payroll table
                             * After process for selected month in the main salary list page.
                             * :: run a query on payroll table based on key-
                             * emp_code, year, month, salary heade id
                             * information to be fetched- salary amount 
                             */
                            $salaries = array();
                            $pay_amount = '';
                            $PSH_id = $SD->PSH_id;
                            $salaries = $con->SelectAllByCondition("payroll", "PES_PSH_id='$PSH_id' AND payroll_emp_code='$empl_code' AND payroll_salary_year='$year' AND payroll_salary_month='$month'");
                            $pay_amount = $salaries{0}->payroll_salary_finalized;
                            echo '<div class="col-md-6">';
                            echo $SD->PSH_header_title . " : </div><div class='col-md-6'>";
                            if ($check_lock[0]->slm_is_locked == 'yes') {
                                echo $pay_amount;
                            } else {
                                echo "<input style='width: 80%' name='fix_sal_" . $SD->PSH_id . "' value='" . $pay_amount . "' />";
                            }
                            echo '</div><br /><br />';
                            $tot_sub_sal += $pay_amount;
                            setlocale(LC_MONETARY, 'en_US');
                        }
                    }
                    $tot_sub_sal = $tot_sub_sal + $tax + $provident_fund + $absent_deduction + $leave_deduction + $advance;
                    if ($check_lock[0]->slm_is_locked == 'yes') {
                        
                    }
                    ?> 
                    <div class="col-md-6"> Tax : </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="tax" id="tax" value="<?php // echo $tax;                 ?>" />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo round($tax);
                        } else {
                            ?>
                            <input style="width: 80%" name="tax" id="tax" value="<?php echo round($tax); ?>" />
                        <?php } ?> 
                    </div>
                    <br/>
                    <br/>
                    <div class="col-md-6">  Provident Fund : </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="provident_fund" id="provident_fund" value="<?php // echo $provident_fund;                   ?>" />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo $provident_fund;
                        } else {
                            ?>
                            <input style="width: 80%" name="provident_fund" id="provident_fund" value="<?php echo $provident_fund; ?>" />
                        <?php } ?>    
                    </div>
                    <br/>
                    <br/>
                    <div class="col-md-6">  Absent Deduction : </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="absent_deduction" id="absent_deduction" value="<?php // echo $absent_deduction;                   ?>" />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo round($absent_deduction);
                        } else {
                            ?> 
                            <input style="width: 80%" name="absent_deduction" id="absent_deduction" value="<?php echo round($absent_deduction); ?>" />
                        <?php } ?>   
                    </div>
                    <br />
                    <br />
                    <div class="col-md-6">  Leave Deduction : </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="leave_deduction" id="leave_deduction" value="<?php // echo $leave_deduction;                   ?>" />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo $leave_deduction;
                        } else {
                            ?> 
                            <input style="width: 80%" name="leave_deduction" id="leave_deduction" value="<?php echo $leave_deduction; ?>" />
                        <?php } ?>       
                    </div>
                    <br/>
                    <br />
                    <div class="col-md-6">  Advance: </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="advance" id="advance" value="<?php // echo $advance;                   ?>"  disabled='disabled' />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo $advance;
                        } else {
                            ?> 
                            <?php echo $advance; ?>
                        <?php } ?>     
                    </div> 
                    <br />
                    <br />

                    <div class="col-md-6">  PF Loan: </div>
                    <!--<div class="col-md-6"><input style="width: 80%" name="advance" id="advance" value="<?php // echo $advance;                   ?>"  disabled='disabled' />-->
                    <div class="col-md-6">
                        <?php
                        if ($check_lock[0]->slm_is_locked == 'yes') {
                            echo $pf_loan;
                        } else {
                            ?> 
                            <input style="width: 80%" name="pf_loan" id="pf_loan" value="<?php echo $pf_loan; ?>"/>
                        <?php } ?>     
                    </div>
                    <br />
                    <br />

                    <br />
                    <hr style="width: 100%">
                    <label class="control-label" for="gros" style="font-weight:bold;font-size:16px;">Total Deduction: &nbsp; <?php echo number_format(round($tot_sub_sal), 2, '.', ','); // money_format('%(#10n', $tot_sub_sal);                                        ?></label><br />
                </div>

                <script type="text/javascript">
                    var maxHeight = 0;
                    $('.bdiv').each(function() {
                        maxHeight = Math.max(maxHeight, $(this).height());
                    }).height(maxHeight + 10);
                </script>
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-12" style="height: 30px; font-size: 14px;font-weight: bold;font-family: verdana;">
                Net Salary Payable : &nbsp;&nbsp;&nbsp;<?php
                $grand_total = ($tot_fix_sal + $tot_add_sal) - $tot_sub_sal;
                echo number_format(round($grand_total), 2, '.', ',');
                /*
                 * Find the identity
                 * Update net salary
                 */
                $existing_data = $con->SelectAllByCondition("payroll_additional", "payroll_additional_emp_code='$empl_code' AND payroll_additional_salary_month='$month' AND payroll_additional_salary_year='$year'");
                if (count($existing_data) > 0) {
                    $pa_id = $existing_data{0}->payroll_additional_id;
                    $update_array = array(
                        "payroll_additional_id" => $pa_id,
                        "pa_net_salary_finalized" => $grand_total
                    );
                    $con->update("payroll_additional", $update_array);
                }
                ?>
                <input type="hidden" name="grand_total" value="<?php echo $grand_total; ?>">
            </div>
            <div class="clearfix"></div>
            <div class="col-md-4">
                <br/><br/>
                <?php if ($check_lock[0]->slm_is_locked != 'yes') { ?>
                    <input type="submit" class="k-button" name="btnSave" value="Save Salary Settings">
                <?php } ?> 
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
</div>   
<?php
include '../view_layout/footer_view.php';

function get_bd_money_format($amount) {
    $output_string = '';
    $fraction = '';
    $tokens = explode('.', $amount);
    $number = $tokens [0];
    if (count($tokens) > 1) {
        $fraction = (double) ('0.' . $tokens[1]);
        $fraction = $fraction * 100;
        $fraction = round($fraction, 0);
        $fraction = '.' . $fraction;
    }

    $number = $number . '';
    $spl = str_split($number);
    $lpcount = count($spl);
    $rem = $lpcount - 3;
    '';

    if ($lpcount % 2 == 0) {
        for ($i = 0; $i <= $lpcount - 1; $i++) {

            if ($i % 2 != 0 && $i != 0 && $i != $lpcount - 1) {
                $output_string .= ",";
            }
            $output_string .= $spl[$i];
        }
    }

    if ($lpcount % 2 != 0) {
        for ($i = 0; $i <= $lpcount - 1; $i++) {
            if ($i % 2 == 0 && $i != 0 && $i != $lpcount - 1) {
                $output_string .= ",";
            }
            $output_string .= $spl[$i];
        }
    }
    return $output_string . $fraction;
}
?>