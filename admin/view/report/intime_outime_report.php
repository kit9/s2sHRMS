<?php
/* Author : Rajan
 * Date: 2nd Feb 15
 */
session_start();

//Importing class library
include('../../config/class.config.php');
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");

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

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//give a number ordinal suffix
function ordinal_suffix($number) {
    $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
    if ((($number % 100) >= 11) && (($number % 100) <= 13))
        return $number . 'th';
    else
        return $number . $ends[$number % 10];
}

//Function to determine if the number is odd or even
function odd_even($num) {
    if ($num % 2 == 0) {
        //return even
        return 0;
    } else {
        return 1;
    }
}

if (isset($_POST["PunchReport"])) {
    extract($_POST);

    $start_date = date("Y-m-d", strtotime($start_date));
    $end_date = date("Y-m-d", strtotime($end_date));
    $all_dates = $con->SelectAllByCondition("dates", "date BETWEEN '$start_date' AND '$end_date' AND company_id = '1'");
    /*
     * find maximum number of elements in certain array
     */

    $header_counter_array = array();
    foreach ($all_dates as $dates) {
        $date_in_hand = $dates->date;
        $attendance_raw = $con->QueryResult("SELECT date, time FROM `attendance_raw` WHERE employee_id= '$emp_code' AND date = '$date_in_hand' GROUP BY time ORDER BY time");
        $header_counter = count($attendance_raw);
        array_push($header_counter_array, $header_counter);
    }

    //Find max value from $header_content_array
    $max_value = max($header_counter_array);

    //Build header array
    $header_array = array("Date");


    $j = 1;
    $density = 0;

    $highest = odd_even($max_value);
    if ($highest == 1) {
        $max_value += 1;
    }
    $semi_final_val = $max_value / 2;

    for ($i = 1; $i <= $semi_final_val; $i ++) {
        array_push($header_array, ordinal_suffix($i) . " In");
        array_push($header_array, ordinal_suffix($i) . " Out");
    }
    array_push($header_array, "remarks");

    /*
     * Now fetch data and push to master array
     */
    $master_array = array();
    foreach ($all_dates as $date) {
        $data_array = array();

        $date_in_hand = $date->date;
        array_push($data_array, $date_in_hand);

        $attendance_raw = $con->QueryResult("SELECT date, time FROM `attendance_raw` WHERE employee_id= '$emp_code' AND date = '$date_in_hand' GROUP BY time ORDER BY time");
        
        if (count($attendance_raw) > 0) {
            $total_element = count($attendance_raw);
            foreach ($attendance_raw as $ar) {
                array_push($data_array, $ar->time);
            }
            if ($total_element < $max_value) {
                $empty_target = $max_value - $total_element;
                for ($m = 1; $m <= $empty_target; $m++) {
                    array_push($data_array, " ");
                }
            }
        } else {
            for ($n = 1; $n <= $max_value; $n++) {
                array_push($data_array, " ");
            }
        }

        array_push($data_array, " ");
        array_push($master_array, $data_array);
    }

    array_unshift($master_array, $header_array);

    /*
     * Now generate column number
     * Now generate excel rows
     */

    $count = count($master_array);
    $countCol = count($master_array[0]);

    $createPHPExcel = new PHPExcel();
    $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
    $rowCount = 0;


    //Collect company info 
    for ($i = 1; $i <= $count; $i++) {
        for ($j = 0; $j <= $countCol - 1; $j++) {
            $cWorkSheet->setCellValueByColumnAndRow(0, 1, "Employee ID: 3016");
            $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Punch Log Report: $start_date TO $end_date");
            $cWorkSheet->setCellValueByColumnAndRow($j, $i + 4, $master_array["$rowCount"]["$j"]);
        }
        $rowCount++;
    }

    $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
    $filename = $company_id . rand(0, 9999999) . "MultiPunchReport.xlsx";
    $objWriter->save("$filename");
    header("location:$filename");
}
?>
<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Multiple Punch Report</h6></div>
    <div class="widget-body" style="background-color: white;">
        <div class="row">
            <form  method="post">
                <div class="col-md-2" style="margin-left: -15px;">
                    <label style="padding-left:5px;">Employee Code</label>
                    <span id="jaxax">
                        <script>
                            $(document).ready(function () {
                                var interval;
                                var htmls;
                                window.setInterval(function () {
                                    var jexa = $('#jaxax').attr('class');
                                    var checkallstring = $('#emp_code').val();
                                    if (checkallstring == "")
                                    {
                                        $('#jaxax').attr('class', '0');
                                    }
                                    if (jexa != "forsting")
                                    {
                                        if (checkallstring !== "")
                                        {
                                            //ajax start
                                            $.ajax({
                                                url: "../../controller/leave_management_controllers/hr_leave_management/emp_for_leave_controller.php?emp_code=" + checkallstring + "",
                                                type: "GET",
                                                dataType: "JSON",
                                                success: function (data) {
                                                    var objects = data.data;
                                                    console.log(objects);
                                                    var htmls = '';
                                                    htmls += '<br /><div style="border: 1px solid silver; border-radius:5px; margin-left:-5px;">';
                                                    $.each(objects, function () {
                                                        htmls += '<div class="col-md-6"><b>Company Name:</b></div><div class="col-md-6">' + this.company_title + ' ';
                                                        htmls += '</div><div class="clearfix"></div>';
                                                        htmls += '<div class="col-md-6"><b>Full Name:</b></div><div class="col-md-6">' + this.emp_firstname + ' ';
                                                        htmls += '</div><div class="clearfix"></div>';
                                                        htmls += '<div class="col-md-6"><b>Department:</b></div><div class="col-md-6">' + this.department_title + ' ';
                                                        htmls += '</div><div class="clearfix"></div>';
                                                        htmls += '<div class="col-md-6"><b>Designation:</b></div><div class="col-md-6">' + this.designation_title + ' ';
                                                        htmls += '</div><div class="clearfix"></div>';
                                                    });
                                                    htmls += '</div>';
                                                    $("#emp_info_container").html(htmls);
                                                }});
                                            $('#jaxax').attr("class", "forsting");
                                            //ajax end
                                        }
                                        else
                                        {
                                            $('#jaxax').attr("class", "1");
                                        }
                                    }
                                }, 1000);
                            });
                        </script>
                    </span>
                    <div style="padding-left:5px">
                        <input class="k-textbox" id="emp_code" name="emp_code" value="<?php echo $emp_code; ?>" list="employees">
                        <datalist id="employees">
                            <?php if (count($employees) >= 1): ?>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?php echo $emp->emp_code; ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                        </datalist> 
                    </div>
                </div>
                <div class="col-md-1"></div>
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
                <div class="clearfix">

                </div>
                <br />
                <div class="col-md-2">
                    <input value="Search" type="submit" class="k-button" name="PunchReport" style="width: 120px; margin-top: 20px; height:30px;"/>
                </div>
                <div class="clearfix"></div>
                <div id="emp_info_container"></div>
            </form>
        </div>
    </div>

    <?php include '../view_layout/footer_view.php'; ?>








