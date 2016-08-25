<?php
include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(),"SELECT  wing.wing_id as wing_id, wing.wing_name as wing_name, wing.dept_id as dept_id, department.dept_name as dept_name, wing.is_active as is_active FROM wing LEFT OUTER JOIN department ON wing.dept_id = department.dept_id ORDER BY wing.wing_id DESC");
    
    while($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    
    echo "{\"data\":" .json_encode($arr). "}";    
}

if ($verb == "POST") {
    $wing_id = mysql_real_escape_string($_POST["wing_id"]);
    $wing_name = mysql_real_escape_string($_POST["wing_name"]);
	$dept_id = mysql_real_escape_string($_POST["dept_id"]);
	$is_active = mysql_real_escape_string($_POST["is_active"]);
    
    $rs = mysqli_query($con->open(),"UPDATE wing SET wing_name = '" .$wing_name ."', dept_id = '" .$dept_id ."', is_active = '" .$is_active ."' WHERE wing_id = " .$wing_id);
	
	if ($rs) {
       echo json_encode($rs);
    }
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Wing ID: " .$wing_id;
    }

}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars );   
    
    $wing_name = mysql_real_escape_string($request_vars["wing_name"]);
	$dept_id = mysql_real_escape_string($request_vars["dept_id"]);
	$is_active = mysql_real_escape_string($request_vars["is_active"]);
	
    $sql = "insert into wing(wing_name,dept_id,is_active) values('$wing_name','$dept_id','$is_active')";
    
    $rs = mysqli_query($con->open(),$sql);

    if ($rs) {
		$wing_id = mysqli_insert_id($con->open());
		echo "".$wing_id."";
    }
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {
 
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars );
    
    $wing_id = mysql_real_escape_string($request_vars["wing_id"]);
    
    $sql= "DELETE FROM wing WHERE wing_id = '".$wing_id."'";
   
    $rs = mysqli_query($con->open(),$sql);

    if ($rs) {
        echo "".$wing_id."";
    }
    else {
       header("HTTP/1.1 500 Internal Server Error");
       echo false;
    }
}
?>