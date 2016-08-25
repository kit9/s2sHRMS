<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();
//Checking if logged in
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
$admin_name = '';
$admin_email = '';
$password = '';
$admin_username = '';
$admin_type = '';
$role_type = '';
$user_id= '';
$admin_updated = '';
$address = '';
$country= '';
$admin_image= '';
$phone = '';
$country_id ='';
$admins = $con->SelectAll("admin");
$countries = $con->SelectAll("tbl_country");
if (isset($_POST['add_con'])) {
    extract($_POST);
   
    $targetfolder = '../../uploads/admin/';
    $filename = basename($_FILES['admin_image']['name']);
    $targetfolder = $targetfolder . $filename;
//    $uploadPath = substr($targetfolder, 6);
    
   $i_name = $_FILES["admin_image"]["name"];
    
   if (empty($admin_name)) {
        $err = "Admin name is not selected";
    } else if (empty($admin_email)) {
        $err = "Admin email is empty";
    } else if (empty($password )) {
        $err = "Password field is empty";
    } else if (empty($admin_username)) {
        $err = "Admin user field is empty";
    } else if (empty($address)) {
        $err = "Address field is empty";
        } else if (empty($country_id)) {
        $err = "Country field is empty";
        } else if (empty($phone)) {
        $err = "Phone field is empty";
     } else {
   if ($con->exists("admin", array("admin_email" => $admin_email)) == 1) {
            $err = "Email address already exists.";
            } else {
     $array = array("admin_name"=>$admin_name,
         "admin_email"=>$admin_email, 
         "password"=>$password,
         "admin_username"=>$admin_username,
         "address"=>$address,
         "user_id"=>$user_id,
         "country_id"=>$country_id,
         "admin_image"=>$i_name,
         "phone"=>$phone);
      $open= $con->open();
     
     if ($con->insert("admin",$array ) == 1) {
         if ($filename != "") {

                move_uploaded_file($_FILES['admin_image']['tmp_name'], $targetfolder);
            }
//           $document_upload_query = "INSERT INTO admin SET ";
////            $document_upload_query .= " c_id='".  mysqli_real_escape_string($open,$c_id)."',";
//            $document_upload_query .= " user_id='".  mysqli_real_escape_string($open,$user_id)."',";
//            $document_upload_query .= " admin_email='".  mysqli_real_escape_string($open,$admin_email)."',";
//            $document_upload_query .= " password='".  mysqli_real_escape_string($open,$password)."',";
//            $document_upload_query .= " admin_username='".  mysqli_real_escape_string($open,$admin_username)."',";
//            $document_upload_query .= " address='".  mysqli_real_escape_string($open,$address)."',";
//            $document_upload_query .= " country_id='".  mysqli_real_escape_string($open,$country_id)."',";
//            $document_upload_query .= "admin_image='".  mysqli_real_escape_string($open,$i_name)."',";
//            $document_upload_query .= " phone='".  mysqli_real_escape_string($open,$phone)."'";
//             $result = $con->QueryResultForNormalEntry($document_upload_query, $open);
            
            $msg = "Admin saved successfully";
            } else {
                $err = "Invalid Query";
            }
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
			<li class="active"><a class="glyphicons display" href="#overview" data-toggle="tab"><i></i>Admin Info</a></li>
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
										<label class="col-md-4 control-label">Admin name</label>
										<div class="col-md-8">
                                                                                   <input type="hidden" value="1" name="user_id"/>
                                                                                    <input style="color: #555555; border-color: #799D37" type="text" value="<?php echo $admin_name; ?>" name="admin_name" class="form-control" />
										</div>
									</div>
									<!-- // Group END -->
									
									
<!--                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Phone</label>
										<div class="col-md-8">
											<input style="color: #555555; border-color: #799D37" type="text" value="<?php// echo $phone; ?>" name="phone" class="form-control" />
										</div>
									</div>-->
<!--                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Email</label>
										<div class="col-md-8">
                                                                                    <textarea style="border-color: #799D37;" id="mustHaveId" name="admin_email" class="wysihtml5 form-control" rows="3"><?php //echo $admin_email; ?></textarea>
                                                                                    
										</div>
									</div>-->
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">User Name</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color: #799D37" type="text" value="<?php echo $admin_username; ?>" name="admin_username" class="form-control" />
										</div>
									</div>

                                                                           <div class="form-group">
										<label class="col-md-4 control-label">Password</label>
										<div class="col-md-8">
											<input style="color: #555555; border-color: #799D37" type="password" value="" name="password" class="form-control" />
										</div>
									</div>

<!--                                                                         <div class="form-group">
										<label class="col-md-4 control-label">Country</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color: #799D37" type="text" value="<?php echo $country; ?>" name="country" class="form-control" />
										</div>
									</div>-->

                                                                        <!-- // Group END -->
									
									<!-- Group -->
<!--									<div class="form-group">
										<label class="col-md-4 control-label">Date of birth</label>
										<div class="col-md-8">
											<div class="input-group">
												<input type="text" id="datepicker1" class="form-control" value="13/06/1988" />
												<span class="input-group-addon"><i class="icon-calendar"></i></span>
											</div>
										</div>
									</div>-->
									<!-- // Group END -->
                                                                        
                                                                         <div class="form-group">
										<label class="col-md-4 control-label">Country</label>
										<div class="col-md-8">
											<select style=" border-color: #799D37"  class="form-control" name="country_id">
                                            <option value="0" > Country Name</option>
                                            <?php if (count($countries) >= 1): ?>
                                                <?php foreach ($countries as $a): ?>
                                                    <option value="<?php echo $a->country_id; ?>" 
                                                    <?php
                                                    if ($a->country_id == $country_id) {
                                                        echo "selected='selected'";
                                                    }
                                                    ?>  
                                                            ><?php echo $a->country_name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select> 
										</div>
									</div>
<!--									<div class="form-group">
                                         <label for="exampleInputPassword1">User Type</label>
                                         
                                         <select class="selectpicker col-md-12" type="text" name="country" value="country" >
                                             <option value="0">Select user</option>
                                                <option value="1" >Admin</option>
						<option value="2" >Consultant</option>
                                                <option value="3">Student</option>
                                                <option value="4">University</option>
					</select>
                                        </div>-->
								</div>
								<!-- // Column END -->
								
								<!-- Column -->
								<div class="col-md-6">
								
									<!-- Group -->
<!--									<div class="form-group">
										<label class="col-md-2 control-label">Gender</label>
										<div class="col-md-10">
											<select class="form-control">
												<option>Male</option>
												<option>Female</option>
											</select>
										</div>
									</div>-->
									<!-- // Group END -->
									
									<!-- Group -->
<!--									<div class="form-group">
										<label class="col-md-2 control-label">Age</label>
										<div class="col-md-10">
											<input type="text" value="25" class="form-control" />
										</div>
									</div>-->
                                                                        
                                                                       <div class="form-group">
										<label class="col-md-4 control-label">Phone</label>
										<div class="col-md-8">
											<input style="color: #555555; border-color: #799D37" type="text" value="<?php echo $phone; ?>" name="phone" class="form-control" />
										</div>
									</div>
                                                                            <div class="form-group">
										<label class="col-md-4 control-label">Email</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color: #799D37;" type="text" value="<?php echo $admin_email; ?>" name="admin_email" class="form-control" />
										</div>
									</div>
<!--                                                                       <div class="form-group">
										<label class="col-md-4 control-label">Permanent Address</label>
										<div class="col-md-8">
                                                                                    <textarea id="mustHaveId" name="c_per_addr" value="<?php //echo $c_per_addr; ?>" class="wysihtml5 form-control" rows="3"></textarea>
										
                                                                                    <textarea style="border-color: #799D37;" type="text" id="mustHaveId" name="c_per_addr" class="wysihtml5 form-control" rows="3" ><?php //echo $c_per_addr; ?></textarea>
                                                                                
                                                                                </div>
									</div>-->

                                                                         <label class="col-md-4 control-label">Image Upload</label>
                                                                             <div class="col-xs-4">

                                                                        <div  class="fileupload fileupload-new margin-none" data-provides="fileupload">
                                                                   <span style="border-color: #799D37;" class="btn btn-default btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span><input type="file" name="admin_image" class="margin-none" /></span>
                                                                   <span class="fileupload-preview"></span>

                                                                  </div>
                                                               </div>

    
									<!-- // Group END -->
									
								</div>
								<!-- // Column END -->
								
							</div>
							<!-- // Row END -->
							
							<div class="separator line bottom"></div>
							
							<!-- Group -->
							<div class="control-group">
								<label class="control-label">Address</label>
								<div class="controls">
									<textarea style="border-color: #799D37; margin-left: -28px;" id="mustHaveId" name="address" class="wysihtml5 form-control" rows="5"><?php echo $address; ?></textarea>
								</div>
							</div>
                                                        
                                                        <div class="separator top">
								<button type="submit" name="add_con" class="btn btn-icon btn-primary glyphicons circle_ok"><i></i>Save changes</button>
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
				<div class="tab-pane widget-body-regular" id="projects">
				
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
										<label class="col-md-4 control-label">Company Name</label>
										<div class="col-md-8">
                                                                                    <input style="color: #555555; border-color:#799D37;" type="text" value="<?php echo $compamy_name; ?>" name="compamy_name" class="form-control" />
										</div>
									</div>
									<!-- // Group END -->
									
									
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Phone</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color:#799D37;" type="text" value="<?php echo $company_phone; ?>" name="company_phone" class="form-control" />
										</div>
									</div>
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Company Address</label>
										<div class="col-md-8">
                                                                                    <textarea style="border-color: #799D37;" id="mustHaveId" name="company_address" class="wysihtml5 form-control" rows="3"><?php echo $company_address; ?></textarea>
										</div>
									</div>
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Email</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color:#799D37;" type="text" value="<?php echo $company_email; ?>" name="company_email" class="form-control" />
										</div>
									</div>
                                                                           <label class="col-md-4 control-label"> Company Image</label>
                                                                             <div class="col-xs-4">

                                                                        <div  class="fileupload fileupload-new margin-none" data-provides="fileupload">
                                                                   <span style="border-color: #799D37;" class="btn btn-default btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span><input type="file" name="company_image" class="margin-none" /></span>
                                                                   <span class="fileupload-preview"></span>

                                                                  </div>
                                                               </div>
                                                                         
                                                                 </div>
								<!-- // Column END -->
								
								<!-- Column -->
								<div class="col-md-6">
								
									
                                                                        
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Fax</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color:#799D37;" type="text" value="<?php echo $company_fax; ?>" name="company_fax" class="form-control" />
										</div>
									</div>
                                                                            <div class="form-group">
										<label class="col-md-4 control-label"> Alternative Phone</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color:#799D37;" type="text" value="<?php echo $company_alt_ph; ?>" name="company_alt_ph" class="form-control" />
										</div>
									</div>
                                                                       <div class="form-group">
										<label class="col-md-4 control-label">Company Web-Address</label>
										<div class="col-md-8">
                                                                                    <textarea style="border-color: #799D37;" id="mustHaveId" name="company_web" class="wysihtml5 form-control" rows="3"><?php echo $company_web; ?></textarea>
										</div>
									</div>

                                                                           <div class="form-group">
										<label class="col-md-4 control-label"> Alternative Email</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color:#799D37;" type="text" value="<?php echo $company_alt_email; ?>" name="company_alt_email" class="form-control" />
										</div>
									</div>
                                     
                                                                            
									<!-- // Group END -->
									
								</div>
								<!-- // Column END -->
								
							</div>
							<!-- // Row END -->
							
							<div class="separator line bottom"></div>
							
							<!-- Group -->
							<div class="control-group">
								<label class="control-label">About Company</label>
								<div class="controls">
									<textarea style="border-color: #799D37; margin-left: -28px;" id="mustHaveId" name="company_about" class="wysihtml5 form-control" rows="5"><?php echo $company_about; ?></textarea>
								</div>
							</div>
                                                        
                                                        
							<!-- // Group END -->
							
							<!-- Form actions -->
<!--							<div class="separator top">
								<button type="submit" class="btn btn-icon btn-primary glyphicons circle_ok"><i></i>Save changes</button>
								<button type="button" class="btn btn-icon btn-default glyphicons circle_remove"><i></i>Cancel</button>
							</div>-->
							<!-- // Form actions END -->
							
							</div>
							
							
							</div>
						
						</div>
					</div>
					
					
					
				</div>
				<!-- // Tab content END -->
			</div>
		</form>
	</div>
</div>










<?php include '../view_layout/footer_view.php'; ?>