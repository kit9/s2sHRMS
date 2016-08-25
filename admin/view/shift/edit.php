<?php
session_start();
/*
 * Author: Rajan
 * Page: Shift Policy Edit
 * 23rd July, 14
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

$shift_title = '';
$shift_start_day = '';
$shift_end_day = '';

//Saturday
$saturday_start_time = '';
$sat_start_day = '';
$saturday_end_time = '';
$sat_end_day = '';

//Sunday
$sunday_start_time = '';
$sun_start_day = '';
$sunday_end_time = '';
$sun_end_day = '';

//Monday
$mon_start_time = '';
$mon_start_day = '';
$mon_end_time = '';
$mon_end_day = '';

//Tuesday
$tue_start_time = '';
$tue_end_time = '';
$tue_start_day = '';
$tue_end_day = '';

//Wednesday
$wed_start_time = '';
$wed_start_day = '';
$wed_end_time = '';
$wed_end_day = '';

//Thursday
$thu_start_time = '';
$thu_start_day = '';
$thu_end_time = '';
$thu_end_day = '';

//Friday
$fri_start_time = '';
$fri_start_day = '';
$fri_end_time = '';
$fri_end_day = '';



/*
 * Collect Shift id from URL
 * Shift ID is from Shift List Page
 * Fetch shift data from shift table
 * condition: based on shift ID
 */
if (isset($_GET['shift_id'])) {
    $shift_id = $_GET['shift_id'];
    $shifts = $con->SelectAllByCondition("shift_policy", "shift_id=$shift_id");
    foreach ($shifts as $shift) {
        //Shift basic info
        $shift_title = $shift->shift_title;
        $shift_start_day = $shift->shift_start_day;
        $shift_end_day = $shift->shift_end_day;   
        
         //Formatting shift start day
        $create_shift_start_day = date_create($shift_start_day);
        $temp_shift_start_day = date_format($create_shift_start_day, 'm/d/Y');

        //Formatting shift end day.
        $create_shift_end_day = date_create($shift_end_day);
        $temp_shift_end_day = date_format($create_shift_end_day, 'm/d/Y');
        
        //Saturday
        $saturday_start = $shift->saturday_start_time;
        $temp_saturday_start_time = date("g:i A", strtotime($saturday_start));
        $sat_start_day = $shift->sat_start_day;
        $saturday_end = $shift->saturday_end_time;
        $temp_saturday_end_time = date("g:i A", strtotime($saturday_end));
        $sat_end_day = $shift->sat_end_day;
      
        
        
//        //Sunday
//        $sunday_start = $shift->sunday_start_time;
//        $sunday_start_time = date("H:i A", strtotime($sunday_start));
//        $sun_start_day = $shift->sun_start_day;
//        $sunday_end = $shift->sunday_end_time;
//        $sunday_end_time = date("H:i A", strtotime($sunday_end));
//        $sun_end_day = $shift->sun_end_day;
//
//        //Monday
//        $mon_start = $shift->mon_end_day;
//        $mon_start_time = date("H:i A", strtotime($mon_start));
//        $mon_start_day = $shift->mon_start_day;
//        $mon_end = $shift->mon_end_time;
//        $mon_end_time = date("H:i A", strtotime($mon_end));
//        $mon_end_day = $shift->mon_end_day;
//
//        //Tuesday
//        $tue_start = $shift->tue_end_day;
//        $tue_start_time = date("H:i A", strtotime($tue_start));
//        $tue_start_day = $shift->tue_start_day;
//        $tue_end = $shift->tue_end_time;
//        $tue_end_time = date("H:i A", strtotime($tue_end));
//        $tue_end_day = $shift->tue_end_day;
//
//        //Wednesday
//        $wed_start = $shift->wed_start_time;
//        $wed_start_time = date("H:i A", strtotime($wed_start));
//        $wed_start_day = $shift->wed_start_day;
//        $wed_end = $shift->wed_end_time;
//        $wed_end_time = date("H:i A", strtotime($wed_end));
//        $wed_end_day = $shift->wed_end_day;
//
//        //thursday
//        $thu_start = $shift->thu_start_time;
//        $thu_start_time = date("H:i A", strtotime($thu_start));
//        $thu_start_day = $shift->thu_start_day;
//        $thu_end = $shift->thu_end_time;
//        $thu_end_time = date("H:i A", strtotime($thu_end));
//        $thu_end_day = $shift->thu_end_day;
//
//        //Friday
//        $fri_start = $shift->fri_start_time;
//        $fri_start_time = date("H:i A", strtotime($fri_start));
//        $fri_start_day = $shift->fri_start_day;
//        $fri_end = $shift->fri_end_time;
//        $fri_end_time = date("H:i A", strtotime($fri_end));
//       $fri_end_day = $shift->fri_end_day;
    }
}
$shift_id = $_GET['shift_id'];
if (isset($_POST["edit_shift"])) {
    extract($_POST);

    //Validating form fields
    if ($shift_title == '') {
        $err = 'Shift title is empty!';
    } elseif ($saturday_start_time == '') {
        $err = 'Select a start time.';
    } elseif ($saturday_end_time == '') {
        $err = 'Select an end time.';
    } else {
        
        //Formatting shift start day
        $create_shift_start_day = date_create($shift_start_day);
        $formatted_shift_start_day = date_format($create_shift_start_day, 'Y-m-d');
        $temp_shift_start_day = date_format($create_shift_start_day, 'm/d/Y');
        
       
        //Formatting shift end day.
        $create_shift_end_day = date_create($shift_end_day);
        $formatted_shift_end_day = date_format($create_shift_end_day, 'Y-m-d');
        $temp_shift_end_day = date_format($create_shift_end_day, 'm/d/Y');

        //Format time for start and end time
        $saturday_start_time = date("G:i:s", strtotime($saturday_start_time));
        $saturday_end_time = date("G:i:s", strtotime($saturday_end_time));
       
        $temp_saturday_start_time = date("g:i A", strtotime($saturday_start_time));
        $temp_saturday_end_time = date("g:i A", strtotime($saturday_end_time));
  

        $array = array(
            "shift_id" => $shift_id,
            "shift_title" => $shift_title,
            "shift_start_day" => $formatted_shift_start_day,
            "shift_end_day" => $formatted_shift_end_day,
            "saturday_start_time" => $saturday_start_time,
            "saturday_end_time" => $saturday_end_time,
            "sat_start_day" => 1,
            "sat_end_day" => $sat_end_day
        );
 
        if ($con->update("shift_policy", $array)) {
            $msg = 'All the changes are succesfully saved.';
        } else {
            $err = 'Shift edit failed!';
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
                <input type="text" name="shift_start_day"  id="shift_start_day" value="<?php echo $temp_shift_start_day; ?>"  data-role="datepicker" placeholder=""/>  
            </div>
            <div class="col-md-4">
                <label for="policy_title" id="lbl_shift_end_day">End Day</label><br/>
                <input type="text" name="shift_end_day"  id="shift_end_day" value="<?php echo $temp_shift_end_day; ?>"  placeholder=""/>  
            </div>
            <div class="clearfix"> </div>
            <br />
            <hr />
            <div class="col-md-2">
                <span style="font-weight: bold;">
                    <br />
                    Saturday:
                </span>
            </div>
            <div class="col-md-3">
                <label for="Start Time" id="lbl_office_start_time">Start Time</label><br/> 
                <input type="text" name="saturday_start_time"  id="timepicker" value="<?php echo $temp_saturday_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="sat_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="Office End Time" id="lbl_saturday_end_time">End Time:</label><br/> 
                <input type="text" name="saturday_end_time" id="timepicker2" value="<?php echo $temp_saturday_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
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
<!--
            <div class="col-md-2">
                <span style="font-weight: bold;">
                    <br />
                    Sunday:
                </span>
            </div>
            <div class="col-md-3">
                <label for="Start Time" id="lbl_sunday_start_time">Start Time:</label><br/> 
                <input type="text" name="sunday_start_time" id="timepicker3" value="<?php // echo $sunday_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="sun_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="End Time" id="lbl_sunday_end_time">End Time:</label><br/> 
                <input type="text" name="sunday_end_time" id="timepicker4" value="<?php // echo $sunday_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
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
                <input type="text" name="mon_start_time" id="timepicker5" value="<?php // echo $mon_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="mon_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="End Time" id="lbl_mon_end_time">End Time:</label><br/> 
                <input type="text" name="mon_end_time" id="timepicker6" value="<?php // echo $mon_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
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
                <input type="text" name="tue_start_time" id="timepicker7" value="<?php // echo $tue_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="tue_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="Office End Time" id="lbl_office_end_time">End Time:</label><br/> 
                <input type="text" name="tue_end_time" id="timepicker8" value="<?php // echo $tue_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
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
                <input type="text" name="wed_start_time" id="timepicker9" value="<?php // echo $wed_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="wed_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="End Time" id="lbl_wed_end_time">End Time:</label><br/> 
                <input type="text" name="wed_end_time" id="timepicker10" value="<?php // echo $wed_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
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
                <input type="text" name="thu_start_time" id="timepicker11" value="<?php // echo $thu_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="thu_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for=" End Time" id="lbl_thu_end_time">End Time:</label><br/> 
                <input type="text" name="thu_end_time" id="timepicker12" value="<?php // echo $thu_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
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
                <input type="text" name="fri_start_time" id="timepicker13" value="<?php // echo $fri_start_time; ?>"  placeholder="e.g: 9:00 AM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">Start Day</label><br/> 
                <input type="text" class="k-textbox" name="fri_start_day" value="1" style="width: 60px;" disabled="true"/>  
            </div>
            <div class="col-md-3">
                <label for="End Time" id="lbl_fri_end_time">End Time:</label><br/> 
                <input type="text" name="fri_end_time" id="timepicker14" value="<?php // echo $fri_end_time; ?>"  placeholder="e.g: 5:00 PM"/>  
            </div>
            <div class="col-md-2">
                <label for="Start Time" id="lbl_office_start_time">End Day</label><br/>
                <div id="options">
                    <select id="size7" name="fri_end_day" style="width: 60px;">
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
            </div>
            <div class="clearfix"></div>
            End of first row
            <br />-->

            <div class="col-md-6">
                <input type="submit" class="k-button" value="Save Changes" name="edit_shift"><br/><br />
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
    
