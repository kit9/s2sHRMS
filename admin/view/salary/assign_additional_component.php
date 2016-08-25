<?php
session_start();
//Importing class library
include ('../../config/class.config.php');

//Configuration classes
$con = new Config();

//Connection string
$open = $con->open();
error_reporting(0);

//Checking if logged inc
if ($con->authenticate() == 1) {
	$con->redirect("../../login.php");
}


//Logging out user
if (isset($_POST['btnLogout'])) {
	if ($con->logout() == 1) {
		$con->redirect("../../login.php");
	}
}



//getting salary headers from database
$arrHeaders = array();
$sqlGetHeader = "SELECT * FROM payroll_salary_header WHERE PSH_show_in_tmp_mod != 'yes'";
$resultGetHeader = mysqli_query($con->open(), $sqlGetHeader);
if ($resultGetHeader) {
	while ($resultGetHeaderObj = mysqli_fetch_object($resultGetHeader)) {
		$arrHeaders[] = $resultGetHeaderObj;
	}
} else {
	echo "resultGetHeader query failed.";
}

?>

<?php include '../view_layout/header_view.php'; ?>

<!-- Widget -->
<div class="widget" style="background-color: white;">
	<div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Add Employee Information</h6></div>
	<div class="widget-body" background-color: white;>
		<?php include("../../layout/msg.php"); ?>
        <form method="POST">
		<div id="example" class="k-content">
			<div id="rpac_bd" >
				<?php if (count($arrHeaders) > 0): ?>
					<?php foreach ($arrHeaders AS $Header): ?>
						<div class="col-md-6 pull-left">
							<br/><br/>
							<label for="<?php echo $Header->PSH_header_title; ?>"><?php echo $Header->PSH_header_title; ?>:</label> 
							<?php if ($Header->PSH_is_optional == "yes"): ?>
								<input type="checkbox" value="yes" name="headerchk_<?php echo $Header->PSH_id; ?>" id="is_applicabl_<?php echo $Header->PSH_id; ?>" />&nbsp;&nbsp;Is Applicable?
								<br><input type="text" value="" id="emp_basic_salary_<?php echo $Header->PSH_id; ?>" placeholder="" class="k-textbox" name="headeropt_<?php echo $Header->PSH_id; ?>" type="text" style="width: 80%;"  disabled="disabled" />
							<?php else: ?>
								<br><input type="text" value="" id="emp_basic_salary_<?php echo $Header->PSH_id; ?>" placeholder="" class="k-textbox" name="headerfix_<?php echo $Header->PSH_id; ?>" type="text" style="width: 80%;" />
							<?php endif; ?>
							<br />
							<script type="text/javascript">
								$("#emp_basic_salary_6").removeAttr("disabled", true);
								$("#emp_basic_salary_7").removeAttr("disabled", true);
								$("#is_applicabl_<?php echo $Header->PSH_id; ?>").click(function () {
									if ($("#is_applicabl_<?php echo $Header->PSH_id; ?>").is(':checked')) {
										$("#emp_basic_salary_<?php echo $Header->PSH_id; ?>").removeAttr("disabled", true);
									} else {
										$("#emp_basic_salary_<?php echo $Header->PSH_id; ?>").attr("disabled", true);
										$("#is_applicabl_<?php echo $Header->PSH_id; ?>").removeAttr("checked");
									}
								});
							</script>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
				<div class="clearfix"></div>
				<input type="submit" class="k-button" value="Assign Salary Component">
                
			</div>
		</div>
	</div>
	</form>
</div>
<?php include '../view_layout/footer_view.php'; ?>