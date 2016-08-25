<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

if (isset($_POST["report"])){
//initialize array
$column = array();
$list = array();
//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}


//Making columns
$columns = array(
    'Employee Name', 'Code', 'Designation',
    'Department', 'Date of Join', 'Section','Grade',
    'Gross Salary', 'Basic', 'HRA', 'Medical',
    'Cal. Days', 'Attn Days',
    'Weekly Off', 'Holiday', 'CL',
    'SL', 'EL', 'Absent',
    'OT', 'OT Earning', 'Bonus',
    'Transport Allowance', 'Others', 'Total Earning',
    'Absent Deduction', 'PF Eligible', 'PF Subscription',
    'Income TAX','Loan(If any)', 'Others', 'Total Deduction',
    'Net Payable'
);
array_push($list, $columns);
/*
 * Making rows
 * Fetching data :: mysql_fetch_array
 */

//Fetch data
$all_salary = "SELECT e.emp_firstname, e.emp_code, e.emp_designation, e.emp_department, e.emp_dateofjoin, e.emp_subsection, e.emp_staff_grade, e.emp_gross_salary, e.emp_basic,e.emp_hra, e.emp_medical,s.calender_days,s.attn_days,s.weekly_off, s.holiday,s.casual_leave, s.sick_leave, s.earned_leave, s.absent_days,s.overtime_hours, s.overtime_earning,s.bonus_amount, s.transport_allowance,s.others, s.total_earning, s.absent_deduction, s.pf_eligible, s.pf_subscription, s.income_tax, s.loan, s.others_category, s.total_deduction, s.net_payble FROM salary s, employee e WHERE e.emp_id=s.emp_id";
$output = mysqli_query($open, $all_salary);
$objects = array();

while($rows = mysqli_fetch_assoc($output)){
   $objects[]= $rows;
}

foreach ($objects as $key=>$val)
{
   $arr = array();
   $arr =array_values($val);
   array_push($list, $arr);
}


//Creating excel file
$fp = fopen('salary_sheet.xls', 'w');
foreach ($list as $fields) {
    fputcsv($fp, $fields, "\t", '"');
}
fclose($fp);
header("location: salary_sheet.xls");
}
?>
<!--Including headers-->
<?php include '../view_layout/header_view.php'; ?>
<form method="post">
    <input type="submit" class="k-button" name="report" value="Download Report">
</form>

<?php include '../view_layout/footer_view.php'; ?>


