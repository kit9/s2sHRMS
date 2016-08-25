<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();

//Checking if logged inc
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$err = "";
$msg = '';
$admin_email = '';
$admin_username = '';
$password = '';
$re_password = '';
$address = '';
$country = '';
$phone = '';
$user_id ='';
if (isset($_GET["id"])){
    $id = $_GET["id"];
    $users = $con->SelectAllByCondition("admin", "ad_id='$id'");
    foreach ($users as $user) {
        $password = $user->password;
        $admin_email = $user->admin_email;
    }
}
if (isset($_POST['edit_password'])) {
    extract($_POST);
    if (empty($old)) {
        $err = "Admin email is empty.";
    } else if (empty($new)) {
        $err = "Admin full name is empty.";
    } else if ($new != $re_password){
        $err = "Please make sure two new passwords match with each other.";
    } else {
        if ($password == $old){
            $array = array(
                "ad_id" => $id,
                "password" => $new
            );
            if ($con->update("admin", $array) == 1) {
                $msg = "Your password has been reset succefully!";
            } else {
                $err = "Password reset operation failed. Given old password does not match with your account credentials.";
            }
        }else {
            $err = "Password reset failed. Your old password did not match the one stores in the database. ";
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>

<div class="widget widget-tabs widget-tabs-gray widget-tabs-double-2 border-bottom-none">

	<!-- Widget heading -->
	<div class="widget-head">
		<ul>
			<li class="active"><a href="#overview" data-toggle="tab"><i></i><br/>Admin Account Information<br/> Reset Password</a></li>
<!--			<li><a class="glyphicons edit" href="#edit-account" data-toggle="tab"><i></i>Consultant Info</a></li>-->
<!--			<li><a class="glyphicons luggage" href="#projects" data-toggle="tab"><i></i> Consultant Firm Info</a></li>-->
		</ul>
	</div>
	<!-- // Widget heading END -->
	
	<div class="widget-body">
	
            <form class="form-horizontal" method= "post" enctype="multipart/form-data">
			<div class="tab-content">
			
				<div class="tab-pane active widget-body-regular padding-none" id="overview">
				
					<div class="tab-pane widget-body-regular containerBg" id="edit-account">
				
					<div class="widget widget-tabs widget-tabs-vertical row row-merge margin-none widget-body-white">

						<!-- Widget heading -->
						
						<!-- // Widget heading END -->
						
						<div class="widget-body col-md-9">
						
							<div class="tab-content">
							<div class="tab-pane active" id="account-details">
						
							<!-- Row -->
							<div class="row">
							
								<!-- Column -->
								<div class="col-md-6">
								
									<!-- Group -->
									<div class="form-group">
                                                                            
										<label class="col-md-4 control-label">Old Password</label>
										<div class="col-md-8">
                                                                                   <input style="color: #555555;border-color: #799D37" name="old" type="password" id="text_element" class="form-control"/>
                                                                                  
										</div>
									</div>
									
                                                                        <div class="form-group">
                                                                            
										<label class="col-md-4 control-label">New Password</label>
										<div class="col-md-8">
                                                                                   <input style="color: #555555;border-color: #799D37" name="new" type="password" id="text_element" class="form-control"/>
                                                                                  
										</div>
									</div>
                                                                        <div class="form-group">
                                                                            
										<label class="col-md-4 control-label">Confirm Password</label>
										<div class="col-md-8">
                                                                                   <input style="color: #555555;border-color: #799D37" name="re_password" type="password" id="text_element" class="form-control"/>
                                                                                  
										</div>
									</div>
                                                                        

                                                                       
                                                                        
                                                       
                                                        
                                                        <div class="separator top">
								<button type="submit" name="edit_password" class="btn btn-icon btn-primary glyphicons circle_ok"><i></i>Save changes</button>
<!--								<button type="button" class="btn btn-icon btn-default glyphicons circle_remove"><i></i>Cancel</button>-->
							</div>
							
							</div>
							
							
							</div>
						
						</div>
					</div>
					
				</div>
				
				</div>
			
				<!-- Tab content -->
				
				<!-- // Tab content END -->
				
				<!-- Tab content -->
				
				<!-- // Tab content END -->
			</div>
		</form>
	</div>
</div>










<?php include '../view_layout/footer_view.php'; ?>