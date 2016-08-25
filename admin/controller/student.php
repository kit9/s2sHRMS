<?php
include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {  
    $arr = array();
    $arr = $con->SelectAll("tbl_student");
    $count = count($arr);
    if($count>=1)
    {
        echo "{\"data\":" .json_encode($arr). "}";  
    }
    else
    {
        echo "{\"data\":"."[]"."}";  
    }
  
}
if ($verb == "POST") { 
     $std_id = '';
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
     $std_bloodg = '';
     $std_religion = '';
     $std_language = '';
     $country_id = '';
     $city_id = '';
     $std_username= '';
     $std_password= '';
     $std_photo = '';
     $std_sign= '';

     extract($_POST);
     $open = $con->open();
     $errors = array();
     $query1 = "SELECT std_email FROM tbl_student WHERE std_email='$std_email'";
     $resul = mysqli_query($open, $query1);
     
     if(mysqli_num_rows($resul)=='0') {
         
     $query="UPDATE tbl_student SET std_email='$std_email',std_fname='$std_fname',std_lname='$std_lname',std_dob='$std_dob',std_pres_addr='$std_pres_addr',std_per_addr='$std_per_addr',std_mob='$std_mob',std_nationality='$std_nationality',std_gender='$std_gender',std_father='$std_father',std_bloodg='$std_bloodg',std_religion='$std_religion',std_language='$std_language',country_id='$country_id',city_id='$city_id',std_username='$std_username',std_password='$std_password'  WHERE std_id='$std_id'";
     
     $rs= mysqli_query($open, $query);
        if($rs) {
       echo json_encode($rs);
       $con->close($open);
            }
        else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for Student ID: " .$std_id;
            }
    }
 elseif(mysqli_num_rows($resul)=='1') {
     
     $query="UPDATE tbl_student SET std_email='$std_email',std_fname='$std_fname',std_lname='$std_lname',std_dob='$std_dob',std_pres_addr='$std_pres_addr',std_per_addr='$std_per_addr',std_mob='$std_mob',std_nationality='$std_nationality',std_gender='$std_gender',std_father='$std_father',std_bloodg='$std_bloodg',std_religion='$std_religion',std_language='$std_language',country_id='$country_id',city_id='$city_id',std_username='$std_username',std_password='$std_password'  WHERE std_id='$std_id'";   
     $rs= mysqli_query($open, $query);
        if($rs) {
       echo json_encode($rs);
       $con->close($open);
            }
        else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for Student ID: " .$std_id;
            }
    }
 else {
          $errors = array("error"=>"yes", "message"=>"Duplicate Entry!");
          echo json_encode($errors);
    }
}
if ($verb == "PUT") {
    $request_vars = Array();
     parse_str(file_get_contents('php://input'), $request_vars);
     
     $std_email = $request_vars["std_email"];
     $std_fname = $request_vars["std_fname"];
     $std_lname = $request_vars["std_lname"];
     $std_dob = $request_vars["std_dob"];
     $std_pres_addr = $request_vars["std_pres_addr"];
     $std_per_addr = $request_vars["std_per_addr"];
     $std_mob = $request_vars["std_mob"];
     $std_nationality = $request_vars["std_nationality"];
     $std_gender = $request_vars["std_gender"];
     $std_father = $request_vars["std_father"];
     $std_bloodg = $request_vars["std_bloodg"];
     $std_religion = $request_vars["std_religion"];
     $std_language = $request_vars["std_language"];
     $country_id = $request_vars["country_id"];
     $city_id = $request_vars["city_id"];
     $std_username = $request_vars["std_username"];
     $std_password = $request_vars["std_password"];
     $std_photo= $request_vars["std_photo"];
     $std_sign= $request_vars["std_sign"];
     $errors = array();
     $open = $con->open();
 
     $query1 = "SELECT std_email FROM tbl_student WHERE std_email='".mysqli_real_escape_string($open,$std_email)."'";
     $resul = mysqli_query($open, $query1);
     
     if(mysqli_num_rows($resul)=='0') {
         
     $query = "INSERT INTO tbl_student SET ";
     $query .= "std_email='".mysqli_real_escape_string($open,$std_email)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_lname='". mysqli_real_escape_string($open,$std_lname)."',";
     $query .= "std_dob='". mysqli_real_escape_string($open,$std_dob)."',";
     $query .= "std_pres_addr='". mysqli_real_escape_string($open,$std_pres_addr)."',";
     $query .= "std_per_addr='". mysqli_real_escape_string($open,$std_per_addr)."',";
     $query .= "std_mob='". mysqli_real_escape_string($open,$std_mob)."',";
     $query .= "std_nationality='". mysqli_real_escape_string($open,$std_nationality)."',";
     $query .= "std_gender='". mysqli_real_escape_string($open,$std_gender)."',";
     $query .= "std_father='". mysqli_real_escape_string($open,$std_father)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."',";
     $query .= "std_fname='". mysqli_real_escape_string($open,$std_fname)."'";
     
     
     
     $result = mysqli_query($open, $query);
     
      if($result) {
                  $country_id = mysqli_insert_id($con->open());
                  echo "".$country_id."";
                  $con->close($open);
                }
            else {
                header("HTTP/1.1 500 Internal Server Error");
                echo "Insert Failed.";
                }
        }    
        else {
            $errors = array("error"=>"yes", "message"=>"Duplicate Entry!");
            echo json_encode($errors);    
       }     
    //$object_array = array("country_name"=>$country_name,"c_nationality"=>$c_nationality);
    //$insert = $con->insert("tbl_country",$object_array);
//    if($insert == 0) {
//        header("HTTP/1.1 500 Internal Server Error");
//    }  
}
if ($verb == "DELETE") {
 
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars );
    
    $country_id = $request_vars["country_id"];
    
    $open= $con->open();
    
    $query="DELETE FROM tbl_country WHERE country_id='".  mysqli_real_escape_string($open,$country_id)."'";
    
    $rs= mysqli_query($open, $query);
     if ($rs) {
        echo "".$country_id."";
      $con->close($open);
    }
    else {
       header("HTTP/1.1 500 Internal Server Error");
       echo false;
    }
}
?>