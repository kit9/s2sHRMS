<?php
session_start();
include("../../config/class.config.php");
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
$con = new Config();
$open = $con->open();
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
        $columns = array('Employee Code', 'Full Name', 'Designation', 'Department', 'Subsection', 'Out Time');
        array_push($list, $columns);
        $zero = date("H:i:s", strtotime("00:00:00"));

        $zeros = "SELECT
	tmp.emp_code,
        tmp.emp_firstname,
	sub.subsection_title,
	desg.designation_title,
	dep.department_title,
        job_card.out_time
        FROM
                job_card
        LEFT JOIN tmp_employee tmp ON job_card.emp_code = tmp.emp_code
        LEFT JOIN department dep ON tmp.emp_department = dep.department_id
        LEFT JOIN designation desg ON tmp.emp_designation = desg.designation_id
        LEFT JOIN subsection sub ON tmp.emp_subsection = sub.subsection_id
        WHERE
        date = '$temp_date'
        AND (out_time = '$zero' OR ISNULL(out_time))
        AND tmp.emp_code IN(
                SELECT
                        emp_code
                FROM
                        tmp_employee
                WHERE
                        company_id = '$company_id'
        )";

        $output = mysqli_query($open, $zeros);
        $objects = array();
        while ($rows = mysqli_fetch_assoc($output)) {
            $objects[] = $rows;
        }
        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);

        $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
        $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Missing Out-Time Report");
        $cWorkSheet->setCellValueByColumnAndRow(0, 5, "Date: $temp_date");

        //Create main headers
        $cWorkSheet->setCellValueByColumnAndRow(0, 8, "Employee Code");
        $cWorkSheet->setCellValueByColumnAndRow(1, 8, "Name");
        $cWorkSheet->setCellValueByColumnAndRow(2, 8, "Designation");
        $cWorkSheet->setCellValueByColumnAndRow(3, 8, "Department");
        $cWorkSheet->setCellValueByColumnAndRow(4, 8, "Section");
        $cWorkSheet->setCellValueByColumnAndRow(5, 8, "Out Time");

        //Create data from the main array
        $xx = 0;
        for ($row = 9; $row < count($objects) + 9; $row++) {
            $cWorkSheet->setCellValueByColumnAndRow(0, $row, $objects["$xx"]["emp_code"]);
            $cWorkSheet->setCellValueByColumnAndRow(1, $row, $objects["$xx"]["emp_firstname"]);
            $cWorkSheet->setCellValueByColumnAndRow(2, $row, $objects["$xx"]["designation_title"]);
            $cWorkSheet->setCellValueByColumnAndRow(3, $row, $objects["$xx"]["department_title"]);
            $cWorkSheet->setCellValueByColumnAndRow(4, $row, $objects["$xx"]["subsection_title"]);
            if ($objects["$xx"]["out_time"] != '') {
                $cWorkSheet->setCellValueByColumnAndRow(5, $row, $objects["$xx"]["out_time"]);
            }else {
                $cWorkSheet->setCellValueByColumnAndRow(5, $row, "00:00:00");
            }
            $xx++;
        }

        //Define filename and create excel report
        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
        $filename = $company_id . rand(0, 9999999) . "_Missing_Outtime.xlsx";
        $objWriter->save("$filename");
        header("location:$filename");
    }
}

$companies = $con->SelectAll("company");
?>

<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Create Missing Out-Time Report</h6></div>
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

