<?php
$con=mysql_connect("localhost", "root", "");
mysql_select_db("doc_track",$con);
$arr = array();
$rs = mysql_query("SELECT city_id as city_id, city_name as city_name FROM tbl_city");
 
while($obj = mysql_fetch_object($rs)) {
	$arr[] = $obj;
}

header("Content-type: application/json"); 

echo "{\"data\":" .json_encode($arr). "}";
?>
