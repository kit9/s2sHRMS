<?php
/* Autjor : Asma
 * Date : 21 March 15
 */
session_start();
//Importing class library
include ('../../config/class.config.php');
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

$err = "";
$msg = '';

/*
* Users select a company
* After submitting, a table shows up
* In each row, employee basic details
* After basic details column, from db
* collect all addition item and place them
* as column. Under them, each row will contain
* an input box. For example, Special bonus; can 
* be updated.   
*/

$companies = array();
$companies = $con->SelectAll("company");

if (isset($_POST["generate_data"])){
    extract($_POST);

    $zero = "0000-00-00";

    $collected_year = $year;
    $collected_month = $month;
    $assumed_day = '01';

    $date_array = array($collected_year, $month, $assumed_day);
    $generated_date = implode("-", $date_array);
    $formatted_date = date("Y-m-d", strtotime($generated_date));

    $temp_array = array();
    $employees = array();
    $final_array = array();
    $employees = $con->QueryResult("select emp_code, emp_firstname from tmp_employee tmp WHERE
    tmp.emp_code IN (SELECT
         ec_emp_code
         FROM
         emp_company
         WHERE
         ec_company_id = '$company_id'
         AND (
             (
                ec_effective_start_date <= '$formatted_date'
                AND ec_effective_end_date >= '$formatted_date'
                )
    OR (
        ec_effective_start_date <= '$formatted_date'
        AND ec_effective_end_date = '$zero'
        )
    ))");

    foreach ($employees as $emp) {
       //Create rows with emp code and emp first name
       array_push($temp_array, $emp->emp_code, $emp->emp_firstname);

       //Get salary headers 
       $get_all_com = $con->SelectAllByCondition("payroll_salary_header", "PSH_display_on='add'");
       foreach ($get_all_com as $key => $value) {
       array_push ($temp_array, $value->PSH_id);    
    }

    array_push($final_array,$temp_array); 

}


//Build header array
$header_array = array();
array_push($header_array, "Employee Code", "Employee Name");
$components = $con->SelectAllByCondition("payroll_salary_header", "PSH_display_on='add'");
foreach ($components as $component) {
   array_push($header_array, $component->PSH_header_title); 
}


}

if (isset($_GET['empl_code'])) {
    $empl_code = base64_decode($_GET['empl_code']);
    $get_detail = $con->QueryResult("SELECT ps.*,e.emp_firstname,e.emp_lastname,h.* FROM payroll_salary_header h"
        . " left join payroll_employee_salary ps on ps.PES_PSH_id=h.PSH_id"
        . " left join tmp_employee e on ps.PES_employee_code=e.emp_code "
        . " WHERE ps.PES_employee_code='$empl_code'");
    $get_all_com = $con->SelectAll("payroll_salary_header");
}




if (isset($_POST["btnSave"])) {
    extract($_POST);
    $quer_header = $con->QueryResult("SELECT * FROM payroll_salary_header");

    foreach ($_POST as $key => $val) {
        $sal_part = explode("_", $key);
        foreach ($quer_header as $PSH) {
            if (isset($sal_part[2]) && $sal_part[2] == $PSH->PSH_id) {

                $check_exist_headr = $con->SelectAllByCondition("payroll", "payroll_emp_code='$empl_code' AND payroll_salary_year='2015' AND payroll_salary_month='2' AND PES_PSH_id='$sal_part[2]'");

                if (count($check_exist_headr) > 0) {
                    $payroll_id = $check_exist_headr{0}->payroll_id;
                    $payroll_salary_original = $check_exist_headr{0}->payroll_salary_original;
                    
                    $update_payment_array = array(
                        "payroll_id" => $payroll_id,
                        "payroll_emp_code" => $empl_code,
                        "payroll_salary_year" => '2015',
                        "payroll_salary_month" => "2", 
                        "payroll_salary_finalized" => $val, 
                    );
                    

                    $update_result = $con->update("payroll", $update_payment_array);
                    if ($update_result == 1){
                        $msg = 'Payment information is updated successfully.';
                    } else {
                        $err = "Payment information update failed.";
                    }                    
                } else {
                    $insert_salary_array = array(
                        "payroll_emp_code" => $empl_code,
                        "payroll_salary_year" => '2015',
                        "payroll_salary_month" => "2", 
                        "payroll_salary_original" => $val,
                        "payroll_salary_finalized" => $val,
                        "PES_PSH_id" => $PSH->PSH_id 
                    );

                    $insertion_result = $con->insert("payroll", $insert_salary_array);
                    if ($insertion_result == 1){
                        $msg = "Payment is successfully updated.";
                    } else {
                        $err = "Payment information update failed.";
                    }
                }
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>

<?php include("../../layout/msg.php"); ?>
<form method="post">      
   <div class="col-md-4" style="padding-left: 0px;"> 
    <label for="Full name">Company Name:</label><br/> 
    <select id="company" style="width: 80%" name="company_id">
        <option value="0">Select Company</option>
        <?php if (count($companies) >= 1): ?>
            <?php foreach ($companies as $com): ?>
                <option value="<?php echo $com->company_id; ?>" 
                    <?php
                    if ($com->company_id == $company_id) {
                        echo "selected='selected'";
                    }
                    ?>><?php echo $com->company_title; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label for="Full name">Year:</label><br/> 
        <input id="year1" name="year" style="width: 80%;" value="<?php echo $year; ?>" />
    </div>
    <div class="col-md-4">
        <label for="Full name">Month:</label> <br />
        <input id="month1" name="month" style="width: 80%;" value="<?php echo $month; ?>" />
    </div>
    <div class="clearfix"></div>
    <br/>
    <input type="submit" name="generate_data" value="Generate Data" class="k-textbox">
</form>

<form method="post">
    <!--Kendo custom grid for main data-->
    <div id="example" class="k-content">

        <table id="grid" style="table-layout: fixed; ">
            <colgroup>
            <?php
            $total = count($header_array);
            for ($i = 1; $i <= $total + 1; $i++){
              echo '<col style="width:130px" />'; 
          }
          ?>
      </colgroup>
      <thead>
        <tr>
            <?php
            foreach ($header_array as $key => $value) {
                echo '<th data-field="' . $value . '">' . $value . '</th>';
            }
            ?>
            <th data-field="action">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($final_array as $data ):?>        
            <tr>
                <?php foreach ($date as $key => $value) {
                    echo '<td>'. $value .'</td>';
                } ?>   
            </tr>
            <td>Action</td>
        <?php endforeach; ?>
    </tbody>
</table>
<script>
    $(document).ready(function () {
        $("#grid").kendoGrid({
            pageable: {
                refresh: true,
                input: true,
                numeric: false,
                pageSize: 10,
                pageSizes: true,
                pageSizes: [10, 20, 50]
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
<!--Kendo custom grid ends here-->
</form>


<?php
include '../view_layout/footer_view.php';

function get_bd_money_format($amount) {
    $output_string = '';
    $fraction = '';
    $tokens = explode('.', $amount);
    $number = $tokens[0];
    if (count($tokens) > 1) {
        $fraction = (double) ('0.' . $tokens[1]);
        $fraction = $fraction * 100;
        $fraction = round($fraction, 0);
        $fraction = '.' . $fraction;
    }
    $number = $number . '';
    $spl = str_split($number);
    $lpcount = count($spl);
    $rem = $lpcount - 3;
    '';

    if ($lpcount % 2 == 0) {
        for ($i = 0; $i <= $lpcount - 1; $i++) {

            if ($i % 2 != 0 && $i != 0 && $i != $lpcount - 1) {
                $output_string .= ",";
            }
            $output_string .= $spl[$i];
        }
    }

    if ($lpcount % 2 != 0) {
        for ($i = 0; $i <= $lpcount - 1; $i++) {
            if ($i % 2 == 0 && $i != 0 && $i != $lpcount - 1) {
                $output_string .= ",";
            }
            $output_string .= $spl[$i];
        }
    }
    return $output_string . $fraction;
}
?>

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

        $("#month1").kendoComboBox({
            autoBind: false,
            cascadeFrom: "year1",
            placeholder: "Select Month..",
            dataTextField: "month",
            dataValueField: "month_id",
            dataSource: {
                transport: {
                    read: {
                        url: "../../controller/month.php",
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
<script type="text/javascript">
    $(document).ready(function () {
        $("#company").kendoDropDownList();
    });
</script>