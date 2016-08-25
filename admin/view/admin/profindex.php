<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();
$admin_email = '';
$phone = '';
$address = '';
$open = $con->open();
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

$admin_email = $_SESSION['admin_email'];
//$users = $con->SelectAllByCondition("admin", "admin_email='$admin_email'");//row query
$query = "SELECT a.*,c.country_name FROM admin a,tbl_country c WHERE a.country_id = c.country_id AND admin_email= '$admin_email' ";
//$users = $con->SelectAllByCondition("admin", "user_email='$user_email'");

    $result = mysqli_query($open,$query);
    while ($rows = mysqli_fetch_object($result)) {
    $users[] = $rows;
}


foreach ($users as $user) {
    $ad_id = $user->ad_id;
    $admin_name = $user->admin_name;
    $admin_email = $user->admin_email;
    $admin_username = $user->admin_username;
    $address = $user->address;
    $country_id = $user->country_id;
    $country_name = $user->country_name;
    $image = $user->admin_image;
//$admin_type = $user->role;
    $edit = $user->admin_updated;
    $phone = $user->phone;
}

//Deleting profile picture
if (isset($_GET['delid'])) {
    $id = base64_decode($_GET['delid']);
    $delete_array = array("ad_id" => $id);
    if ($con->delete("admin", $delete_array) == 1) {
        unlink("../../uploads/photo/" . $filename);
        $con->redirect("index.php");
    }
}

if (isset($_GET['delid'])) {
    $id = base64_decode($_GET['delid']);
    $delete_array = array("ad_id" => $id);
    if ($con->delete("admin", $delete_array) == 1) {
        $con->redirect("list.php");
    }
}

$close = $con->close($open);
?>
<?php include '../view_layout/header_view.php';?>
<div class="innerAll spacing-x2">
    
    <div class="widget widget-inverse">
				<div class="widget-head">
					<h3 class="heading"> Unread Message</h3>
				</div>
		
		<div class="widget-body">

			<!-- Row -->
			<div class="row">
				<div class="col-md-8">
				
					<!-- Stats Widget -->
                                      <a href="" >You have 6 new messages</a> 

<!-- // Stats Widget END -->

					
				</div>
				
				
				
<!--				<div class="col-md-2">
				
					 Stats Widget 
					 Stats Widget 

 // Stats Widget END 



					 // Stats Widget END 
					
				</div>-->
			</div>
			<!-- // Row END -->
		</div>
	</div>
    
    

	<!-- Widget -->
	<div class="widget finances_summary widget-inverse">

		<div class="row row-merge">
                    
                    
                    
			<!-- col -->
			<div class="col-sm-12 col-md-3">
						<!-- Profile Photo -->
						<div class="border-bottom">
							<a href="">
                                                            <img style="border: 4px solid white;" src="<?php
                                                            if (!empty($image)) {
                                                                echo $con->baseUrl("uploads/admin/$image");
                                                            }else {
                                                                echo 'empty.jpg';
                                                            }
                                                            ?>" width="320" height="300" class="img-responsive img-clean"/>
                                                            
                                                      </a>
						</div>
						<div style="margin: -21px; margin-bottom: -51px" class="innerAll inner-2x text-center">
                                                    <p class="lead strong margin-none"><?php echo $admin_name;?></p>
							<p class="lead">Admin</p>
						
<!--							<div class="btn-group">
								<a href="" class="btn btn-primary"><i class="fa fa-plus fa-fw"></i> Add to list</a>
								<a href="" class="btn btn-primary btn-stroke"><i class="fa fa-envelope"></i></a>
							</div>-->
						</div>
						<hr class="separator"/>
							

<!--						<div class="innerAll border-bottom bg-gray">
							<h4 class="text-primary"><i class=""></i> Skills</h4>
							<ul class="list-unstyled">
								<li>Photoshop</li>
								<li>HTML/CSS</li>
								<li>User Experience</li>
								
							</ul>
						</div>-->
                                                <div style="margin-top: -74px;" class="innerAll border-bottom">
<!--							<h4 class="text-primary"><i class=""></i> Experience</h4>-->
							<div style=" border: none;" class="innerAll half box-generic">
								<p class="margin-none strong">User Name</p>
								<span ><?php echo $admin_username; ?></span>
							</div>
                                                    <div style=" border: none; margin-top: -10px;" class="innerAll half box-generic">
								<p class="margin-none strong">Email</p>
								<span ><?php echo $admin_email; ?></span>
							</div>
						</div>
<!--						<div class="innerAll">
							<h4 class="text-primary"><i class=""></i> Education</h4>
							<div class="bg-gray innerAll half box-generic">
								<p class="margin-none">Adobe Photoshop Cerficate</p>
								<span class="label label-default ">on 16 Dec 2013</span>
							</div>
							<div class="bg-gray innerAll half box-generic">
								<p class="margin-none">Bachelor Degree in Web Graphics</p>
								<span class="label label-default ">on 16 Dec 2013</span>
							</div>
							<a href="#" class="btn btn-primary btn-xs">View all</a>
						</div>-->

						
			</div> 
			<!-- // END col -->

		
			<!-- col -->
			<div class="col-lg-9 col-md-9 col-sm-12">

				<div class="innerAll half border-bottom">
					<h4 class="pull-left innerT half margin-none">Address</h4>		
					<div class="clearfix"></div>
				
				</div>
				<div class="innerAll ">
					<p><?php echo $address;?></p>
					
				</div>

			

				<div class="innerAll half heading-buttons border-bottom">
					<h4 class="margin-none pull-left">Others Information</h4>
					
					
					
					<div class="clearfix"></div>
					
				</div>
				<div class="innerAll">
					<ul class="list-unstyled resume-documents">
                                            <li><i class="fa fa-file-o"></i> <b> Phone No: </b><span><?php echo $phone; ?></span></li>
                                            <li><i class="fa fa-file-o"></i><b> Country: </b> <span><?php echo $country_name; ?></span></li>
						
					</ul>
					<div class="clearfix"></div>
					<div class="innerLR innerB">
<!--						<a href="" class="btn btn-primary btn-sm"><i class="fa fa-pencil fa-fw"></i> Write New </a>-->
					</div>
				</div>

		
<!--				<div class="innerAll half heading-buttons border-bottom">
					<h4 class="margin-none pull-left">Work History	</h4>
					
					<div class="clearfix"></div>
				</div>-->

<!--				<ul class="timeline-activity list-unstyled">
	<li class="active">
		<i class="list-icon fa fa-share"></i>
		<div class="block block-inline">
			<div class="caret"></div>
			<div class="box-generic">
				<div class="timeline-top-info content-filled border-bottom">
					<i class="fa fa-user"></i> <a href="">Bill</a> got a review for <a href="" class="text-primary">FLAT PLUS UI Interface Design</a> from <a href="#"><img src="../assets/images/people/80/8.jpg" alt="photo" width="20"></a> <a href="">Andrew M.</a>
					<div class="timeline-bottom">
						<i class="fa fa-clock-o"></i> 2 days ago 
					 
					</div>
				</div>
				<div class="media innerAll margin-none">
			        <a class="pull-left" href="#"><img src="../assets/images/people/80/8.jpg" alt="photo" class="media-object" width="35"></a>
			        <div class="media-body">
			          	<a href="" class="strong">Andrew</a> Good Job. Congrats and hope to see more admin templates like this in the future.
		     			<div class="timeline-bottom">
							<i class="fa fa-clock-o"></i> 2 days ago  
						</div>
			        </div>
			    </div>
		    
			</div>
			
		</div>
	</li>

	<li>
		<i class="list-icon fa fa-share"></i>
		<div class="block block-inline">
			<div class="caret"></div>
			<div class="box-generic">
				<div class="timeline-top-info content-filled border-bottom">
					<i class="fa fa-user"></i> <a href="">Bill</a> got a review for <a href="" class="text-primary">Support &amp; Ticket System</a> from <a href="#"><img src="../assets/images/people/80/20.jpg" alt="photo" width="20"></a> <a href="">Andrew M.</a>
					<div class="timeline-bottom">
						<i class="fa fa-clock-o"></i> 2 days ago 
					 
					</div>
				</div>
				<div class="media innerAll margin-none">
			        <a class="pull-left" href="#"><img src="../assets/images/people/80/20.jpg" alt="photo" class="media-object" width="35"></a>
			        <div class="media-body">
			          	<a href="" class="strong">Bogdan</a> Good Job. Congrats and hope to see more admin templates like this in the future.
		     			<div class="timeline-bottom">
							<i class="fa fa-clock-o"></i> 2 days ago  
						</div>
			        </div>
			    </div>
		    
			</div>
			
		</div>
	</li>

	<li>
		<i class="list-icon fa fa-share"></i>
		<div class="block block-inline">
			<div class="caret"></div>
			<div class="box-generic">
				<div class="timeline-top-info content-filled border-bottom">
					<i class="fa fa-user"></i> <a href="">Bill</a> got a review for <a href="" class="text-primary">Project Management</a> from <a href="#"><img src="../assets/images/people/80/12.jpg" alt="photo" width="20"></a> <a href="">Andrew M.</a>
					<div class="timeline-bottom">
						<i class="fa fa-clock-o"></i> 2 days ago 
					 
					</div>
				</div>
				<div class="media innerAll margin-none">
			        <a class="pull-left" href="#"><img src="../assets/images/people/80/12.jpg" alt="photo" class="media-object" width="35"></a>
			        <div class="media-body">
			          	<a href="" class="strong">John </a> Good Job. Congrats and hope to see more admin templates like this in the future.
		     			<div class="timeline-bottom">
							<i class="fa fa-clock-o"></i> 2 days ago  
						</div>
			        </div>
			    </div>
		    
			</div>
			
		</div>
	</li>

	<li>
		<i class="list-icon fa fa-share"></i>
		<div class="block block-inline">
			<div class="caret"></div>
			<div class="box-generic">
				<div class="timeline-top-info content-filled border-bottom">
					<i class="fa fa-user"></i> <a href="">Bill</a> got a review for <a href="" class="text-primary">FLAT PLUS UI Interface Design</a> from <a href="#"><img src="../assets/images/people/80/10.jpg" alt="photo" width="20"></a> <a href="">Andrew M.</a>
					<div class="timeline-bottom">
						<i class="fa fa-clock-o"></i> 2 days ago 
					 
					</div>
				</div>
				<div class="media innerAll margin-none">
			        <a class="pull-left" href="#"><img src="../assets/images/people/80/10.jpg" alt="photo" class="media-object" width="35"></a>
			        <div class="media-body">
			          	<a href="" class="strong">Andrew</a> Good Job. Congrats and hope to see more admin templates like this in the future.
		     			<div class="timeline-bottom">
							<i class="fa fa-clock-o"></i> 2 days ago  
						</div>
			        </div>
			    </div>
		    
			</div>
			
		</div>
	</li>

</ul>-->

	
										

			</div> 
			<!-- // END col -->

		</div>		

		</div>
		<!-- // END row -->
	</div>
<?php
include '../view_layout/footer_view.php';
?>

