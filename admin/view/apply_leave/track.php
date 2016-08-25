<?php
session_start();
//include ('../../config/class.web.config.php');
//$con = new Config();
//Checking if logged in
//if ($con->authenticate() == 1) {
//    $con->redirect("../../login.php");
//}

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

$err = '';
$msg = '';
$erp_no = '';

//if (isset($_POST["Addquotation"])) {
//   extract($_POST);
//   $rbo = $_POST["RBO_id"];
//   $pgm = $_POST["Program_id"];
//   $vendor = $_POST["Vendor_id"];
//}
?>
<?php include '../view_layout/header_view.php'; ?>
<!-- Form -->
<style type="text/css">
    .widget-header-custom { /* fallback */
        height: 35px;
        background-color: #EFEFEF;
        /*      background-repeat: repeat-x; */
        /*      background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#FBFBFB), to(#EFEFEF));
                background: -webkit-linear-gradient(top, #FBFBFB, #EFEFEF);
                background: -moz-linear-gradient(top, #FBFBFB, #EFEFEF);
                background: -ms-linear-gradient(top, #FBFBFB, #EFEFEF);
                background: -o-linear-gradient(top, #FBFBFB, #EFEFEF); */
    }
    .heading-test{
        margin-left: 5px;
        font-family: sans-serif;
    }
</style>
<form class="form-horizontal margin-none" id="validateSubmitForm" method="post" autocomplete="off">
    <!-- Widget -->
    <div class="widget widget-inverse">
        <!-- Widget heading -->
        <div class="widget-header-custom">
            <div style="height: 5px; width: 100%;"></div>
            <h4 class="heading-test">Track Design Layout</h4>
        </div>
        <!-- // Widget heading END -->
        <div class="widget-body">
            <div class="row">
                <?php include("../../layout/msg.php"); ?>
            </div>
             <!-- Row -->
            <div class="row">
                <!-- Column -->
                <div class="col-md-6">
                    <!-- Group -->
                    <div class="form-group">
                        <label class="col-md-4 control-label" for="erp">ERP No: <span style="color: red;"> *</span></label>
                        <div class="col-md-8">
                            <label for="erp"></label>
                            <input class="form-control k-textbox" style="border-radius: 4px; width: 100% !important;height:35px !important" id="erp_no" name="erp_no" type="text" value="<?php echo $erp_no; ?>" />
                        </div>
                    </div>

                    <!-- // Group END -->
                </div>
                <div class="col-md-6">
                    <!-- Form actions -->
                    <div class="form-actions">
                        <input type="submit" name="SearchLayout" value="Search" class="btn btn-success" />
                    </div>
                </div>
            </div>
              <!-- Row end -->
                 
            <!-- Row -->
            <div class="row">
               <div class="col-md-12" align="center">
                    <div style="width: 180px; height:90px; background-color:#82CAFA;align:center;">Recieved  Factory Sheet</div><br />
               </div>
                    <div class="col-md-6">
                    <div style="width: 180px; height:90px; background-color:#82CAFA;align:center;">Approved by customer</div>
                    </div>   <div class="col-md-6">
                    <div style="width: 180px; height:90px; background-color:#82CAFA;align:center;">Return by customer</div><br />
                    </div>
                    <div class="col-md-6">
                    <div style="width: 180px; height:90px; background-color:#82CAFA;align:center;">Redesign Stage</div>
                    </div>   <div class="col-md-6">
                    <div style="width: 180px; height:90px; background-color:#82CAFA;align:center;">Layout Complete</div><br />
                </div>

            </div>
            <!-- // Row END -->
         
            <hr class="separator" />   
        </div>
        <!-- // Widget END -->
</form>
<!-- // Form END -->
<?php include '../view_layout/footer_view.php'; ?>
