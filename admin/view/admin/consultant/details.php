<?php
include ('../../config/class.config.php');
$con = new Config();
$err = "";
$msg = '';
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
$c_id = '';
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
if (isset($_GET['id'])) {
$id = base64_decode($_GET['id']);
    $object_array = array("c_id" => $id);
    $consultants = $con->SelectAllByID("tbl_consultant", $object_array);
     

    foreach ($consultants as $n) {
        $c_id = $n->c_id;
        $c_f_name=$n->c_f_name;
        $c_l_name=$n->c_l_name;
        $c_phone=$n->c_phone;
        $c_pres_addr=$n->c_pres_addr;
        $c_per_addr=$n->c_per_addr;
        $c_email=$n->c_email;  
        $compamy_name=$n->compamy_name;  
        $company_address=$n->company_address;  
        $company_phone=$n->company_phone;  
        $company_web=$n->company_web;  
        $company_fax=$n->company_fax;  
        $company_image=$n->company_image;  
    }
}

if (isset($_POST['edit_con'])) {
    extract($_POST);
//    $con->debug($_POST);

   $targetfolder = '../../images/consultant/';
    $filename = basename($_FILES['company_image']['name']);
    $targetfolder = $targetfolder . $filename;
//    $uploadPath = substr($targetfolder, 6);
    
   $i_name = $_FILES["company_image"]["name"];
    
    
    if (empty($c_f_name)) {
        $err = "First name is not selected";
    } else if (empty($c_l_name)) {
        $err = "Last name is empty";
    } else if (empty($c_phone)) {
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
        } else if (empty($company_fax)) {
        $err = "Company Fax is empty";
   
        } else {

        $update_array = array("c_f_name"=>$c_f_name, "c_l_name"=>$c_l_name, "c_phone"=>$c_phone,"c_pres_addr"=>$c_pres_addr,"c_per_addr"=>$c_per_addr,"c_email"=>$c_email,"compamy_name"=>$compamy_name,"company_address"=>$company_address,"company_phone"=>$company_phone,"company_web"=>$company_web,"company_fax"=>$company_fax,"company_image"=>$i_name);
        if ($con->update("tbl_consultant", $update_array) == 1) {
           if ($filename != "") {

            move_uploaded_file($_FILES['company_image']['tmp_name'], $targetfolder);
            }
            $msg = "Consultant Update successfully";
        } else {
            $err = "Invalid Query";
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>

   <div class="widget widget-inverse">
	<div class="widget-head">
		<h4 class="heading">Consultant Info</h4>
	</div>
	<div class="widget-body">
		<div class="row">
			
		<div class="row innerLR ">
                 <h5>First Name:</h5>
            <input type="text" name="c_f_name" id="text_element" value="<?php echo $c_f_name; ?>" class="form-control" />
                 </div>
                    <br>
                    <div class="row innerLR ">
                 <h5>Last Name:</h5>
            <input type="text" name="c_l_name" id="text_element" value="<?php echo $c_l_name; ?>" class="form-control" />
                 </div>
                    <br>
                    <div class="row innerLR ">
                 <h5>Phone Number:</h5>
            <input type="text" name="c_phone" id="text_element" value="<?php echo $c_phone; ?>" class="form-control" />
                 </div>
                    <br>
                    
                   <div class="form-group">
                        <h5>Present Address:</h5>
                        <textarea style="color: #C4C4C4;" type="text" id="text_element" name="c_pres_addr" class="form-control"rows="1" cols="20"><?php echo $c_pres_addr; ?></textarea>
                        </div>
                    <div class="form-group">
                        <h5>Permanent Address:</h5>
                        <textarea style="color: #C4C4C4;" type="text" id="text_element" name="c_per_addr" class="form-control"rows="1" cols="20"><?php echo $c_per_addr; ?></textarea>
                        </div>
                     
                    <div class="row innerLR ">
                 <h5>Email:</h5>
                <input type="text" name="c_email" id="text_element" value="<?php echo $c_email; ?>" class="form-control" />
                 </div>
                    <br/>
                    <div class="row innerLR ">
                 <h5>Company Name:</h5>
            <input type="text" name="compamy_name" id="text_element" value="<?php echo $compamy_name; ?>" class="form-control" />
                 </div>
                     <br>
                   
                    <div class="form-group">
                        <h5>Company Address:</h5>
                        <textarea style="color: #C4C4C4;" type="text" id="text_element" name="company_address" placeholder="Company Address" class="form-control"rows="1" cols="20"><?php echo $company_address; ?></textarea>
                        </div>
                     <br>
                    
                     <div class="row innerLR ">
                 <h5>Company Phone:</h5>
                 <input type="text" name="company_phone" id="text_element" value="<?php echo $company_phone; ?>" class="form-control" />
                 </div>
                    <br/>
                    
                    
                    <div class="row innerLR ">
                 <h5>Company Website:</h5>
            <input type="text" name="company_web" id="text_element" value="<?php echo $company_web; ?>" class="form-control" />
                 </div>
                    <br/>
                    <div class="row innerLR ">
                 <h5>Company Fax:</h5>
              <input type="text" name="company_fax" id="text_element" value="<?php echo $company_fax; ?>" class="form-control" />
                 </div>
                    <br/>
                    <div class="row innerLR ">
                        <h5>Image</h5>
                    <img src="<?php echo $con->baseUrl("images/consultant/".$company_image);?>" height="100px" width="180px"/>
                    </div>
                    
			</div>
			
		</div>
           
	</div> 
    
    
     


<?php include '../view_layout/footer_view.php'; ?>