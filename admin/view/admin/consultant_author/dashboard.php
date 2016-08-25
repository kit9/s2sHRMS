<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
$con_auth_name = '';
$con_auth_user_name = '';
$con_auth_email = '';
$open = $con->open();
$err = "";
$msg = '';

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
/** Query */
$consultants = array();
$query = "SELECT * FROM consultant_author order by con_auth_id DESC Limit 0,20";
$result = mysqli_query($open, $query);
while ($rows = mysqli_fetch_object($result)) {
    $consultants[] = $rows;
}
/** end*/


$close = $con->close($open);
?>
<?php include '../view_layout/header_view.php'; ?>


<div class="innerLR border-top">
	
	<div class="row row-merge">
		        <?php if(count($consultants)>=1): ?>
                        <?php foreach ($consultants as $c): ?>
			<div class="col-md-6 bg-white border-bottom ">
			<div class="row">

				<div class="col-sm-9">
					
					<div class="media">
						<a class="pull-left" href="#">
							<img class="media-object" height="100px" width="100px" src="<?php echo $con->baseUrl("uploads/consultant_author/$c->con_auth_img"); ?>" alt="...">
						</a>
						<div class="media-body innerAll half">
							 <h4 class="media-heading padding-none"><a href=""><?php echo $c->con_auth_name; ?></a> </h4>
							 <small class="text-success"><i class="fa fa-check"></i> <?php echo $c->con_auth_user_name; ?></small> 
							 <p>Lives in Michigan, USA</p>
						</div>
					</div>
					
				</div>
				<div class="col-sm-3 ">
<!--					<div class="innerAll half text-right">
						<div class="innerT half">
							<a href="" class="btn btn-info btn-xs"><i class="fa fa-thumbs-up"></i> Like</a>
						</div>
						<div class="innerT half">
							<a href="" class="btn btn-primary btn-xs margin-top "><i class="fa fa-envelope-o"></i> Chat</a>
						</div>
					</div>-->
				</div>
			
				
			</div>
			
			
		</div>
            
            <?php endforeach; ?>
            <?php endif; ?>  
						
					
	</div>
</div>




<?php include '../view_layout/footer_view.php'; ?>




