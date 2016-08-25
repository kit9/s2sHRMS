<?php
include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {  
    $arr = array();
    $arr = $con->SelectAll("consultant_author");
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
     $con_auth_id = '';
     $con_auth_name = '';
     $con_auth_pass = '';
     $con_auth_user_name = '';
     $con_auth_email= '';
     $con_auth_img= ''; 
     
     
     extract($_POST);
     $open = $con->open();
     $errors = array();
     $query1 = "SELECT con_auth_email FROM consultant_author WHERE con_auth_email='$con_auth_email'";
     $resul = mysqli_query($open, $query1);
     if(mysqli_num_rows($resul)=='0') {
         
     $query="UPDATE consultant_author SET con_auth_email='$con_auth_email',con_auth_name='$con_auth_name',con_auth_user_name='$con_auth_user_name',con_auth_pass='$con_auth_pass',con_auth_img='$con_auth_img' WHERE con_auth_id='$con_auth_id'";
     
     $rs= mysqli_query($open, $query);
        if($rs) {
       echo json_encode($rs);
       $con->close($open);
            }
        else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for consultant_author ID: " .$con_auth_id;
            }
    }
 elseif(mysqli_num_rows($resul)=='1') {
     
     $query="UPDATE consultant_author SET con_auth_email='$con_auth_email',con_auth_name='$con_auth_name',con_auth_user_name='$con_auth_user_name',con_auth_pass='$con_auth_pass',con_auth_img='$con_auth_img' WHERE con_auth_id='$con_auth_id'";     
     $rs= mysqli_query($open, $query);
        if($rs) {
       echo json_encode($rs);
       $con->close($open);
            }
        else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for consultant_author ID: " .$con_auth_id;
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
     
     $con_auth_email = $request_vars["con_auth_email"];
     $con_auth_name = $request_vars["con_auth_name"];
     $con_auth_pass= $request_vars["con_auth_pass"];
     $con_auth_user_name = $request_vars["con_auth_user_name"];
     $con_auth_img = $request_vars["con_auth_img"];
     $errors = array();
     $open = $con->open();
    // ****  TO DO... 24/04/2014  ****
     $query1 = "SELECT con_auth_email FROM consultant_author WHERE con_auth_email='".mysqli_real_escape_string($open,$con_auth_email)."'";
     $resul = mysqli_query($open, $query1);
     
     if(mysqli_num_rows($resul)=='0') {
         
     $query = "INSERT INTO consultant_author SET ";
     $query .= "con_auth_email='".mysqli_real_escape_string($open,$con_auth_email)."',";
     $query .= "con_auth_name='". mysqli_real_escape_string($open,$con_auth_name)."',";
     $query .= "con_auth_pass='". mysqli_real_escape_string($open,$con_auth_pass)."',";
      $query .= "con_auth_user_name='". mysqli_real_escape_string($open,$con_auth_user_name)."',";
     $query .= "con_auth_img='". mysqli_real_escape_string($open,$con_auth_img)."'";
     
     $result = mysqli_query($open, $query);
     
      if($result) {
                  $con_auth_id = mysqli_insert_id($con->open());
                  echo "".$con_auth_id."";
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
    
    $con_auth_id = $request_vars["con_auth_id"];
    
    $open= $con->open();
    
    $query="DELETE FROM consultant_author WHERE con_auth_id='".  mysqli_real_escape_string($open,$con_auth_id)."'";
    
    $rs= mysqli_query($open, $query);
     if ($rs) {
        echo "".$con_auth_id."";
      $con->close($open);
    }
    else {
       header("HTTP/1.1 500 Internal Server Error");
       echo false;
    }
}
?>