<?php
$con=mysql_connect("localhost", "root", "");
mysql_select_db("doc_track",$con);
$arr = array();
$rs = mysql_query("SELECT dept_id as dept_id, dept_name as dept_name FROM department where is_active='true'");
 
while($obj = mysql_fetch_object($rs)) {
	$arr[] = $obj;
}

header("Content-type: application/json"); 

echo "{\"data\":" .json_encode($arr). "}";
?>