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
$company_id = '';

$leave_types = $con->SelectAll("leave_policy");

/*
 * Requirement: Excel Report for Type Wise
 * Within Date Range
 * Web view with the same data
 */

//Web view generation
if (isset($_POST["search"])) {
    extract($_POST);

    //Validation 
    if ($company_id == '') {
        $err = 'No Company Was Selected!';
    } else if ($start_date == '') {
        $err = 'No Start Date Was Selected!';
    } else if ($end_date == '') {
        $err = 'No End Date Was Selected!';
    } else if ($start_date > $end_date) {
        $err = 'Invalid Date Range!';
    } else if ($leave_type_id == '') {
        $err = 'No Leave Type Was Selected!';
    } else {

        //Build date ranges
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        //Collect Leave Type
        $leave_title_info = $con->SelectAllByCondition("leave_policy", "leave_policy_id='$leave_type_id'");
        if (count($leave_title_info) > 0) {
            $leave_title = $leave_title_info{0}->leave_title;
        }

        //Collect company title
        $company_info = $con->SelectAllByCondition("company", "company_id='$company_id'");
        if (count($company_info) > 0) {
            $company_title = $company_info{0}->company_title;
        }

        /*
         * Build query string
         * Condition : Company Id, Department Id, Date Range
         */
        $query_string = "SELECT COUNT(*) AS totalLeave, leave_application_details.emp_code, tmp.emp_firstname, tmp.emp_dateofjoin, dept.department_title ";
        $query_string .= "FROM leave_application_details ";
        $query_string .= "JOIN leave_application_master lam ON lam.leave_application_master_id = leave_application_details.leave_application_master_id ";
        $query_string .= "JOIN tmp_employee tmp on tmp.emp_code = leave_application_details.emp_code ";
        $query_string .= "JOIN  department dept on dept.department_id = tmp.emp_department ";
        $query_string .= "WHERE leave_type_id = '" . $leave_type_id . "' ";
        $query_string .= "AND lam.company_id = '" . $company_id . "' ";
        if ($department_id != '') {
            $query_string .= "AND tmp.emp_department = '" . $department_id . "' ";
        }
        $query_string .= "AND details_date BETWEEN '" . $start_date . "' AND '" . $end_date . "' ";
        $query_string .= "GROUP BY leave_application_details.emp_code";

        //Execute query
        $leave_summary = $con->QueryResult($query_string);


        //Initiate arrays
        $column = array();
        $data_array = array();

        //Headers Array
        array_push($column, "Employee Code", "Full Name", "Department", "Joining Date", $leave_title);
        if (count($leave_summary) > 0) {
            foreach ($leave_summary as $ls) {

                //Elements array
                $primary_data_array = array();

                //Employee code
                if (isset($ls->emp_code)) {
                    array_push($primary_data_array, $ls->emp_code);
                } else {
                    array_push($primary_data_array, " ");
                }

                //Full Name
                if (isset($ls->emp_firstname)) {
                    array_push($primary_data_array, $ls->emp_firstname);
                } else {
                    array_push($primary_data_array, " ");
                }

                //Department
                if (isset($ls->department_title)) {
                    array_push($primary_data_array, $ls->department_title);
                } else {
                    array_push($primary_data_array, " ");
                }

                //Joining Date
                if (isset($ls->emp_dateofjoin)) {
                    array_push($primary_data_array, $ls->emp_dateofjoin);
                } else {
                    array_push($primary_data_array, " ");
                }

                //Total Leave
                if (isset($ls->totalLeave)) {
                    array_push($primary_data_array, $ls->totalLeave);
                } else {
                    array_push($primary_data_array, " ");
                }

                //Push the main array with the primary area.
                array_push($data_array, $primary_data_array);
            }

            //Add columns at first of the elements array
            array_unshift($data_array, $column);

            //Count rows
            $count = count($data_array);

            //Count columns
            $countCol = count($data_array[0]);

            $createPHPExcel = new PHPExcel();
            $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
            $rowCount = 0;

            //Set static fields
            $cWorkSheet->setCellValueByColumnAndRow(0, 1, "$company_title");
            $cWorkSheet->setCellValueByColumnAndRow(0, 2, "Date Range: $start_date  TO $end_date");

            //Write data to escel
            for ($i = 1; $i <= $count; $i++) {
                for ($j = 0; $j <= $countCol - 1; $j++) {
                    $cWorkSheet->setCellValueByColumnAndRow($j, $i + 3, $data_array["$rowCount"]["$j"]);
                }
                $rowCount++;
            }

            $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
            $filename = $company_id . rand(0, 9999999) . "LeaveSummary.xlsx";
            $objWriter->save("$filename");
            header("location:$filename");
            
        } else {
            $err = 'No Leave Information was Found Against Selected Criteria.';
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Type Wise Leave Register</h6></div>
    <div class="widget-body" style="background-color: white;">
        <!--Employee Code-->

        <!--Declare error message-->
        <?php include("../../layout/msg.php"); ?>
        <form method="post">
            <div class="col-md-6">
                <label for="Full name">Company:</label><br/>
                <input type="text" id="company_id" name="company_id" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="col-md-6">
                <label for="Full name">Department:</label><br/>
                <input type="text" id="departments" name="department_id" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-6">
                <label for="Start Date">Start Date:</label><br/>
                <input type="text" id="start_date" class="emp_datepicker" value="<?php echo $start_date; ?>" name="start_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>

            <div class="col-md-6">
                <label for="Start Date">End Date:</label><br/>
                <input id="end_date" type="text" class="emp_datepicker" value="<?php echo $end_date; ?>" name="end_date" placeholder="" class="k-textbox" style="width: 80%;"/>
            </div>

            <div class="clearfix"></div>
            <br />
            <div class="col-md-6">
                <label for="Start Date">Leave Type:</label><br/>
                <select id="leave_type" style="width: 80%" name="leave_type_id">
                    <option value="0">Select Leave Type</option>
                    <?php if (count($leave_types) >= 1): ?>
                        <?php foreach ($leave_types as $leave): ?>
                            <option value="<?php echo $leave->leave_policy_id; ?>" 
                            <?php
                            if ($leave->leave_policy_id == $leave_type_id) {
                                echo "selected='selected'";
                            }
                            ?>>
                                <?php echo $leave->leave_title; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <script>
                $(document).ready(function() {
                    $("#start_date").kendoDatePicker();
                    $("#end_date").kendoDatePicker();
                    $("#leave_type").kendoDropDownList();
                });
            </script>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-3">
                <input type="submit" class="k-button" name="search" value="View Summary">
            </div>

            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#company_id").kendoComboBox({
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

        jQuery("#departments").kendoComboBox({
            placeholder: "Select department...",
            dataTextField: "department_title",
            dataValueField: "department_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/department.php",
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