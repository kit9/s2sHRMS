<?php
include ('../../config/class.config.php');
$con = new Config();
$err = "";
$msg = '';
//Checking if logged inc
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
$mother_ocu = '';
$extra_curiculam = '';
//$std_edu_info_id = '';


$country_id = '';
$city_id = '';
$countries = $con->SelectAll("tbl_country");
$cities = $con->SelectAll("tbl_city");
if (isset($_GET['id'])) {
$id = $_GET['id'];
    $object_array = array("std_id" => $id);
    $tbl_students = $con->SelectAllByID("tbl_student", $object_array);
     

      foreach ($tbl_students as $n) {
        $std_id = $n->std_id;
        $std_fname=$n->std_fname;
        $std_lname=$n->std_lname;
        $std_dob=$n->std_dob;
        $std_pres_addr=$n->std_pres_addr;
        $std_per_addr=$n->std_per_addr;
        $std_mob=$n->std_mob;  
        $std_email=$n->std_email;
        $std_nationality=$n->std_nationality;  
        $std_gender=$n->std_gender;
        $std_father=$n->std_father;  
        $std_mother=$n->std_mother;
        $std_bloodg=$n->std_bloodg;  
        $std_religion=$n->std_religion;
        $std_language=$n->std_language;  
        $country_id=$n->country_id;
        $city_id=$n->city_id;  
        $std_username=$n->std_username;
        $std_password=$n->std_password;  
        $std_photo=$n->std_photo;  
        $father_ocu=$n->father_ocu;  
        $mother_ocu=$n->mother_ocu;  
        $extra_curiculam=$n->extra_curiculam;  
     }
}
//Formatting the date
     $fdate = date_create($std_dob);
    $date = date_format($fdate, 'Y-m-d');
//    $con->debug($std_dob);
    

//$date = new DateTime('2000-01-01');
//echo $date->format('Y-m-d H:i:s');
    
if (isset($_POST['edit_std'])) {
    extract($_POST);
    //$Fcount = count($_FILES);
   
    

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
    }  else {

        $update_array = array(
            "std_id"=>$std_id,
            "std_fname"=>$std_fname, 
            "std_lname"=>$std_lname, 
            "std_dob"=>$date,
            "std_pres_addr"=>$std_pres_addr,
            "std_per_addr"=>$std_per_addr,
            "std_email"=>$std_email,
            "std_mob"=>$std_mob,
            "std_nationality"=>$std_nationality,
            "std_gender"=>$std_gender,
            "std_father"=>$std_father,
            "std_mother"=>$std_mother,
            "std_bloodg"=>$std_bloodg,
            "std_religion"=>$std_religion,
            "std_language"=>$std_language,
            "std_username"=>$std_username,
            "std_password"=>$std_password,
            "std_image"=>$i_name,
            "father_ocu"=>$father_ocu,
            "mother_ocu"=>$mother_ocu,
             "city_id"=>$city_id,
            "country_id"=>$country_id,
            "extra_curiculam"=>$extra_curiculam);
        if ($con->update("tbl_student", $update_array) == 1) {
             if ($filename != "") {

                move_uploaded_file($_FILES['std_image']['tmp_name'], $targetfolder);
            }
            $msg = "STUDENT Update successfully";
        } else {
            $err = "Invalid Query";
        }
    }
}
?>
<?php include '../view_layout/header_view.php'; ?>
<?php include("../../layout/msg.php"); ?>
<form  method= "post" enctype="multipart/form-data">
    
  <div id="example" class="k-content">
    <div class="widget">
        <div class="widget-head"><h3 class="heading">Student Information</h3></div>
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
                                    
                                    <div class="col-xs-6"><input type="hidden" value="<?php echo $std_id ?>"name="std_id"/>
                                        <input type="text" name="std_fname" id="text_element" placeholder="First Name" value="<?php echo $std_fname; ?>" class="form-control" />
                                    </div>  
<!--                                    <div class="col-xs-6">
                                        <input type="text" name="std_fname" id="text_element" placeholder="First Name" value="<?php// echo $std_fname; ?>" class="form-control" />
                                    </div>-->
                                    <div class="col-xs-6">
                                        <input type="text" name="std_lname" id="text_element" placeholder="Last Name" value="<?php echo $std_lname; ?>" class="form-control" />
                                    </div>
                                </div>   

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-6">
                                        <input type="text" name="std_father" id="text_element" placeholder="Father Name" value="<?php echo $std_father; ?>" class="form-control" />
                                    </div>
                                    <div class="col-xs-6">
                                        <input type="text" name="std_mother" id="text_element" placeholder="Mother Name" value="<?php echo $std_mother; ?>" class="form-control" />
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-6">
                                        <textarea style="color: #C4C4C4;" type="text" id="text_element" name="father_ocu" class="form-control"rows="1" cols="20"><?php echo $father_ocu; ?></textarea>
                                     </div>
                                    <div class="col-xs-6">
                                       
                                         <textarea style="color: #C4C4C4;" type="text" id="text_element" name="mother_ocu" class="form-control"rows="1" cols="20"><?php echo $mother_ocu; ?></textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-6">
                                      <textarea style="color: #C4C4C4;" type="text" id="text_element" name="std_pres_addr" class="form-control"rows="1" cols="20"><?php echo $std_pres_addr; ?></textarea>
                                    </div>
                                    <div class="col-xs-6">
                                      <textarea style="color: #C4C4C4;" type="text" id="text_element" name="std_per_addr" class="form-control"rows="1" cols="20"><?php echo $std_per_addr; ?></textarea>
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
<!--                                    <div class="col-xs-4" id="to-do">
                                        <input name="std_dob" id="datetimepicker" value="" />
                                    </div>-->
                                    
                                    <div class="col-xs-4" >
                                        <input id="datepickerr" name="std_dob" value="<?php echo $std_dob; ?>" />
                                      </div>

                                    <div class="col-xs-4">
                                        <input type="text" name="std_email" id="text_element" value="<?php echo $std_email; ?>" placeholder="Email" class="form-control" />
                                    </div>
                                    <div class="col-xs-4">
                                        <input type="text" name="std_mob" id="text_element" value="<?php echo $std_mob; ?>" placeholder="Phone" class="form-control" />
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-4">
                                        <input type="text" name="std_nationality" id="text_element" value="<?php echo $std_nationality; ?>" placeholder="Nationality" class="form-control" />
                                    </div>

           <div class="col-xs-4">
               <select name="std_gender" class="form-control">
                   <option value="<?php echo $std_gender; ?>"> 
                        <?php
                        if($std_gender == 'Male') {
                            echo "Male";                        
                        }
                        elseif($std_gender == 'Female') {
                            echo "Female";
                        }
                        else{
                            echo "others";
                        }
                        ?>  
                       </option>
                     <option value="Male" >Male</option>
                        <option value="Female" >Female</option>
                        <option value="Others" >Others</option>
                         	
                </select>
            </div>
                                    
<!--                <div class="col-xs-4">
               <select name="std_bloodg" class="form-control">
                  <option value="<?php echo $std_bloodg; ?>"> 
                        <?php
                        if($std_gender == 'O−') {
                            echo "O−";                        
                        }
                        elseif($std_gender == 'O+') {
                            echo "O+";
                        }
                        elseif($std_gender == 'A−') {
                            echo "A−";
                        }
                        elseif($std_gender == 'A+') {
                            echo "A+";
                        }
                        elseif($std_gender == 'B−') {
                            echo "B−";
                        }
                        elseif($std_gender == 'B+') {
                            echo "B+";
                        }
                        elseif($std_gender == 'AB−') {
                            echo "AB−";
                        }
                         elseif($std_gender == 'AB+') {
                            echo "AB+";
                        }
                        ?>  
                       </option>
                     
           
                        <option value="O−" >O−</option>
                        <option value="O+" >O+</option>
                        <option value="A−" >A−</option>
                         <option value="A+" >A+</option>
                         <option value="B−" >B−</option>
                         <option value="B+" >B+</option>
                         <option value="AB−" >AB−</option>
                         <option value="AB+" >AB+</option>
                </select>
            </div>-->
                                    
                                    
                     <div class="col-xs-4">
               <select name="std_bloodg" class="form-control">
                   <option value="<?php echo $std_bloodg; ?>"> 
                        <?php
                        if($std_bloodg == 'O−') {
                            echo "O−";                        
                        }
                        elseif($std_bloodg == 'O+') {
                            echo "O−";
                        }
                        elseif($std_bloodg == 'A−') {
                            echo "A−";
                        }
                        elseif($std_bloodg == 'A+') {
                            echo "A+";
                        }
                         elseif($std_bloodg == 'B−') {
                            echo "B−";
                        }
                         elseif($std_bloodg == 'B+') {
                            echo "B+";
                        }
                         elseif($std_bloodg == 'AB−') {
                            echo "AB−";
                         }
                         elseif($std_bloodg == 'AB+') {
                            echo "AB+";
                         }
                        ?>  
                       </option>
                    <option value="O−" >O−</option>
                        <option value="O+" >O+</option>
                        <option value="A−" >A−</option>
                         <option value="A+" >A+</option>
                         <option value="B−" >B−</option>
                         <option value="B+" >B+</option>
                         <option value="AB−" >AB−</option>
                         <option value="AB+" >AB+</option>
                 </select>
            </div>               
                                    
                                    
                                    
                                </div>
                                    
                                      <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-3">
                                        <input type="text" name="std_language" id="text_element" value="<?php echo $std_language; ?>" placeholder="Language" class="form-control" />
                                    </div>
                <div class="col-xs-3">
               <select name="std_religion" class="form-control">
                   <option value="<?php echo $std_religion; ?>"> 
                        <?php
                        if($std_religion == 'Islam') {
                            echo "Islam";                        
                        }
                        elseif($std_religion == 'Christian') {
                            echo "Christian";
                        }
                        elseif($std_religion == 'Buddhist') {
                            echo "Buddhist";
                        }
                        elseif($std_religion == 'Hindu') {
                            echo "Hindu";
                        }
                        ?>  
                       </option>
                    <option value="Islam" >Islam</option>
                        <option value="Christian" >Christian</option>
                        <option value="Buddhist" >Buddhist</option>
                         <option value="Hindu" >Hindu</option>
                         	
                </select>
            </div>
<!--                                    <div class="col-xs-3">
                                        <input type="text" name="std_religion" id="text_element" value="<?php echo $std_religion; ?>" placeholder="Religion" class="form-control" />
                                    </div>-->
                                    <div class="col-xs-6">
                                         <textarea style="color: #C4C4C4; height: 34px;"  type="text" id="text_element" name="extra_curiculam" class="form-control"rows="1" cols="20"><?php echo $extra_curiculam; ?></textarea>
                                       
                                    </div>
                                </div>

                                <div style="margin-top: 18px;" class="row">
                                    <div class="col-xs-4">
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
                            </br>
                            <div class="row innerLR ">
                                <h5>User Name:</h5>
                                <input type="text" name="std_username" id="text_element" value="<?php echo $std_username; ?>" placeholder="User Name" class="form-control" />
                            </div> 
<!--                            <br/>
                            <div class="row innerLR ">
                                <h5>Password:</h5>
                                <input type="password" name="std_password" value="<?php echo $std_password; ?>" id="text_element" placeholder="Password" class="form-control" />
                            </div> 
                            <br/>-->
<!--                            <div class="weather">
                                <div class="row innerLR ">
                                    <h5>Applicant Photo Upload</h5>

                                    <input name="std_photo[]" id="photos" type="file" />
                                    <input type="text" name="student_photos" id="student_photos"/>
                               <input class="k-button" id="std_photo" type="file" name="std_photo"/>
                                </div>


                            </div>-->
                            <br/>
                            <button style="margin-left: 5px;" class="k-button" name="edit_std" type="submit">Save changes</button>

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


