<?php
session_start();
error_reporting(0);
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Connection string
$open = $con->open();

if (isset($_GET["emp_id"])) {
    $emp_id = $_GET["emp_id"];

//Fetching employee info
    $employees = $con->SelectAllByCondition("tmp_employee", "emp_id='$emp_id'");
    foreach ($employees as $employee) {
        $emp_id = $employee->emp_id;
        $emp_firstname = $employee->emp_firstname;
        $emp_code = $employee->emp_code;
        $company_id = $employee->company_id;
        $emp_lastname = $employee->emp_lastname;
        $emp_email = $employee->emp_email;
        $emp_designation = $employee->emp_designation;
        $emp_department = $employee->emp_department;
        $emp_subsection = $employee->emp_subsection;
        $emp_dateofjoin = $employee->emp_dateofjoin;
        $emp_staff_grade = $employee->emp_staff_grade;
        $staffgrade_id = $employee->emp_staff_grade;
        $supervisor_id = $employee->supervisor_id;
        $reporting_id = $employee->reporting_id;
        $attendance_policy_id = $employee->attendance_policy_id;
        $shift_id = $employee->shift_id;
        $emp_gross_salary = $employee->emp_gross_salary;
        $emp_basic_salary = $employee->emp_basic;
        $emp_hra = $employee->emp_hra;
        $lunch = $employee->lunch;
        $special = $employee->special;
        $conveyance = $employee->conveyance;
        $emp_medical = $employee->emp_medical;
        $emp_location = $employee->emp_location;
        $emp_gender = $employee->emp_gender;
        $emp_prop_confirmation_date = $employee->emp_prop_confirmation_date;
        $emp_dateofbirth = $employee->emp_dateofbirth;
        $emp_blood_group = $employee->emp_blood_group;
        $emp_contact_number = $employee->emp_contact_number;
        $emp_resignation_date = $employee->emp_resignation_date;
        $emp_replacement_of = $employee->emp_replacement_of;
        $emp_notes_salary_hub = $employee->emp_notes_salary_hub;
        $emp_bank_title = $employee->emp_bank_title;
        $emp_remarks = $employee->emp_remarks;
        $emp_photo = $employee->emp_photo;
        $emp_marital_status = $employee->emp_marital_status;
        $emp_city = $employee->emp_city;
        $emp_contact_number_2 = $employee->emp_contact_number_2;
        $emp_address = $employee->emp_address;
        $emp_account_number = $employee->emp_account_number;
        $country_id = $employee->country;
        $city_id = $employee->city;
    }

    if (isset($_POST["confirm_struck_off"])) {
        extract($_POST);

        $create_struck_off_date = date_create($struck_off_date);
        $formatted_struck_off_date = date_format($create_struck_off_date, 'Y-m-d');
        
        //for form field after post
        $temp_struck_off_date = date_format($create_struck_off_date, 'm/d/Y');
        if ($struck_off_date == '') {
            $err = "Struck-off date must be selected";
        } else {
            $emp_array = array(
                "emp_id" => $emp_id,
                "emp_code" => $emp_code,
                "emp_firstname" => $emp_firstname,
                "emp_email" => $emp_email,
                "emp_designation" => $emp_designation,
                "emp_department" => $emp_department,
                "emp_subsection" => $emp_subsection,
                "emp_dateofjoin" => $emp_dateofjoin,
                "emp_staff_grade" => $staffgrade_id,
                "emp_gross_salary" => $emp_gross_salary,
                "emp_location" => $emp_location,
                "emp_gender" => $emp_gender,
                "emp_prop_confirmation_date" => $emp_prop_confirmation_date,
                "emp_dateofbirth" => $emp_dateofbirth,
                "emp_address" => $emp_address,
                "emp_contact_number" => $emp_contact_number,
                "emp_resignation_date" => $emp_resignation_date,
                "emp_replacement_of" => $emp_replacement_of,
                "emp_notes_salary_hub" => $emp_notes_salary_hub,
                "emp_account_number" => $emp_account_number,
                "emp_bank_title" => $emp_bank_title,
                "emp_remarks" => $temp_emp_remarks,
                "emp_photo" => $uploadPath,
                "emp_blood_group" => $emp_blood_group,
                "emp_marital_status" => $emp_marital_status,
                "emp_city" => $city_id,
                "emp_contact_number_2" => $emp_contact_number_2,
                "emp_basic" => $emp_basic_salary,
                "emp_hra" => $emp_hra,
                "emp_medical" => $emp_medical,
                "conveyance" => $conveyance,
                "special" => $special,
                "lunch" => $lunch,
                "others" => $others,
                "emp_type" => $emp_type,
                "password" => $emp_password,
                "user_type" => $user_type,
                "supervisor_id" => $supervisor_id,
                "reporting_id" => $reporting_id,
                "attendance_policy_id" => $attendance_policy_id,
                "shift_id" => $shift_id,
                "company_id" => $company_id,
                "country" => $country_id,
                "city" => $city_id,
                "struck_off_date" => $formatted_struck_off_date
            );
            
            if ($con->insert("struck_off", $emp_array) == 1) {
                $object_array = array("emp_id" => $emp_id);

                if ($con->delete("tmp_employee", $object_array) == 1) {
                    $msg = "This employee is struck-off. All the information of this employee is moved to struck-off records.";
                }
            }
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<script type="text/javascript">
    $(document).ready(function () {
        $("#struck_off_date").kendoDatePicker();

    });
</script>


<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Confirm Struck Off</h6></div>
    <div class="widget-body" style="background-color: white;">

        <a href="all.php" class="k-button pull-right" style="text-decoration: none;">
            View Struck-off List
        </a>
        <br />
        <div class="clearfix"></div>

        <?php include("../../layout/msg.php"); ?>

        <form method="post">
            <div class="col-md-5">
                <label for="Struck Off Date" style="width: 100%" id="lbl_shift_title">Select a Struck Off Day</label><br/>
                <input type="text" style="width: 80%" name="struck_off_date"  id="struck_off_date" value="<?php echo $temp_struck_off_date; ?>"/>
            </div>

            <div class="col-md-4">
                <input type="submit" class="k-button" style="margin-top: 21px;" name="confirm_struck_off" value="Confirm Struck Off">
            </div> 
            <div class="clearfix"> </div>

        </form>



    </div>
</div>
<div class="clearfix"></div><br />
<!-- Widget -->
<?php if (count($employees) > 0): ?>
    <div class="widget finances_summary widget-inverse">
        <div class="row row-merge">
            <!-- col -->
            <div class="col-sm-12 col-md-3">
                <!-- Profile Photo -->
                <div class="border-bottom">

                    <?php if ($emp_photo == '') { ?>
                        <img style="width: 100%; height: 35%; padding: 4%" src="empty.jpg" class="img-responsive img-clean"/>
                    <?php } else { ?>
                        <a href="">
                            <img style="width: 100%; height: 35%; padding: 4%" src="<?php echo $con->baseUrl($emp_photo); ?>" class="img-responsive img-clean"/>
                        </a>
                    <?php } ?>
                </div>
                <div class="innerAll inner-2x text-center">
                    <p class="lead strong margin-none"><?php echo $emp_firstname; ?></p>
                    <p class="lead"><?php echo $designation_title; ?></p>
                </div>

            </div> 

            <div class="col-lg-9 col-md-9 col-sm-12">

                <div class="innerAll half border-bottom">
                    <h4 class="pull-left innerT half margin-none">Basic Information</h4>		
                    <div class="clearfix"></div>

                </div>
                <div class="innerAll ">
                    <div class="col-md-2"><b>Employee Code:</b></div>
                    <div class="col-md-2"><?php echo $emp_code; ?></div>

                    <div class="col-md-2"><b>Marital Status:</b></div>
                    <?php if ($emp_marital_status == '') { ?>
                        Marital Status is not Inserted!
                    <?php } else { ?>
                        <div class="col-md-3"><?php echo $emp_marital_status; ?></div>
                    <?php } ?>

                    <div class="clearfix"></div>
                    <br />
                    <div class="col-md-2"><b>Gender: </b></div>
                    <div class="col-md-2"><?php echo $emp_gender; ?></div>
                    <div class="col-md-2"><b>Blood Group:</b> </div>
                    <div class="col-md-2"><?php echo $emp_blood_group; ?></div>
                    <div class="clearfix"></div>
                    <br />
                    <div class="col-md-2"><b>Date of Birth:</b></div>
                    <div class="col-md-3"><?php echo $emp_dateofbirth; ?></div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>

                <div class="innerAll half border-bottom">
                    <h4 class="pull-left innerT half margin-none">Contact Information</h4>		
                    <div class="clearfix"></div>

                </div>
                <div class="innerAll ">
                    <!--                <div class="col-md-2"><b>Country: </b></div>
                                    <div class="col-md-2"><?php // echo $emp_location;            ?></div>
                                    <div class="col-md-2"><b>City: </b></div>
                                    <div class="col-md-2"><?php // echo $emp_city;            ?></div>
                                    <div class="clearfix"></div>
                    
                                    <br />-->


                    <div class="col-md-2"><b>Phone 1: </b></div>
                    <div class="col-md-2"><?php echo $emp_contact_number; ?></div>

                    <div class="col-md-2"><b>Phone 2: </b></div>
                    <?php if ($emp_contact_number_2 == '') { ?>
                        Phone 2 is not Inserted!
                    <?php } else { ?>
                        <div class="col-md-3"><?php echo $emp_contact_number_2; ?></div>
                    <?php } ?>

                    <div class="clearfix"></div>
                    <br />

                    <div class="col-md-2"><b>Address: </b></div>
                    <div class="col-md-6"><?php echo $emp_address; ?></div>
                    <div class="clearfix"></div>


                </div>
                <div class="clearfix"></div>

                <div class="innerAll half border-bottom">
                    <h4 class="pull-left innerT half margin-none">Department Information</h4>		
                    <div class="clearfix"></div>

                </div>
                <div class="innerAll ">
                    <div class="col-md-2"><b>Department:</b></div>
                    <div class="col-md-2"><?php echo $emp_department; ?></div>
                    <div class="col-md-2"><b>Designation: </b></div>
                    <div class="col-md-3"><?php echo $emp_designation; ?></div>

                    <div class="clearfix"></div>

                    <br />

                    <div class="col-md-2"><b>Subsection:</b></div>
                    <div class="col-md-2"><?php echo $emp_subsection; ?></div>
                    <div class="col-md-2"><b>Date of Join:</b></div>
                    <div class="col-md-3"><?php echo $emp_dateofjoin; ?></div>
                    <div class="clearfix"></div>
                    <br />

                    <div class="col-md-2"><b>Staff Grade:</b></div>
                    <div class="col-md-4"><?php echo $emp_staff_grade; ?></div>
                    <div class="clearfix"></div>

                    <br />
                </div>
                <div class="clearfix"></div>

                <div class="innerAll half border-bottom">
                    <h4 class="pull-left innerT half margin-none">Salary Information</h4>		
                    <div class="clearfix"></div>

                </div>
                <div class="innerAll ">
                    <div class="col-md-2"><b>Gross Salary (BDT):</b></div>
                    <div class="col-md-2"><?php echo $emp_gross_salary; ?></div>
                    <div class="col-md-2"><b>Salary Hub: </b></div>
                    <div class="col-md-3"><?php echo $company_title; ?></div>

                    <div class="clearfix"></div>

                    <br />
                    <div class="col-md-2"><b>Bank:</b></div>
                    <div class="col-md-2"><?php echo $emp_bank_title; ?></div>

                    <div class="col-md-2"><b>Account No:</b></div>
                    <?php if ($emp_account_number == 0) { ?>
                        Account number is not Inserted!
                    <?php } else { ?>
                        <div class="col-md-3"><?php echo $emp_account_number; ?></div>
                    <?php } ?>


                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>

                <div class="innerAll half border-bottom">
                    <h4 class="pull-left innerT half margin-none">Other Information</h4>		
                    <div class="clearfix"></div>
                </div>
                <div class="innerAll ">
                    <div class="col-md-3"><b>Confirmation Date:</b></div>
                    <div class="col-md-2"><?php echo $emp_prop_confirmation_date; ?></div>
                    <div class="col-md-3"><b>Replacement of: </b></div>
                    <div class="col-md-3"><?php echo $emp_replacement_of; ?></div>

                    <!--                <div class="clearfix"></div>
                    
                                    <br />-->
                    <!--                <div class="col-md-2"><b>Remarks:</b></div>
                                    <div class="clearfix"></div>
                                    <div class="col-md-9">
                    <?php // echo html_entity_decode($emp_remarks);      ?>
                                    </div>-->

                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>

            </div> 
            <!-- // END col -->

        </div>		

    </div>
    <!-- // END row -->

    <!-- // END row-app -->




    </div>
<?php endif; ?>
<!-- // Content END -->
<?php include '../view_layout/footer_view.php'; ?>
    



