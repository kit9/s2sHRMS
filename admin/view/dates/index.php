<?php
session_start();
//Importing class library
include ('../../config/class.config.php');
//Configuration classes
$con = new Config();
//Connection string
$open = $con->open();
error_reporting(0);

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Permission ID from permission table
if (isset($_GET["permission_id"])) {
    $permission_id = $_GET["permission_id"];
}

if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
$year = "";
$month = "";

$companiesN = $con->SelectAll("company");

if (isset($_POST['btnEnterDates'])) {
    extract($_POST);

    $year = $_POST["year"];
    $month = $_POST["month"];
    $companyName = $_POST["company_id"];

    if ($month <= 9) {
        $month = "0" . $month;
    }

    $company_array = array("company_id" => $companyName);
    $dates_arrays = $con->SelectAllByField("dates", $company_array);

    $day_type_query = $con->SelectAll("day_type");
    $holiday_type_query = $con->SelectAll("holiday");


    $selected_dates_array = array();

    foreach ($dates_arrays as $dt) {


        $temp_date = explode('-', $dt->date);
        $mon = $temp_date[1];
        $yar = $temp_date[0];

        $datview = $temp_date[2];


        if ($year == $yar && $month == $mon) {

            array_push($selected_dates_array, $dt);
        }
    }
}
?>

<?php include '../view_layout/header_view.php'; ?>
<!-- Widget -->
<div class="widget" style="background-color: white;">
    <div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Monthly Calendar</h6></div>
    <div class="widget-body" background-color: white;>
    <?php include("../../layout/msg.php"); ?>
         <div id="example" class="k-content">
            <form method="post" enctype="multipart/form-data">

                <div class="col-md-6">
                    <label for="Full name">Company Name:</label><br/> 
                    <select id="company" style="width: 80%" name="company_id" >
                        <option value="0">Select Company</option>
                        <?php if (count($companiesN) >= 1): ?>
                            <?php foreach ($companiesN as $com): ?>
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
                <script type="text/javascript">
                    $(document).ready(function() {
                        $("#company").kendoDropDownList();
                    });
                </script>

                <div class="clearfix"></div>
                <br/>

                <div class="col-md-6">
                    <label for="Full name">Year:</label><br/> 
                    <input id="year1" name="year" style="width: 80%;" value="<?php echo $year; ?>" />
                </div>
                <div class="col-md-6">
                    <label for="Full name">Month:</label> <br />
                    <input id="month1" name="month" style="width: 80%;" value="<?php echo $month; ?>" />
                </div>
                <div class="clearfix"></div>
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
                                //                        type: "json",
                                //                        data: productsData
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

                <div class="clearfix"></div>
                <br />
                <div class="col-md-5">
                    <input class="k-button" type="submit" value="Generate Information" name="btnEnterDates" />
                </div>
                <div class="clearfix"></div>
                <br />
        </div>
        </form>


        <?php if (isset($_POST['btnEnterDates'])) { ?> 
            <div id="details"></div> 
            <div class="row">
                <div id="example" class="k-content">
                    <table id="grid" style="font-size: 14px;">
                        <colgroup>
                            <col style="width:130px"/>
                            <col style="width:150px" />
                            <col style="width:130px"/>
                            <col style="width:150px" />
                        </colgroup>
                        <thead>
                            <tr>

                                <?php // if (isset($_SESSION["user_type"])):  ?>

                                <?php // if ($_SESSION["user_type"] == "super_admin"):  ?>

                                <th data-field="date">Date</th>
                                <th data-field="day_type_id">Day Type Title</th>
                                <th data-field="holiday_id">Holiday</th>
                                <th data-field="action">Action</th>
                                <?php // else:  ?>
                                <!--<th data-field="standard_out_time">Out Time</th>-->
                                <!--<th data-field="standard_ot_hours">Over Time</th>-->
                                <?php // endif;  ?>

                                <?php // endif;  ?>


                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($selected_dates_array) >= 1): ?>
                                <?php foreach ($selected_dates_array as $jr): ?>



                                    <?php $day_type_id_new = $jr->day_type_id; ?>
                                    <?php $holiday_id_new = $jr->holiday_id; ?>


                                    <?php
                                    $subsday = $con->SelectAllByCondition("day_type", "day_type_id='$day_type_id_new'");
                                    if (count($subsday) == 0) {
                                        $day_type_grid_n = "";
                                    } else {
                                        $day_type_grid_n = $subsday{0}->day_title;
                                    }
                                    $holidays = $con->SelectAllByCondition("holiday", "holiday_id='$holiday_id_new'");

                                    if (count($holidays) == 0) {
                                        $holiday_type_grid_n = "";
                                    } else {

                                        $holiday_type_grid_n = $holidays{0}->holiday_type;
                                    }
                                    ?>

                                    <tr id="<?php echo $jr->dates_id; ?>_tr">
                                        <td id="<?php echo $jr->dates_id; ?>_date"><?php echo $jr->date; ?></td>
                                        <td>
                                            <div id="<?php echo $jr->dates_id; ?>_in_time"><?php echo $day_type_grid_n; ?></div>
                                            <div class="k-widget k-dropdown k-header" style="display: none;" id="<?php echo $jr->dates_id; ?>_in_time_pick">
                                                <select id="<?php echo $jr->dates_id; ?>_day_type_picker" style="width: 80%" name="day_type_id">
                                                    <option value="0">Select Day Type</option>
                                                    <?php if (count($day_type_query) >= 1): ?>
                                                        <?php foreach ($day_type_query as $st): ?>
                                                            <option value="<?php echo $st->day_type_id; ?>" 
                                                            <?php
                                                            if ($st->day_type_id == $jr->day_type_id) {
                                                                echo "selected='selected'";
                                                            }
                                                            ?>><?php echo $st->day_title; ?></option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                </select>   
                                            </div>

                                        </td>
                                        <td>
                                            <div id="<?php echo $jr->dates_id; ?>_out_time"><?php echo $holiday_type_grid_n; ?></div>
                                            <div class="k-widget k-dropdown k-header" style="display: none;" id="<?php echo $jr->dates_id; ?>_out_time_pick">
                                                <select id="<?php echo $jr->dates_id; ?>_holiday_picker"  style="width: 80%" >
                                                    <option value="0">Select Holiday Type</option>
                                                    <?php if (count($holiday_type_query) >= 1): ?>
                                                        <?php foreach ($holiday_type_query as $ht): ?>
                                                            <option value="<?php echo $ht->holiday_id; ?>" 
                                                            <?php
                                                            if ($ht->holiday_id == $jr->holiday_id) {
                                                                echo "selected='selected'";
                                                            }
                                                            ?>><?php echo $ht->holiday_type; ?></option>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                </select>  

                                            </div>
                                        </td>

                                        <td role="gridcell">
                                            <?php if ($con->hasPermissionUpdate($permission_id) == "yes"):?>
                                            <a id="<?php echo $jr->dates_id; ?>_click" class="k-button k-button-icontext k-grid-edit" href="javascript:void(0);">
                                                <span class="k-icon k-edit"></span>
                                                Edit
                                            </a>
                                            <?php endif; ?>

                                            <a style="display:none;" id="<?php echo $jr->dates_id; ?>_update_click" class="k-button k-button-icontext k-grid-edit" href="javascript:void(0);">
                                                <span class="k-icon k-edit"></span>
                                                Update
                                            </a>

                                            <script type="text/javascript">
                                                $(document).ready(function() {
                                                    $(document).on('click', '#<?php echo $jr->dates_id; ?>_click', function() {
                                                        $("#<?php echo $jr->dates_id; ?>_in_time").hide();
                                                        $("#<?php echo $jr->dates_id; ?>_out_time").hide();
                                                        $("#<?php echo $jr->dates_id; ?>_in_time_pick").show();
                                                        $("#<?php echo $jr->dates_id; ?>_out_time_pick").show();
                                                        $("#<?php echo $jr->dates_id; ?>_in_time_picker").val($("#<?php echo $jr->dates_id; ?>_in_time").html());
                                                        $("#<?php echo $jr->dates_id; ?>_out_time_picker").val($("#<?php echo $jr->dates_id; ?>_out_time").html());
                                                        $("#<?php echo $jr->dates_id; ?>_click").hide();
                                                        $("#<?php echo $jr->dates_id; ?>_update_click").show();
                                                    });
                                                    $(document).on('click', '#<?php echo $jr->dates_id; ?>_update_click', function() {
                                                        var dates_id_<?php echo $jr->dates_id; ?> = <?php echo $jr->dates_id; ?>;
                                                        var dat_type_<?php echo $jr->dates_id; ?> = $("#<?php echo $jr->dates_id; ?>_day_type_picker option:selected").val();
                                                        var holiday_type_<?php echo $jr->dates_id; ?> = $("#<?php echo $jr->dates_id; ?>_holiday_picker option:selected").val();


                                                        $.ajax({
                                                            type: "POST",
                                                            url: "processupdate.php",
                                                            data: {dates_id: dates_id_<?php echo $jr->dates_id; ?>, day_type_id: dat_type_<?php echo $jr->dates_id; ?>, holiday_id: holiday_type_<?php echo $jr->dates_id; ?>},
                                                            success: function(response) {
                                                                var string_arr = response.split(',');
                                                                //console.log(response);
                                                                $("#<?php echo $jr->dates_id; ?>_in_time").show();
                                                                $("#<?php echo $jr->dates_id; ?>_out_time").show();
                                                                $("#<?php echo $jr->dates_id; ?>_in_time_pick").hide();
                                                                $("#<?php echo $jr->dates_id; ?>_out_time_pick").hide();
                                                                $("#<?php echo $jr->dates_id; ?>_click").show();
                                                                $("#<?php echo $jr->dates_id; ?>_update_click").hide();

                                                                $("#<?php echo $jr->dates_id; ?>_in_time").html(string_arr[0]);
                                                                $("#<?php echo $jr->dates_id; ?>_out_time").html(string_arr[1]);
                                                                $("#<?php echo $jr->dates_id; ?>_ot_hours").html(string_arr[2]);


                                                            },
                                                            error: function(a, b, c) {
                                                                //alert(a.responseText);
                                                            }

                                                        });
                                                    });


                                                });
                                            </script>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?> 

                        </tbody>
                    </table>
                    <div class="clearfix"></div>


                    <script>
                        $(document).ready(function() {
                            $("#grid").kendoGrid({
                                pageable: {
                                    refresh: true,
                                    input: true,
                                    numeric: false,
                                    pageSize: 40,
                                    pageSizes: true,
                                    pageSizes: [40, 80, 120],
                                },
                                filterable: true,
                                sortable: true,
                                groupable: true
                            });
                        });

                    </script>
                </div>
                <div class="clearfix"></div>
            </div>

        <?php } ?>
    </div>
</div>
<br />
<div class="clearfix"></div>


<?php include '../view_layout/footer_view.php'; ?>



