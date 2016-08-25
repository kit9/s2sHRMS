<?php
include '../config/class.config.php';
$con= new Config();
header("Content-type: application/json");
$verb = $_SERVER["REQUEST_METHOD"];

if ($verb == "GET") {
    $arr = array();
    $rs = mysqli_query($con->open(),"SELECT u.user_role_id, u.role_name, u.role_type, u.user_id, c.user_typ,u.is_active FROM user_role u,user_type_in c WHERE u.user_id=c.user_id");
    
    while($obj = mysqli_fetch_object($rs)) {
        $arr[] = $obj;
    }
    
    echo "{\"data\":" .json_encode($arr). "}";    
}

if ($verb == "POST") {
    $user_role_id = mysql_real_escape_string($_POST["user_role_id"]);
    $role_name = mysql_real_escape_string($_POST["role_name"]);
	$user_id = mysql_real_escape_string($_POST["user_id"]);
	$is_active = mysql_real_escape_string($_POST["is_active"]);
    
    $rs = mysqli_query($con->open(),"UPDATE user_role SET role_name = '" .$role_name ."', user_id = '" .$user_id ."', is_active = '" .$is_active ."' WHERE user_role_id = " .$user_role_id);
	
	if ($rs) {
       echo json_encode($rs);
    }
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Update failed for Wing ID: " .$user_role_id;
    }

}

if ($verb == "PUT") {
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars );   
    
        $role_name = mysql_real_escape_string($request_vars["role_name"]);
	$user_id = mysql_real_escape_string($request_vars["user_id"]);
	$is_active = mysql_real_escape_string($request_vars["is_active"]);
	
    $sql = "insert into user_role(role_name,user_id,is_active) values('$role_name','$user_id','$is_active')";
    $rs = mysqli_query($con->open(),$sql);

    if ($rs) {
		$user_role_id = mysqli_insert_id($con->open());
		echo "".$user_role_id."";
    }
    else {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Insert Failed";
    }
}

if ($verb == "DELETE") {
 
    $request_vars = Array();
    parse_str(file_get_contents('php://input'), $request_vars );
    
    $user_role_id = mysql_real_escape_string($request_vars["user_role_id"]);
    
    $sql= "DELETE FROM wing WHERE user_role_id = '".$user_role_id."'";
   
    $rs = mysqli_query($con->open(),$sql);

    if ($rs) {
        echo "".$user_role_id."";
    }
    else {
       header("HTTP/1.1 500 Internal Server Error");
       echo false;
    }
}
?>
