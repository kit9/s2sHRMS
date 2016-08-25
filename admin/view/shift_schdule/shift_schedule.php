<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
error_reporting(0);


//initializing variables
$shift_id = array();
$shift_title = '';
$company_id = '';
$company_title = '';
$saturday_start_time = '';
$companies = $con->SelectAll("company");
$attendances = $con->SelectAll("attendance_policy");
$shifts = $con->SelectAll("shift_policy");

$shiftquery = array();

if (isset($_GET['shift_id'])) {
    $shift_id = $_GET['shift_id'];
    $_SESSION["shift_id"] = $shift_id;
    $condition = "shift_id='" . $shift_id . "'order by shift_id DESC";
    $shiftquery = $con->SelectAllByCondition("shift_policy", $condition);
    if (count($shiftquery) >= 1) {
        foreach ($shiftquery as $n) {
            $shift_start_day = $n->shift_start_day;
            $shift_end_day = $n->shift_end_day;
            $saturday_start_time = $n->saturday_start_time;
            $saturday_end_time = $n->saturday_end_time;
        }
    }
}

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

//Declaring local variables
$resul = '';
$err = "";
$msg = '';

//Submitting the form
if (isset($_POST["btnSubmit"])) {
    extract($_POST);

    $con->debug($_POST);
    exit();


    $shiftStart = date_create($shift_start_day);
    $shiftStartDate = date_format($shiftStart, 'Y-m-d');

    $shiftEnd = date_create($shift_end_day);
    $shiftEndDate = date_format($shiftEnd, 'Y-m-d');

    $strFirstTime = date("H:i:s", strtotime($_POST["saturday_start_time"]));
    $strEndTime = date("H:i:s", strtotime($_POST["saturday_end_time"]));

//    $con->debug($strFirstTime);
//    $con->debug($strEndTime);
//     exit();

    if (empty($shift_start_day)) {
        $err = "Shift Start date is not selected";
    } else if (empty($shift_end_day)) {
        $err = "Shift End Date is empty";
    } else if (empty($saturday_start_time)) {
        $err = "Saturday Start Time field is empty";
    } else if (empty($saturday_end_time)) {
        $err = "Saturday End Time field is empty";
    } else {
        $emp_shift = array(
            "shift_id" => $shift_id,
            "shift_start_day" => $shiftStartDate,
            "shift_end_day" => $shiftEndDate,
            "saturday_start_time" => $strFirstTime,
            "saturday_end_time" => $strEndTime
        );
    }
    if ($con->insert("shift_policy", $emp_shift) == 1) {
        $msg = "Data Inserted successfully!";
    } else {
        $err = "Something went wrong!";
    }
}
?>

<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Employee Information</h6></div>
    <div class="widget-body" background-color: white;>
    <?php include("../../layout/msg.php"); ?>

         <div>
            <div style="border: 1px solid #72AF46;height:375px;padding: 3%; " class="col-md-2">
                <label for="Full name">Shift Name</label> <br />
                <?php if (count($shifts) >= 1): ?>
                    <?php foreach ($shifts as $p): ?>
                        <label for="Full name"> <a href="shift_schedule.php?shift_id=<?php echo $p->shift_id; ?>"> <?php echo $p->shift_title; ?></a></label> <br />
                    <?php endforeach; ?>
                <?php endif; ?> 
            </div>

        </div>  

        <div style="height: auto; width:82%; float: right;" id="example" class="k-content">
            <form method="post" enctype="multipart/form-data">
                <div id="tabstrip">
                    <ul>

                        <li class="k-state-active">
                            Schedule
                        </li>

                    </ul>
                    <div>

                        <div class="weather">

                            <div style="margin-left: 30px;" class="col-md-11">
                                <?php if (isset($_GET['shift_id'])) { ?>

                                    <div class="col-md-6">
                                        <label for="Full name">Start Date:</label> <br />
                                        <input type="text" value="<?php echo $shift_start_day; ?>" id="shift_start_day" placeholder="" name="shift_start_day" type="text" style="width: 80%;"/>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="Full name">End Date:</label> <br />
                                        <input type="text" value="<?php echo $shift_end_day; ?>" id="shift_end_day" placeholder="" name="shift_end_day" type="text" style="width: 80%;"/>
                                    </div>

                                    <div class="clearfix"></div>
                                    <br/>

                                    <div class="col-md-6">
                                        <label for="Full name">Shift Start Time:</label> <br />
                                        <input type="text" value="<?php echo $saturday_start_time; ?>" id="saturday_start_time" placeholder="" name="saturday_start_time" type="text" style="width: 80%;"/>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="Full name">Shift End Time:</label> <br />
                                        <input type="text" value="<?php echo $saturday_end_time; ?>" id="saturday_end_time" placeholder="" name="saturday_end_time" type="text" style="width: 80%;"/>
                                    </div>

                                    <div class="clearfix"></div>
                                    <br/>

                                    <script>
                                        $(document).ready(function() {
                                            // create DatePicker from input HTML element
                                            $("#shift_start_day").kendoDatePicker();
                                            $("#shift_end_day").kendoDatePicker();
                                            $("#saturday_start_time").kendoTimePicker();
                                            $("#saturday_end_time").kendoTimePicker();
                                        });
                                    </script>

                                    <?php
                                } else {
                                    echo "Please Select a Shifting Policy!";
                                }
                                ?>

                            </div>


                            <div class="clearfix"></div>
                            <br />

                            <div class="col-md-2">
                                <input type="submit" class="k-button" value="Submit" name="btnSubmit">
                            </div>

                            <div class="clearfix"></div>
                            <br />

                        </div>
                    </div>
                    <div>

                        <!-- User Information was in this div and it is deleted -->

                    </div>
            </form>



            <style scoped>
                #forecast {
                    width: 100%;
                    height: auto;
                    margin: 30px auto;
                    padding: 80px 15px 0 15px;
                    background: url('../../content/web/tabstrip/forecast.png') transparent no-repeat 0 0;
                }

                .sunny, .cloudy, .rainy {
                    display: inline-block;
                    margin: 20px 0 20px 10px;
                    width: 128px;
                    height: auto;
                    background: url('../../content/web/tabstrip/weather.png') transparent no-repeat 0 0;
                }

                .cloudy{
                    background-position: -128px 0;
                }

                .rainy{
                    background-position: -256px 0;
                }

                .weather {
                    width: 100%;
                    padding: 40px 0 0 0;

                }

                #forecast h2 {
                    font-weight: lighter;
                    font-size: 5em;
                    padding: 0;
                    margin: 0;
                }

                #forecast h2 span {
                    background: none;
                    padding-left: 5px;
                    font-size: .5em;
                    vertical-align: top;
                }

                #forecast p {
                    margin: 0;
                    padding: 0;
                }
            </style>

            <script type="text/javascript">
                $(document).ready(function() {
                    $("#tabstrip").kendoTabStrip({
                        animation: {
                            open: {
                                effects: "fadeIn"
                            }
                        }
                    });
                });

                $(document).ready(function() {
                    $("#size").kendoDropDownList();
                    $("#size2").kendoDropDownList();
                    $("#size3").kendoDropDownList();
                    $("#size4").kendoDropDownList();
                    $("#size5").kendoDropDownList();
                    $("#size6").kendoDropDownList();
                    $("#size7").kendoDropDownList();
                    $("#size8").kendoDropDownList();
                    $("#size9").kendoDropDownList();
                    $("#size10").kendoDropDownList();
                    $("#size11").kendoDropDownList();
                    $("#size13").kendoDropDownList();
                    $("#size14").kendoDropDownList();
                    $("#size15").kendoDropDownList();
                    $("#new").kendoDropDownList();
                });

                $(document).ready(function() {
                    $("#files").kendoUpload();
                });
            </script>

        </div>
    </div>
    <br />
    <div class="clearfix"></div>

</div>
</div>

<?php include '../view_layout/footer_view.php'; ?>



