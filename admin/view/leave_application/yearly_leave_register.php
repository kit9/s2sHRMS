<?php
session_start();
/** Author: Rajan Hossain
 * Page: Yearly leave register
 */
//Importing class library
include ('../../config/class.config.php');
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");

//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

$companies = '';
$year = '';
$company_title = '';
$permission_id = '';
$process_flag = '';

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if (isset($_GET["process_flag"])) {
    $process_flag = $_GET["process_flag"];
}



/*
 * Process leave register
 * script 'migration_total' will run and populate or update leave status meta table
 * script 'migration_availed' will run and count all the appoved leave and update leave_status_meta_table
 * script 'migration_carry_forward'  
 */

if (isset($_POST["process_leave_register"])) {
    $con->redirect("migration_total.php?permission_id=" . $permission_id);
}

if (isset($_POST["generate_excel"])) {
    extract($_POST);
    $today = date("Y/m/d");
    $sys_date = date_create($today);
    $formatted_today = date_format($sys_date, 'Y-m-d');
    $zero = "0000-00-00";

    $build_first_date = $year . "-01-01";

    if ($companies == '') {
        $err = "Please select a company.";
    } else if ($year == '') {
        $err = "Please select a year.";
    } else {
        $company = $con->SelectAllByCondition("company", "company_id='$companies'");
        if (count($company) > 0) {
            $company_title = $company{0}->company_title;
        }
        $get_all_leave = $con->QueryResult("SELECT
	e.emp_code,
	e.emp_firstname,
        lp.leave_title,lp.short_code, 
	ls.*
FROM
	leave_status_meta ls
        LEFT JOIN tmp_employee e ON e.emp_code = ls.emp_code
LEFT JOIN leave_policy lp on lp.leave_policy_id=ls.leave_type_id
WHERE ls.year = '$year' AND ls.company_id = '$companies'");

        
        $get_all_emp = $con->QueryResult("SELECT
        e.emp_code,
        e.emp_firstname,
        desg.designation_title,
	dept.department_title,
        e.emp_dateofjoin,
        sg.staffgrade_title
        FROM tmp_employee e
    LEFT JOIN department dept ON e.emp_department = dept.department_id
    LEFT JOIN designation desg ON e.emp_designation = desg.designation_id
    LEFT JOIN staffgrad sg ON e.emp_staff_grade = sg.staffgrade_id
            where e.emp_code IN (
                SELECT
                                ec_emp_code
                        FROM
                                emp_company
                        WHERE
                                ec_company_id = '$companies'
                        AND (
                                (
                                        ec_effective_start_date <= '$formatted_today'
                                        AND ec_effective_end_date >= '$zero'
                                )
                                OR (
                                        ec_effective_start_date <= '$formatted_today'
                                        AND ec_effective_end_date = '$zero'
                                )
                        ))");

        $get_leave = $con->SelectAll("leave_policy");
   
        $Arr = array();
        $header_array = array();
        $header_array_two = array();

        array_push($header_array, "Employee Code", "Name", "Designation", "Department", "DOJ", "Staff Grade");
        array_push($header_array_two, " ", " ", " ", " ", " ", " ");

        if (count($get_leave) > 0) {
            foreach ($get_leave as $leave) {
                $leave_title = $leave->leave_title;
                array_push($header_array, " ", $leave_title, " ");
            }
            foreach ($get_leave as $leave) {
                array_push($header_array_two, "Total", "Availed", "Balance");
            }
        }



        if (count($get_all_emp) > 0) {
            foreach ($get_all_emp as $key => $val) {
                $i = 0;
                $Arr["$key"][$i] = $val->emp_code;
                $i++;
                $Arr["$key"][$i] = $val->emp_firstname;
                $i++;

                if ($val->designation_title != '') {
                    $Arr["$key"][$i] = $val->designation_title;
                } else {
                    $Arr["$key"][$i] = " ";
                }
                $i++;

                if ($val->department_title != '') {
                    $Arr["$key"][$i] = $val->department_title;
                } else {
                    $Arr["$key"][$i] = " ";
                }

                $i++;
                if ($val->emp_dateofjoin != '') {
                    $Arr["$key"][$i] = $val->emp_dateofjoin;
                } else {
                    $Arr["$key"][$i] = " ";
                }

                $i++;
                if ($val->staffgrade_title != '') {
                    $Arr["$key"][$i] = $val->staffgrade_title;
                } else {
                    $Arr["$key"][$i] = " ";
                }

                $i++;

                foreach ($get_all_leave as $al) {
                    if ($val->emp_code == $al->emp_code) {
                        $Arr["$key"][$i] = $al->total_days;
                        $i++;
                        $avail = $al->availed_days;
                        if (empty($avail)) {
                            $Arr["$key"][$i] = " ";
                        } else {
                            $Arr["$key"][$i] = $al->availed_days;
                        }
                        $i++;
                        $Arr["$key"][$i] = $al->remaining_days;
                        $i++;
                    }
                }
            }
        }

        /*
         * collect and summ up leave data
         */
        $dates = $con->SelectAllByCondition("dates", "date between '$build_first_date' AND '$formatted_today' and company_id='$companies'");
        foreach ($dates as $date) {
            $each_date = date('Y-m-d', strtotime($date->date));
            foreach ($get_all_emp as $emp) {
                $employee_code = $emp->emp_code;
            }
        }

        array_unshift($Arr, $header_array, $header_array_two);
        
        $count = count($Arr);
        $countCol = count($Arr[0]);

        $createPHPExcel = new PHPExcel();
        $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
        $rowCount = 0;

        for ($i = 1; $i <= $count; $i++) {
            for ($j = 0; $j <= $countCol - 1; $j++) {
                $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
                $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Yearly Register Leave - $year");
                $cWorkSheet->setCellValueByColumnAndRow(0, 3, "Date: $formatted_today");
                $cWorkSheet->setCellValueByColumnAndRow($j, $i + 4, $Arr["$rowCount"]["$j"]);
            }
            $rowCount++;
        }

        $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
        $filename = $company_id . rand(0, 9999999) . "Yearly_Leave_Register.xlsx";
        $objWriter->save("$filename");
        header("location:$filename");
    }
}
?>

<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Yearly Leave Register</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->

        <!--Declare error message-->
        <?php include("../../layout/msg.php"); ?>
        <form method="post">
            <div class="col-md-6">
                <label for="Full name">Company:</label><br/>
                <input type="text" id="company" name="companies" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="col-md-6">
                <label for="Full name">Year:</label><br/>
                <input type="text" id="year" name="year" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-3">
                <input type="submit" class="k-button" name="process_leave_register" value="Process Yearly Register">
            </div>
            <?php if ($process_flag == 1): ?>
                <div class="col-md-3">
                    <input type="submit" class="k-button" name="generate_excel" value="Download Yearly Register">
                </div>
            <?php endif; ?>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>
</form>
</div>
<?php include '../view_layout/footer_view.php'; ?>
<!--Company Combo-->
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#company").kendoComboBox({
            placeholder: "Select company...",
            dataTextField: "company_title",
            dataValueField: "company_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/company_global.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");
    });

    jQuery(document).ready(function() {
        jQuery("#year").kendoComboBox({
            placeholder: "Select year...",
            dataTextField: "year",
            dataValueField: "year",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/leave_management_controllers/year_list_leave_register_controller.php",
                        type: "GET"
                    }
                },
                schema: {
                    data: "data"
                }
            }
        }).data("kendoComboBox");
    });
</script>