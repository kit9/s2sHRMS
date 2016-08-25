<?php
session_start();
error_reporting(1);
//Importing class library
include ('../../config/class.config.php');

//Configuration classes
$con = new Config();

//Connection string
$open = $con->open();


//Check if user has permission on this page
//include '../../config/request_controller.php';
//Log out user upon request
include '../../config/logout_controller.php';

/**
 * Check if PHP blowfish is enabled
 */

// if(defined("CRYPT_BLOWFISH") && CRYPT_BLOWFISH) { 
// 	echo "CRYPT_BLOWFISH is enabled!"; 
// } else { 
// 	echo "CRYPT_BLOWFISH is NOT enabled!"; 
// }

if (isset($_POST["save_info"])) {
	extract($_POST);

	if ($access_all_concerns == 'on') {
		$access_all_concerns = 'yes';
	} else {
		$access_all_concerns = '';
	}


	if ($emp_code == ''){
		$err = "Please select an employee.";
	} else if ($user_type_value == ''){
		$err = "Please select a user type.";
	} else if ($password == ''){
		$err = "Password can not be empty.";
	} else if ($password != $retype_password){
		$err = "Password didn't match.";
	} else {
		$emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
		if (count($emp_info) > 0){
			$emp_id = $emp_info{0}->emp_id;
		}

    /**
     * When the user tries to login you simply check whether the hash of
     * the password they enter, using the database hash as a salt, re-creates
     * (matches) the database hash:
     */
    $password_hash = crypt($password);
    $update_array = array(
    	"emp_id" => $emp_id,
    	"is_super" => $access_all_concerns,
    	"user_type" => $user_type_value,
    	"password" => $password_hash
    );
    $output = $con->update("tmp_employee", $update_array);
    if ($output == 1){
    	$msg = "User information is updated succesfully.";
    } else {
    	$err = "Something went wrong!";
    }
}


}
?>
<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
	<div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Manage User Information</h6></div>
	<div class="widget-body" style="background-color: white;">
		<form method="POST" id="">
			<?php include("../../layout/msg.php"); ?>
			<div class="col-md-6">
				<label for="emp_code">Select Employee Code : </label><br/>
				<input type="text"  name="emp_code" id="emp_code_hr" value="<?php echo $emp_code; ?>" style="width: 80%;">
			</div>
			<div class="clearfix"></div>
			<br />
			<div id="emp_info_container" style="font-size: 12px;"></div>
			<hr />
			
			<div id="user_info" <?php if (isset($_POST["save_info"])):?> style="display:block;" <?php else: ?> style="display:none;" <?php endif; ?>>
				<div class="col-md-6">
					<label for="Full name">Assign a type</label>
					<!--Assign access for all concerns-->
					&nbsp;&nbsp; <input type="checkbox" name="access_all_concerns" <?php
					if ($is_super == 'yes') {
						echo "checked";
					}
					?> id='is_super'> &nbsp;<b> Access to All Concerns</b>
					<br />
					<input id="user_type_combo" name="user_type_value" style="width:80%">
				</div>
				<div class="col-md-6">
					<label for="emp_code">Password: </label><br/>
					<input type="password" class="k-textbox" name="password" id="password" style="width: 80%;">
				</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-md-6">
					<label for="emp_code">Retype Password: </label><br/>
					<input type="password" class="k-textbox" name="retype_password" id="retype_password"  style="width: 80%;">
				</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-md-6">
					<input type="submit" class="k-button" value="Update User Information" name="save_info">
				</div>
				<br />
			</div>

		</form>
		<br />


	</div>
</div>
<?php include '../view_layout/footer_view.php'; ?>
<script type="text/javascript">
	$(document).ready(function () {
		$("#emp_code_hr").kendoComboBox({
			placeholder: "Select Employee...",
			dataTextField: "emp_name",
			dataValueField: "emp_code",
			dataSource: {
				transport: {
					read: {
						url: "../../controller/leave_management_controllers/employee_list.php",
						type: "GET"
					}
				},
				schema: {
					data: "data"
				}
			}
		}).data("kendoComboBox");

            //User type combo box
            function user_type (){
            	$("#user_type_combo").kendoComboBox({
            		placeholder: "Select User Type...",
            		dataTextField: "user_type_title",
            		dataValueField: "user_type_title",
            		dataSource: {
            			transport: {
            				read: {
            					url: "../../controller/leave_management_controllers/user_type_list.php",
            					type: "GET"
            				}
            			},
            			schema: {
            				data: "data"
            			}
            		}
            	}).data("kendoComboBox");	
            }


            $("#emp_code_hr").change(function () {
            //Collect variables
            var emp_code = $("#emp_code_hr").val();
            $('#user_info').css("display", "block");
            //Ajax call to fetch remaining days
            $.ajax({
            	url: "../../controller/leave_management_controllers/hr_leave_management/emp_for_leave_controller.php?emp_code=" + emp_code + "",
            	type: "GET",
            	dataType: "JSON",
            	success: function (data) {
            		var objects = data.data;
            		var html = '';
            		$.each(objects, function () {
            			html += '<div class="col-md-3"><b>Company Name:</b></div><div class="col-md-3">' + this.company_title + ' ';
            			html += '</div><br /><div class="clearfix"></div>';
            			html += '<div class="col-md-3"><b>Full Name:</b></div><div class="col-md-3">' + this.emp_firstname + ' ';
            			html += '</div><br /><div class="clearfix"></div>';
            			html += '<div class="col-md-3"><b>Department:</b></div><div class="col-md-3">' + this.department_title + ' ';
            			html += '</div><br /><div class="clearfix"></div>';
            			html += '<div class="col-md-3"><b>Designation:</b></div><div class="col-md-3">' + this.designation_title + ' ';
            			html += '</div><br /><div class="clearfix"></div><br />';
                        
            			html += '<div class="col-md-6" style="background-color:lightyellow; border: 1px solid grey; border-radius:5px">';

            			html += '<span style="text-align:justify;">For an existing user, user type and access type of concern will be auto selected. For secutiry reasons, password will be empty. If you want to update password too, simply type new password and update information.</span>';
            			html += '</div>';
            			html += '</div><br /><div class="clearfix"></div><br />';


	            		//Set existing information
	            		if (this.is_super == 'yes'){
	            			$('#is_super').attr('checked', true);
	            		} else {
	            			$('#is_super').attr('checked', false);
	            		}
	            		if (this.user_type != null){
	            			$('#user_type_combo').val(this.user_type);
	            			user_type();
	            		} else {
	            			$("#user_type_combo").val('');
	            			user_type();
	            		}
	            	});
            		$("#emp_info_container").html(html);
            	}
            });
        });
    });
</script>

