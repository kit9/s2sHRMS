<?php
include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    
    $arr = array();
  
    $arr = $con->SelectAll("tbl_city");
    
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
     $city_id = '';
     $city_name = '';
     
     extract($_POST);
     $open = $con->open();
     $query="UPDATE tbl_city SET city_name='$city_name' WHERE city_id='$city_id'";
     
     $rs= mysqli_query($open, $query);
    if ($rs) {
       echo json_encode($rs);
       $con->close($open);
    }
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Wing ID: " .$wing_id;
    }
    
}
if ($verb == "PUT") {
    $request_vars = Array();
     parse_str(file_get_contents('php://input'), $request_vars);
     
     $city_name = $request_vars["city_name"];
    
     
     $open = $con->open();
     $query = "INSERT INTO tbl_city SET ";
     $query .= "city_name='".mysqli_real_escape_string($open,$city_name)."'";
    
      $result = mysqli_query($open, $query);
     
      if ($result) {
		$city_id = mysqli_insert_id($con->open());
		echo "".$city_id."";
              $con->close($open);
      }
      else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
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
    
    $city_id = $request_vars["city_id"];
    
    $open= $con->open();
    
    $query="DELETE FROM tbl_city WHERE city_id='".  mysqli_real_escape_string($open,$city_id)."'";
    
    $rs= mysqli_query($open, $query);
     if ($rs) {
        echo "".$city_id."";
      $con->close($open);
    }
    else {
       header("HTTP/1.1 500 Internal Server Error");
       echo false;
    }
}
?>