<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$open = $con->open();

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


$masterID = 0;
$empCode = '';
$currentStep = 0;
$review_trigger_flag = '';
$no_review_flag = '';

if (isset($_SESSION['emp_code'])) {
    $empCode = $_SESSION['emp_code'];
}

if (isset($_GET['mid'])) {
    $masterID = $_GET['mid'];
}

if (isset($_GET['aws_id'])) {
    $aws_id = $_GET['aws_id'];
}

//This will decide if review buttons will be visible
if (isset($_GET["from_all"])) {
    $no_review_flag = $_GET["from_all"];
}


if ($masterID > 0) {
    $arrAppDetails = array();
    $sqlGetAppDetails = "SELECT *, temp1.emp_firstname AS Applicant, temp2.emp_firstname AS Inferior "
            . "FROM approval_workflow_status AS aws "
            . "LEFT JOIN tmp_employee AS temp1 ON aws.aws_emp_code=temp1.emp_code "
            . "LEFT JOIN tmp_employee AS temp2 ON aws.aws_sup_emp_code=temp2.emp_code "
            . "WHERE aws.leave_application_master_id=$masterID";
    $resultGetAppDetails = mysqli_query($open, $sqlGetAppDetails);
    if ($resultGetAppDetails) {
        while ($resultGetAppDetailsObj = mysqli_fetch_object($resultGetAppDetails)) {
            $arrAppDetails[] = $resultGetAppDetailsObj;
        }
    } else {
        echo "resultGetAppDetails query failed.";
    }
}

//Find current steps
foreach ($arrAppDetails AS $AppSteps) {
    if ($AppSteps->aws_sup_emp_code == $empCode) {
        $currentStep = $AppSteps->aws_step;
    }
}

//Find leave details against leave appplication master ID
$history_query = "select  A.leave_application_master_id, A.leave_type_id, A.mindate, B.maxdate, A.details_no_of_days,  A.status, A.remarks, A.leave_title FROM
    (SELECT  leave_application_master_id, leave_type_id, min(details_date) as mindate , details_no_of_days,  leave_application_details.status, leave_title, remarks from leave_application_details 
    LEFT JOIN leave_policy  ON leave_policy.leave_policy_id =  leave_application_details.leave_type_id
     where leave_application_master_id 
    in(SELECT  leave_application_master_id from leave_application_master  WHERE leave_application_master_id='$masterID')
    GROUP BY leave_application_master_id, leave_type_id)  A,
    (SELECT  leave_application_master_id, leave_type_id, max(details_date) as maxdate from leave_application_details where leave_application_master_id 
    in(SELECT  leave_application_master_id from leave_application_master  WHERE leave_application_master_id='$masterID')
    GROUP BY leave_application_master_id, leave_type_id) B
    WHERE A.leave_application_master_id = B.leave_application_master_id and A.leave_type_id = B.leave_type_id";
$histories = $con->QueryResult($history_query);
?>
<?php include '../view_layout/header_view.php'; ?>
<div id="test_container"></div>
<div class="clearfix"></div>
<br />

<a href="index.php" class="k-button pull-right">Back to List</a>
<div class="clearfix"></div>

<div class="col-md-6" style="padding-left: 0px;">
    <span><b>Leave Application Review Status</b></span>
</div>
<hr />
<?php
$count = 1;
foreach ($arrAppDetails AS $AppSteps) {
    if ($AppSteps->aws_step <= $currentStep) {
        $review_trigger_flag = TRUE;
        ?>
        <?php if ($AppSteps->aws_sup_emp_code == $empCode): ?>
            <div class="col-md-2" style="padding-left: 0px; color:yellowgreen;">
                <span><b>Review Step:</b></span>
                <span><?php echo $AppSteps->aws_step; ?></span>
            </div>
            <div class="col-md-3" style="color:yellowgreen;">
                <span><b>Supervisor Name:</b></span><span><?php echo $AppSteps->Inferior; ?></span>
            </div>
            <div class="col-md-3" style="color:yellowgreen;">
                <span><b>Supervisor Code:</b></span>
                <span><?php echo $AppSteps->aws_sup_emp_code; ?></span>
            </div>
            <div class="col-md-3" style="color:yellowgreen;">
                <span><b>Review Status:</b></span>
                <span><?php echo $AppSteps->aws_status; ?></span>
            </div>
            <div class="col-md-1" style="color:yellowgreen;">(me)</div>
            <div class="clearfix"></div>
            <br />
        <?php else: ?>
            <div class="col-md-2" style="padding-left: 0px;">
                <span><b>Review Step:</b></span>
                <span><?php echo $AppSteps->aws_step; ?></span>
            </div>
            <div class="col-md-3">
                <span><b>Supervisor Name:</b></span><span><?php echo $AppSteps->Inferior; ?></span>
            </div>
            <div class="col-md-3">
                <span><b>Supervisor Code:</b></span>
                <span><?php echo $AppSteps->aws_emp_code; ?></span>
            </div>
            <div class="col-md-3">
                <span><b>Review Status:</b></span>
                <span><?php echo $AppSteps->aws_status; ?></span>
            </div>
            <div class="clearfix"></div>
            <br />
        <?php endif; ?>
        <?php
    }
    $count++;
}
if ($review_trigger_flag != TRUE) {
    echo "<span><i>No review data to display</i></span>";
}
?>

<div class="col-md-6" style="padding-left: 0px;">
    <span><b>Leave Application Details</b></span>
</div>
<hr />
<?php if (count($histories) > 0): ?>
    <?php foreach ($histories as $leave): ?>
        <div class="col-md-3" style="padding-left: 0px;">
            <b>Leave Type:</b> <?php echo $leave->leave_title; ?>
        </div>
        <div class="col-md-3">
            <b>Start Date:</b> <?php echo $leave->mindate; ?>
        </div>
        <div class="col-md-3">
            <b>End Date:</b> <?php echo $leave->maxdate; ?>
        </div>
        <div class="clearfix"></div>
        <br />
    <?php endforeach; ?>
<?php endif; ?>
<br/><br />

<!--If user is here from all list, then no review should be allowed-->
<?php if ($no_review_flag != 1): ?>
    <input type="button" id="approve" value="Approve This Application" class="k-button"> &nbsp;&nbsp;
    <input type="button" id="reject" value="Reject This Application" class="k-button">
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#approve").click(function() {
            var aws_id = "<?php echo $aws_id; ?>";
            $.ajax({
                type: "POST",
                url: "../../controller/leave_management_controllers/leave_applications_approval.php",
                data: {
                    aws_id: aws_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.data.success_flag === "yes") {
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                        $('#grid').data('kendoGrid').dataSource.read();
                        $('#grid').data('kendoGrid').refresh();
                    } else {
                        $("#test_container").html("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                    }
                }
            });
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#reject").click(function() {
            var aws_id = "<?php echo $aws_id; ?>";
            $.ajax({
                type: "POST",
                url: "../../controller/leave_management_controllers/leave_applications_reject.php",
                data: {
                    aws_id: aws_id
                },
                dataType: "json",
                success: function(data) {
                    if (data.data.success_flag === "yes") {
                        $("#test_container").html("<div class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                        $('#grid').data('kendoGrid').dataSource.read();
                        $('#grid').data('kendoGrid').refresh();
                    } else {
                        $("#test_container").html("<div style=\"color:red; background-color: lightyellow;\" class=\"alert alert-success fade in\"><button class=\"close\" type=\"button\" data-dismiss=\"alert\" aria-hidden=\"true\">×</button>" + data.data.error_msg + "</div>");
                    }
                }
            });
        });
    });
</script>
<?php include '../view_layout/footer_view.php'; ?>
