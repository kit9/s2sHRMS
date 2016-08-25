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

if (isset($_POST["edit_password"])) {
	extract($_POST);
	if ($new != $re_password){
		$err = "Password didn't match.";
	} else {
		$emp_code = $_SESSION['emp_code'];
		$emp_info = $con->SelectAllByCondition("tmp_employee", "emp_code='$emp_code'");
		if (count($emp_info) > 0){
			$password_hash = $emp_info{0}->password;
			$emp_id = $emp_info{0}->emp_id;
		}
		if(crypt($old, $password_hash) == $password_hash) {
			$new_hash = crypt($new);
			$update_array =  array(
				'emp_id' => $emp_id,
				'password' => $new_hash
				);
			if ($con->update("tmp_employee", $update_array) == 1){
				$msg='Password has been successfully reset.';
			}
		} else {
			$err = "Old password did not match with the one stored in database.";
		}
	}	
}

?>
<?php include '../view_layout/header_view.php'; ?>
<div class="widget" style="background-color: white;">
	<div class="widget-head"><h6 class="heading" style="color:whitesmoke;">Reset Password</h6></div>
	<div class="widget-body" style="background-color: white;">
	    <form method="POST">
	    <?php include("../../layout/msg.php"); ?>
		<div class="col-md-6">
		<label class="control-label">Old Password</label><br />
		    <input style="width:100%;" name="old" type="password" class="k-textbox" />
			</div>
			<div class="col-md-6">
			<label class="control-label">New Password</label><br />
				<input style="width:100%;" name="new" type="password" class="k-textbox" />
			</div>
			<div class="clearfix"></div>
			<br /> 
			<div class="col-md-6">
					<label>Confirm Password</label><br />
				<input class="k-textbox" style="width:100%;" name="re_password" type="password" />
			</div>
			<div class="clearfix"></div>
			<br />
            <div class="col-md-6">
		       <input type="submit" name="edit_password" class="k-button" value="Save changes">
		    </div>
		    <div class="clearfix"></div>

		</div>
		</form>
</div>
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




