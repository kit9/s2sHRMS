<?php
session_start();
include("../../config/class.config.php");
$con = new Config();
//Checking if logged in

//Checking if logged in
if ($con->authenticate1() == 1) {
    $con->redirect("../../login.php");
}

////Checking access permission
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

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

$open = $con->open();
$err = "";
$msg = '';
/** Query */
$c_email = $_SESSION['c_email'];
$querycon =array("c_email"=> $c_email);
$resultcon=$con->SelectAllByField("tbl_consultant", $querycon);
if (count($resultcon) >= 1):
foreach ($resultcon as $n): 
 $con_id = $n->c_id;
endforeach;
endif;

//$con->debug($con_id);

$consultants_co = array();
$query = "SELECT s.*,cn.country_name FROM tbl_student s, tbl_consultant c, tbl_country cn WHERE s.c_id=c.c_id AND s.country_id=cn.country_id order by std_id DESC ";
$result = mysqli_query($open, $query);
while ($rows = mysqli_fetch_object($result)) {
    $consultants_co[] = $rows;
}

//$con->debug($query);
/** end*/


$close = $con->close($open);
?>
<?php include '../view_layout/header_view.php'; ?>


<div class="innerLR border-top">
	
	<div class="row row-merge">
		        <?php if(count($consultants_co)>=1): ?>
                        <?php foreach ($consultants_co as $c): ?>
			<div class="col-md-6 bg-white border-bottom ">
			<div class="row">

				<div class="col-sm-9">
					
					<div class="media">
						<a class="pull-left" href="#">
							<img class="media-object" height="100px" width="100px" src="<?php echo $con->baseUrl("uploads/student/image/$c->std_image"); ?>" alt="...">
						</a>
						<div class="media-body innerAll half">
							 <h4 class="media-heading padding-none"><a href=""><?php echo $c->std_fname; ?> <?php echo $c->std_lname; ?></a> </h4>
							 <small class="text-success"><i class="fa fa-check"></i> <?php echo $c->std_username; ?></small> 
							 <p>Lives in <?php echo $c->country_name; ?> </p>
						</div>
					</div>
					
				</div>
				<div class="col-sm-3 ">
					<div class="innerAll half text-right">
						<div class="innerT half">
							<a href="view_std.php?id=<?php echo $c->std_id; ?>" class="btn btn-info btn-xs">Details</a>
						</div>
                                            <div class="innerT half">
                                                <a style="width:55%" href="edit_std.php?id=<?php echo $c->std_id; ?>" class="btn btn-info btn-xs">Edit</a>
						</div>
<!--						<div class="innerT half">
							<a href="" class="btn btn-primary btn-xs margin-top "><i class="fa fa-envelope-o"></i> Chat</a>
						</div>-->
					</div>
				</div>
			
				
			</div>
			
			
		</div>
            
            <?php endforeach; ?>
            <?php endif; ?>  
						
					
	</div>
</div>




<?php include '../view_layout/footer_view.php'; ?>





