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
    $allemp_array = array();
    $employees = mysqli_query($open, "SELECT * FROM tmp_employee");
    while ($obj = mysqli_fetch_object($employees)) {
        //$arr :: changed to $allemp_array
        $allemp_array[] = $obj;
    }
    //Bind all employee data to array
    echo "{\"data\":" . json_encode($allemp_array) . "}";
}

/*
 * Update employee data
 */
if ($verb == "POST"){
    $emp_id = mysql_real_escape_string($_POST["emp_id"]);
    $emp_fullname = mysql_real_escape_string($_POST["emp_fullname"]);
    $emp_email = mysql_real_escape_string($_POST["emp_email"]);
    $emp_designation = mysql_real_escape_string($_POST["emp_designation"]);
    $emp_department = mysql_real_escape_string($_POST["emp_department"]);
    $emp_subsection = mysql_real_escape_string($_POST["emp_subsection"]);
    $emp_dateofjoin = mysql_real_escape_string($_POST["emp_dateofjoin"]);
    $emp_staff_grade = mysql_real_escape_string($_POST["emp_staff_grade"]);
    $emp_gross_salary = mysql_real_escape_string($_POST["emp_gross_salary"]);
    $emp_location = mysql_real_escape_string($_POST["emp_location"]);
    $emp_gender = mysql_real_escape_string($_POST["emp_gender"]);
    //Proposed confirmation date
    $emp_prop_confirmation_date = mysql_real_escape_string($_POST["emp_prop_confirmation_date"]);
    $emp_dateofbirth = mysql_real_escape_string($_POST["emp_dateofbirth"]);
    $emp_bloodgroup = mysql_real_escape_string($_POST["emp_bloodgroup"]);
    $emp_address = mysql_real_escape_string($_POST["emp_address"]);
    $emp_contact_number = mysql_real_escape_string($_POST["emp_contact_number"]);
    $emp_resignation_date = mysql_real_escape_string($_POST["emp_resignation_date"]);
    $emp_replacement_of = mysql_real_escape_string($_POST["emp_replacement_of"]);
    $emp_notes_salary_hub = mysql_real_escape_string($_POST["emp_notes_salary_hub"]);
    $emp_account_number = mysql_real_escape_string($_POST["emp_account_number"]);
    $emp_bank_title = mysql_real_escape_string($_POST["emp_bank_title"]);
    $emp_remarks = mysql_real_escape_string($_POST["emp_remarks"]);
    
    //Employee update query
    $rs = mysqli_query($open, "UPDATE employee SET 
            emp_id = '" . $emp_id . "',
            emp_fullname = '" . $emp_firstname . "',
            emp_email = '" . $emp_email . "',
            emp_designation= '" . $emp_designation . "',
            emp_department= '" . $emp_department . "',
            emp_subsection= '" . $emp_subsection . "',
            emp_dateofjoin = '" . $emp_dateofjoin . "',
            emp_staff_grade = '" . $emp_staff_grade . "',
            emp_location = '" . $emp_location . "',
            emp_gender = '" . $emp_gender . "',
            emp_prop_confirmation_date = '" . $emp_prop_confirmation_date . "',
            emp_dateofbirth = '" . $emp_dateofbirth . "',
            emp_bloodgroup = '" . $emp_bloodgroup . "',
            emp_address = '" . $emp_address . "',
            emp_contact_number = '" . $emp_contact_number . "',
            emp_resignation_date = '" . $emp_resignation_date . "',
            emp_replacement_of = '" . $emp_replacement_of . "',
            emp_notes_salary_hub = '" . $emp_notes_salary_hub . "',
            emp_account_number = '" . $emp_account_number . "',
            emp_bank_title = '" . $emp_bank_title . "',
            emp_remarks = '" . $emp_remarks . "'
            WHERE emp_id = " . $emp_id);

    if ($rs) {
        echo json_encode($rs);
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed!";
    }
}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);
    
    //Collecting variables
    $emp_fullname = mysql_real_escape_string($_POST["emp_fullname"]);
    $emp_email = mysql_real_escape_string($_POST["emp_email"]);
    $emp_designation = mysql_real_escape_string($_POST["emp_designation"]);
    $emp_department = mysql_real_escape_string($_POST["emp_department"]);
    $emp_subsection = mysql_real_escape_string($_POST["emp_subsection"]);
    $emp_dateofjoin = mysql_real_escape_string($_POST["emp_dateofjoin"]);
    $emp_staff_grade = mysql_real_escape_string($_POST["emp_staff_grade"]);
    $emp_gross_salary = mysql_real_escape_string($_POST["emp_gross_salary"]);
    $emp_location = mysql_real_escape_string($_POST["emp_location"]);
    $emp_gender = mysql_real_escape_string($_POST["emp_gender"]);
    //Proposed confirmation date
    $emp_prop_confirmation_date = mysql_real_escape_string($_POST["emp_prop_confirmation_date"]);
    $emp_dateofbirth = mysql_real_escape_string($_POST["emp_dateofbirth"]);
    $emp_bloodgroup = mysql_real_escape_string($_POST["emp_bloodgroup"]);
    $emp_address = mysql_real_escape_string($_POST["emp_address"]);
    $emp_contact_number = mysql_real_escape_string($_POST["emp_contact_number"]);
    $emp_resignation_date = mysql_real_escape_string($_POST["emp_resignation_date"]);
    $emp_replacement_of = mysql_real_escape_string($_POST["emp_replacement_of"]);
    $emp_notes_salary_hub = mysql_real_escape_string($_POST["emp_notes_salary_hub"]);
    $emp_account_number = mysql_real_escape_string($_POST["emp_account_number"]);
    $emp_bank_title = mysql_real_escape_string($_POST["emp_bank_title"]);
    $emp_remarks = mysql_real_escape_string($_POST["emp_remarks"]);
    
    $sql = "insert into employee(
            emp_fullname,
            emp_email,
            emp_designation,
            emp_department,
            emp_subsection,
            emp_dateofjoin,
            emp_staff_grade,
            emp_location,
            emp_gender,
            emp_prop_confirmation_date,
            emp_dateofbirt,
            emp_bloodgroup,
            emp_address,
            emp_contact_number,
            emp_resignation_date,
            emp_replacement_of,
            emp_notes_salary_hub,
            emp_account_number,
            emp_bank_title,
            emp_remarks
            ) values(
            '$emp_fullname',
            '$emp_email',
            '$emp_designation',
            '$emp_department',
            '$emp_subsection',
            '$emp_dateofjoin',
            '$emp_staff_grade',
            '$emp_gross_salary',
            '$emp_location',
            '$emp_gender',
            '$emp_prop_confirmation_date',
            '$emp_dateofbirth',
            '$emp_bloodgroup',
            '$emp_address',
            '$emp_contact_number',
            '$emp_resignation_date',
            '$emp_replacement_of',
            '$emp_notes_salary_hub',
            '$emp_account_number',
            '$emp_bank_title',
            '$emp_remarks'
            )";
    
    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        $u_id = mysqli_insert_id($con->open());
        echo "" . $u_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {

    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars);

    $u_id = mysql_real_escape_string($request_vars["u_id"]);

    $sql = "DELETE FROM wing WHERE u_id = '" . $u_id . "'";

    $rs = mysqli_query($con->open(), $sql);

    if ($rs) {
        echo "" . $u_id . "";
    } else {
        header("HTTP/1.1 500 Internal Server Error");
        echo false;
    }
}
?>
