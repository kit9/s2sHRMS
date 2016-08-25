<?php
/* Author : Asma
 * Date: 6 April 15
 */
session_start();
//Importing class library
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
include ('../../config/class.config.php');
$con = new Config();

$open = $con->open();
$emp_code = '';
$logged_emp_code = '';

if (isset($_SESSION['emp_code'])) {
    $logged_emp_code = $_SESSION['emp_code'];
    $emp_code = $_SESSION['emp_code'];
}
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
$err = "";
$msg = '';
$pf_all = array();

$arr = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
$month = array_combine(range(1, count($arr)), array_values($arr));

if (isset($_POST["view_list"])) {
    extract($_POST);
    $year = mysql_real_escape_string($_POST["year"]);
    $company = mysql_real_escape_string($_POST["company_id"]);

//    $pf_all = $con->SelectAllByCondition("provident_fund_details", "pfd_company_id='$company' AND pfd_year='$year' GROUP BY pfd_emp_code");
    $pf_query1 = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code WHERE p.pfd_year='$year' AND p.pfd_company_id='$company' GROUP BY p.pfd_emp_code";
    $pf_all = $con->QueryResult($pf_query1);
    $_SESSION["year"] = $year;
    $_SESSION["company"] = $company;
}


//Priority range of the logged in employee
$range_start = '';
$range_end = '';

$staff_grade_permission = array();

//Find staff grade permission
$staff_grade_permission = $con->SelectAllByCondition("salary_view_permission", "svp_emp_code='$logged_emp_code'");

if (count($staff_grade_permission) > 0) {
    $range_start = $staff_grade_permission{0}->svp_sg_position_from;
    $range_end = $staff_grade_permission{0}->svp_sg_position_to;
}


//=====================================================================================================
if (isset($_POST["generate_excel"])) {
    extract($_POST);
    $today = date("Y/m/d");
    $sys_date = date_create($today);
    $formatted_today = date_format($sys_date, 'Y-m-d');
    $zero = "0000-00-00";
    $Arr = array();

    $year = $_SESSION["year"];
    $company = $_SESSION["company"];
    $pf_query1 = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code WHERE p.pfd_year='$year' AND p.pfd_company_id='$company' GROUP BY p.pfd_emp_code";
    $pf_all = $con->QueryResult($pf_query1);

    //============================================================
    $c = 0;
    $new_arr = array();
    $tot_row = count($pf_all);
    if ($tot_row >= 1) {
        foreach ($pf_all as $pa) {
            $x = 0;
            $new_arr["$c"]["$x"] = $pa->emp_code;
            $x++;
            $new_arr["$c"]["$x"] = $pa->emp_firstname;
            $x++;

            foreach ($month as $key => $val) {
                $emp_codee = $pa->emp_code;
                $pf_query = "SELECT p.* FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code WHERE p.pfd_year='$year' AND p.pfd_company_id='$company' AND p.pfd_emp_code='$emp_codee' AND p.pfd_month='$key' order by p.pfd_month ASC";
                $pfd_detail = $con->QueryResult($pf_query);

                //Find this employee's staff grade
                $priority = '';
                $current_sgrade = array();
                $current_sgrade = $con->SelectAllByCondition("emp_staff_grade", "es_emp_code='$emp_codee' ORDER BY emp_staff_grade_id DESC LIMIT 0,1");

                if (count($current_sgrade) > 0) {
                    $emp_staff_grade = $current_sgrade{0}->es_staff_grade_id;

                    //find staff grade priority
                    $staff_meta = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
                    if (count($staff_meta) > 0) {
                        $priority = $staff_meta{0}->priority;
                    }
                }

                if (count($pfd_detail) >= 1) {
                    foreach ($pfd_detail as $rb) {

                        if ($priority >= $range_start && $priority <= $range_end) {
                            $new_arr["$c"]["$x"] = $rb->pfd_emp_amount;
                            $x++;
                            $new_arr["$c"]["$x"] = $rb->pfd_com_amount;
                            $x++;
                        } else if ($emp_codee == $logged_emp_code) {
                            $new_arr["$c"]["$x"] = $rb->pfd_emp_amount;
                            $x++;
                            $new_arr["$c"]["$x"] = $rb->pfd_com_amount;
                            $x++;
                        } else {
                            $new_arr["$c"]["$x"] = ' ';
                            $x++;
                            $new_arr["$c"]["$x"] = ' ';
                            $x++;
                        }
                    }
                } else {
                    $new_arr["$c"]["$x"] = '';
                    $x++;
                    $new_arr["$c"]["$x"] = '';
                    $x++;
                }
            }
            $Prof_detail = $con->QueryResult("select * from provident_fund_details_yearly where PFDY_emp_code='$emp_codee'");
            if ($priority >= $range_start && $priority <= $range_end || ($emp_codee == $logged_emp_code)) {
                $new_arr["$c"]["$x"] = $Prof_detail[0]->PFDY_pfd_others;
                $x++;
                $new_arr["$c"]["$x"] = $Prof_detail[0]->PFDY_pfd_total;
                $x++;
                $new_arr["$c"]["$x"] = $Prof_detail[0]->PFDY_eligible_total;
                $x++;
                $c++;
            } else {
                $new_arr["$c"]["$x"] = '';
                $x++;
                $new_arr["$c"]["$x"] = '';
                $x++;
                $c++;
            }
        }
    }

//============================================================
    $pf_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname,e.company_id,c.company_title FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code LEFT JOIN company c on c.company_id=e.company_id order by p.pfd_year DESC";
    $pfd_detail = $con->QueryResult($pf_query);

    $name = $pfd_detail[0]->emp_firstname;
    $company_id = $pfd_detail[0]->company_id;
    $company_title = $pfd_detail[0]->company_title;

    $header_array = array();
    $header_array_two = array();

    array_push($header_array, "Employee code", "Employee Name", "January", " ", "February", " ", "March", " ", "April", " ", "May", " ", "June", " ", "July", " ", "August", " ", "September", " ", "October", " ", "November", " ", "December", " ", "Others", "Provident Fund Total", "Eligible Provident Fund");
    array_push($header_array_two, " ", " ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", "EC ", "CC ", " ", " ", " ");

    array_unshift($Arr, $header_array, $header_array_two);

    $arr = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $month = array_combine(range(1, count($arr)), array_values($arr));

    $count = count($pf_all) + 2;
    $countCol = count($Arr[0]);

    foreach ($new_arr as $ar) {
        $Arr[] = $ar;
    }
//
    $createPHPExcel = new PHPExcel();
    $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
    $rowCount = 0;
    $pfd_total = 0;

//        $style = array('alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
//        $style_two = array('alignment' => array('vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP));
    $styleArray = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '000000'),
            'size' => 12,
            'name' => 'Verdana'));

    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'color' => array('rgb' => '000000'),
            'size' => 10,
            'name' => 'Verdana'));

    $cWorkSheet->getStyle("G1:G3")->applyFromArray($styleArray);
    $cWorkSheet->getStyle("A6:AD6")->applyFromArray($styleArray1);

    for ($i = 0; $i < $count; $i++) {
        for ($j = 0; $j <= $countCol - 1; $j++) {
            $cWorkSheet->setCellValueByColumnAndRow(6, 1, "$company_title");
            $cWorkSheet->setCellValueByColumnAndRow(6, 2, "Employee Name: $name");
            $cWorkSheet->setCellValueByColumnAndRow(6, 3, "Date: $formatted_today");
            $cWorkSheet->setCellValueByColumnAndRow(6, 4, "NB: EC = Employee Contribution, CC = Company Contribution");
            $cWorkSheet->setCellValueByColumnAndRow($j, $i + 6, $Arr["$rowCount"]["$j"]);
        }
        $rowCount++;
    }
    $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
    $filename = $company_id . rand(0, 9999999) . "Emp_Prov_FundList.xlsx";
    $objWriter->save("$filename");
    header("location:$filename");
    unset($_SESSION["year"]);
    unset($_SESSION["company"]);
}
?>
<?php include '../view_layout/header_view.php'; ?>
<style type="text/css">  
    .k-edit,.k-delete,.k-add {
        margin-top: -2px !important;
    }
</style>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head">
        <h6 class="heading" style="color:whitesmoke;">Employee Provident Fund Details</h6>
    </div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <form method="post" name="form">
            <div class="col-md-6">
                <label for="Full name">Company:</label> <br />
                <input id="company_id" name="company_id" style="width: 80%;" value="<?php echo $company_id; ?>" />
                <!-- auto complete start-->
            </div>
            <script type="text/javascript">
                $(document).ready(function() {
                    var company_id = jQuery("#company_id").kendoComboBox({
                        placeholder: "Select company...",
                        dataTextField: "company_title",
                        dataValueField: "company_id",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/company.php",
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
            <div class="col-md-6">
                <label for="Year">Year:</label><br/> 
                <input id="year1" name="year" style="width: 80%;" value="<?php echo $year; ?>" />
            </div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $("#year1").kendoComboBox({
                        placeholder: "Select Year...",
                        dataTextField: "year_name",
                        dataValueField: "year_name",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/year.php",
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

            <div class="clearfix"></div>
            <br/>
            <script type="text/javascript">
                $(document).ready(function() {
                    var companieEm = $("#companieEm").kendoComboBox({
                        placeholder: "Select Company...",
                        dataTextField: "company_title",
                        dataValueField: "company_id",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/company.php",
                                    type: "GET"
                                }
                            },
                            schema: {
                                data: "data"
                            }
                        }
                    }).data("kendoComboBox");

                    var employeesEm = $("#employeesEm").kendoComboBox({
                        placeholder: "Select Employee..",
                        autoBind: true,
                        dataTextField: "emp_name",
                        dataValueField: "emp_id",
                        dataSource: {
                            transport: {
                                read: {
                                    url: "../../controller/employee_list_manage_permission.php",
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
            <div class="col-md-6">
                <input type="submit" class="k-button" name="view_list" value="Submit" name="View PF List"><br/><br />
            </div>
            <?php if (isset($_POST["view_list"])) { ?>
                <div class="col-md-6">
                    <input type="submit" class="k-button pull-right" name="generate_excel" value="Generate Report">
                </div>
            <?php } ?>
            <div class="clearfix"></div>
        </form>      

        <div id="example" class="k-content" style="">
            <table id="grid">
                <!--style="table-layout: fixed; "-->
                <colgroup>
                    <col style="width:100px" />
                    <col style="width:250px" />
                    <?php for ($l = 1; $l <= 24; $l++) { ?>
                        <col style="width:70px;" />
                    <?php } ?>
                    <col style="width:180px" />
                    <col style="width:180px" />
                    <col style="width:180px" />
                    <col style="width:100px" />
                    <!--style="pointer-events: none;width:90px; word-wrap: break-word; white-space: pre-wrap;"-->
                </colgroup>
                <thead>
                    <tr>
                        <td style="border-bottom:1px solid gray;width: 100px;" >Employee code</td>
                        <td style="border-bottom:1px solid gray;width: 250px;" >Employee Name</td>
                        <?php foreach ($month as $m) { ?>
                            <td style="border-bottom: 1px solid gray;text-align:center; width:140px;" colspan="2"><?php echo $m; ?></td>
                            <!--<th style="border-bottom: 1px solid gray;word-wrap: break-word; white-space: pre-wrap;text-align:center;" colspan="2"></th>-->
                        <?php } ?>
                        <td style="border-bottom:1px solid gray;width: 180px;" >Others </td>
                        <td style="border-bottom:1px solid gray;width: 180px;" >PF_Total </td>
                        <td style="border-bottom:1px solid gray;width: 180px;" >Eligible PF Amount </td>
                        <td style="border-bottom:1px solid gray;width: 100px;" >Action </td>
                    </tr>
                    <tr>
                        <th data-field='emp_code' style="border-bottom: 1px solid gray;width: 100px;"></th>
                        <th data-field='emp_firstname' style="border-bottom: 1px solid gray;width: 250px;"></th>
                        <?php foreach ($month as $m) { ?>
                            <th data-field="EE_<?php echo $m; ?>" style="border-bottom: 1px solid gray;text-align:center;width: 70px;">EE</th>
                            <th data-field="EC_<?php echo $m; ?>" style="border-bottom: 1px solid gray;text-align:center;width: 70px;">EC</th>
    <!--                        <th data-field="ee" style="border-bottom: 1px solid gray;word-wrap: break-word; white-space: pre-wrap;text-align:center;">EE</th>
                            <th data-field="ec" style="border-bottom: 1px solid gray;word-wrap: break-word; white-space: pre-wrap;text-align:center;">EC</th>-->
                        <?php } ?>
                        <th data-field="pfd_others" style="border-bottom: 1px solid gray;width: 180px;"></th>
                        <th data-field="pfd_total" style="border-bottom: 1px solid gray;width: 180px;"></th> 
                        <th data-field="PFDY_eligible_total" style="border-bottom: 1px solid gray;width: 180px;"></th>    
                        <th data-field="action" style="border-bottom: 1px solid gray;width: 100px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tot_row = count($pf_all);
                    if ($tot_row >= 1) {
                        foreach ($pf_all as $pa) {
                            ?>
                            <tr>
                                <td style="width: 100px;"><?php echo $pa->emp_code; ?></td>
                                <td style="width: 250px;"><?php echo $pa->emp_firstname . ' ' . $pa->emp_lastname; ?></td>
                                <?php
                                foreach ($month as $key => $val) {
                                    $emp_codee = $pa->emp_code;

                                    //Find this employee's staff grade
                                    $priority = '';
                                    $current_sgrade = array();
                                    $current_sgrade = $con->SelectAllByCondition("emp_staff_grade", "es_emp_code='$emp_codee' ORDER BY emp_staff_grade_id DESC LIMIT 0,1");

                                    if (count($current_sgrade) > 0) {
                                        $emp_staff_grade = $current_sgrade{0}->es_staff_grade_id;

                                        //find staff grade priority
                                        $staff_meta = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
                                        if (count($staff_meta) > 0) {
                                            $priority = $staff_meta{0}->priority;
                                        }
                                    }

                                    $pf_query = "SELECT p.*, e.emp_code,e.emp_firstname, e.emp_lastname FROM provident_fund_details p left join tmp_employee e on e.emp_code=p.pfd_emp_code WHERE p.pfd_year='$year' AND p.pfd_company_id='$company' AND p.pfd_emp_code='$emp_codee' AND p.pfd_month='$key' order by p.pfd_month ASC";
                                    $pfd_detail = $con->QueryResult($pf_query);
                                    if (count($pfd_detail) >= 1) {
                                        foreach ($pfd_detail as $rb) {
                                            if ($priority >= $range_start && $priority <= $range_end || ($logged_emp_code == $emp_codee)) {
                                                if ($rb->pfd_emp_amount != '' && $rb->pfd_com_amount != '') {
                                                    echo '<td style="width: 70px;">' . $rb->pfd_emp_amount . '</td>';
                                                    echo '<td style="width: 70px;">' . $rb->pfd_com_amount . '</td>';
                                                } else {
                                                    echo '<td style="width: 70px;"> &nbsp;&nbsp;</td>';
                                                    echo '<td style="width: 70px;">&nbsp;&nbsp;</td>';
                                                }
                                            } else {
                                                echo '<td style="width: 70px;"> &nbsp;&nbsp;</td>';
                                                echo '<td style="width: 70px;">&nbsp;&nbsp;</td>';
                                            }
                                        }
                                    } else {
                                        echo '<td style="width: 70px;"> &nbsp;</td>';
                                        echo '<td style="width: 70px;"> &nbsp;</td>';
                                    }
                                }
                                $Prof_detail = $con->QueryResult("select * from provident_fund_details_yearly where PFDY_emp_code='$emp_codee'");
                                if ($priority >= $range_start && $priority <= $range_end || ($logged_emp_code == $emp_codee)) {
                                    ?>
                                    <td style="width: 180px;"><?php echo $Prof_detail[0]->PFDY_pfd_others; ?></td>
                                    <td style="width: 180px;"><?php echo $Prof_detail[0]->PFDY_pfd_total; ?></td>
                                    <td style="width: 180px;"><?php echo $Prof_detail[0]->PFDY_eligible_total; ?></td>
                                    <td role="gridcell" style="width: 100px;">
                                        <a class="k-button k-button-icontext k-grid-edit" href="PF_details.php?empl_code=<?php echo base64_encode($pa->emp_code); ?>">
                                            <span class="k-edit"></span> Details</a>
                                    </td>
                                <?php } else { ?>
                                    <td style="width: 180px;">&nbsp;&nbsp;</td>
                                    <td style="width: 180px;">&nbsp;&nbsp;</td>
                                    <td style="width: 180px;">&nbsp;&nbsp;</td>
                                    <td role="gridcell" style="width: 100px;">&nbsp;&nbsp; </td>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                    }
                    ?> 
                </tbody>
            </table>
            <script>
                $(document).ready(function() {
                    $("#grid").kendoGrid({
                        pageable: {
                            refresh: true,
                            input: true,
                            numeric: false,
                            pageSize: 20,
                            pageSizes: true,
                            pageSizes: [20, 40, 60, 80, 120],
                        },
                        sortable: true,
                        groupable: true,
                        filterable: true,
                        resizable: true,
                        scrollable: true
                    });
                });
            </script>
        </div>
    </div>
</div>
<?php include '../view_layout/footer_view.php'; ?>