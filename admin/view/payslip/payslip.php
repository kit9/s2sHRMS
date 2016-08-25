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
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<a href="report.php?emp_id=<?php echo $emp_id; ?>&action=pdf" class="k-button" style="text-decoration: none;">Create PDF</a>
<br /><br />
<div class="widget" style="background-color: white;">


    <br /><br />
    <div class="col-md-6">
        <div class="col-md-3">
            <span>Name: </span> 
        </div>
        <div class="col-md-6">
            <span><?php echo $emp_firstname . "  " . $emp_lastname; ?></span>
        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-3">
            <span>Designation:</span> 
        </div>
        <div class="col-md-6">
            <span><?php echo $emp_designation; ?></span>
        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-3">
            <span>Employee Code:</span> 
        </div>
        <div class="col-md-6">
            <span><?php echo $emp_code; ?></span>
        </div>
        <div class="clearfix"></div>  
    </div>

    <div class="col-md-6">
        <div class="col-md-3">
            <span>Date: </span> 
        </div>
        <div class="col-md-6">
            <span><?php echo date('d-m-Y'); ?></span>
        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-3">
            <span>Bank Acc:</span> 
        </div>
        <div class="col-md-6">
            <span><?php echo $emp_account_number; ?></span>
        </div>
        <div class="clearfix"></div>
        <br />
        <div class="col-md-3">
            <span>Email:</span> 
        </div>
        <div class="col-md-6">
            <span><?php echo $emp_email; ?></span>
        </div>
        <div class="clearfix"></div>  
    </div>
    <div class="clearfix"></div>
    <br /><br />
    <div class="col-md-12">
        <table border="1" width="100%">
            <tr>
                <th>Payments</th>
                <th>TK</th>
                <th>Deduction</th>
                <th>Tk</th>
            </tr>
            <tr>
                <td>Gross Salary</td>
                <td><?php echo $emp_gross_salary; ?></td>                
                <td>Tax Deduction</td>
                <td><?php echo $income_tax; ?></td>
            </tr>
            <tr>
                <td>PF</td>
                <td><?php echo $pf_subscription; ?></td>                
                <td>Advance Loan (if any)</td>
                <td><?php echo $loan; ?></td>
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
                <td><?php echo $total_earning; ?></td>                
                <td>Total Deduction</td>
                <td><?php echo $total_deduction; ?></td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>
    <br />
    <div class="col-md-12" style="font-weight: bold;">
        <span>Net Payable: <?php echo $net_payble; ?> Tk Only /=</span>
    </div>
    <div class="clearfix"></div>
    <hr />

    <div class="col-md-6"><span style="font-weight: bold;">Remarks:</span> </div>
    <div class="col-md-6"><span style="font-weight: bold;">Approved by:</span> </div>
    <div class="clearfix"></div>
    <br />
    <div class="col-md-6">
        <span style="font-weight: bold;">Authorized By:</span> 
    </div>
    <div class="clearfix"></div>

    <br />
</div>
<?php include '../view_layout/footer_view.php'; ?>

