<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Checking if logged in
if ($con->authenticate1() == 1) {
    $con->redirect("../../login.php");
}
//Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}
$c_id='';
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
//$std_edu_info_id = '';

$open = $con->open();

if (isset($_GET['id'])) {
$id = $_GET['id'];
$query = "SELECT * FROM tbl_consultant WHERE c_id=$id";
    $result = mysqli_query($open,$query);
    while ($rows = mysqli_fetch_object($result)) {
    $conct[] = $rows;
}
// $con->debug($conct);
} 
      
$close = $con->close($open);
?>
<?php include '../view_layout/header_view.php'; ?>

<div class="widget widget-tabs widget-tabs-gray widget-tabs-double-2 border-bottom-none">

	<!-- Widget heading -->
	<div class="widget-head">
		<ul>
			<li class="active"><a class="glyphicons display" href="#overview" data-toggle="tab"><i></i>Consultant Details</a></li>
			<li><a class="glyphicons edit" href="#edit-account" data-toggle="tab"><i></i>Company Details</a></li>
<!--			<li><a class="glyphicons luggage" href="#projects" data-toggle="tab"><i></i>Projects</a></li>-->
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
                                                                <a href="" class="thumb"><img style="height: 160px; width: 280px;" src="<?php echo $con->baseUrl("uploads/consultant/consultant_image/$c->consultant_image"); ?>" alt="Profile" class="img-responsive" /></a>
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
                                                                    <div class="tweet">Name: <span><?php echo $c->c_f_name ?></span>&nbsp<?php echo $c->c_l_name ?> 
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
											<div class="widget-head"><h4 class="heading glyphicons user"><i></i>Personal Information</h4></div>
											<div class="widget-body">
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;"><i></i> User Name:<span style="color: #7C7C7C;"> <?php echo $c->c_user_name; ?></span></li> <br/> 
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px;"><i></i> First Name:<span style="color: #7C7C7C;"> <?php echo $c->c_f_name; ?></span></li> <br/>
                                                                                                       <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px;"><i></i> Last Name:<span style="color: #7C7C7C;"> <?php echo $c->c_l_name; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Phone: <span style="color: #7C7C7C;"> <?php echo $c->c_phone; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Email: <span style="color: #7C7C7C;"> <?php echo $c->c_email; ?></span></li> <br/> 
                                                                                                        
											 </div>
										</div>
										<!-- // About END -->
									
									</div>
									<div class="col-md-5">
								
										<!-- Bio -->
										<div class="widget widget-heading-simple widget-body-gray" data-toggle="collapse-widget">
			
											<!-- Widget Heading -->
											<div class="widget-head">
												<h4 class="heading glyphicons user"><i></i>About Consultant</h4>
											</div>
											<!-- // Widget Heading END -->
											
											<div class="widget-body">
											<?php echo $c->consultant_about; ?>	
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
														
														<span class="title"> Present Address:<br/><strong><?php echo $c->c_pres_addr; ?></strong></span>
														<span class="count"></span>
													</li>
													<!-- // List item END -->
													
																										<!-- List item -->
													<li>
														
														<span class="title"> Permanent Address:<br/><strong><?php echo $c->c_per_addr; ?></strong></span>
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
									
								</div>
							</div>
							
						</div>
					</div>
				
				</div>
			
				<!-- Tab content -->
				<div class="tab-pane widget-body-regular containerBg" id="edit-account">
				
                                    <div style="margin-top: -5px;" class="innerL row row-merge">
						<div class="col-md-3 center innerL innerTB">
						
							<div class="innerR innerT">
								<!-- Profile Photo -->
                                                                <a href="" class="thumb"><img style="height: 160px; width: 280px;" src="<?php echo $con->baseUrl("uploads/consultant/company_image/$c->company_image"); ?>" alt="Profile" class="img-responsive" /></a>
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
                                                                    <div class="tweet">Consultant: <span><?php echo $c->c_f_name ?></span>&nbsp<?php echo $c->c_l_name ?> 
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
											<div class="widget-head"><h4 class="heading glyphicons user"><i></i>Company Information</h4></div>
											<div class="widget-body">
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;"><i></i> Company Name:<span style="color: #7C7C7C;"> <?php echo $c->compamy_name; ?></span></li> <br/> 
                                                                                           <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px;"><i></i> Company Address:<span style="color: #7C7C7C;"> <?php echo $c->company_address; ?></span></li> <br/>
                                                                                                      
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Company Phone: <span style="color: #7C7C7C;"> <?php echo $c->company_phone; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Company Alternative Phone: <span style="color: #7C7C7C;"> <?php echo $c->company_alt_ph; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Company Email: <span style="color: #7C7C7C;"> <?php echo $c->company_email; ?></span></li> <br/> 
                                                                                                        <li style="color: #1D1D1B; display: inline-block; position: relative; text-decoration: none; vertical-align: middle;margin-top:5px"><i></i>Company Alternative Email: <span style="color: #7C7C7C;"> <?php echo $c->company_alt_ph; ?></span></li> <br/> 
                                                                                                        
											 </div>
										</div>
										<!-- // About END -->
									
									</div>
									<div class="col-md-5">
								
										<!-- Bio -->
										<div class="widget widget-heading-simple widget-body-gray" data-toggle="collapse-widget">
			
											<!-- Widget Heading -->
											<div class="widget-head">
												<h4 class="heading glyphicons user"><i></i>About Company</h4>
											</div>
											<!-- // Widget Heading END -->
											
											<div class="widget-body">
											<?php echo $c->company_about; ?>	
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
														
														<span class="title"> Web Address:<br/><strong><?php echo $c->company_web; ?></strong></span>
														<span class="count"></span>
													</li>
													<!-- // List item END -->
													
																										<!-- List item -->
													<li>
														
														<span class="title"> Company Address:<br/><strong><?php echo $c->company_address; ?></strong></span>
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






