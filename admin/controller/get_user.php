<?php
$con=mysql_connect("localhost", "root", "");
mysql_select_db("doc_track",$con);
$arr = array();
$rs = mysql_query("SELECT user_id as user_id, user_typ as user_typ FROM user_type_in");
 
while($obj = mysql_fetch_object($rs)) {
	$arr[] = $obj;
}

header("Content-type: application/json"); 

echo "{\"data\":" .json_encode($arr). "}";
?>