<?php
include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {  
    $arr = array();
     $rs = mysqli_query($con->open(), "SELECT * FROM subsection order by subsection_id DESC");
    while ($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
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
     $subsection_id = '';
     $subsection_title = '';
 
    extract($_POST);
     $open = $con->open();
     $errors = array();
    $query1 = "SELECT subsection_title FROM subsection WHERE subsection_title='$subsection_title' AND department_id='$department_id'";
     $resul = mysqli_query($open, $query1);
     
     if(mysqli_num_rows($resul)=='0') {
         
     $query="UPDATE subsection SET subsection_id='$subsection_id' WHERE subsection_id='$subsection_id'";
     
     $rs= mysqli_query($open, $query);
        if($rs) {
       echo json_encode($rs);
       $con->close($open);
            }
        else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for Subsection ID: " .$subsection_id;
            }
    }
 elseif(mysqli_num_rows($resul)=='1') {
     
     $query="UPDATE subsection SET subsection_title='$subsection_title' WHERE subsection_id='$subsection_id'";     
     $rs= mysqli_query($open, $query);
        if($rs) {
       echo json_encode($rs);
       $con->close($open);
            }
        else {
            header("HTTP/1.1 500 Internal Server Error");
            echo "Update failed for Subsection ID: " .$subsection_id;
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
     
     $pco_name = $request_vars["pco_name"];
     $errors = array();
     $open = $con->open();
         $query1 = "SELECT subsection_title FROM subsection WHERE subsection_title='".mysqli_real_escape_string($open,$subsection_title)."' AND department_id='".mysqli_real_escape_string($open,$department_id)."'";
     $resul = mysqli_query($open, $query1);
     
     if(mysqli_num_rows($resul)=='0') {
         
     $query = "INSERT INTO subsection SET ";
     $query .= "subsection_title='".mysqli_real_escape_string($open,$subsection_title)."',";
     $query .= "department_id='". mysqli_real_escape_string($open,$department_id)."'";
     
     
     $result = mysqli_query($open, $query);
     
      if($result) {
                  $subsection_id = mysqli_insert_id($con->open());
                  echo "".$subsection_id."";
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
    
    $subsection_id = $request_vars["subsection_id"];
    
    $open= $con->open();
    
    $query="DELETE FROM subsection WHERE subsection_id='".  mysqli_real_escape_string($open,$subsection_id)."'";
    
    $rs= mysqli_query($open, $query);
     if ($rs) {
        echo "".$subsection_id."";
      $con->close($open);
    }
    else {
       header("HTTP/1.1 500 Internal Server Error");
       echo false;
    }
}
?>