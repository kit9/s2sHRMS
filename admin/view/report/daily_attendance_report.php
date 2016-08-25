<?php
session_start();
include("../../config/class.config.php");
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
$con = new Config();
$open = $con->open();
error_reporting(0);
//Set up time configuration to UTC
date_default_timezone_set('UTC');

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

if (isset($_POST["SearchMissingIntime"])) {
    extract($_POST);
    if ($company_id <= 0) {
        $err = "Please Select a Company.";
    } else if ($date == '') {
        $err = "Please Select Start Date.";
    } else {
        $_SESSION["excelValue"] = $_POST;
        $data = $_SESSION["excelValue"];
        $temp_date = date('Y-m-d', strtotime($data["date"]));
        $company_id = $data["company_id"];
        $companies = $con->SelectAllByCondition("company", "company_id='$company_id'");
        $company_title = $companies{0}->company_title;
        $column = array();
        $list = array();
        //Headers Array
        $columns = array('Employee Code', 'Full Name', 'Designation', 'Department', 'Subsection', 'Status');
        array_push($list, $columns);
        //Check if selected date is a holiday, weekend or festival
        $date_query = "SELECT 
            *
        FROM
        day_type
        WHERE
        day_type_id = (SELECT 
            day_type_id
            FROM
            dates
            WHERE
            date = '$temp_date' AND company_id = '$company_id' AND day_type_id in (select day_type_id from day_type where day_shortcode='H' or day_shortcode='W')
            )";

        $results = $con->QueryResult($date_query);

        //if the date is a holiday or weekend
        if (count($results) >= 1) {
            $status = "SELECT 
    emp_code,
    emp_firstname,
    desg.designation_title,
    dept.department_title,
    sub.subsection_title,
    (SELECT 
        day_shortcode
        FROM
        day_type
        WHERE
        day_type_id = (SELECT 
            day_type_id
            FROM
            dates
            WHERE
            date = '$temp_date' AND company_id = '$company_id')) AS atttype
            FROM
            tmp_employee
            LEFT JOIN
            designation desg ON tmp_employee.emp_designation = desg.designation_id
            LEFT JOIN
            department dept ON tmp_employee.emp_department = dept.department_id
            LEFT JOIN subsection sub ON tmp_employee.emp_subsection = sub.subsection_id
            
            WHERE tmp_employee.emp_code IN (SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$company_id'
		AND (
			(
				ec_effective_start_date <= '$temp_date'
				AND ec_effective_end_date >= '$temp_date'
			)
			OR (
				ec_effective_start_date <= '$temp_date'
				AND ec_effective_end_date = '0000-00-00'
			)
		))";
        } else {
            //if the date is normal
            $status = "SELECT 
            A.emp_code,
            A.emp_firstname,
            A.designation_title,
            A.department_title,
            A.subsection_title,
            B.atttype
        FROM
            (SELECT 
                    emp_code,
                    emp_firstname,
                    sub.subsection_title,
                    designation_title,
                    department_title
            FROM
                tmp_employee
            LEFT JOIN designation desg ON tmp_employee.emp_designation = desg.designation_id
            LEFT JOIN department dept ON tmp_employee.emp_department = dept.department_id
            LEFT JOIN subsection sub ON tmp_employee.emp_subsection = sub.subsection_id
            WHERE tmp_employee.emp_code IN (
            SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$company_id'
		AND (
			(
				ec_effective_start_date <= '$temp_date'
				AND ec_effective_end_date >= '$temp_date'
			)
			OR (
				ec_effective_start_date <= '$temp_date'
				AND ec_effective_end_date = '0000-00-00'
			)
		)  
            )
            ) A,

           (SELECT 
                emp_code, 'A' AS atttype
            FROM
                tmp_employee
            WHERE
                emp_code NOT IN (SELECT 
                        emp_code
                    FROM
                        job_card
                    WHERE
                        date = '$temp_date' )
                    AND emp_code NOT IN (SELECT 
                        emp_code
                    FROM
                        leave_application_details
                    WHERE
                        details_date = '$temp_date' 
                            AND status = 'approved' AND is_half != 'yes') UNION SELECT 
                emp_code, 'P' AS atttype
            FROM
                job_card
            WHERE
                date = '$temp_date'
            UNION SELECT 
                emp_code, leave_policy.short_code AS atttype
            FROM
                leave_application_details
            LEFT JOIN leave_policy ON leave_application_details.leave_type_id = leave_policy.leave_policy_id
            WHERE
                details_date = '$temp_date') B
        WHERE
            A.emp_code = B.emp_code
        ";
        }


        $output = mysqli_query($open, $status);
        while ($rows = mysqli_fetch_assoc($output)) {
            $objects[] = $rows;
        }

        //Implementing alternate attendance policy for weekend
        $i = 0;
        foreach ($objects as $key => $val) {
            $emp_code = $val["emp_code"];
            $temp_date = $temp_date;
            $existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$temp_date' AND implement_end_date >= '$temp_date' LIMIT 0,1");
            if (count($existing_awesome) > 0) {
                $alt_company_id = $existing_awesome{0}->alt_company_id;
            } else {
                $existing_awesome = $con->SelectAllByCondition("alternate_attn_policy", "emp_code='$emp_code' AND implement_from_date <= '$temp_date' AND implement_end_date='0000-00-00'");
                if (count($existing_awesome) > 0) {
                    $alt_company_id = $existing_awesome{0}->alt_company_id;
                    //Now look into calendar table for weekend
                    if ($alt_company_id > 0) {
                        $alt_dates_query = "SELECT 
                        day_type.day_shortcode, dates.day_type_id
                        FROM
                        dates
                        INNER JOIN
                        day_type ON day_type.day_type_id = dates.day_type_id
                        WHERE
                        dates.company_id = '$alt_company_id'
                        AND dates.`date` = '$temp_date'";
                        $alt_dates_result = $con->QueryResult($alt_dates_query);
                        $alt_day_short_code = $alt_dates_result{0}->day_shortcode;
                        if ($alt_day_short_code == "W") {
                            $objects["$i"]["atttype"] = "W";
                        } else if ($alt_day_short_code == "H") {
                            $objects["$i"]["atttype"] = "H";
                        }
                    }
                }
            }

            /*
             * Leave in weekend or holiday should be assigned here
             */
            $leave_type = '';
            $emp_leave_information_query = "SELECT lp.short_code FROM leave_application_details lad
            LEFT JOIN leave_policy lp ON lp.leave_policy_id = lad.leave_type_id
            WHERE lad.details_date = '$temp_date' AND lad.emp_code = '$emp_code'";
            $emp_leave_information = $con->QueryResult($emp_leave_information_query);
            if (count($emp_leave_information) > 0) {
                $leave_type = $emp_leave_information_query{0}->short_code;
            }
            if ($leave_type != ''){
                $objects["$i"]["atttype"] = $leave_type;
            } 

            //Replacement weekend or holiday
            $replacement_status = '';
            $replacement_weekend = $con->SelectAllByCondition("replacement_weekend", "replacement_weekend_date='$temp_date' AND rw_emp_code='$emp_code'");
            if (count($replacement_weekend) > 0) {
                if (isset($replacement_weekend{0}->replacement_weekend_status)) {
                    $replacement_status = $replacement_weekend{0}->replacement_weekend_status;
                }
            }
            if ($replacement_status != '') {
                $objects["$i"]["atttype"] = $replacement_status;
            }

            /*
             * New modification
             * Check employee's joining date
             * Compare with the this date in hand
             * Any date before the joining date should be 
             * displayed with the status A.
             */

            $joining_date = '';
            $joining_date_fetch = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code' LIMIT 0,1");
            if (count($joining_date_fetch) > 0) {
                $joining_date = date("Y-m-d", strtotime($joining_date_fetch{0}->emp_dateofjoin));
            }
            if ($temp_date < $joining_date) {
                $objects["$i"]["atttype"] = "A";
            }

            $i++;
        }



        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);

        $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
        $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Daily Attendance Report");
        $cWorkSheet->setCellValueByColumnAndRow(0, 5, "Date: $temp_date");

//Create main headers
        $cWorkSheet->setCellValueByColumnAndRow(0, 8, "Employee Code");
        $cWorkSheet->setCellValueByColumnAndRow(1, 8, "Name");
        $cWorkSheet->setCellValueByColumnAndRow(2, 8, "Designation");
        $cWorkSheet->setCellValueByColumnAndRow(3, 8, "Department");
        $cWorkSheet->setCellValueByColumnAndRow(4, 8, "Section");
        $cWorkSheet->setCellValueByColumnAndRow(5, 8, "Status");

//Create data from the main array
        $xx = 0;
        for ($row = 9; $row < count($objects) + 9; $row++) {
            $cWorkSheet->setCellValueByColumnAndRow(0, $row, $objects["$xx"]["emp_code"]);
            $cWorkSheet->setCellValueByColumnAndRow(1, $row, $objects["$xx"]["emp_firstname"]);
            $cWorkSheet->setCellValueByColumnAndRow(2, $row, $objects["$xx"]["designation_title"]);
            $cWorkSheet->setCellValueByColumnAndRow(3, $row, $objects["$xx"]["department_title"]);
            $cWorkSheet->setCellValueByColumnAndRow(4, $row, $objects["$xx"]["subsection_title"]);
            $cWorkSheet->setCellValueByColumnAndRow(5, $row, $objects["$xx"]["atttype"]);
            $xx++;
        }

//Define filename and create excel report
        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
        $filename = $company_id . rand(0, 9999999) . "_" . $temp_date . "_Daily_Attendance_Report.xlsx";
        $objWriter->save("$filename");
        header("location:$filename");
    }
}

$companies = $con->SelectAll("company");
?>

<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Create Daily Attendance Report</h6></div>
    <div class="widget-body" style="background-color: white;">
        <div class="col-md-12">
            <?php include("../../layout/msg.php"); ?>
            <form method="post">
                <div class="col-md-3">
                    <label>Select Company</label>
                    <select id="company" style="width: 60%" name="company_id">
                        <option value="0">Select Company</option>

                        <?php if (count($companies) >= 1): ?>
                            <?php foreach ($companies as $com): ?>
                                <option value="<?php echo $com->company_id; ?>" 
                                <?php
                                if ($com->company_id == $company_id) {
                                    echo "selected='selected'";
                                }n
                                ?>><?php echo $com->company_title; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                    </select>

                </div>
                <div class="col-md-3">
                    <label>Select a Date</label>
                    <div><?php echo $con->DateTimePicker("date", "date", $date, "", ""); ?></div>
                </div>

                <div class="col-md-2">
                    <input value="Create Report" type="submit" id="SearchOT" class="k-button" name="SearchMissingIntime" style="width: 120px; margin-top: 20px; height:30px;"/>
                </div>

                <div class="clearfix"></div>
            </form>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="clearfix"></div>
<?php include '../view_layout/footer_view.php'; ?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#company").kendoDropDownList();
    });
</script>

