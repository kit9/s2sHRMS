<?php
session_start();
include("../../config/class.config.php");
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
$con = new Config();
$open = $con->open();
//Set up time configuration to UTC
date_default_timezone_set('UTC');
//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_POST["SearchOT"])) {
    extract($_POST);
    if ($company_id <= 0) {
        $err = "Please Select a Company.";
    } else if ($start_date == '') {
        $err = "Please Select Start Date.";
    } else if ($end_date == '') {
        $err = "Please Select End Date.";
    } else {
        $_SESSION["excelValue"] = $_POST;
        $data = $_SESSION["excelValue"];
        $temp_start_date = date('Y-m-d', strtotime($data["start_date"]));
        $temp_end_date = date('Y-m-d', strtotime($data["end_date"])); //new DateTime();
        $company_id = $data["company_id"];

        $column = array();
        $list = array();
        $dates = "SELECT DISTINCT date FROM dates WHERE date BETWEEN '$temp_start_date' AND '$temp_end_date' ORDER BY date";
        $result = mysqli_query($open, $dates);
        $object = array();
        while ($rows = mysqli_fetch_assoc($result)) {
            $object[] = $rows;
        }
        //$con->debug($object);
        $Header_array = array();
        //* push the static file in array **//
        array_push($Header_array, "Employee Code", "Employee Name", "Sub Section", "Department");
        // $con->debug($Header_array);
        //* push the static file in array **//
        foreach ($object as $key => $val) {
            array_push($Header_array, date("d-M", strtotime($val["date"])));
        }
        array_push($Header_array, "Total OT");
        //Fetching all the array
        $querySting = "select emp_code, emp_staff_grade, emp_firstname, emp_subsection, emp_staff_grade, department.department_title from tmp_employee inner join department on tmp_employee.emp_department = department.department_id where tmp_employee.emp_code='RPAC0521'";
        $employees = $con->QueryResult($querySting);
        $con->debug($employees);
        
        $dataArray = array();

        foreach ($employees as $emp) {
            $tmpArray = array();
            $emp_code = $emp->emp_code;
            $staff_grade = $emp->emp_staff_grade;
            array_push($tmpArray, $emp_code, $emp->emp_firstname, $emp->emp_subsection, $emp->department_title);
            $queryString = "SELECT
            A.date,
            B.ot_hours,
            B.in_time,
            B.out_time,
            B.standard_ot_hours,
            A.day_type_id
            FROM
                    (
                            SELECT
                                    dates.date,
                                    dates.day_type_id
                            FROM
                                    dates
                            
                            WHERE
                            dates.date >= '$temp_start_date'
                            AND dates.date <= '$temp_end_date'
                            AND company_id = '$company_id'
                    )AS A
            LEFT OUTER JOIN(
                    SELECT
                            job_card.date,
                            job_card.ot_hours,
                            job_card.in_time,
                            job_card.out_time,
                            job_card.standard_ot_hours
                    FROM
                            job_card
                    WHERE
                    job_card.date >= '$temp_start_date'
                    AND job_card.date <= '$temp_end_date'
                    AND job_card.emp_code = 'RPAC0521'
            )AS B ON A.date = B.date ";
            
            
            $result = mysqli_query($open, $queryString);
            $object_2 = array();
            while ($rows = mysqli_fetch_assoc($result)) {
                $object_2[] = $rows;
            }
            
            $con->debug($object_2);
            $xhours = '';
            $xMinutes = '';
            
            
            foreach ($object_2 as $otKey => $val2) {
                //for super admin::actual OT
                if ($_SESSION["user_type"] == "super_admin") {  
                        if ($val2["ot_hours"] > date("H:i:s", strtotime("00:00:00"))) {
                            array_push($tmpArray, date("H:i", strtotime($val2["ot_hours"])));
                            //prepare value for total ot for super admin
                            $x_time = date("H:i", strtotime($val2["ot_hours"]));
                            
                            $x_time_array = explode(":", $x_time);
                            $xhours += $x_time_array[0];
                            $xMinutes += $x_time_array[1];
                            
                        } else {
                            array_push($tmpArray, " ");
                        }                                                                   
                } else {
                    //For other types of users
                    if ($val2["standard_ot_hours"] > date("H:i:s", strtotime("00:00:00"))) {
                        array_push($tmpArray, date("H:i", strtotime($val2["standard_ot_hours"])));
                        //prepare value for total ot for super admin
                        $x_time = date("H:i", strtotime($val2["standard_ot_hours"]));
                        $x_time_array = explode(":", $x_time);
                        $xhours += $x_time_array[0];
                        $xMinutes += $x_time_array[1];
                    } else {
                        array_push($tmpArray, " ");
                    }
                }
            }            
            //Calculating total ot
            $tem_x_hours_add = 0;
            if ($xMinutes >= 60) {
                $tem_x_hours_add = $xMinutes / 60;
                //$con->debug($tem_x_hours_add);
                $tem_x_hours_arr = explode(".", $tem_x_hours_add);
                //$con->debug($tem_x_hours_arr);
                $xhours = $xhours + $tem_x_hours_arr[0];
                $temp_min_multipy = $xMinutes - ($tem_x_hours_arr[0] * 60);
                //$con->debug($temp_min_multipy);
                $xMinutes = $temp_min_multipy;
            }
            //check if hour and minutes is not empty.
            if ($xhours != '' || $xMinutes != '') {
                $total_ot = $xhours . ":" . $xMinutes;
                //insert  total OT
                array_push($tmpArray, $total_ot);
            } else {
                //insert empty space
                array_push($tmpArray, " ");
            }
            //push final array
            array_push($dataArray, $tmpArray);
        }
        
        $con->debug($dataArray); exit();
        
        $dataArray[0] = $Header_array;
        $count = count($dataArray);
        $countCol = count($dataArray[0]);
        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
        $rowCount = 0;
        for ($i = 1; $i <= $count - 1; $i++) {
            for ($j = 0; $j <= $countCol -1 ; $j++) {
                $cWorkSheet->setCellValueByColumnAndRow($j, $i, $dataArray["$rowCount"]["$j"]);
            }
            $rowCount++;
        }

        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);

        $filename = $company_id . rand(0, 9999999) . "OtReprot.xlsx";
        $objWriter->save("$filename");
        header("location:$filename");
    }
}
$companies = $con->SelectAll("company");
?>

<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Create OT Report</h6></div>
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
                    <label>Start date</label>
                    <div><?php echo $con->DateTimePicker("start_date", "start_date", $start_date, "", ""); ?></div>
                </div>
                <div class="col-md-3">
                    <label>End date</label>
                    <div>
                        <?php echo $con->DateTimePicker("end_date", "end_date", $end_date, "", ""); ?>
                    </div>
                </div>
                <div class="col-md-2">
                    <input value="Create Report" type="submit" id="SearchOT" class="k-button" name="SearchOT" style="width: 120px; margin-top: 20px; height:30px;"/>
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