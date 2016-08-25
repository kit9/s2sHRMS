<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if ($con->hasPermissionView($permission_id) != "yes") {
    $con->redirect("../dashboard/index.php");
}

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
//Local variables
$emp_code = '';
$advances = '';
$PEL_number_of_install = '';
$PEL_installment = '';

//Get values
if (isset($_GET["emp_code"])) {
    $emp_code = $_GET["emp_code"];
}

if (isset($_GET["PEL_id"])) {
    $PEL_id = $_GET["PEL_id"];
}

//Collect advance data to make installment plan
$advances = $con->SelectAllByCondition("advance_details", "ad_emp_code='$emp_code'");
$PEL_number_of_install = round($advances{0}->ad_install_no);
$PEL_installment = round($advances{0}->amount_per_installment);
$PEL_advance_amount = round($advances{0}->advance_total);
$PEL_remain_amount = round($advances{0}->advance_due);
$PEL_paid_amount = round($advances{0}->ad_paid_amount);

$year = $advances{0}->ad_year;
$month = $advances{0}->ad_month;

$employee_info = array();
$employee_info = $con->QueryResult("SELECT 
            e.emp_code,
            e.emp_firstname,
            d.designation_title,
            dep.department_title,
            sg.staffgrade_title,
            c.company_title
        FROM
                        tmp_employee e
                LEFT JOIN emp_designation ed ON ed.edes_emp_code = e.emp_code
                LEFT JOIN emp_department edp ON edp.edept_emp_code = e.emp_code
                LEFT JOIN emp_staff_grade eg ON eg.es_emp_code = e.emp_code
                
                LEFT JOIN designation d ON d.designation_id = ed.edes_designation_id
                LEFT JOIN department dep ON dep.department_id = edp.edept_dept_id
                LEFT JOIN staffgrad sg on sg.staffgrade_id = eg.es_staff_grade_id
                LEFT JOIN company c on c.company_id = e.company_id
            WHERE
               e.emp_code='$emp_code' AND ed.edes_emp_code ='$emp_code' AND ed.emp_designation_id IN(SELECT max(emp_designation_id) FROM emp_designation where edes_emp_code='$emp_code')
            AND edp.edept_emp_code ='$emp_code' AND edp.emp_department_id IN(SELECT max(emp_department_id) FROM emp_department where edept_emp_code='$emp_code')
            AND eg.es_emp_code ='$emp_code' AND eg.emp_staff_grade_id IN(SELECT max(emp_staff_grade_id) FROM emp_staff_grade where es_emp_code='$emp_code')");

$emp_firstname = '';
$designation_title = '';
$department_title = '';
$staffgrade_title = '';

if (count($employee_info) > 0) {
    $company_title = $employee_info{0}->company_title;
    $emp_firstname = $employee_info{0}->emp_firstname;
    $designation_title = $employee_info{0}->designation_title;
    $department_title = $employee_info{0}->department_title;
    $staffgrade_title = $employee_info{0}->staffgrade_title;
};
if (isset($_POST["btnPdf"])) {
    include("../job_card/MPDF/mpdf.php");
    $html = "";
    $html .= "<h4 style=\"text-align:center;\">";
    $html .= $company_title;
    $html .= "</h4>";

    $html .= "<table style=\"width:60%; font-size:12px;\">";
    $html .= "<tr>";
    $html .= "<td><b>Full Name:</b></td>";
    $html .= "<td>" . $emp_firstname . "</td>";
    $html .= "</tr>";

    $html .= "<tr>";
    $html .= "<td><b>Department:</b></td>";
    $html .= "<td>" . $department_title . "</td>";
    $html .= "</tr>";

    $html .= "<tr>";
    $html .= "<td><b>Designation:</b></td>";
    $html .= "<td>" . $designation_title . "</td>";
    $html .= "</tr>";

    $html .= "<tr>";
    $html .= "<td><b>Staff Grade:</b></td>";
    $html .= "<td>" . $staffgrade_title . "</td>";
    $html .= "</tr>";
    $html .= "</table>";
    $html .= "<br />";

    //Creating table header
    $html .= "<table style=\"width:100%; font-size:11px; border-collapse: collapse;\">";
    $html .= "<tr style=\"border-width:1px; border-style:solid;\">";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Installment No.</th>";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Month</th>";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Year</th>";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Installment</th>";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Realized</th>";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Total Advance</th>";
    $html .= "<th style=\"border-width:1px; width:80px; border-style:solid;\">Pending</th>";
    $html .= "</tr>";
 
    foreach($advances as $ad){
            $i = 1;
            $generate_date = $ad->ad_year;
            $generate_date .= "-";
            $generate_date .= $ad->ad_month;
            $generate_date .= "-";
            $generate_date .= "01";

            $frmt_date = date("Y-m-d", strtotime($generate_date));
            $final_date = date("Y-m-d", strtotime("$frmt_date -1 month"));

        $html .= "<tr>";
        $html .= "<td style=\"font-size:11px; height: 20px; width:80px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">". $ad->ad_install_no. "</td>";

        $html .= "<td style=\"font-size:11px; height: 20px; width:80px;  border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">";
        $view_month = date("Y-m-d", strtotime("$final_date +$i month"));

        $html .= date("F", strtotime($view_month)) . "</td>";
        $html .= "<td style=\"font-size:11px; height: 20px; width:80px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">";
        $view_month = date("Y-m-d", strtotime("$final_date +$i month"));
        $html .= date("Y", strtotime($view_month));
        $html .= "</td>";

        $html .= "<td style=\"font-size:11px; height: 20px; width:80px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">";
        $html .= $ad->amount_per_installment;;
        $html .= "</td>";
        $html .= "<td style=\"font-size:11px; height: 20px; width:80px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">";

        $html .= round($ad->advance_realized);

        $html .= "</td>";
        $html .= "<td style=\"font-size:11px; height: 20px; width:80px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">";
        $html .= $ad->advance_total;
        $html .= "</td>";
        $html .= "<td style=\"font-size:11px; height: 20px; width:80px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;\">";
        $html .= $ad->advance_due;
        $html .= "</td>";
        $html .= "</tr>";
    $i++;
    }
    $html .= "</table>";
    $mpdf = new mPDF('c', 'A4', '', '', 32, 25, 27, 25, 16, 13);
    $mpdf->SetDisplayMode('fullpage');
    $mpdf->list_indent_first_level = 0; // 1 or 0 - whether to indent the first level of a list
    //$stylesheet = file_get_contents('../../../resource/css/bootstrap.css');
    $mpdf->WriteHTML($stylesheet, 1); // The parameter 1 tells that this is css/style only and no body/html/text
    $mpdf->WriteHTML($html, 2);
    $mpdf->Output('Installment.pdf', 'I');
}
?>
<!--Including header files-->
<?php include '../view_layout/header_view.php'; ?>

<div class="col-md-12">
    <div class="col-md-6">
        <h4>Loan Type: Loan Against Salary</h4><br />
    </div>
    <div class="col-md-6 pull-right">
        <form method="post">
            <input type="hidden" name="tmp_employee_code" value="<?php echo $emp_code; ?>" />
            <input type="hidden" name="PEL_id" value="<?php echo $PEL_id; ?>" />
            <input type="submit" class="k-button pull-right" value="Export To PDF" name="btnPdf">
        </form>
    </div>
    <div class="clearfix"></div>
    <br />
    <div class="col-md-3">
        <span> Full Name: </span>
    </div>
    <div class="col-md-4">
        <span> <?php echo $emp_firstname; ?> </span>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-3">
        <span> Department: </span>
    </div>
    <div class="col-md-4">
        <span> <?php echo $department_title; ?> </span>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-3">
        <span> Designation:  </span>
    </div>
    <div class="col-md-4">
        <span><?php echo $designation_title; ?> </span>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-3">
        <span> Staff Grade:  </span>
    </div>
    <div class="col-md-4">
        <span> <?php echo $staffgrade_title; ?> </span>
    </div>
    <div class="clearfix"></div>
    <hr />
    <?php if (count($advances) > 0) : ?>
        <table id="grid">
            <colgroup>
                <col style="width:80px"/>
                <col style="width:80px" />
                <col style="width:80px" />
                <col style="width:80px" />
                <col style="width:80px" />
                <col style="width:80px" />
                <col style="width:80px" />
            </colgroup>
            <thead>
                <tr>
                    <th data-field="ad_install_no">Inst. No.</th>
                    <th data-field="ad_month">Month</th>
                    <th data-field="ad_year">Year</th>
                    <th data-field="amount_per_installment">Installment</th>
                    <th data-field="ad_paid_amount">Realized</th>
                    <th data-field="advance_total">Total Advance</th>
                    <th data-field="advance_due">Pending</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($advances as $ad){ //$con->debug($ad);exit();
                    /** Collect installment payment details
                     * compare : value of $i as installment no.
                     * and empcode to identify employee. */
//                    $inst_details = $con->SelectAllByCondition("advance_details", "PEA_id='$PEA_id' AND ad_emp_code='$emp_code' AND ad_install_no='$i'");
                    $i = 1;
                    $generate_date = $ad->ad_year;
                    $generate_date .= "-";
                    $generate_date .= $ad->ad_month;
                    $generate_date .= "-";
                    $generate_date .= "01";

                    $frmt_date = date("Y-m-d", strtotime($generate_date));
                    $final_date = date("Y-m-d", strtotime("$frmt_date -1 month"));
//                                        $con->debug($final_date);exit();
                    ?>
                    <tr>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php echo $ad->ad_install_no; ?>
                        </td>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php
                            $view_month = date("Y-m-d", strtotime("$final_date +$i month"));
                            echo date("F", strtotime($view_month));
                            ?>
                        </td>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php echo date("Y", strtotime($view_month)); ?>
                        </td>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php echo $ad->amount_per_installment; ?>
                        </td>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php
                                echo round($ad->ad_paid_amount); ?>
                        </td>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php echo $ad->advance_total; ?>
                        </td>
                        <td style="font-size:11px; height: 20px; border-style: solid; border-width:1px; border-color: darkgray; border-collapse: collapse;">
                            <?php echo $ad->advance_due; ?>
                        </td>
                    </tr>
                    <?php
               $i++; } ?> 
            </tbody>
        </table>
    <?php else : ?>
    <?php endif; ?>
    <script>
        $(document).ready(function() {
            $("#grid").kendoGrid({
                pageable: {
                    refresh: true,
                    input: true,
                    numeric: false,
                    pageSize: 30,
                    pageSizes: true,
                    pageSizes: [30, 50, 100, 200]
                },
                filterable: true,
                sortable: true,
                groupable: true
            });
        });
    </script>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>