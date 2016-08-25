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
$c_f_name = '';
$c_l_name = '';
$c_phone = '';
$c_pres_addr = '';
$c_per_addr = '';
$c_email = '';
$compamy_name = '';
$company_address = '';
$company_phone = '';
$company_web = '';
$company_fax = '';
$company_image = '';
$consultant_image = '';
$c_user_name ='';
$consultant_about ='';
$company_about ='';
$company_email ='';
$company_alt_email ='';
$company_alt_ph='';
$c_password='';
$role_type='';
$user_id='';
$consultants = $con->SelectAll("tbl_consultant");

if (isset($_POST['add_con'])) {
    extract($_POST);
   
    $targetfolder = '../../uploads/consultant/company_image/';
    $filename = basename($_FILES['company_image']['name']);
    $targetfolder = $targetfolder . $filename;
//    $uploadPath = substr($targetfolder, 6);
    
   $i_name = $_FILES["company_image"]["name"];
   
   $targetfolders = '../../uploads/consultant/consultant_image/';
    $filenames = basename($_FILES['consultant_image']['name']);
    $targetfolders = $targetfolders . $filenames;
//    $uploadPath = substr($targetfolder, 6);
    
   $i_name_con = $_FILES["consultant_image"]["name"];
   
   
   if (empty($c_f_name)) {
        $err = "First name is not selected";
    } else if (empty($c_l_name)) {
        $err = "Last name is empty";
    } else if (empty($c_phone )) {
        $err = "Phone field is empty";
    } else if (empty($c_pres_addr)) {
        $err = "Present Address field is empty";
    } else if (empty($c_per_addr)) {
        $err = "Permanent Address is empty";
        } else if (empty($c_email)) {
        $err = "Email Address is empty";
        } else if (empty($compamy_name)) {
        $err = "Company Name is empty";
        } else if (empty($company_address)) {
        $err = "Company Address is empty";
        } else if (empty($company_phone)) {
        $err = "Company phone is empty";
        } else if (empty($company_web)) {
        $err = "Company website is empty";
         } else if (empty($c_user_name)) {
        $err = "Consultant user name is empty";
         } else if (empty($company_email)) {
        $err = "Consultant email is empty";
         } else if (empty($company_alt_ph)) {
        $err = "Company alternative phone is empty";
         } else if (empty($company_alt_email)) {
        $err = "Company alternative email is empty";
         } else if (empty($consultant_about)) {
        $err = "About consultant is empty";
        } else if (empty($c_password)) {
        $err = "Password field is empty";
        } else if (empty($company_about)) {
        $err = "About Company is empty";
        } else if (empty($company_fax)) {
        $err = "Company Fax is empty";
     } else {
   if ($con->exists("tbl_consultant", array("c_email" => $c_email)) == 1) {
            $err = "Email address already exists.";
            } else {
     $array = array("c_f_name"=>$c_f_name,
         "c_l_name"=>$c_l_name, 
         "c_phone"=>$c_phone,
         "c_pres_addr"=>$c_pres_addr,
         "c_per_addr"=>$c_per_addr,
         "c_email"=>$c_email,
         "compamy_name"=>$compamy_name,
         "company_address"=>$company_address,
         "company_phone"=>$company_phone,
         "company_web"=>$company_web,
         "company_fax"=>$company_fax,
         "company_image"=>$i_name, 
         "consultant_image"=>$i_name_con, 
         "c_user_name" =>$c_user_name,
         "company_email" =>$company_email,
         "company_alt_ph" =>$company_alt_ph,
         "company_alt_email" =>$company_alt_email,
         "consultant_about" =>$consultant_about,
         "role_type"=>$role_type,
         "company_about" =>$company_about);
      $open= $con->open();
     
     if ($con->insert("tbl_consultant",$array ) == 1) {
         if ($filename != "") {

                move_uploaded_file($_FILES['company_image']['tmp_name'], $targetfolder);
            }
             if ($filenames != "") {

                move_uploaded_file($_FILES['consultant_image']['tmp_name'], $targetfolders);
            }
            $document_upload_query = "INSERT INTO admin_module SET ";
//            $document_upload_query .= " c_id='".  mysqli_real_escape_string($open,$c_id)."',";
            $document_upload_query .= " user_id='".  mysqli_real_escape_string($open,$user_id)."',";
            $document_upload_query .= " c_email='".  mysqli_real_escape_string($open,$c_email)."',";
            $document_upload_query .= " c_password='".  mysqli_real_escape_string($open,$c_password)."'";
             $result = $con->QueryResultForNormalEntry($document_upload_query, $open);
            
            if($result){
                     $msg = "Consultant saved successfully";
            }
 else {
      $msg = "error saving consultant data ";
 }
       
            } else {
                $err = "Invalid Query";
            }
            
           $con->close($open); 
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
			<li class="active"><a class="glyphicons display" href="#overview" data-toggle="tab"><i></i>Consultant Info</a></li>
<!--			<li><a class="glyphicons edit" href="#edit-account" data-toggle="tab"><i></i>Consultant Info</a></li>-->
			<li><a class="glyphicons luggage" href="#projects" data-toggle="tab"><i></i> Consultant Firm Info</a></li>
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
										<label class="col-md-4 control-label">First name</label>
										<div class="col-md-8">
                                                                                   <input type="hidden" value="2" name="user_id"/>
                                                                                    <input style="color: #555555; border-color: #799D37" type="text" value="<?php echo $c_f_name; ?>" name="c_f_name" class="form-control" />
										</div>
									</div>
									<!-- // Group END -->
									
									
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Phone</label>
										<div class="col-md-8">
											<input style="color: #555555; border-color: #799D37" type="text" value="<?php echo $c_phone; ?>" name="c_phone" class="form-control" />
										</div>
									</div>
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">Present Address</label>
										<div class="col-md-8">
                                                                                    <textarea style="border-color: #799D37;" id="mustHaveId" name="c_pres_addr" class="wysihtml5 form-control" rows="3"><?php echo $c_pres_addr; ?></textarea>
                                                                                    
										</div>
									</div>
                                                                        <div class="form-group">
										<label class="col-md-4 control-label">User Name</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color: #799D37" type="text" value="<?php echo $c_user_name; ?>" name="c_user_name" class="form-control" />
										</div>
									</div>
                                                                        <label class="col-md-4 control-label">Image Upload</label>
                                                                             <div class="col-xs-4">

                                                                        <div  class="fileupload fileupload-new margin-none" data-provides="fileupload">
                                                                   <span style="border-color: #799D37;" class="btn btn-default btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span><input type="file" name="consultant_image" class="margin-none" /></span>
                                                                   <span class="fileupload-preview"></span>

                                                                  </div>
                                                               </div>
                                                                         
                                        
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
										<label class="col-md-4 control-label">Last name</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color: #799D37" type="text" value="<?php echo $c_l_name; ?>" name="c_l_name" class="form-control" />
										</div>
									</div>
                                                                            <div class="form-group">
										<label class="col-md-4 control-label">Email</label>
										<div class="col-md-8">
											<input style="color: #555555;border-color: #799D37;" type="text" value="<?php echo $c_email; ?>" name="c_email" class="form-control" />
										</div>
									</div>
                                                                       <div class="form-group">
										<label class="col-md-4 control-label">Permanent Address</label>
										<div class="col-md-8">
<!--                                                                                    <textarea id="mustHaveId" name="c_per_addr" value="<?php //echo $c_per_addr; ?>" class="wysihtml5 form-control" rows="3"></textarea>-->
										
                                                                                    <textarea style="border-color: #799D37;" type="text" id="mustHaveId" name="c_per_addr" class="wysihtml5 form-control" rows="3" ><?php echo $c_per_addr; ?></textarea>
                                                                                
                                                                                </div>
									</div>

                                                                         <div class="form-group">
										<label class="col-md-4 control-label">Password</label>
										<div class="col-md-8">
											<input style="color: #555555; border-color: #799D37" type="password" value="" name="c_password" class="form-control" />
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
								<label class="control-label">About Consultant</label>
								<div class="controls">
									<textarea style="border-color: #799D37; margin-left: -28px;" id="mustHaveId" name="consultant_about" class="wysihtml5 form-control" rows="5"><?php echo $consultant_about; ?></textarea>
								</div>
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
                                                        
                                                        <div class="separator top">
								<button type="submit" name="add_con" class="btn btn-icon btn-primary glyphicons circle_ok"><i></i>Save changes</button>
<!--								<button type="button" class="btn btn-icon btn-default glyphicons circle_remove"><i></i>Cancel</button>-->
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