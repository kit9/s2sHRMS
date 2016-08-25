<?php
session_start();
/*
 * Author: Rajan Hossain
 * Page: Shift Settings
 * 9th August, 2014
 */
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();

//Checking if logged inc
//if ($con->authenticate() == 1) {
//    $con->redirect("../../login.php");
//}
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

//Initialize variables

$shift_title = '';
$shift_start_day = '';
$shift_end_day = '';
$saturday_start_time = '';
$saturday_end_time = '';
$sunday_start_time = '';
$sunday_end_time = '';
$mon_start_time = '';
$mon_end_time = '';
$tue_start_time = '';
$tue_end_time = '';
$wed_start_time = '';
$wed_end_time = '';
$thu_start_time = '';
$thu_end_time = '';
$fri_start_time = '';
$fri_end_time = '';
$status = '';
$shift_pattern_id = '';
$shift_patterns = array();

//All Shift Pattern
$shift_patterns = $con->SelectAll("shift_pattern");

if (isset($_POST["create_shift"])) {
    extract($_POST);

    //Validating form fields
    if ($shift_title == '') {
        $err = 'Shift title is empty!';
    } elseif ($saturday_start_time == '') {
        $err = 'Select a satrt time.';
    } elseif ($saturday_end_time == '') {
        $err = 'Select an end time.';
    } else {
        
        //Formatting shift start day
        $create_shift_start_day = date_create($shift_start_day);
        $formatted_shift_start_day = date_format($create_shift_start_day, 'Y-m-d');

        //Formatting shift end day.
        $create_shift_end_day = date_create($shift_end_day);
        $formatted_shift_end_day = date_format($create_shift_end_day, 'Y-m-d');

        //Format time for end time
        $saturday_start_time = date("G:i:s", strtotime($saturday_start_time));
        $saturday_end_time = date("G:i:s", strtotime($saturday_end_time));
        
        
        $array = array(
            "shift_pattern_id" => $shift_pattern_id,
            "shift_title" => $shift_title,
            "shift_start_day" => $formatted_shift_start_day,
            "shift_end_day" => $formatted_shift_end_day,
            "saturday_start_time" => $saturday_start_time,
            "saturday_end_time" => $saturday_end_time,
            "sat_start_day" => 1,
            "sat_end_day" => $sat_end_day,
            "status" => $status
        );
        

        if ($con->insert("shift_policy", $array)) {
            $msg = 'A new shift is successfully created.';
        } else {
            $err = 'Shift creation failed!';
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
        $("#timepicker4").kendoTimePicker({
            animation: false
        });
        $("#timepicker5").kendoTimePicker({
            animation: false
        });
        $("#timepicker6").kendoTimePicker({
            animation: false
        });
        $("#timepicker7").kendoTimePicker({
            animation: false
        });
        $("#timepicker8").kendoTimePicker({
            animation: false
        });
        $("#timepicker9").kendoTimePicker({
            animation: false
        });
        $("#timepicker10").kendoTimePicker({
            animation: false
        });
        $("#timepicker11").kendoTimePicker({
            animation: false
        });
        $("#timepicker12").kendoTimePicker({
            animation: false
        });
        $("#timepicker13").kendoTimePicker({
            animation: false
        });
        $("#timepicker14").kendoTimePicker({
            animation: false
        });

        $("#shift_start_day").kendoDatePicker();
        $("#shift_end_day").kendoDatePicker();

        //End day for each day
        $("#size7").kendoDropDownList();
        $("#size6").kendoDropDownList();
        $("#size5").kendoDropDownList();
        $("#size4").kendoDropDownList();
        $("#size3").kendoDropDownList();
        $("#size2").kendoDropDownList();
        $("#size1").kendoDropDownList();
        $("#shift_pattern_id").kendoDropDownList();
    });
</script>
<!-- Widget -->
<a href="index.php" class="k-button pull-right" style="width:100px;">All Shifts</a>
<div class="clearfix"></div>
<br />
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Shift Settings</h6></div>
    <div class="widget-body" style="background-color: white;">
        <?php include("../../layout/msg.php"); ?>
        <!--Employee Code-->
        <form method="post">
            <div class="col-md-4">
                <label for="policy_title" id="lbl_shift_title">Shift Title</label><br/>
                <input type="text" class="k-textbox" name="shift_title"  id="shift_title" value="<?php echo $shift_title; ?>"  placeholder="e.g: Exec. Shift"/>  
            </div>
            <div class="col-md-4">
                <label for="Start Day" id="lbl_shift_start_day">Start Day</label><br/>
                <input type="text" name="shift_start_day"  id="shift_start_day" value="<?php echo $shift_start_day; ?>"  placeholder=""/>  
            </div>
            <div class="col-md-4">
                <label for="policy_title" id="lbl_shift_end_day">End Day</label><br/>
                <input type="text" name="shift_end_day"  id="shift_end_day" value="<?php echo $shift_end_day; ?>"  placeholder=""/>  
            </div>
            <div class="clearfix"> </div>
            <br />
            <hr />
            <div class="col-md-2">
                <span style="font-weight: bold;">
                    <br />
                    Schedule:
                </span>
            </div>
            <div class="col-md-3">
                <label for="Start Time" id="lbl_office_start_time">Start Time</label><br/> 
                <input type="text" name="saturday_start_time" id="timepicker" value="<?php echo $saturday_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="sat_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="Office End Time" id="lbl_saturday_end_time">End Time:</label><br/> 
                <input type="text" name="saturday_end_time" id="timepicker2" value="<?php echo $saturday_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                <div id="options">
                    <select id="size1" name="sat_end_day" style="width: 60px;">
                        <?php if ($sat_end_day == 1): ?>
                            <option value="1" selected="true">1</option>
                        <?php else: ?>
                            <option value="1">1</option>
                        <?php endif; ?>
                        <?php if ($sat_end_day == 2): ?>
                            <option value="2" selected="true">2</option>
                        <?php else: ?>
                            <option value="2">2</option>
                        <?php endif; ?>
                    </select>
                </div>   
            </div>
            <div class="clearfix"></div>
            <!--End of first row-->
            <br />

            <!--        To be included next :: Right now not needed
                    <div class="col-md-2">
                        <span style="font-weight: bold;">
                            <br />
                            Sunday:
                        </span>
                    </div>
                    <div class="col-md-3">
                        <label for="Start Time" id="lbl_sunday_start_time">Start Time:</label><br/> 
                        <input type="text" name="sunday_start_time" id="timepicker3" value="<?php // echo $sunday_start_time;   ?>"  placeholder="e.g: 9:00 AM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                        <input type="text" class="k-textbox" name="sun_start_day" value="1" style="width: 60px;" disabled="true"/>  
                    </div>
                    <div class="col-md-3">
                        <label for="End Time" id="lbl_sunday_end_time">End Time:</label><br/> 
                        <input type="text" name="sunday_end_time" id="timepicker4" value="<?php // echo $sunday_end_time;   ?>"  placeholder="e.g: 5:00 PM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                        <div id="options">
                            <select id="size2" name="sun_end_day" style="width: 60px;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>   
                    </div>
                    <div class="clearfix"></div>
                    End of first row
                    <br />
            
                    <div class="col-md-2">
                        <span style="font-weight: bold;">
                            <br />
                            Monday:
                        </span>
                    </div>
                    <div class="col-md-3">
                        <label for="Start Time" id="lbl_mon_start_time">Start Time</label><br/> 
                        <input type="text" name="mon_start_time" id="timepicker5" value="<?php // echo $mon_start_time;   ?>"  placeholder="e.g: 9:00 AM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                        <input type="text" class="k-textbox" name="mon_start_day" value="1" style="width: 60px;" disabled="true"/>  
                    </div>
                    <div class="col-md-3">
                        <label for="End Time" id="lbl_mon_end_time">End Time:</label><br/> 
                        <input type="text" name="mon_end_time" id="timepicker6" value="<?php // echo $mon_end_time;   ?>"  placeholder="e.g: 5:00 PM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                        <div id="options">
                            <select id="size3" name="mon_end_day" style="width: 60px;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>   
                    </div>
                    <div class="clearfix"></div>
                    End of first row
                    <br />
            
                    <div class="col-md-2">
                        <span style="font-weight: bold;">
                            <br />
                            Tuesday:
                        </span>
                    </div>
                    <div class="col-md-3">
                        <label for="Start Time" id="lbl_tue_start_time">Start Time</label><br/> 
                        <input type="text" name="tue_start_time" id="timepicker7" value="<?php // echo $tue_start_time;   ?>"  placeholder="e.g: 9:00 AM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                        <input type="text" class="k-textbox" name="tue_start_day" value="1" style="width: 60px;" disabled="true"/>  
                    </div>
                    <div class="col-md-3">
                        <label for="Office End Time" id="lbl_office_end_time">End Time:</label><br/> 
                        <input type="text" name="tue_end_time" id="timepicker8" value="<?php // echo $tue_end_time;   ?>"  placeholder="e.g: 5:00 PM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                        <div id="options">
                            <select id="size4" name="tue_end_day" style="width: 60px;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>   
                    </div>
                    <div class="clearfix"></div>
                    End of first row
                    <br />
            
                    <div class="col-md-2">
                        <span style="font-weight: bold;">
                            <br />
                            Wednesday:
                        </span>
                    </div>
                    <div class="col-md-3">
                        <label for="Start Time" id="lbl_wed_start_time">Start Time</label><br/> 
                        <input type="text" name="wed_start_time" id="timepicker9" value="<?php // echo $wed_start_time;   ?>"  placeholder="e.g: 9:00 AM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                        <input type="text" class="k-textbox" name="wed_start_day" value="1" style="width: 60px;" disabled="true"/>  
                    </div>
                    <div class="col-md-3">
                        <label for="End Time" id="lbl_wed_end_time">End Time:</label><br/> 
                        <input type="text" name="wed_end_time" id="timepicker10" value="<?php // echo $wed_end_time;   ?>"  placeholder="e.g: 5:00 PM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                        <div id="options">
                            <select id="size5" name="wed_end_day" style="width:60px;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    End of first row
                    <br />
            
                    <div class="col-md-2">
                        <span style="font-weight: bold;">
                            <br />
                            Thursday:
                        </span>
                    </div>
                    <div class="col-md-3">
                        <label for="Start Time" id="lbl_thu_start_time">Start Time</label><br/> 
                        <input type="text" name="thu_start_time" id="timepicker11" value="<?php // echo $thu_start_time;   ?>"  placeholder="e.g: 9:00 AM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                        <input type="text" class="k-textbox" name="thu_start_day" value="1" style="width: 60px;" disabled="true"/>  
                    </div>
                    <div class="col-md-3">
                        <label for=" End Time" id="lbl_thu_end_time">End Time:</label><br/> 
                        <input type="text" name="thu_end_time" id="timepicker12" value="<?php // echo $thu_end_time;   ?>"  placeholder="e.g: 5:00 PM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                        <div id="options">
                            <select id="size6" name="thu_end_day" style="width: 60px;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    End of first row
                    <br />
            
                    <div class="col-md-2">
                        <span style="font-weight: bold;">
                            <br />
                            Friday:
                        </span> 
                    </div>
                    <div class="col-md-3">
                        <label for="Start Time" id="lbl_fri_start_time">Start Time</label><br/> 
                        <input type="text" name="fri_start_time" id="timepicker13" value="<?php // echo $fri_start_time;   ?>"  placeholder="e.g: 9:00 AM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                        <input type="text" class="k-textbox" name="fri_start_day" value="1" style="width: 60px;" disabled="true"/>  
                    </div>
                    <div class="col-md-3">
                        <label for="End Time" id="lbl_fri_end_time">End Time:</label><br/> 
                        <input type="text" name="fri_end_time" id="timepicker14" value="<?php // echo $fri_end_time;   ?>"  placeholder="e.g: 5:00 PM"/>  
                    </div>
                    <div class="col-md-2">
                        <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                        <div id="options">
                            <select id="size7" name="fri_end_day" style="width: 60px;">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>-->
            <div class="clearfix"></div>
            <!--End of first row-->
            <br />

            <div class="col-md-6">
                <input type="submit" class="k-button" value="Create New Shift" name="create_shift"><br/><br />
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
    




