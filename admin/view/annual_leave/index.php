<?php
session_start();
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

$leaves = array();

//Collecting leave data
$annual_leaves = "SELECT      
         l.start_date, l.end_date, 
         e.emp_id, e.emp_firstname, e.emp_lastname,
         e.emp_code, a.app_start_date, a.app_end_date FROM annual_leave l, 
         employee e, applied_annual_leave a WHERE e.emp_id=l.emp_id AND a.emp_id=l.emp_id";

//generating results 
$result = mysqli_query($open, $annual_leaves);
while ($rows = mysqli_fetch_object($result)) {
    $leaves[] = $rows;
}
?>
<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->

<!--Employee Information-->
<div style="float:left;">
    <table border="1" style="font-size:13px;" cellpadding="7" style="float:left;">
        <tr>
            <th>Name</th>
            <th>Id</th>
            <th>Action</th>
        </tr>
        <?php foreach ($leaves as $leave): ?>

            <tr>
                <td><?php
                    echo $leave->emp_firstname;
                    echo " ";
                    echo $leave->emp_lastname;
                    ?></td>
                <td><?php echo $leave->emp_code; ?></td>
                <td><a href=" edit.php?emp_id=<?php echo $leave->emp_id; ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>



<!--Annual Leave plan-->
<div style=" width:75%;overflow: scroll; float: left;">
    <table border="1" cellpadding="7" style="font-size:13px;">
        <tr>
            <th colspan="31" style="text-align:center">January</th>
            <th colspan="28" style="text-align:center">February</th>
            <th colspan="31" style="text-align:center">March</th>
            <th colspan="30" style="text-align:center">April</th>
            <th colspan="31" style="text-align:center">May</th>
            <th colspan="30" style="text-align:center">June</th>
            <th colspan="31" style="text-align:center">July</th>
            <th colspan="31" style="text-align:center">August</th>
            <th colspan="30" style="text-align:center">September</th>
            <th colspan="31" style="text-align:center">October</th>
            <th colspan="30" style="text-align:center">November</th>
            <th colspan="31" style="text-align:center">December</th>
        </tr>

        <?php foreach ($leaves as $leave) {
            ?><tr id="<?php echo $leave->emp_code; ?>">

                <?php
                $emp_fullname = '';
                $emp_firstname = $leave->emp_firstname;
                $emp_lastname = $leave->emp_lastname;
                $emp_fullname .= $emp_firstname . ' ';
                $emp_fullname .= $emp_lastname;
                $emp_code = $leave->emp_code;

                //Gathering date :: Start Date
                $start_date = strtotime($leave->start_date);
                $day = date('d', $start_date);
                $month = date('m', $start_date);
                $year = date('y', $start_date);

                //Gathering date :: End date
                $end_date = strtotime($leave->end_date);
                $end_day = date('d', $end_date);
                $end_month = date('m', $end_date);

                //Getting applied data 
                $app_start_date = strtotime($leave->app_start_date);
                $app_start_day = date('d', $app_start_date);
                $app_start_month = date('m', $app_start_date);

                $app_end_date = strtotime($leave->app_end_date);
                $app_end_day = date('d', $app_end_date);
                $app_end_month = date('m', $app_end_date);

                //January the 
                $mon_jan = 01;
                for ($i = 1; $i <= 31; $i++) {
                    //if start day, start month, end day, end month is in same month
                    if ($i >= $day && $i <= $end_day && $month == $mon_jan && $end_month == $mon_jan) {
                        ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>").click(function () {
                                console.log($(".blue_rpac001").siblings());
                                $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>_<?php echo $i; ?>").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");

                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $i . '_' . $leave->emp_code . '_' . $i . '"><a href="javascript:void(0);" id="test_' . $i . '_' . $leave->emp_code . '" title="01_' . $year . '_' . $i . '" >' . $i . '</a></td>';
                }
                //if range is not in the same month
                else if ($i >= $day && $i <= 31 && $month == $mon_jan && $end_month != $mon_jan) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {

                            //Modifying color on click
                            $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>").click(function () {
                                console.log($(".blue_rpac001").siblings().length);
                                $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>_<?php echo $i; ?>").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $i . '_' . $leave->emp_code . '_' . $i . '"><a style="text_decoration:none;" href="javascript:void(0);" id="test_' . $i . '_' . $leave->emp_code . '" title="01_' . $year . '_' . $i . '" >' . $i . '</a></td>';
                }
                //Applied range is in the same month
                else if ($i >= $app_start_day && $i <= $app_end_day && $app_start_month == $mon_jan && $app_end_month == $mon_jan) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>").click(function () {
                                console.log($(".blue_rpac001").siblings().length);
                                $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>_<?php echo $i; ?>").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $i . '_' . $leave->emp_code . '_' . $i . '"><a href="javascript:void(0);" id="test_' . $i . '_' . $leave->emp_code . '" title="01_' . $year . '_' . $i . '" >' . $i . '</a></td>';
                }
                //Applied range is not in the same month
                else if ($i >= $app_start_day && $i <= 31 && $app_start_month == $mon_jan && $app_end_month != $mon_jan) {
                    echo '<td bgcolor="green"> ' . $i . '</td>';
                }
                //Normal :: No effect
                else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>").click(function () {
                                //alert("work");
                                console.log($(".blue_rpac001").siblings().length);
                                $("#test_<?php echo $i; ?>_<?php echo $leave->emp_code; ?>_<?php echo $i; ?>").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>

                    <style type = "text/css">
                        a {
                            text-decoration: none;
                        }
                        .blue_<?php echo $leave->emp_code; ?>
                        {
                            background-color: blue;
                            color:white;   
                        }
                        .blue_<?php echo $leave->emp_code; ?> a {
                            color: white;
                            font-weight: bold;
                            text-decoration: none;
                        }
                    </style>
                    <?php
                    echo '<td id="test_' . $i . '_' . $leave->emp_code . '_' . $i . '"><a style="text-decoration:none;" href="javascript:void(0);" id="test_' . $i . '_' . $leave->emp_code . '" title="01_' . $year . '_' . $i . '" >' . $i . '</a></td>';
                }
            }

            //February
            $mon_feb = 02;
            for ($j = 1; $j <= 28; $j++) {
                //if planned range is in the same month
                if ($j >= $day && $j <= $end_day && $month == $mon_feb && $end_month == $mon_feb) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($j >= $day && $j <= 28 && $month == $mon_feb && $end_month != $mon_feb) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                }
                //if planned range is from preceeding month
                else if ($j <= $end_day && $end_month == $mon_feb && $month != $mon_feb) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                }
                //if applied range is in the same month
                else if ($j >= $app_start_day && $j <= $app_end_day && $app_start_month == $mon_feb) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                }
                //Applied range is not in the same month
                else if ($j >= $app_start_date && $j <= 28 && $app_start_month == $mon_feb && $app_end_month != $mon_feb) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                }
                //Applied range is from the preceeding month
                else if ($j <= $app_end_day && $app_end_month == $mon_feb && $app_start_month != $mon_feb) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_feb").click(function () {
                                $(" #test_<?php echo $j; ?>_<?php echo $leave->emp_code; ?>_<?php echo $j; ?>_feb").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $j . '_' . $leave->emp_code . '_' . $j . '_feb"><a href="javascript:void(0);" id="test_' . $j . '_' . $leave->emp_code . '_feb" title="01_' . $year . '_' . $j . '" >' . $j . '</a></td>';
                }
            }


            //March
            $mon_mar = 03;
            for ($k = 1; $k <= 31; $k++) {
                //If planned range is in the same month
                if ($k >= $day && $k <= $end_day && $month == $mon_mar && $end_month == $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($k >= $day && $k <= 31 && $month == $mon_mar && $end_month != $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($k <= $end_day && $end_month == $mon_mar && $month != $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                }
                //if applied range is in the same month
                else if ($k >= $app_start_day && $k <= $app_end_day && $app_start_month == $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($k >= $app_start_date && $k <= 31 && $app_start_month == $mon_mar && $app_end_month != $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($k <= $app_end_day && $app_end_month == $mon_mar && $app_start_month != $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_mar").click(function () {
                                $(" #test_<?php echo $k; ?>_<?php echo $leave->emp_code; ?>_<?php echo $k; ?>_mar").toggleClass("blue_<?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $k . '_' . $leave->emp_code . '_' . $k . '_mar"><a href="javascript:void(0);" id="test_' . $k . '_' . $leave->emp_code . '_mar" title="03_' . $year . '_' . $k . '" >' . $k . '</a></td>';
                }
            }

            //April
            $mon_april = 04;
            for ($l = 1; $l <= 30; $l++) {
                //If planned range is in the same month
                if ($l >= $day && $l <= $end_day && $month == $mon_april && $end_month == $mon_april) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($l >= $day && $l <= 30 && $month == $mon_april && $end_month != $mon_april) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $(" #test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($l <= $end_day && $end_month == $mon_april && $month != $mon_mar) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $(" #test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                }
                //if applied range is in the same month
                else if ($l >= $app_start_day && $l <= $app_end_day && $app_start_month == $mon_april) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $(" #test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($l >= $app_start_date && $l <= 30 && $app_start_month == $mon_april && $app_end_month != $mon_april) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $(" #test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td  bgcolor="green" id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($l <= $app_end_day && $app_end_month == $mon_april && $app_start_month != $mon_april) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $(" #test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_april").click(function () {
                                $(" #test_<?php echo $l; ?>_<?php echo $leave->emp_code; ?>_<?php echo $l; ?>_april").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $l . '_' . $leave->emp_code . '_' . $l . '_april"><a href="javascript:void(0);" id="test_' . $l . '_' . $leave->emp_code . '_april" title="03_' . $year . '_' . $l . '" >' . $l . '</a></td>';
                }
            }

            //May
            $mon_may = 05;
            for ($m = 1; $m <= 31; $m++) {
                //If planned range is in the same month
                if ($m >= $day && $m <= $end_day && $month == $mon_may && $end_month == $mon_may) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($m >= $day && $l <= 31 && $month == $mon_may && $end_month != $mon_may) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($m <= $end_day && $end_month == $mon_may && $month != $mon_may) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                }
                //if applied range is in the same month
                else if ($m >= $app_start_day && $m <= $app_end_day && $app_start_month == $mon_may) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($m >= $app_start_date && $m <= 31 && $app_start_month == $mon_may && $app_end_month != $mon_may) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($m <= $app_end_day && $app_end_month == $mon_may && $app_start_month != $mon_may) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_may").click(function () {
                                $(" #test_<?php echo $m; ?>_<?php echo $leave->emp_code; ?>_<?php echo $m; ?>_may").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $m . '_' . $leave->emp_code . '_' . $m . '_may"><a href="javascript:void(0);" id="test_' . $m . '_' . $leave->emp_code . '_may" title="03_' . $year . '_' . $m . '" >' . $m . '</a></td>';
                }
            }

            //June
            $mon_june = 06;
            for ($n = 1; $n <= 30; $n++) {
                //If planned range is in the same month
                if ($n >= $day && $n <= $end_day && $month == $mon_june && $end_month == $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($n >= $day && $n <= 30 && $month == $mon_june && $end_month != $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($n <= $end_day && $end_month == $mon_june && $month != $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                }
                //if applied range is in the same month
                else if ($n >= $app_start_day && $n <= $app_end_day && $app_start_month == $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($n >= $app_start_date && $n <= 30 && $app_start_month == $mon_june && $app_end_month != $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($n <= $app_end_day && $app_end_month == $mon_june && $app_start_month != $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_june").click(function () {
                                $(" #test_<?php echo $n; ?>_<?php echo $leave->emp_code; ?>_<?php echo $n; ?>_june").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $n . '_' . $leave->emp_code . '_' . $n . '_june"><a href="javascript:void(0);" id="test_' . $n . '_' . $leave->emp_code . '_june" title="03_' . $year . '_' . $n . '" >' . $n . '</a></td>';
                }
            }

            //July
            $mon_july = 07;
            for ($o = 1; $o <= 31; $o++) {
                //If planned range is in the same month
                if ($o >= $day && $o <= $end_day && $month == $mon_july && $end_month == $mon_july) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($o >= $day && $o <= 31 && $month == $mon_july && $end_month != $mon_july) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($o <= $end_day && $end_month == $mon_july && $month != $mon_july) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                }
                //if applied range is in the same month
                else if ($o >= $app_start_day && $o <= $app_end_day && $app_start_month == $mon_july) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($o >= $app_start_date && $o <= 31 && $app_start_month == $mon_july && $app_end_month != $mon_july) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($o <= $app_end_day && $app_end_month == $mon_july && $app_start_month != $mon_july) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_july").click(function () {
                                $(" #test_<?php echo $o; ?>_<?php echo $leave->emp_code; ?>_<?php echo $o; ?>_july").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $o . '_' . $leave->emp_code . '_' . $o . '_july"><a href="javascript:void(0);" id="test_' . $o . '_' . $leave->emp_code . '_july" title="03_' . $year . '_' . $o . '" >' . $o . '</a></td>';
                }
            }

            //August
            $mon_aug = 08;
            for ($p = 1; $p <= 31; $p++) {
                //If planned range is in the same month
                if ($p >= $day && $p <= $end_day && $month == $mon_aug && $end_month == $mon_aug) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($p >= $day && $p <= 31 && $month == $mon_aug && $end_month != $mon_aug) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($p <= $end_day && $end_month == $mon_aug && $month != $mon_aug) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                }
                //if applied range is in the same month
                else if ($p >= $app_start_day && $p <= $app_end_day && $app_start_month == $mon_aug) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($p >= $app_start_date && $p <= 31 && $app_start_month == $mon_aug && $app_end_month != $mon_aug) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($p <= $app_end_day && $app_end_month == $mon_june && $app_start_month != $mon_june) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_aug").click(function () {
                                $(" #test_<?php echo $p; ?>_<?php echo $leave->emp_code; ?>_<?php echo $p; ?>_aug").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $p . '_' . $leave->emp_code . '_' . $p . '_aug"><a href="javascript:void(0);" id="test_' . $p . '_' . $leave->emp_code . '_aug" title="03_' . $year . '_' . $p . '" >' . $p . '</a></td>';
                }
            }

            //September
            $mon_sep = 09;
            for ($q = 1; $q <= 30; $q++) {
                //If planned range is in the same month
                if ($q >= $day && $q <= $end_day && $month == $mon_sep && $end_month == $mon_sep) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($q >= $day && $q <= 30 && $month == $mon_sep && $end_month != $mon_sep) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($q <= $end_day && $end_month == $mon_sep && $month != $mon_sep) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                }

                //if applied range is in the same month
                else if ($q >= $app_start_day && $q <= $app_end_day && $app_start_month == $mon_sep) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($q >= $app_start_date && $q <= 30 && $app_start_month == $mon_sep && $app_end_month != $mon_sep) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                }

                //Applied range is from the preceeding month :: Deemed solved for single equation
                else if ($q <= $app_end_day && $app_end_month = $mon_sep && $app_start_month != $mon_sep) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                } else {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_sep").click(function () {
                                $("#test_<?php echo $q; ?>_<?php echo $leave->emp_code; ?>_<?php echo $q; ?>_sep").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $q . '_' . $leave->emp_code . '_' . $q . '_sep"><a href="javascript:void(0);" id="test_' . $q . '_' . $leave->emp_code . '_sep" title="03_' . $year . '_' . $q . '" >' . $q . '</a></td>';
                }
            }

            //October
            $mon_oct = 10;
            for ($r = 1; $r <= 31; $r++) {
                //If planned range is in the same month
                if ($r >= $day && $r <= $end_day && $month == $mon_oct && $end_month == $mon_oct) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($r >= $day && $r <= 31 && $month == $mon_oct && $end_month != $mon_oct) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($r <= $end_day && $end_month == $mon_oct && $month != $mon_oct) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                }
                //if applied range is in the same month
                else if ($r >= $app_start_day && $r <= $app_end_day && $app_start_month == $mon_oct) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($r >= $app_start_date && $r <= 31 && $app_start_month == $mon_oct && $app_end_month != $mon_oct) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($r <= $app_end_day && $app_end_month == $mon_oct && $app_start_month != $mon_oct) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                } else {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_oct").click(function () {
                                $("#test_<?php echo $r; ?>_<?php echo $leave->emp_code; ?>_<?php echo $r; ?>_oct").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $r . '_' . $leave->emp_code . '_' . $r . '_oct"><a href="javascript:void(0);" id="test_' . $r . '_' . $leave->emp_code . '_oct" title="03_' . $year . '_' . $r . '" >' . $r . '</a></td>';
                }
            }

            //November
            $mon_nov = 11;
            for ($s = 1; $s <= 30; $s++) {
                //If planned range is in the same month
                if ($s >= $day && $s <= $end_day && $month == $mon_nov && $end_month == $mon_nov) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($s >= $day && $s <= 30 && $month == $mon_nov && $end_month != $mon_nov) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($s <= $end_day && $end_month == $mon_nov && $month != $mon_nov) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                }
                //if applied range is in the same month
                else if ($s >= $app_start_day && $s <= $app_end_day && $app_start_month == $mon_nov) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($s >= $app_start_date && $s <= 30 && $app_start_month == $mon_nov && $app_end_month != $mon_nov) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($s <= $app_end_day && $app_end_month == $mon_nov && $app_start_month != $mon_nov) {
                    ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                } else {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_nov").click(function () {
                                $("#test_<?php echo $s; ?>_<?php echo $leave->emp_code; ?>_<?php echo $s; ?>_nov").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $s . '_' . $leave->emp_code . '_' . $s . '_nov"><a href="javascript:void(0);" id="test_' . $s . '_' . $leave->emp_code . '_nov" title="03_' . $year . '_' . $s . '" >' . $s . '</a></td>';
                }
            }

            //December
            $mon_dec = 12;
            for ($u = 1; $u <= 31; $u++) {
                //If planned range is in the same month
                if ($u >= $day && $u <= $end_day && $month == $mon_dec && $end_month == $mon_dec) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                }
                //if planned range is not in the same month
                else if ($u >= $day && $u <= 31 && $month == $mon_dec && $end_month != $mon_dec) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                }
                //if planned end day is from the preceeding month
                else if ($u <= $end_day && $end_month == $mon_dec && $month != $mon_dec) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="red" id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                }
                //if applied range is in the same month
                else if ($u >= $app_start_day && $u <= $app_end_day && $app_start_month == $mon_dec) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                }

                //if applied range is not in the same month
                else if ($u >= $app_start_date && $u <= 31 && $app_start_month == $mon_dec && $app_end_month != $mon_dec) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                }

                //Applied range is from the preceeding month
                else if ($u <= $app_end_day && $app_end_month == $mon_dec && $app_start_month != $mon_dec) {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td bgcolor="green" id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                } else {
                    ?>

                    <script type="text/javascript">
                        $(document).ready(function () {
                            //Modifying color on click
                            $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_dec").click(function () {
                                $("#test_<?php echo $u; ?>_<?php echo $leave->emp_code; ?>_<?php echo $u; ?>_dec").toggleClass("blue_<?php echo $leave->emp_code; ?><?php echo $leave->emp_code; ?>");
                            });
                        });
                    </script>
                    <?php
                    echo '<td id="test_' . $u . '_' . $leave->emp_code . '_' . $u . '_dec"><a href="javascript:void(0);" id="test_' . $u . '_' . $leave->emp_code . '_dec" title="12_' . $year . '_' . $u . '" >' . $u . '</a></td>';
                }
            }
            ?>
            </tr>
        <?php } ?>
    </table>



</div>
<input type="submit" class="k-textbox pull-right" value="Save Changes" name="save_schedule" style="margin-top:10px;">
</div>
</div>

<div style="clear:both;"></div>

<br /><br />


</div>
</div>
</div>


<?php include '../view_layout/footer_view.php'; ?>
    
