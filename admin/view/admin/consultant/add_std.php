<?php
session_start();
include ('../../config/class.config.php');
$con = new Config();
error_reporting(0);
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
$c_email = $_SESSION['c_email'];
$query =array("c_email"=> $c_email);
$result=$con->SelectAllByField("tbl_consultant", $query);
if (count($result) >= 1):
foreach ($result as $n): 
 $a_id = $n->c_id;
endforeach;
endif;


//$con->debug($a_id );
$err = "";
$msg = '';
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
$mother_ocu = '';
$extra_curiculam = '';
$user_id='';
$role_type='';
//$std_edu_info_id = '';


$country_id = '';
$city_id = '';
$countries = $con->SelectAll("tbl_country");
$cities = $con->SelectAll("tbl_city");

if (isset($_POST['add_con'])) {
    extract($_POST);
    $Fcount = count($_FILES);
//    $con->debug($_POST);
   
    $fdate = date_create($std_dob);
    $date = date_format($fdate, 'Y-m-d');
    
    $targetfolder = '../../uploads/student/image/';
    $filename = basename($_FILES['std_image']['name']);
    $targetfolder = $targetfolder . $filename;
//    $uploadPath = substr($targetfolder, 6);
    
   $i_name = $_FILES["std_image"]["name"];


    if (empty($std_fname)) {
        $err = "First name is not selected";
    } else if (empty($std_lname)) {
        $err = "Last name is empty";
    } else if (empty($std_dob)) {
        $err = "Date of birth field is empty";
    } else if (empty($std_pres_addr)) {
        $err = "Present Address field is empty";
    } else if (empty($std_per_addr)) {
        $err = "Permanent Address is empty";
    } else if (empty($std_email)) {
        $err = "Email Address is empty";
    } else if (empty($std_mob)) {
        $err = "Phone number is empty";
    } else if (empty($std_nationality)) {
        $err = "Student Nationality field is empty";
    } else if (empty($std_gender)) {
        $err = "Gender field is empty";
    } else if (empty($std_father)) {
        $err = "Father name field is empty";
    } else if (empty($std_mother)) {
        $err = "Mother name field is empty";
    } else if (empty($std_bloodg)) {
        $err = "Blood group field is empty";
    } else if (empty($std_religion)) {
        $err = "Religion field is empty";
    } else if (empty($std_language)) {
        $err = "Religion field is empty";
    } else if (empty($city_id)) {
        $err = "City field is empty";
    } else if (empty($country_id)) {
        $err = "Country field is empty";
    } else if (empty($std_username)) {
        
        $err = "Username field is empty";
    } else if (empty($std_password)) {
        $err = "Password field is empty";
    } else if (empty($father_ocu)) {
        $err = "Father occupation field is empty";
    } else if (empty($mother_ocu)) {
        $err = "Mother occupation field is empty";
    } else if (empty($extra_curiculam)) {
        $err = "Extra Curriculam field is empty";
    } else if ($Fcount == 0)  {
        $err = "Please Upload the Student Document";
    } else {
        if ($con->exists("tbl_student", array("std_email" => $std_email)) == 1) {
            $err = "Email address already exists.";
        } else {

            $open = $con->open();
        
            $query = "INSERT INTO tbl_student SET ";
            $query .= " std_fname='" . mysqli_real_escape_string($open, $std_fname) . "',";
            $query .= " std_lname='" . mysqli_real_escape_string($open, $std_lname) . "', ";
            $query .= " std_dob='" . mysqli_real_escape_string($open, $date) . "', ";
            $query .= " std_pres_addr='" . mysqli_real_escape_string($open, $std_pres_addr) . "',";
            $query .= " std_per_addr='" . mysqli_real_escape_string($open, $std_per_addr) . "',";
            $query .= "std_email='" . mysqli_real_escape_string($open, $std_email) . "', ";
            $query .= "std_mob='" . mysqli_real_escape_string($open, $std_mob) . "',";
            $query .= "std_nationality='" . mysqli_real_escape_string($open, $std_nationality) . "',";
            $query .= "std_gender='" . mysqli_real_escape_string($open, $std_gender) . "',";
            $query .= "std_father='" . mysqli_real_escape_string($open, $std_father) . "',";
            $query .= "std_mother='" . mysqli_real_escape_string($open, $std_mother) . "',";
            $query .= "std_bloodg='" . mysqli_real_escape_string($open, $std_bloodg) . "',";
            $query .= " std_religion='" . mysqli_real_escape_string($open, $std_religion) . "',";
            $query .= "std_language='" . mysqli_real_escape_string($open, $std_language) . "', ";
             $query .= "std_image='" . mysqli_real_escape_string($open, $i_name) . "', ";
            $query .= "std_username='" . mysqli_real_escape_string($open, $std_username) . "', ";
            $query .= "std_password='" . mysqli_real_escape_string($open, $std_password) . "',";
            $query .= "father_ocu='" . mysqli_real_escape_string($open, $father_ocu) . "',";
            $query .= " mother_ocu='" . mysqli_real_escape_string($open, $mother_ocu) . "', ";
            $query .= "city_id='" . mysqli_real_escape_string($open, $city_id) . "', ";
            $query .= "role_type='" . mysqli_real_escape_string($open, $role_type) . "', ";
            $query .= "c_id='" . mysqli_real_escape_string($open, $a_id) . "', ";
            $query .= "user_id='" . mysqli_real_escape_string($open, $user_id) . "', ";
            $query .= "country_id='" . mysqli_real_escape_string($open, $country_id) . "', ";
            $query .= "extra_curiculam='" . mysqli_real_escape_string($open, $extra_curiculam) . "' ";
            if ($filename != "") {

                move_uploaded_file($_FILES['std_image']['tmp_name'], $targetfolder);
            }
            $res = $con->QueryResultForNormalEntry($query, $open);
            if ($res == 1) {
                $student_id = mysqli_insert_id($open);
                $msg .= "Student Info AND ";
                $StdPhoto = $_FILES["std_photo"];

                //------------upload Multiple Document file and saved to Database -----------------//
               // $con->debug($count = count($_FILES["std_photo"]["name"]));

                for ($i = 0; $i <= 3; $i++) {
                    $targetfolder = '../../uploads/student/';
                    $filename = "uin_image_".$student_id."_". rand(1, 99999)."_". basename($_FILES['std_photo']['name'][$i]);
                   $targetfolder = $targetfolder . $filename;
                    move_uploaded_file($_FILES['std_photo']['tmp_name'][$i], $targetfolder);
                    $document_upload_query = "INSERT INTO student_document SET ";
                    $document_upload_query .= " std_id='".  mysqli_real_escape_string($open,$student_id)."',";
                    $document_upload_query .= " doc_name='".  mysqli_real_escape_string($open,$filename)."'";
                    $result = $con->QueryResultForNormalEntry($document_upload_query, $open);
                    if($result)
                    {
                        $msg = "Student Info Saved Successfully";
                    }
                    //$newfilename = rand(1,99999).end(explode(".",$_FILES["std_photo"]["name"]));
                    //echo "uploaded";
                }
            }
            $con->close($open);
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>



<div id="example" class="k-content">
    <div class="widget">
        <div class="widget-head"><h3 class="heading">Student Information</h3>
            
            </div>
       
        </div>
        <div class="widget-body">
            
            <div id="forecast">
                <form method= "post" enctype="multipart/form-data">
                    <div id="tabstrip">
                        <ul>
                            <li class="k-state-active">
                                Personal Information
                            </li>

                            <li>
                                User Information
                            </li>
                        </ul>
                        <div>

                            <div class="weather">
                                <div style="margin-top: 18px;"class="row">
                                    <div class="col-xs-6">
                                       <label for="inputTitle">First Name:</label>
                                      <input type="hidden" value="3" name="user_id"/>
                                        <input type="text" name="std_fname" id="text_element" placeholder="First Name" value="<?php echo $std_fname; ?>" class="form-control" />
                                    </div>   
<!--                                    <div class="col-xs-6">
                                        <input type="text" name="std_fname" id="text_element" placeholder="First Name" value="<?php// echo $std_fname; ?>" class="form-control" />
                                    </div>-->
                                    <div class="col-xs-6">
                                       <label for="inputTitle">Last Name:</label>
                                        <input type="text" name="std_lname" id="text_element" placeholder="Last Name" value="<?php echo $std_lname; ?>" class="form-control" />
                                    </div>
                                </div>   

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-6">
                                      <label for="inputTitle">Father Name:</label>
                                        <input type="text" name="std_father" id="text_element" placeholder="Father Name" value="<?php echo $std_father; ?>" class="form-control" />
                                    </div>
                                    <div class="col-xs-6">
                                       <label for="inputTitle">Mother Name:</label>
                                        <input type="text" name="std_mother" id="text_element" placeholder="Mother Name" value="<?php echo $std_mother; ?>" class="form-control" />
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-6">
                                      <label for="inputTitle">Father Profession:</label>
                                        <textarea style="color:#C4C4C4" type="text" id="text_element" name="father_ocu" value="<?php echo $father_ocu; ?>" placeholder="Father Ocupation" class="form-control"rows="1" cols="20"></textarea>
                                    </div>
                                    <div class="col-xs-6">
                                      <label for="inputTitle">Mother Profession:</label>
                                        <textarea style="color:#C4C4C4" type="text" id="text_element" name="mother_ocu" value="<?php echo $mother_ocu; ?>" placeholder="Mother Ocupation" class="form-control"rows="1" cols="20"></textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-6">
                                       <label for="inputTitle">Present Address:</label>
                                        <textarea style="color:#C4C4C4" type="text" id="text_element" name="std_pres_addr" value="<?php echo $std_pres_addr; ?>" placeholder="Present Address" class="form-control"rows="1" cols="20"></textarea>
                                    </div>
                                    <div class="col-xs-6">
                                  <label for="inputTitle">permanent Address:</label>
                                        <textarea style="color:#C4C4C4" type="text" id="text_element" name="std_per_addr" value="<?php echo $std_per_addr; ?>" placeholder="permanent Address" class="form-control"rows="1" cols="20"></textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
<!--                                    <div class="col-xs-4" id="to-do">
                                        <input name="std_dob" id="datetimepicker" value="" />
                                    </div>-->
                                    
                                    <div class="col-xs-4" >
                                       <label for="inputTitle">Date of Birth:</label>
                                        <input id="datepickerr" name="std_dob" value="<?php echo $std_dob; ?>" />
                                      </div>

                                    <div class="col-xs-4">
                                      <label for="inputTitle">Email:</label>
                                        <input type="text" name="std_email" id="text_element" value="<?php echo $std_email; ?>" placeholder="Email" class="form-control" />
                                    </div>
                                    <div class="col-xs-4">
                                        <label for="inputTitle">Phone:</label>
                                        <input type="text" name="std_mob" id="text_element" value="<?php echo $std_mob; ?>" placeholder="Phone" class="form-control" />
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-4">
                                        <label for="inputTitle">Nationality:</label>
                                        <input type="text" name="std_nationality" id="text_element" value="<?php echo $std_nationality; ?>" placeholder="Nationality" class="form-control" />
                                    </div>

                                    <div class="col-xs-4">
                                     <label for="inputTitle">Gender:</label>
                                        <select style="color:#C4C4C4;" class="form-control" value="<?php echo $std_gender; ?>" id="std_gender" name="std_gender">
                                            <option value="0">SELECT ONE</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    
                                   <div class="col-xs-4">
                                     <label for="inputTitle">Blood Group:</label>
                                        <select style="color:#C4C4C4;" class="form-control" value="<?php echo $std_bloodg; ?>" id="std_bloodg" name="std_bloodg">
                                            <option value="0">SELECT ONE</option>
                                            <option value="O−">O−</option>
                                            <option value="O+">O+</option>
                                            <option value="A−">A−</option>
                                            <option value="A+">A+</option>
                                            <option value="B−">B−</option>
                                            <option value="B+">B+</option>
                                            <option value="AB−">AB−</option>
                                            <option value="AB+">AB+</option>
                                        </select>  
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-3">
                                        <label for="inputTitle">Language:</label>
                                        <input type="text" name="std_language" id="text_element" value="<?php echo $std_language; ?>" placeholder="Language" class="form-control" />
                                    </div>
                                   
                                    <div class="col-xs-3">
                                     <label for="inputTitle">Religion:</label>
                                        <select style="color:#C4C4C4;" class="form-control" value="<?php echo $std_religion; ?>" id="std_gender" name="std_religion">
                                            <option value="0">SELECT ONE</option>
                                            <option value="Islam">Islam</option>
                                            <option value="Christian">Christian</option>
                                            <option value="Buddhist">Buddhist</option>
                                            <option value="Hindu">Hindu</option>
                                        </select>
                                    </div>
                                    
                                    
                                    
                                    <div class="col-xs-6">
                                         <label for="inputTitle">Extracuriculam Activities:</label>
                                        <textarea style="color:#C4C4C4; height: 34px;" value="<?php echo $extra_curiculam; ?>" type="text" id="text_element" name="extra_curiculam" placeholder="Extracuriculam Activities" class="form-control"rows="1" cols="20"></textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-4">
                                       <label for="inputTitle">Country Name:</label>
                                        <select style="color:#C4C4C4;" class="form-control" name="country_id">
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

                                    <div class="col-xs-4">
                                        <label for="inputTitle">City Name:</label>
                                        <select style="color:#C4C4C4;" class="form-control" name="city_id">
                                            <option value="0"> City Name</option>
                                                <?php if (count($cities) >= 1): ?>
                                                    <?php foreach ($cities as $c): ?>
                                                    <option value="<?php echo $c->city_id; ?>" 
                                                    <?php
                                                    if ($c->city_id == $city_id) {
                                                        echo "selected='selected'";
                                                    }
                                                    ?>  
                                                            ><?php echo $c->city_name; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select> 

                                    </div>
                                     <div class="col-xs-4">
                                         <label for="inputTitle">Image Upload:</label>
                                    <div class="fileupload fileupload-new margin-none" data-provides="fileupload">
                                    <span class="btn btn-default btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span><input type="file" name="std_image" class="margin-none" /></span>
                                    <span class="fileupload-preview"></span>
  	
                                   </div>
                                </div>
                                </div>
                                <br/>

                            </div>

                            <span class="cloudy">&nbsp;</span>
                        </div>
                        
                        
                        
                        <div>
                            <div class="row innerLR ">
                               <label for="inputTitle">User Name</label>
                                <input type="text" name="std_username" id="text_element" value="<?php echo $std_username; ?>" placeholder="User Name" class="form-control" />
                            </div> 
                            <br/>
                            <div class="row innerLR ">
                               <label for="inputTitle">Password</label>
                                <input type="password" name="std_password" value="<?php echo $std_password; ?>" id="text_element" placeholder="Password" class="form-control" />
                            </div> 
                            <br/>
                            <div class="weather">
                                <div class="row innerLR ">
                                  <label for="inputTitle">Applicant Photo Upload</label>

                                    <input name="std_photo[]" id="photos" type="file" />
<!--                                    <input type="text" name="student_photos" id="student_photos"/>-->
               <!--                <input class="k-button" id="std_photo" type="file" name="std_photo"/>-->
                                </div>


                            </div>
                            <br/>
                            <button class="k-button" name="add_con" type="submit">Save changes</button>

                        </div>

                        <span class="rainy">&nbsp;</span>


                    </div>

            </div>



            </form>

        </div>

        <script>
            $(document).ready(function() {
                $("#tabstrip").kendoTabStrip({
                    animation: {
                        open: {
                            effects: "fadeIn"
                        }
                    }
                });
                $("#grid").kendoGrid({
                    sortable: true
                });

            });
        </script>
<!--        <script>
            $(document).ready(function() {
                // create DateTimePicker from input HTML element
                $("#datetimepicker").kendoDateTimePicker({
                    value: new Date()
                });
            });
        </script>-->
<!--         <script>
                $(document).ready(function() {
                    $("#files").kendoUpload();
                    multiple: true,
                });
            </script>-->

        <style>
            #to-do {
                height: 32px;
                width: 250px;
            }
        </style>



        <script>
            $(document).ready(function() {
                $("#tabstrip").kendoTabStrip({
                    animation: {
                        open: {
                            effects: "fadeIn"
                        }
                    }
                });
            });
        </script>

        <script>
            $(document).ready(function() {
                $("#photos").kendoUpload({
                    //                         async: {
                    //                            saveUrl: "../../controller/savefile.php",
                    ////                            removeUrl: "remove",
                    //                            autoUpload: true
                    //                        },
                    //                         success: function (e) {
                    //                        //console.log(e.response);
                    //                          $("#student_photos").val($("#student_photos").val()+","+e.response.u_image);
                    //                        }
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                // create DatePicker from input HTML element
                $("#datepickerr").kendoDatePicker();

               
            });
        </script>





    </div>

</div>
</div>


<?php include '../view_layout/footer_view.php'; ?>
