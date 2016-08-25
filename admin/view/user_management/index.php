<?php
/*
 * Primary written by: Shahnaz
 * Enhanced By: Rajan
 */
session_start();
//Importing class library
include ('../../config/class.config.php');
include("../../lib/PHPExcel/PHPExcel/IOFactory.php");
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

//error_reporting(0);

$company_id = "";

$is_super = $_SESSION["is_super"];
$user_type = $_SESSION["user_type"];
$com_id = $_SESSION["company_id"];
$userarray = array();

$department_id = '';

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Generate today's date
//Collect and format date from the system.
$today = date("Y/m/d");
$sys_date = date_create($today);
$formatted_today = date_format($sys_date, 'Y-m-d');
$zero = "0000-00-00";

//Disable company filter temporarily
$companies = array();
//if ($is_super == "yes") {
$companies = $con->SelectAll("company");
//} else {
//$companies = $con->SelectAllByCondition("company", " company_id='$com_id'");
//}
// search_user
if (isset($_POST["btnSearch"])) {
    extract($_POST);
    $_SESSION["company_id"] = $company_id;
    if ($department_id != '') {
        $_SESSION["department_id"] = $department_id;
    } else {
        unset($_SESSION["department_id"]);
    }

    if ($is_super == "yes") {
        $combo_company = $_POST["company_id"];
        //$userarray = $con->SelectAllByCondition("tmp_employee", " company_id='$combo_company' order by emp_id DESC");
        $user_query = "SELECT
	*
FROM
	tmp_employee
WHERE
	emp_code IN (
SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$combo_company'
		AND (
			(
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date >= '$formatted_today'
			)
			OR (
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date = '$zero'
			)
		)

	) ";
        
        //If department is selected
        if ($department_id != '') {
            $user_query .="AND emp_department='" . $department_id . "'";
        }
        $userarray = $con->QueryResult($user_query);
    } else {
        $s_com_id = $_SESSION["company_id"];
        //$userarray = $con->SelectAllByCondition("tmp_employee", " company_id='$s_com_id' order by emp_id DESC");
        $user_query = "SELECT
	*
FROM
	tmp_employee
WHERE
	emp_code IN (
SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$s_com_id'
		AND (
			(
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date >= '$formatted_today'
			)
			OR (
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date = '$zero'
			)
		)

	) ";

        //If department is selected
        if ($department_id != '') {
            $user_query .="AND emp_department='" . $department_id . "'";
        }

        $userarray = $con->QueryResult($user_query);
    }
}

$logged_emp_code = '';
$range_start = '';
$range_end = '';

$staff_grade_permission = array();

if (isset($_SESSION['emp_code'])) {
    $logged_emp_code = $_SESSION['emp_code'];
}

//Find staff grade permission
$staff_grade_permission = $con->SelectAllByCondition("salary_view_permission", "svp_emp_code='$logged_emp_code'");

if (count($staff_grade_permission) > 0) {
    $range_start = $staff_grade_permission{0}->svp_sg_position_from;
    $range_end = $staff_grade_permission{0}->svp_sg_position_to;
}

if (isset($_POST["report"])) {
    extract($_POST);
    $column = array();
    $list = array();

    $company_id = $_SESSION["company_id"];

    //Headers Array
    array_push($column, "Employee Code", "Company", "Full Name", "Email (Office)", "Email(Persona)", "Designation", "Department", "Job Location", "Subsection", "Date of Join", "Staff Grade", "Reporting Method", "Supervisor");

    //Now build dynamic salary header and push it to array
    //Collect salary headers displayed in employee module.
    $salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_show_in_tmp_mod='yes'");
    if (count($salary_headers) > 0) {
        foreach ($salary_headers as $header) {
            //Add emp module salary headers to sub header array
            array_push($column, $header->PSH_header_title);
        }
    }
    array_push($column, "Gender", "Prop. Confirmatin Date", "Date of Birth", "Present Address", "Permanent Address", "Mobile No (Office)", "Mobile No(Personal)", "Land Phone", "Resignation Date", "Replacement", "Account No.", "Bank", "Blood Group", "Marital Status", "City", "Country", "Wedding Date", "Spouse Name", "No of Children", "Family Member", "Nominee Name", "Emergency Contact", "Emergency Contact Name", "PF Eligible", "PF Effective From", "OT Eligible");
    /*
     * Making rows
     * Fetching data :: mysql_fetch_array
     */
    $data_array = array();
    $all_info = "
	SELECT
        tmp.emp_code,
	com.company_title,
	tmp.emp_firstname,
	tmp.emp_email_office,
        tmp.emp_email_personal,
	desg.designation_title,
	dept.department_title,
	tmp.job_location,
	sub.subsection_title,
	tmp.emp_dateofjoin,
	sg.staffgrade_title,
	rep.reporting_title,
	tmp.supervisor_id,
	tmp.emp_gender,
	tmp.emp_prop_confirmation_date,
	tmp.emp_dateofbirth,
	tmp.emp_address_present,
        tmp.emp_address_permanent,
	tmp.emp_phone_company,
        tmp.emp_phone_personal,
        tmp.emp_landphone,
	tmp.emp_resignation_date,
	tmp.emp_replacement_of,
	tmp.emp_account_number,
	tmp.emp_bank_title,
	tmp.emp_blood_group,
	tmp.emp_marital_status,
	city.city_name,
	con.country_name,
	tmp.user_type,
        tmp.wedding_date,
        tmp.spouse_name,
        tmp.no_of_children,
        tmp.nominee_name,
        tmp.emergency_contact_name,
        tmp.emergency_contact_phone,
        tmp.is_pf_eligible,
        tmp.pf_effective_from,
        tmp.is_ot_eligible,
        tmp.family_member
        
FROM
    tmp_employee tmp
LEFT JOIN department dept ON tmp.emp_department = dept.department_id
LEFT JOIN designation desg ON tmp.emp_designation = desg.designation_id
LEFT JOIN staffgrad sg ON tmp.emp_staff_grade = sg.staffgrade_id
LEFT JOIN subsection sub on tmp.emp_subsection = sub.subsection_id
LEFT JOIN company com ON tmp.company_id = com.company_id
LEFT JOIN country con ON tmp.country = con.country_id
LEFT JOIN city ON tmp.city = city.city_id
LEFT JOIN company sal ON tmp.emp_notes_salary_hub = sal.company_id
LEFT JOIN reporting_method rep ON tmp.reporting_id = rep.reporting_id
WHERE tmp.emp_code IN (

SELECT
			ec_emp_code
		FROM
			emp_company
		WHERE
			ec_company_id = '$company_id' 
		AND (
			(
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date >= '$formatted_today'
			)
			OR (
				ec_effective_start_date <= '$formatted_today'
				AND ec_effective_end_date = '$zero'
			)
		)

) ";
    //Collect department id from session
    $department_id = $_SESSION["department_id"];
    //If department is selected
    if ($department_id != '') {
        $all_info .="AND emp_department='" . $department_id . "'";
    }
    $output = $con->QueryResult($all_info);
    foreach ($output as $data) {
        $primary_data_array = array();
        $emp_code = $data->emp_code;

        //Employee code
        if (isset($data->emp_code)) {
            array_push($primary_data_array, $data->emp_code);
        } else {
            array_push($primary_data_array, " ");
        }
        //Company
        if (isset($data->company_title)) {
            array_push($primary_data_array, $data->company_title);
        } else {
            array_push($primary_data_array, " ");
        }

        //Name
        if (isset($data->emp_firstname)) {
            array_push($primary_data_array, $data->emp_firstname);
        } else {
            array_push($primary_data_array, " ");
        }

        //Email office
        if (isset($data->emp_email_office)) {
            array_push($primary_data_array, $data->emp_email_office);
        } else {
            array_push($primary_data_array, " ");
        }

        //Email personal
        if (isset($data->emp_email_personal)) {
            array_push($primary_data_array, $data->emp_email_personal);
        } else {
            array_push($primary_data_array, " ");
        }

        //Desingation title 
        if (isset($data->designation_title)) {
            array_push($primary_data_array, $data->designation_title);
        } else {
            array_push($primary_data_array, " ");
        }

        //Desingation title 
        if (isset($data->department_title)) {
            array_push($primary_data_array, $data->department_title);
        } else {
            array_push($primary_data_array, " ");
        }

        //Job location
        if (isset($data->job_location)) {
            array_push($primary_data_array, $data->job_location);
        } else {
            array_push($primary_data_array, " ");
        }

        //Subsection
        if (isset($data->subsection_title)) {
            array_push($primary_data_array, $data->subsection_title);
        } else {
            array_push($primary_data_array, " ");
        }

        //Date of Join
        if (isset($data->emp_dateofjoin)) {
            array_push($primary_data_array, $data->emp_dateofjoin);
        } else {
            array_push($primary_data_array, " ");
        }

        //Staff grade title
        if (isset($data->staffgrade_title)) {
            array_push($primary_data_array, $data->staffgrade_title);
        } else {
            array_push($primary_data_array, " ");
        }

        //reporting method
        if (isset($data->reporting_title)) {
            array_push($primary_data_array, $data->reporting_title);
        } else {
            array_push($primary_data_array, " ");
        }



        //Supervisor
        if (isset($data->supervisor_id)) {
            //Build supervisor
            $sup_id = $data->supervisor_id;
            $info = $con->SelectAllByCondition("tmp_employee", "emp_id='$sup_id'");
            if (count($info) > 0) {
                $sup_emp_code = $info{0}->emp_code;
            }

            array_push($primary_data_array, $sup_emp_code);
        } else {
            array_push($primary_data_array, " ");
        }


        //Find this employee's staff grade
        $priority = '';
        $current_sgrade = array();
        $current_sgrade = $con->SelectAllByCondition("emp_staff_grade", "es_emp_code='$emp_code' ORDER BY emp_staff_grade_id DESC LIMIT 0,1");
        // echo $query_sgrade = "SELECT * FROM emp_staff_grade WHERE es_emp_code='$emp_code' ORDER BY emp_staff_grade_id DESC LIMIT 0,1";


        if (count($current_sgrade) > 0) {
            $emp_staff_grade = $current_sgrade{0}->es_staff_grade_id;
            //find staff grade priority
            $staff_meta = $con->SelectAllByCondition("staffgrad", "staffgrade_id='$emp_staff_grade'");
            if (count($staff_meta) > 0) {
                $priority = $staff_meta{0}->priority;
            }
        }

        $salary_headers = $con->SelectAllByCondition("payroll_salary_header", "PSH_show_in_tmp_mod='yes'");
        if (count($salary_headers) > 0) {
            foreach ($salary_headers as $header) {
                //Add emp module salary headers to sub header array
                $header_id = $header->PSH_id;
                $salary_info = $con->SelectAllByCondition("payroll_employee_salary", "PES_employee_code='$emp_code' AND PES_PSH_id='$header_id'");
                if (count($salary_info) > 0) {
                    $salary_amount = $salary_info{0}->PES_amount;
                    if ($priority >= $range_start && $priority <= $range_end) {
                        //Find priorty for this employee code
                        if ($salary_amount != '') {
                            array_push($primary_data_array, $salary_amount);
                        } else {
                            array_push($primary_data_array, " ");
                        }
                    } else if ($logged_emp_code == $emp_code) {
                        if ($salary_amount != '') {
                            array_push($primary_data_array, $salary_amount);
                        } else {
                            array_push($primary_data_array, " ");
                        }
                    } else {
                        array_push($primary_data_array, "unauthorized");
                    }
                } else {
                    array_push($primary_data_array, " ");
                }
            }
        }

        //Gender
        if (isset($data->emp_gender)) {
            array_push($primary_data_array, $data->emp_gender);
        } else {
            array_push($primary_data_array, " ");
        }

        //Proposed confirmation date
        if (isset($data->emp_prop_confirmation_date)) {
            array_push($primary_data_array, $data->emp_prop_confirmation_date);
        } else {
            array_push($primary_data_array, " ");
        }

        //Date of birth
        if (isset($data->emp_dateofbirth)) {
            array_push($primary_data_array, $data->emp_dateofbirth);
        } else {
            array_push($primary_data_array, " ");
        }

        //Address (present)
        if (isset($data->emp_address_present)) {
            array_push($primary_data_array, $data->emp_address_present);
        } else {
            array_push($primary_data_array, " ");
        }

        //Address (parmanent)
        if (isset($data->emp_address_permanent)) {
            array_push($primary_data_array, $data->emp_address_permanent);
        } else {
            array_push($primary_data_array, " ");
        }

        //Contact Number (company)
        if (isset($data->emp_phone_company)) {
            array_push($primary_data_array, $data->emp_phone_company);
        } else {
            array_push($primary_data_array, " ");
        }

        //Contact Number (Personal)
        if (isset($data->emp_phone_personal)) {
            array_push($primary_data_array, $data->emp_phone_personal);
        } else {
            array_push($primary_data_array, " ");
        }

        //Contact Number (Land Phone)
        if (isset($data->emp_landphone)) {
            array_push($primary_data_array, $data->emp_landphone);
        } else {
            array_push($primary_data_array, " ");
        }

        //Resignation date
        if (isset($data->emp_resignation_date)) {
            array_push($primary_data_array, $data->emp_resignation_date);
        } else {
            array_push($primary_data_array, " ");
        }

        //Replacement of 
        if (isset($data->emp_replacement_of)) {
            array_push($primary_data_array, $data->emp_replacement_of);
        } else {
            array_push($primary_data_array, " ");
        }

        //Replacement of 
        if (isset($data->emp_account_number)) {
            array_push($primary_data_array, $data->emp_account_number);
        } else {
            array_push($primary_data_array, " ");
        }

        //Bank title
        if (isset($data->emp_bank_title)) {
            array_push($primary_data_array, $data->emp_bank_title);
        } else {
            array_push($primary_data_array, " ");
        }

        //Blood group
        if (isset($data->emp_blood_group)) {
            array_push($primary_data_array, $data->emp_blood_group);
        } else {
            array_push($primary_data_array, " ");
        }

        //Marital Status
        if (isset($data->emp_marital_status)) {
            array_push($primary_data_array, $data->emp_marital_status);
        } else {
            array_push($primary_data_array, " ");
        }

        //City Name
        if (isset($data->city_name)) {
            array_push($primary_data_array, $data->city_name);
        } else {
            array_push($primary_data_array, " ");
        }

        //Country Name
        if (isset($data->country_name)) {
            array_push($primary_data_array, $data->country_name);
        } else {
            array_push($primary_data_array, " ");
        }

        //Additional Custom Fields :: wedding date
        if (isset($data->wedding_date)) {
            array_push($primary_data_array, $data->wedding_date);
        } else {
            array_push($primary_data_array, " ");
        }

        //Spouse Name
        if (isset($data->spouse_name)) {
            array_push($primary_data_array, $data->spouse_name);
        } else {
            array_push($primary_data_array, " ");
        }

        //No of children
        if (isset($data->no_of_children)) {
            array_push($primary_data_array, $data->no_of_children);
        } else {
            array_push($primary_data_array, " ");
        }

        //Family Member
        if (isset($data->family_member)) {
            array_push($primary_data_array, $data->family_member);
        } else {
            array_push($primary_data_array, " ");
        }

        //Nominee Name
        if (isset($data->nominee_name)) {
            array_push($primary_data_array, $data->nominee_name);
        } else {
            array_push($primary_data_array, " ");
        }

        //Emergency Contact Phone
        if (isset($data->emergency_contact_phone)) {
            array_push($primary_data_array, $data->emergency_contact_phone);
        } else {
            array_push($primary_data_array, " ");
        }

        //Emergency Contact Name
        if (isset($data->emergency_contact_name)) {
            array_push($primary_data_array, $data->emergency_contact_name);
        } else {
            array_push($primary_data_array, " ");
        }

        //Pf Eligibility
        if (isset($data->is_pf_eligible)) {
            array_push($primary_data_array, $data->is_pf_eligible);
        } else {
            array_push($primary_data_array, " ");
        }

        //PF Effective From
        if (isset($data->pf_effective_from)) {
            array_push($primary_data_array, $data->pf_effective_from);
        } else {
            array_push($primary_data_array, " ");
        }

        //Pf Eligibility
        if (isset($data->is_ot_eligible)) {
            if ($data->is_ot_eligible == 1) {
                array_push($primary_data_array, "yes");
            } else {
                array_push($primary_data_array, " ");
            }
        } else {
            array_push($primary_data_array, " ");
        }

        //Push the main array with the primary area.
        array_push($data_array, $primary_data_array);
    }

    array_unshift($data_array, $column);


    $count = count($data_array);
    $countCol = count($data_array[0]);

    $createPHPExcel = new PHPExcel();
    $cWorkSheet = $createPHPExcel->setActiveSheetIndex(0);
    $rowCount = 0;

    for ($i = 1; $i <= $count; $i++) {
        for ($j = 0; $j <= $countCol - 1; $j++) {
            $cWorkSheet->setCellValueByColumnAndRow($j, $i, $data_array["$rowCount"]["$j"]);
        }
        $rowCount++;
    }


    $objWriter = new PHPExcel_Writer_Excel2007($createPHPExcel);
    $filename = $company_id . rand(0, 9999999) . "Employee_List.xlsx";
    $objWriter->save("$filename");
    header("location:$filename");
}
?>

<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Employee Information</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <form method="POST">

            <div class="col-md-6"> 
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
            <div class="col-md-6"> 
                <label for="Full name">Department:</label><br/>
                <input type="text" id="department_id" name="department_id" value="<?php echo $department_id; ?>" placeholder=""  style="width: 80%;"/>
            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-2">
                <input class="k-button" type="submit" value="View Employee List" name="btnSearch"/>
            </div>


        </form>

        <div class="clearfix"></div>
        <br/>

        <?php if (isset($_POST['btnSearch'])) { ?>
            <?php
            $s_com_id = $_SESSION["company_id"];
            ?>

            <?php if ($con->hasPermissionExport($permission_id) == "yes"): ?>
                <form method="post">
                    <input type="submit" class="k-button pull-right" name="report" value="Export to Excel">
                </form> 
            <?php endif; ?>

            <div class="clearfix"></div>
            <br />
            <?php if ($con->hasPermissionCreate($permission_id) == "yes"): ?>
                <div style="border-left: 1px solid #DADADA;border-right: 1px solid #DADADA; " class="k-toolbar k-grid-toolbar">
                    <a class="k-button k-button-icontext k-grid-add" href="<?php echo $con->baseUrl("view/employee/add.php") ?>"> 
                        <span class="k-icon k-add"></span>
                        Add
                    </a>
                </div>
            <?php endif; ?>
            <div id="example" class="k-content">
                <form class="form-horizontal" method= "post" enctype="multipart/form-data">
                    <table id="grid">
                        <colgroup>

                            <col style="width:18%"/>
                            <col style="width:13%" />
                            <col style="width:18%" />
                            <col style="width:19%" />
                            <col style="width:23%" />
                        </colgroup>
                        <thead>
                            <tr>
                                <th data-field="emp_firstname">Name</th>
                                <th data-field="emp_code">Employee Code</th>
                                <th data-field="emp_email">Email (Office)</th>
                                <th data-field="action">Action</th>
                            </tr>
                        </thead>
                        <tbody>


                            <?php if (count($userarray) >= 1): ?>
                                <?php foreach ($userarray as $ap): ?>

                                    <tr>
                                        <td style="font-size: 14; font-family: calibri;"><?php echo $ap->emp_firstname; ?></td>
                                        <td><?php echo $ap->emp_code; ?></td>
                                        <td><?php echo $ap->emp_email_office; ?></td>
                                        <td style="width:250px;" role="gridcell">
                                            <?php if ($con->hasPermissionView($permission_id) == "yes"): ?>
                                                <a target="_blank" class="k-button k-button-icontext k-grid-edit" href="<?php echo $con->baseUrl("view/employee/details.php?emp_id=$ap->emp_id") ?>">
                                                    <span class="k-icon k-detail-row"></span>
                                                    Details
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($con->hasPermissionUpdate($permission_id) == "yes"): ?>
                                                <a target="_blank" class="k-button k-button-icontext k-grid-edit" href="<?php echo $con->baseUrl("view/employee/edit.php?emp_id=$ap->emp_id") ?>"> 
                                                    <span class="k-icon k-edit"></span>
                                                    Edit </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?> 
                        </tbody>
                    </table>
                </form>
                <script>
                    $(document).ready(function() {
                        $("#grid").kendoGrid({
                            pageable: {
                                refresh: true,
                                input: true,
                                numeric: false,
                                pageSize: 10,
                                pageSizes: true,
                                pageSizes: [10, 20, 50],
                            },
                            filterable: true,
                            sortable: true,
                            groupable: true
                        });
                    });

                </script>
            </div>
        <?php } ?>



        <script type="text/javascript">
            $(document).ready(function() {
                $("#company").kendoDropDownList();
                $("#department_id").kendoComboBox({
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



    </div>  
</div>

<div class="clearfix"></div>
<br />

<?php include '../view_layout/footer_view.php'; ?>