<?php
session_start();

/*
 * Author: Rajan Hossain
 * Page: Payslip
 * Importing class library
 * Call main class 
 * Connection String
 */

include ('../../config/class.config.php');
$con = new Config();
$open = $con->open();

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Ctach employee code
if (isset($_POST["emp_code"])) {
    $emp_code = $_POST["emp_code"];
    $_SESSION["emp_code"] = $emp_code;
}

/*
 * create session
 * Data exists in page reload
 */

$emp_code = $_SESSION["emp_code"];

//Fetch employee id
$employees = $con->SelectAllByCondition("employee", " emp_code='$emp_code'");
if (count($employees) > 0) {
    foreach ($employees as $employee) {
        $emp_id = $employee->emp_id;
        $emp_firstname = $employee->emp_firstname;
        $emp_lastname = $employee->emp_lastname;
        $emp_email = $employee->emp_email;
        $emp_designation = $employee->emp_designation;
        $emp_department = $employee->emp_department;
        $emp_subsection = $employee->emp_subsection;
        $emp_dateofjoin = $employee->emp_dateofjoin;
        $emp_staff_grade = $employee->emp_staff_grade;
        $emp_gross_salary = $employee->emp_gross_salary;
        $total_earning = $emp_gross_salary;
        $emp_location = $employee->emp_location;
        $emp_gender = $employee->emp_gender;
        $emp_prop_confirmation_date = $employee->emp_prop_confirmation_date;
        $emp_dateofbirth = $employee->emp_dateofbirth;
        $emp_blood_group = $employee->emp_blood_group;
        $emp_contact_number = $employee->emp_contact_number;
        $emp_resignation_date = $employee->emp_resignation_date;
        $emp_replacement_of = $employee->emp_replacement_of;
        $emp_notes_salary_hub = $employee->emp_notes_salary_hub;
        $emp_bank_title = $employee->emp_bank_title;
        $emp_remarks = $employee->emp_remarks;
        $emp_photo = $employee->emp_photo;
        $emp_marital_status = $employee->emp_marital_status;
        $emp_city = $employee->emp_city;
        $emp_contact_number_2 = $employee->emp_contact_number_2;
        $emp_address = $employee->emp_address;
        $emp_account_number = $employee->emp_account_number;
        $emp_basic = $employee->emp_basic;
        $emp_hra = $employee->emp_hra;
        $emp_medical = $employee->emp_medical;
    }
}

//Fetch salary data
$salaries = $con->SelectAllByCondition("salary", " emp_id='$emp_id'");
if (count($salaries) > 0) {
    foreach ($salaries as $salary) {
        $calender_days = $salary->calender_days;
        $attn_days = $salary->attn_days;
        $weekly_off = $salary->weekly_off;
        $holiday = $salary->holiday;
        $casual_leave = $salary->casual_leave;
        $earned_leave = $salary->earned_leave;
        $sick_leave = $salary->sick_leave;
        $absent_days = $salary->absent_days;
        $overtime_hours = $salary->overtime_hours;
        $overtime_earning = $salary->overtime_earning;
        $bonus_amount = $salary->bonus_amount;
        $transport_allowance = $salary->transport_allowance;
        $others = $salary->others;
        $total_earning = $salary->total_earning;
        $absent_deduction = $salary->absent_deduction;
        $pf_subscription = $salary->pf_subscription;
        $income_tax = $salary->income_tax;
        $loan = $salary->loan;
        $others_category = $salary->others_category;
        $total_deduction = $salary->total_deduction;
        $net_payble = $salary->net_payble;
    }
}

if (@$_GET['action'] == 'pdf') {
    include("MPDF/mpdf.php");
    $html .= '<div class="widget" style="background-color: white;">
    <table style="width:100%">
        <tr>
            <td style="width:50%">
                Name: <span>' . $emp_firstname . '  ' . $emp_lastname . '</span>
            </td>
            <td style="width:50%">
                Date: <span>' . date('d-m-Y') . '</span>
            </td>
        </tr>
        <tr>
            <td style="width:50%">
                Designation: <span> ' . $emp_designation . '</span>
            </td>
            <td>Bank Account: 
                <span> ' . $emp_account_number . '</span>
            </td>
        </tr>
          <tr>
            <td style="width:50%">
                Employee ID: <span> ' . $emp_code . '</span>
            </td>
            <td>Email: 
                <span> ' . $emp_email . '</span>
            </td>
        </tr>
      </tr>
    </table>
    <br /><br />
    <div class="col-md-12">
        <table width="100%" style="border-style:solid; border-width: 1px;">
            <tr>
                <td><b>Payments</b></td>
                <td><b>TK</b></td>
                <td><b>Deduction</b></td>
                <td><b>Tk</b></td>
            </tr>
            <tr>
                <td>Gross Salary</td>
                <td>' . $emp_gross_salary . '</td>                
                <td>Tax Deduction</td>
                <td>' . $income_tax . '</td>
            </tr>
            <tr>
                <td>PF</td>
                <td> ' . $pf_subscription . '</td>                
                <td>Advance Loan (if any)</td>
                <td>' . $loan . '</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
             <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
             <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Total Earning</td>
                <td> ' . $total_earning . '</td>                
                <td>Total Deduction</td>
                <td> ' . $total_deduction . '</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>
    <br />
    <div class="col-md-12" style="font-weight: bold;">
    <span>Net Payable: ' . $net_payble . ' Tk Only /=</span>
    </div>
    <div class="clearfix"></div>
    <hr />
    
    <table style="border-style:none; width:100%">
     <tr>
        <td><span style="font-weight: bold;">Remarks:</span> </td>
        <td><span style="font-weight: bold;">Approved by:</span> 
     </tr>
     <tr>
     <td>&nbsp; </td>
     <td></td>
      </tr>
     <tr>
     <td><span style="font-weight: bold;">Authorized By:</span> </td>
     <td></td>
      </tr>
      </table>
    <br />
</div>';

    $mpdf = new mPDF('c', 'A4', '', '', 32, 25, 27, 25, 16, 13);
    $mpdf->SetDisplayMode('fullpage');
    $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
    $stylesheet = file_get_contents('../../../resource/css/bootstrap.css');
    $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
    $mpdf->WriteHTML($html, 2);
    $mpdf->Output('mpdf.pdf', 'I');
}
?>
<!DOCTYPE html>
<html lang = "en">


</body>
</html>
