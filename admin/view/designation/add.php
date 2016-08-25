<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();
$err = "";
$msg = '';

$open = $con->open();

//Checking if logged in
if ($con->authenticate() == 1) {
    $con->redirect("../../login.php");
}

//Logging out user
if (isset($_POST['btnLogout'])) {
    if ($con->logout() == 1) {
        $con->redirect("../../login.php");
    }
}

$degss = $con->SelectAll("designation");
$departments = $con->SelectAll("department");
$subsections = $con->SelectAll("subsection");

if (isset($_POST['next'])) {
    extract($_POST);
//   $con->debug($_POST); 
if (empty($designation_title)) {
        $err = "University name is not selected";
    } else if (empty($subsection_id)) {
        $err = "Course level is empty";
    } else if (empty($department_id)) {
        $err = "Website field is empty";
    } else if (empty($designation_title)) {
        $err = "Campus field is empty";
    } else if (empty($status)) {
        $err = "Course name field is empty";
    } else {   
   
//$con->debug($_SESSION["form1"]);
$con->redirect("addtwo.php");
   
    

      $con->close($open);  
}
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>

<div style="margin-bottom: -1px;"class="widget widget-inverse">
    <div style="background-color: #ccc;height: 30px; padding-top: 6px;">
        <span style="color: white; margin-left: 10px;"><i class="icon-documents fa fa-1x"></i>&nbsp; Add Application </span>
</div>
</div>
<div class="widget widget-tabs widget-tabs-gray widget-tabs-double-2 border-bottom-none">

    <!-- Widget heading -->
    <div class="widget-head">
        <ul>
            <li class="active"><a class="glyphicons" href="#overview" data-toggle="tab"><i></i>University Info</a></li>
<!--			<li><a class="glyphicons edit" href="#edit-account" data-toggle="tab"><i></i>Consultant Info</a></li>-->
<!--            <li><a class="glyphicons luggage" href="#projects" data-toggle="tab"><i></i> Others</a></li>-->
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
                                            <div class="col-md-16">
                                                
                                                 
                                                
                                                
                                                <!-- Group -->
                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Select Institutions:<span style="color: red;">*</span></label>
                                                   <div style="border-color: #799D37;" class="row innerLR">
                                                    <div class="col-md-5">
                                                        <label for="categories"></label><input id="categories" name="department_title" style="width: 276px; height: 26px;" value="<?php echo $department_title; ?>" />
                                                    </div>
                                                </div>
                                                </div>
                                                  <!-- // Group END -->


                                                <div class="form-group">
                                                    <label class="col-md-4 control-label">Sub-Section:<span style="color: red;">*</span></label>
                                                    <div class="col-md-8">
                                                      <label for="products"></label><input id="products" name="subsection_title" disabled="disabled" value="<?php echo $subsection_title; ?>" style="width: 276px; height: 26px;"/>
                                                    </div>
                                                </div>
                                                
                                                 <!-- // Column END -->
                                             
                                                
                                        </div>
                                        <!-- // Row END -->

                                       
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


<style scoped>
            .demo-section {
                width: 460px;
                padding: 30px;
            }
            .demo-section h2 {
                text-transform: uppercase;
                font-size: 1.2em;
                margin-bottom: 30px;
            }
            .demo-section label {
                display: inline-block;
                width: 120px;
                padding-right: 5px;
                text-align: right;
            }
            .demo-section .k-button {
                margin: 20px 0 0 125px;
            }
            .k-readonly
            {
                color: gray;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function() {
                var categories = $("#categories").kendoComboBox({
                    placeholder: "Select category...",
                    dataTextField: "department_title",
                    dataValueField: "department_id",
                    dataSource: {
//                            type: "json",
//                            data: categoriesData

                        transport: {
                            read: {
                                url: "../../controller/drpartment.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox");

                var products = $("#products").kendoComboBox({
                    autoBind: false,
                    cascadeFrom: "categories",
                    placeholder: "Select Sub-section..",
                    dataTextField: "subsection_title",
                    dataValueField: "subsection_id",
                    dataSource: {
//                        type: "json",
//                        data: productsData
                        transport: {
                            read: {
                                url: "../../controller/sub_section.php",
                                type: "GET"
                            }
                        },
                        schema: {
                            data: "data"
                        }
                    }
                }).data("kendoComboBox");
                
              });
        </script>



<script>
    $(document).ready(function() {
        $("#files").kendoUpload();
    });
</script>


<?php include '../view_layout/footer_view.php'; ?>