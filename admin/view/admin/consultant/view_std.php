<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Checking if logged inc
if ($con->authenticate1() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
$std_id='';
$std_fname = '';
$std_lname = '';
$std_dob = '';
$std_pres_addr = '';
$std_per_addr = '';
$std_email = '';
$std_mob = '';
$std_nationality = '';
$std_gender = '';
$std_father = '';
$std_mother = '';
$std_bloodg = '';
$std_religion = '';
$std_language = '';
$std_username = '';
$std_password = '';
$std_photo = '';
$std_image = '';
$father_ocu = '';
$country_name = '';
$mother_ocu = '';
$extra_curiculam = '';
//$std_edu_info_id = '';

$open = $con->open();
//$students_info = array();
//$querystd = "SELECT s.std_fname, s.std_lname, s.city_name, s.country_name FROM tbl_student s,tbl_country c.tbl_city ct where s.country_id=c.country_id AND s.city_id=cn.city_id";
//$resultstd = mysqli_query($open, $querystd);
//while ($rows_std = mysqli_fetch_object($resultstd)) {
//    $students_info[] = $rows_std ;
//}


$country_id = '';
$city_id = '';
$countries = $con->SelectAll("tbl_country");
$cities = $con->SelectAll("tbl_city");
if (isset($_GET['id'])) {
$id = $_GET['id'];
$query = "SELECT s.*,c.country_name,cn.city_name FROM tbl_student s,tbl_country c,tbl_city cn WHERE s.country_id = c.country_id AND s.city_id = cn.city_id AND s.std_id=$id  ";
    $result = mysqli_query($open,$query);
    while ($rows = mysqli_fetch_object($result)) {
    $conct[] = $rows;
}
// $con->debug($conct);
//    exit();
//    $object_array = array("std_id" => $id);
//    $tbl_students = $con->SelectAllByID("tbl_student", $object_array);
//    $countriess = array();
//    $country_id = 
//    $getcountry = " std_id='$std_id'";
//    $cnresult = $con->SelectAllByCondition("tbl_student", $getcountry);
//    if (count($cnresult) >= 1) {
//        foreach ($cnresult as $n) {
//            $country_name = $n->country_name;
//        }
//    }
   } 
    
//   foreach ($tbl_students as $n) {
//        $std_id = $n->std_id;
//        $std_fname=$n->std_fname;
//        $std_lname=$n->std_lname;
//        $std_dob=$n->std_dob;
//        $std_pres_addr=$n->std_pres_addr;
//        $std_per_addr=$n->std_per_addr;
//        $std_mob=$n->std_mob;  
//        $std_email=$n->std_email;
//        $std_nationality=$n->std_nationality;  
//        $std_gender=$n->std_gender;
//        $std_father=$n->std_father;  
//        $std_mother=$n->std_mother;
//        $std_bloodg=$n->std_bloodg;  
//        $std_religion=$n->std_religion;
//        $std_language=$n->std_language;  
//        $country_id=$n->country_id;
//        $city_id=$n->city_id;  
//        $std_username=$n->std_username;
//        $std_password=$n->std_password;  
//        $std_photo=$n->std_photo;  
//        
//        $father_ocu=$n->father_ocu;  
//        $mother_ocu=$n->mother_ocu;  
//        $extra_curiculam=$n->extra_curiculam;  
//     }

//Formatting the date
     $fdate = date_create($std_dob);
    $date = date_format($fdate, 'Y-m-d');
//    $con->debug($std_dob);
    

//$date = new DateTime('2000-01-01');
//echo $date->format('Y-m-d H:i:s');
    
if (isset($_POST['edit_std'])) {
    extract($_POST);
    //$Fcount = count($_FILES);
   
    

//$targetfolder = '../../uploads/student/image/';
//    $filename = basename($_FILES['std_image']['name']);
//    $targetfolder = $targetfolder . $filename;
////    $uploadPath = substr($targetfolder, 6);
//    
//   $i_name = $_FILES["std_image"]["name"];
    
    
//    if (empty($std_fname)) {
//        $err = "First name is not selected";
//    } else if (empty($std_lname)) {
//        $err = "Last name is empty";
//    } else if (empty($std_dob)) {
//        $err = "Date of birth field is empty";
//    } else if (empty($std_pres_addr)) {
//        $err = "Present Address field is empty";
//    } else if (empty($std_per_addr)) {
//        $err = "Permanent Address is empty";
//    } else if (empty($std_email)) {
//        $err = "Email Address is empty";
//    } else if (empty($std_mob)) {
//        $err = "Phone number is empty";
//    } else if (empty($std_nationality)) {
//        $err = "Student Nationality field is empty";
//    } else if (empty($std_gender)) {
//        $err = "Gender field is empty";
//    } else if (empty($std_father)) {
//        $err = "Father name field is empty";
//    } else if (empty($std_mother)) {
//        $err = "Mother name field is empty";
//    } else if (empty($std_bloodg)) {
//        $err = "Blood group field is empty";
//    } else if (empty($std_religion)) {
//        $err = "Religion field is empty";
//    } else if (empty($std_language)) {
//        $err = "Religion field is empty";
//    } else if (empty($city_id)) {
//        $err = "City field is empty";
//    } else if (empty($country_id)) {
//        $err = "Country field is empty";
//    } else if (empty($std_username)) {
//        
//     $err = "Username field is empty";
//    } else if (empty($std_password)) {
//        $err = "Password field is empty";
//    } else if (empty($father_ocu)) {
//        $err = "Father occupation field is empty";
//    } else if (empty($mother_ocu)) {
//        $err = "Mother occupation field is empty";
//    } else if (empty($extra_curiculam)) {
//        $err = "Extra Curriculam field is empty";
//    }  else {
//
//        $update_array = array(
//            "std_id"=>$std_id,
//            "std_fname"=>$std_fname, 
//            "std_lname"=>$std_lname, 
//            "std_dob"=>$date,
//            "std_pres_addr"=>$std_pres_addr,
//            "std_per_addr"=>$std_per_addr,
//            "std_email"=>$std_email,
//            "std_mob"=>$std_mob,
//            "std_nationality"=>$std_nationality,
//            "std_gender"=>$std_gender,
//            "std_father"=>$std_father,
//            "std_mother"=>$std_mother,
//            "std_bloodg"=>$std_bloodg,
//            "std_religion"=>$std_religion,
//            "std_language"=>$std_language,
//            "std_username"=>$std_username,
//            "std_password"=>$std_password,
//            "std_image"=>$i_name,
//            "father_ocu"=>$father_ocu,
//            "mother_ocu"=>$mother_ocu,
//             "city_id"=>$city_id,
//            "country_id"=>$country_id,
//            "extra_curiculam"=>$extra_curiculam);
//        if ($con->update("tbl_student", $update_array) == 1) {
//             if ($filename != "") {
//
//                move_uploaded_file($_FILES['std_image']['tmp_name'], $targetfolder);
//            }
//            $msg = "STUDENT Update successfully";
//        } else {
//            $err = "Invalid Query";
//        }
//    }
}
$close = $con->close($open);
?>
<?php include '../view_layout/header_view.php'; ?>

<div class="widget widget-tabs widget-tabs-gray widget-tabs-double-2 border-bottom-none">

	<!-- Widget heading -->
	<div class="widget-head">
		<ul>
			<li class="active"><a class="glyphicons display" href="#overview" data-toggle="tab"><i></i>Student Details</a></li>
			<li><a class="glyphicons edit" href="#edit-account" data-toggle="tab"><i></i>Edit account</a></li>
			<li><a class="glyphicons luggage" href="#projects" data-toggle="tab"><i></i>Projects</a></li>
		</ul>
	</div>
	<!-- // Widget heading END -->
	
	<div class="widget-body">
            
	<?php if(count($conct)>=1): ?>
           <?php foreach ($conct as $c): ?>
		<form class="form-horizontal">
			<div class="tab-content">
			
				<div class="tab-pane active widget-body-regular padding-none" id="overview">
				
					<div class="innerL row row-merge">
						<div class="col-md-3 center innerL innerTB">
						
							<div class="innerR innerT">
								<!-- Profile Photo -->
								<a href="" class="thumb"><img src="<?php echo $con->baseUrl("uploads/student/image/$c->std_image"); ?>" alt="Profile" class="img-responsive" /></a>
								<div class="separator bottom"></div>
								<!-- // Profile Photo END -->
								
								<!-- Social Icons -->
<!--								<a href="" class="glyphicons glyphicons-social standard primary facebook"><i></i></a>
								<a href="" class="glyphicons glyphicons-social standard twitter"><i></i></a>
								<a href="" class="glyphicons glyphicons-social standard linked_in"><i></i></a>
								<div class="clearfix separator bottom"></div>-->
								<!-- // Social Icons END -->
								
								<!-- Twitter Section -->
								<h5 class="glyphicons single twitter"><i></i> Profile</h5>
								<section class="twitter-feed">
                                                                    <div class="tweet">Name: <span><?php echo $c->std_fname ?></span>&nbsp<?php echo $c->std_lname ?> 
                                                                        <br/>
                                                                        
<!--                                                                        <span class="label label-inverse">01/11/2012</span></div>-->
								</section>
								<!-- Twitter Section END -->
								
							</div>
							
						</div>
						<div class="col-md-9 containerBg innerTB">
						
							<div class="innerLR">
								<div class="row innerTB">
									<div class="col-md-7">
									
										<!-- About -->
										<div class="widget widget-heading-simple widget-body-white margin-none">
											<div class="widget-head"><h4 class="heading glyphicons user"><i></i><?php echo $c->std_username; ?></h4></div>
											<div class="widget-body">
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;"><i></i> Father Name: <span style="color: #7C7C7C;"> <?php echo $c->std_father; ?></span></li> <br/> 
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i> Father Profession: <span style="color: #7C7C7C;"> <?php echo $c->father_ocu; ?></span></li> <br/> 
											   <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle; margin-top:5px"><i></i> Mother Name: <span style="color: #7C7C7C;"> <?php echo $c->std_mother; ?></span></li> <br/> 
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i> Mother Profession: <span style="color: #7C7C7C;"> <?php echo $c->mother_ocu; ?></span></li> <br/> 
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i> Religion: <span style="color: #7C7C7C;"> <?php echo $c->std_religion; ?></span></li> <br/> 
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i> Language: <span style="color: #7C7C7C;"> <?php echo $c->std_language; ?></span></li> <br/>
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i> Extra Curricular Activities: <span style="color: #7C7C7C;"> <?php echo $c->extra_curiculam; ?></span></li> <br/> 
                                                                                        </div>
										</div>
										<!-- // About END -->
									
									</div>
									<div class="col-md-5">
								
										<!-- Bio -->
										<div class="widget widget-heading-simple widget-body-white margin-none">
											<div class="widget-head"><h4 class="heading glyphicons calendar"><i></i><span>Personal Information</span></h4></div>
											<div class="widget-body">
												<ul class="unstyled icons margin-none">
												
                                                                                                       <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;"><i></i> Date of Birth: <span class="label label-default"><span> <?php echo $c->std_dob; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px;"><i></i>First Name: <span style="color: #7C7C7C;"> <?php echo $c->std_fname; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px;"><i></i> Last Name:<span style="color: #7C7C7C;"> <?php echo $c->std_lname; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Gender: <span style="color: #7C7C7C;"> <?php echo $c->std_gender; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Email: <span style="color: #7C7C7C;"> <?php echo $c->std_email; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Phone: <span style="color: #7C7C7C;"> <?php echo $c->std_mob; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Blood Group: <span style="color: #7C7C7C;"> <?php echo $c->std_bloodg; ?></span></li> <br/> 
                                                                                                         
												</ul>
											</div>
										</div>
										<!-- // Bio END -->
										
									</div>
								</div>
								<div class="row">
									<div class="col-md-7">
									
										<!-- Latest Orders/List Widget -->
										<div class="widget widget-heading-simple widget-body-gray" data-toggle="collapse-widget">
										
											<!-- Widget Heading -->
											<div class="widget-head">
												<h4 class="heading glyphicons user"><i></i>Address</h4>
<!--												<a href="" class="details pull-right">view all</a>-->
											</div>
											<!-- // Widget Heading -->
											
											<div class="widget-body list products">
											<ul>
													
													<!-- List item -->
													<li>
														
														<span class="title"> Present Address<br/><strong><?php echo $c->std_pres_addr; ?></strong></span>
														<span class="count"></span>
													</li>
													<!-- // List item END -->
													
																										<!-- List item -->
													<li>
														
														<span class="title"> Permanent Address<br/><strong><?php echo $c->std_per_addr; ?></strong></span>
														<span class="count"></span>
													</li>
													<!-- // List item END -->
																										<!-- List item -->
<!--													<li>
														<span class="img">photo</span>
														<span class="title">Product name<br/><strong>&euro;2,900</strong></span>
														<span class="count"></span>
													</li>-->
													<!-- // List item END -->
																										
												</ul>	
											</div>
										</div>
										<!-- // Latest Orders/List Widget END -->
										
<!--                                                                                <div class="alert alert-primary"> 
                                                                                       <div style="margin-top: 5px;"> Extra Curricular Activities </div>
											<a class="close" data-dismiss="alert">&times;</a>
											<p>Integer quis tempor mi. Donec venenatis dui in neque fringilla at iaculis libero ullamcorper. In velit sem, sodales id hendrerit ac, fringilla et est. Pellentesque at justo urna, eu pharetra tortor. Aenean aliquam, tellus vel suscipit luctus.</p>
										</div>-->
									
									</div>
									<div class="col-md-5">
										
										<div class="widget widget-heading-simple widget-body-gray" data-toggle="collapse-widget">
			
											<!-- Widget Heading -->
											<div class="widget-head">
												<h4 class="heading glyphicons history"><i></i>Nation state</h4>
<!--												<a href="" class="details pull-right">view all</a>-->
											</div>
											<!-- // Widget Heading END -->
											
											<div class="widget-body list">
												<ul>
												
													<!-- List item -->
													<li>
														<span>Country Name:</span>
                                                                                                                <span style="color: black; margin-left: 4px;"><?php echo $c->country_name; ?></span>
													</li>
													<!-- // List item END -->
													
																										<!-- List item -->
													<li>
														<span>City Name:</span>
														<span style="color: black; margin-left: 4px;"><?php echo $c->city_name; ?></span>
													</li>
													<!-- // List item END -->
																										<!-- List item -->
<!--													<li>
														<span>Some other stats</span>
														<span class="count">28,141</span>
													</li>-->
													<!-- // List item END -->
																										
												</ul>
											</div>
										</div>
										
										<div class="widget widget-heading-simple widget-body-gray" data-toggle="collapse-widget">
			
											<!-- Widget Heading -->
											<div class="widget-head">
												<h4 class="heading glyphicons user"><i></i>Extra Curricular Activities</h4>
											</div>
											<!-- // Widget Heading END -->
											
											<div class="widget-body">
											<?php echo $c->extra_curiculam; ?>	
											</div>
										</div>
										
									</div>
								</div>
							</div>
							
						</div>
					</div>
				
				</div>
			
				<!-- Tab content -->
				<div class="tab-pane widget-body-regular containerBg" id="edit-account">
				
					<div class="widget widget-tabs widget-tabs-vertical row row-merge margin-none widget-body-white">

						<!-- Widget heading -->
						<div class="widget-head col-md-3">
							<ul>
								<li class="active"><a class="glyphicons pencil" href="#account-details" data-toggle="tab"><i></i>Account details</a></li>
								<li><a class="glyphicons settings" href="#account-settings" data-toggle="tab"><i></i>Account settings</a></li>
								<li><a class="glyphicons eye_open" href="#privacy-settings" data-toggle="tab"><i></i>Privacy settings</a></li>
							</ul>
						</div>
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
											<input type="text" value="John" class="form-control" />
										</div>
									</div>
									<!-- // Group END -->
									
									<!-- Group -->
									<div class="form-group">
										<label class="col-md-4 control-label">Last name</label>
										<div class="col-md-8">
											<input type="text" value="Doe" class="form-control" />
										</div>
									</div>
									<!-- // Group END -->
									
									<!-- Group -->
									<div class="form-group">
										<label class="col-md-4 control-label">Date of birth</label>
										<div class="col-md-8">
											<div class="input-group">
												<input type="text" id="datepicker1" class="form-control" value="13/06/1988" />
												<span class="input-group-addon"><i class="icon-calendar"></i></span>
											</div>
										</div>
									</div>
									<!-- // Group END -->
									
								</div>
								<!-- // Column END -->
								
								<!-- Column -->
								<div class="col-md-6">
								
									<!-- Group -->
									<div class="form-group">
										<label class="col-md-2 control-label">Gender</label>
										<div class="col-md-10">
											<select class="form-control">
												<option>Male</option>
												<option>Female</option>
											</select>
										</div>
									</div>
									<!-- // Group END -->
									
									<!-- Group -->
									<div class="form-group">
										<label class="col-md-2 control-label">Age</label>
										<div class="col-md-10">
											<input type="text" value="25" class="form-control" />
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
								<label class="control-label">About me</label>
								<div class="controls">
									<textarea id="mustHaveId" class="wysihtml5 form-control" rows="5">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium.</textarea>
								</div>
							</div>
							<!-- // Group END -->
							
							<!-- Form actions -->
							<div class="separator top">
								<button type="submit" class="btn btn-icon btn-primary glyphicons circle_ok"><i></i>Save changes</button>
								<button type="button" class="btn btn-icon btn-default glyphicons circle_remove"><i></i>Cancel</button>
							</div>
							<!-- // Form actions END -->
							
							</div>
							<div class="tab-pane" id="account-settings">
							
								<!-- Row -->
								<div class="row">
								
									<!-- Column -->
									<div class="col-md-3">
										<strong>Change password</strong>
										<p class="muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
									</div>
									<!-- // Column END -->
									
									<!-- Column -->
									<div class="col-md-9">
										<label for="inputUsername">Username</label>
										<div class="input-group">
											<input type="text" id="inputUsername" class="form-control" value="john.doe2012" disabled="disabled" />
											<span class="input-group-addon" data-toggle="tooltip" data-placement="top" data-container="body" data-original-title="Username can't be changed"><i class="icon-question-sign"></i></span>
										</div>
										<div class="separator"></div>
												
										<label for="inputPasswordOld">Old password</label>
										<div class="input-group">
											<input type="password" id="inputPasswordOld" class="form-control" value="" placeholder="Leave empty for no change" />
											<span class="input-group-addon" data-toggle="tooltip" data-placement="top" data-container="body" data-original-title="Leave empty if you don't wish to change the password"><i class="icon-question-sign"></i></span>
										</div>
										<div class="separator"></div>
										
										<label for="inputPasswordNew">New password</label>
										<input type="password" id="inputPasswordNew" class="form-control" value="" placeholder="Leave empty for no change" />
										<div class="separator"></div>
										
										<label for="inputPasswordNew2">Repeat new password</label>
										<input type="password" id="inputPasswordNew2" class="form-control" value="" placeholder="Leave empty for no change" />
										<div class="separator"></div>
									</div>
									<!-- // Column END -->
									
								</div>
								<!-- // Row END -->
								
								<div class="separator line bottom"></div>
								
								<!-- Row -->
								<div class="row">
								
									<!-- Column -->
									<div class="col-md-3">
										<strong>Contact details</strong>
										<p class="muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
									</div>
									<!-- // Column END -->
									
									<!-- Column -->
									<div class="col-md-9">
										<div class="row">
											<div class="col-md-6">
												<label for="inputPhone">Phone</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-phone"></i></span>
													<input type="text" id="inputPhone" class="form-control" placeholder="01234567897" />
												</div>
												<div class="separator"></div>
													
												<label for="inputEmail">E-mail</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-envelope"></i></span>
													<input type="text" id="inputEmail" class="form-control" placeholder="contact@mosaicpro.biz" />
												</div>
												<div class="separator"></div>
													
												<label for="inputWebsite">Website</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-link"></i></span>
													<input type="text" id="inputWebsite" class="form-control" placeholder="http://www.mosaicpro.biz" />
												</div>
												<div class="separator"></div>
											</div>
											<div class="col-md-6">
												<label for="inputFacebook">Facebook</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-facebook"></i></span>
													<input type="text" id="inputFacebook" class="form-control" placeholder="mosaicpro" />
												</div>
												<div class="separator"></div>
												
												<label for="inputTwitter">Twitter</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-twitter"></i></span>
													<input type="text" id="inputTwitter" class="form-control" placeholder="mosaicpro" />
												</div>
												<div class="separator"></div>
												
												<label for="inputSkype">Skype ID</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-skype"></i></span>
													<input type="text" id="inputSkype" class="form-control" placeholder="mosaicpro" />
												</div>
												<div class="separator"></div>
												
												<label for="inputgplus">Google</label>
												<div class="input-group">
													<span class="input-group-addon"><i class="icon-google-plus-sign"></i></span>
													<input type="text" id="inputgplus" class="form-control" placeholder="google ID" />
												</div>
												<div class="separator"></div>
											</div>
										</div>
									</div>
									<!-- // Column END -->
									
								</div>
								<!-- // Row END -->
								
								<!-- Form actions -->
								<div class="form-actions" style="margin: 0;">
									<button type="submit" class="btn btn-icon btn-primary glyphicons circle_ok"><i></i>Save changes</button>
								</div>
								<!-- // Form actions END -->
							
							</div>
							<div class="tab-pane" id="privacy-settings">
								<div class="uniformjs">
									<label class="checkbox"><input type="checkbox" checked="checked" /> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</label>
									<label class="checkbox"><input type="checkbox" /> Vivamus et risus vel metus feugiat semper at sed odio.</label>
									<label class="checkbox"><input type="checkbox" /> Aenean bibendum faucibus tellus, et facilisis justo imperdiet vel.</label>
									<div class="separator top"></div>

									<div class="alert alert-primary">
										<a class="close" data-dismiss="alert">&times;</a>
										<p>Integer quis tempor mi. Donec venenatis dui in neque fringilla at iaculis libero ullamcorper. In velit sem, sodales id hendrerit ac, fringilla et est. Pellentesque at justo urna, eu pharetra tortor. Aenean aliquam, tellus vel suscipit luctus, risus enim ornare tellus, ac ultrices nisi enim sed magna.</p>
									</div>
								</div>
							</div>
							</div>
						
						</div>
					</div>
					
				</div>
				<!-- // Tab content END -->
				
				<!-- Tab content -->
				<div class="tab-pane widget-body-regular" id="projects">
				
					<div class="well">
						<button type="button" class="btn btn-primary btn-icon glyphicons circle_plus pull-right"><i></i>Add project</button>
						<p class="lead margin-none"><strong>1024</strong> sales this week</p>
						<div class="clearfix"></div>
					</div>
					
					<table class="table table-striped table-vertical-center table-projects table-bordered">
						<thead>
							<tr>
								<th colspan="2">Project</th>
								<th width="120" class="center"></th>
								<th width="120" class="center"></th>
								<th width="160" class="center"></th>
								<th width="120" class="center"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td width="80" class="center"><span class="thumb"><img src="http://2.s3.envato.com/files/50444644/80-avatar.jpg" alt="" /></span></td>
								<td class="important">Smashing - Premium Admin Template</td>
								<td class="center stats"><span>Sales today</span><span class="count">153</span></td>
								<td class="center stats"><span>Sales total</span><span class="count">1,365</span></td>
								<td class="center stats"><span>Earnings</span><span class="count">&dollar;25,356.00</span></td>
								<td class="center"><button type="button" class="btn btn-default">Manage</button></td>
							</tr>
							<tr>
								<td width="80" class="center"><span class="thumb"><img src="http://0.s3.envato.com/files/52347478/admin-avatar-12.jpg" alt="" /></span></td>
								<td class="important">AdminPlus - Premium Bootstrap Admin Template</td>
								<td class="center stats"><span>Sales today</span><span class="count">153</span></td>
								<td class="center stats"><span>Sales total</span><span class="count">1,365</span></td>
								<td class="center stats"><span>Earnings</span><span class="count">&dollar;25,356.00</span></td>
								<td class="center"><button type="button" class="btn btn-default">Manage</button></td>
							</tr>
							<tr>
								<td width="80" class="center"><span class="thumb"><img src="http://2.s3.envato.com/files/50868169/avatar80.jpg" alt="" /></span></td>
								<td class="important">AIR - Responsive Bootstrap Admin Template</td>
								<td class="center stats"><span>Sales today</span><span class="count">153</span></td>
								<td class="center stats"><span>Sales total</span><span class="count">1,365</span></td>
								<td class="center stats"><span>Earnings</span><span class="count">&dollar;25,356.00</span></td>
								<td class="center"><button type="button" class="btn btn-default">Manage</button></td>
							</tr>
							<tr>
								<td width="80" class="center"><span class="thumb"><img src="http://3.s3.envato.com/files/47008628/boot-admin-80_v13.jpg" alt="" /></span></td>
								<td class="important">BootAdmin - All-In-One Admin Responsive Template</td>
								<td class="center stats"><span>Sales today</span><span class="count">153</span></td>
								<td class="center stats"><span>Sales total</span><span class="count">1,365</span></td>
								<td class="center stats"><span>Earnings</span><span class="count">&dollar;25,356.00</span></td>
								<td class="center"><button type="button" class="btn btn-default">Manage</button></td>
							</tr>
						</tbody>
					</table>
					
				</div>
				<!-- // Tab content END -->
			</div>
		</form>
            
            <?php endforeach; ?>
            <?php endif; ?>  
	</div>
</div>
 
<?php include '../view_layout/footer_view.php'; ?>






