<?php
session_start();
/*
 * Author: Rajan Hossain
 * Page: Search Employee
 */
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

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


//Initialize variables
$policy_title = '';
$office_start_time = '';
$office_end_time = '';
$total_hours = '';
$office_start_day = '';
$office_end_day = '';
$weekend_days = '';
$is_ot_applicable = '';
$ot_buffer_minute = '';
$is_late_cut_applicable = '';
$no_of_latedays = '';
$office_entry_buffer = '';
$isabsent_cut_applicable = '';
$isattn_bonus_applicable = '';
$attn_bonus_prcnt = '';
$half_day_work = '';
$halfday_office_endtime = '';
$status = '';
$ot_percentage = '';



if (isset($_POST["create_policy"])) {
    extract($_POST);
    $array = array(
        "policy_title" => $policy_title,
        "office_start_time" => $office_start_time,
        "office_end_time" => $office_end_time,
        "total_hours" => $total_hours,
        "office_start_day" => $office_start_day,
        "office_end_day" => $office_end_day,
        "weekend_days" => $weekend_days,
        "is_ot_applicable" => $is_ot_applicable,
        "ot_buffer_minute" => $ot_buffer_minute,
        "is_late_cut_applicable" => $is_late_cut_applicable,
        "no_of_latedays" => $no_of_latedays,
        "office_entry_buffer" => $office_entry_buffer,
        "isattn_bonus_applicable" => $isattn_bonus_applicable,
        "attn_bonus_prcnt" => $attn_bonus_prcnt,
        "half_day_work" => $half_day_work,
        "halfday_office_endtime" => $halfday_office_endtime,
        "status" => $status
    );
    if ($con->insert("attendance_policy", $array)){
        $msg = 'A new policy is successfully created.';
    }else {
        $err = 'Policy creation failed';
    }
    
}

/*
 * Globally usable employee ID
 * Not generated until above form posted
 */


//Create schedule
if (isset($_POST['save'])) {
    extract($_POST);
    if ($start_date == '') {
        $err = 'Please specify start date.';
    } else if ($end_date == '') {
        $err = 'Please specify end date.';
    } else {

        // Format start date
        $frm_start_date = date_create($start_date);
        $formatted_start_date = date_format($frm_start_date, 'Y-m-d');

        //Format end date
        $frm_end_date = date_create($end_date);
        $formatted_end_date = date_format($frm_end_date, 'Y-m-d');

        //Now applied annual leave table data will be inserted with same dates
        $app_array = array(
            "applied_annual_leave_id" => $applied_annual_leave_id,
            "emp_id" => $emp_id,
            "app_start_date" => $formatted_start_date,
            "app_end_date" => $formatted_end_date,
            "status" => 1
        );
        if ($con->update("applied_annual_leave", $app_array) == 1) {
            $msg = 'A leave shcedule is requested!';
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#office_start_day").kendoDropDownList();
        $("#office_end_day").kendoDropDownList();
        $("#half_day_work").kendoDropDownList();
        $("#timepicker").kendoTimePicker({
            animation: false
        });
        $("#timepicker2").kendoTimePicker({
            animation: false
        });
        $("#timepicker3").kendoTimePicker({
            animation: false
        });
    });
</script>

<!--link to main page-->
<a href="index.php" class="k-button pull-right" style="text-decoration: none;">All Attendance Policies</a>
<div class="clearfix"></div>
<br />

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Attendance Policy</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-4">
                <label for="policy_title" id="lbl_policy_title">Policy Title</label><br/>
                <input type="text" class="k-textbox" name="policy_title" id="policy_title" value="<?php echo $policy_title; ?>"  placeholder="e.g: Exec. Policy"/>  
            </div>
            <div class="col-md-4">
                <label for="Start Time" id="lbl_office_start_time">Office Start Time:</label><br/> 
                <input type="text" name="office_start_time" id="timepicker" value="<?php echo $office_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-4">
                <label for="Office End Time" id="lbl_office_end_time">Office End Time:</label><br/> 
                <input type="text" name="office_end_time" id="timepicker2" value="<?php echo $office_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
            </div>
            <div class="clearfix"></div>
            <!--End of first row-->
            <br />
            <div class="col-md-4">
                <label for="Entry Buffer Minute" id="lbl_office_entry_buffer">Entry Buffer Minute</label><br/>
                <input type="text" class="k-textbox" name="office_entry_buffer" id="office_entry_buffer" value="<?php echo $office_entry_buffer; ?>"  placeholder="e.g: 15"/>  
            </div>

            <div class="col-md-4">
                <label for="Start Date" id="lbl_office_start_day">Office Start Day:</label><br/>
                <div id="options">
                    <select id="office_start_day" name="office_start_day">
                        <option value="0">--Select--</option>
                        <option value="sat">Saturday</option>
                        <option value="sun">Sunday</option>
                        <option value="mon">Monday</option>
                        <option value="tues">Tuesday</option>
                        <option value="wed">Wednesday</option>
                        <option value="thu">Thursday</option>
                        <option value="fri">Friday</option>
                    </select>
                </div>       
            </div>

            <div class="col-md-4">
                <label for="Office End Day" id="lbl_office_end_day">Office End Day:</label><br/>
                <div id="options">
                    <select id="office_end_day" name="office_end_day">
                        <option value="0">--Select--</option>
                        <option value="sat">Saturday</option>
                        <option value="sun">Sunday</option>
                        <option value="mon">Monday</option>
                        <option value="tue">Tuesday</option>
                        <option value="wed">Wednesday</option>
                        <option value="thu">Thursday</option>
                        <option value="fri">Friday</option>
                    </select>
                </div>       
            </div>
            <div class="clearfix"></div>

            <br />

            <div class="col-md-4">
                <label for="Weekend Days" id="lbl_weekend_days">Weekend Days:</label><br/>
                <input type="text" class="k-textbox" name="weekend_days" id="weekend_days" value="<?php echo $weekend_days; ?>"  placeholder="e.g: 2"/>  
            </div>

            <div class="col-md-4">
                <label for="Total Hours" id="lbl_total_hours">Total Hours:</label><br/>
                <input type="text" class="k-textbox" name="total_hours" id="total_hours" value="<?php echo $total_hours; ?>"  placeholder="e.g.: 8"/>  
            </div>

            <div class="col-md-4">
                <label for="Half Day Work" id="lbl_half_day_work">Half Day Work:</label><br/>
                <div id="options">
                    <select id="half_day_work" name="half_day_work">
                        <option value="0">--Select--</option>
                        <option value="sat">Saturday</option>
                        <option value="sun">Sunday</option>
                        <option value="mon">Monday</option>
                        <option value="tues">Tuesday</option>
                        <option value="wed">Wednesday</option>
                        <option value="thu">Thursday</option>
                        <option value="fri">Friday</option>
                    </select>
                </div>       
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-4">
                <label for="Total Hours" id="lbl_halfday_office_endtime">Half-day Office End Time:</label><br/>
                <input type="text" id="timepicker3" name="halfday_office_endtime" value="<?php echo $halfday_office_endtime; ?>"  placeholder="e.g: 2:00 PM"/>  
            </div>
            <div class="clearfix"></div>
            <br />

            <div class="col-md-8">
                <input type="checkbox" name="is_ot_applicable" value="yes" id="FavouriteView"/>
                <label for="Start Date">Is OT Applicable?</label><br />
                <div id="otherfields" style="display: none;">
                    <label for="FavouriteView">Another Label</label>
                </div>

            </div>
            <div class="clearfix"></div>

            <br />
            <div class="col-md-4">
                <label for="OT Percentage" id="lbl_ot_percentage">OT Percentage</label><br/>
                <input type="text" class="k-textbox" value="<?php echo $ot_percentage;?>" name="ot_percentage" placeholder=""/> 
            </div>
            <div class="col-md-4">
                <label for="OT Buffer Minute" id="lbl_ot_buffer_minute">OT Buffer Minute:</label><br/>
                <input type="text" class="k-textbox" value="<?php echo $ot_buffer_minute;?>" name="ot_buffer_minute" placeholder=""/>
            </div>
            <div class="clearfix">

            </div>
            <br />

            <div class="col-md-8">
                <input type="checkbox" name="is_late_cut_applicable" id="is_late_cut_applicable"/>
                <label for="Start Date">Is Late Cut Applicable?</label><br />

            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-4">
                <label for="Leave Days Permitted" id="no_of_latedays">Late Days Permitted:</label><br />
                <input type="text" class="k-textbox" name="no_of_latedays" id="no_of_latedays" value="<?php echo $no_of_latedays; ?>"  placeholder=""/>  
            </div>
            <div class="clearfix">

            </div>
            <br />

            <div class="col-md-8">
                <input type="checkbox" name="isattn_bonus_applicable" id="isattn_bonus_applicable"/>
                <label for="  Date">Is Attn. Bonus Applicable?</label><br />

            </div>
            <div class="clearfix"></div>
            <br />
            <div class="col-md-4">
                <label for="Attn Bonus Percentage" id="lbl_attn_bonus_prcnt">Attn Bonus Percentage:</label><br />
                <input type="text" class="k-textbox" name="attn_bonus_prcnt" id="attn_bonus_prcnt" value="<?php echo $attn_bonus_prcnt; ?>"  placeholder=""/>  
            </div>
            <div class="clearfix">

            </div>
            <br />

            <div class="col-md-6">
                <input type="submit" class="k-button" value="Create Policy" name="create_policy"><br /><br />
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="clearfix"></div>
</form>
</div>
</div>
</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
    




